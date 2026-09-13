<?php
// api/xui.php - 3x-ui / X-UI Panel Integration for EKROM Shop

function xui_request($server, $path, $method = 'GET', $data = null) {
    if (empty($server['panel_url'])) {
        return ['code' => 0, 'data' => null, 'error' => 'No panel URL configured'];
    }

    $panelUrl = rtrim(trim($server['panel_url']), '/');
    $token = trim($server['password'] ?? '');
    $url = $panelUrl . $path;

    $ch = curl_init($url);
    $headers = [
        'User-Agent: EkromShop/1.0',
        'Accept: application/json'
    ];

    if ($token !== '') {
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    if ($data !== null) {
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if ($response === false || $httpCode === 0) {
        return ['code' => $httpCode, 'data' => null, 'error' => $curlErr ?: 'Connection failed'];
    }

    $parsed = json_decode($response, true);
    return ['code' => $httpCode, 'data' => $parsed, 'raw' => $response, 'error' => null];
}

function xui_make_client_email($displayName, $defaultPrefix = 'VPN') {
    $clean = trim((string)$displayName);
    if ($clean === '') {
        $clean = $defaultPrefix;
    }
    // In Xray-core / 3x-ui, client email validator forbids characters <= ' ' (ASCII space/control) and '/'
    // We convert '/' to Fraction Slash U+2044 (⁄) which renders identical to '/'
    // and whitespace to Non-Breaking Space U+00A0 ( ) which renders identical to ' '
    $clean = str_replace('/', "\u{2044}", $clean);
    $clean = preg_replace('/\s+/u', "\u{00A0}", $clean);
    return $clean;
}

function xui_add_client($server, $uuid, $email, $expiryTimeStr, $displayName = '') {
    if (empty($server['panel_url']) || empty($server['password'])) {
        return ['success' => false, 'message' => 'เซิร์ฟเวอร์ยังไม่ได้ตั้งค่า Panel URL หรือ API Token'];
    }

    $inboundId = (int)($server['inbound_id'] ?: 1);
    $expiryMs = strtotime($expiryTimeStr) * 1000;

    // Use displayName if available to format the 3x-ui client email/remark like the web shop
    $baseName = !empty($displayName) ? $displayName : $email;
    $targetEmail = xui_make_client_email($baseName);
    $finalEmail = $targetEmail;

    $attempt = 1;
    $maxAttempts = 10;
    $res = null;

    while ($attempt <= $maxAttempts) {
        $payload = [
            'client' => [
                'email' => $finalEmail,
                'id' => $uuid,
                'totalGB' => 0,
                'expiryTime' => $expiryMs,
                'tgId' => 0,
                'limitIp' => 0,
                'enable' => true
            ],
            'inboundIds' => [$inboundId]
        ];

        $res = xui_request($server, '/panel/api/clients/add', 'POST', $payload);
        if ($res['code'] === 200 && !empty($res['data']['success'])) {
            break;
        }

        $msg = $res['data']['msg'] ?? $res['error'] ?? '';
        if (stripos($msg, 'already in use') !== false && $attempt < $maxAttempts) {
            $attempt++;
            $finalEmail = $targetEmail . "\u{00A0}#" . $attempt;
            continue;
        }

        break;
    }

    if ($res['code'] !== 200 || empty($res['data']['success'])) {
        $msg = $res['data']['msg'] ?? $res['error'] ?? ('HTTP ' . $res['code']);
        return ['success' => false, 'message' => 'เพิ่ม Client บน 3x-ui ไม่สำเร็จ: ' . $msg];
    }

    // Fetch config link from 3x-ui
    $linkRes = xui_request($server, '/panel/api/clients/links/' . rawurlencode($finalEmail), 'GET');
    $configLink = '';
    if (!empty($linkRes['data']['success']) && !empty($linkRes['data']['obj'][0])) {
        $rawLink = $linkRes['data']['obj'][0];
        $configLink = xui_format_config_link($rawLink, $displayName ?: $server['name'], $server);
    }

    if (empty($configLink)) {
        $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '127.0.0.1');
        $port = (int)($server['port'] ?: 80);
        $protocol = $server['protocol'] ?: 'vmess';
        $remark = $displayName ?: $server['name'];

        if ($protocol === 'vmess') {
            $bug = !empty($server['bug_host']) ? trim($server['bug_host']) : $targetAddress;
            $vmessObj = [
                'v' => '2',
                'ps' => $remark,
                'add' => $targetAddress,
                'port' => $port,
                'id' => $uuid,
                'aid' => 0,
                'scy' => 'auto',
                'net' => 'ws',
                'type' => 'none',
                'host' => $bug,
                'path' => '/',
                'tls' => 'none'
            ];
            $configLink = 'vmess://' . base64_encode(json_encode($vmessObj, JSON_UNESCAPED_UNICODE));
        } else {
            $sni = !empty($server['bug_host']) ? trim($server['bug_host']) : 'speedtest.net';
            $vPort = ($protocol === 'vless' && !empty($server['vless_port'])) ? (int)$server['vless_port'] : $port;
            $pbkParam = !empty($server['pbk']) ? '&pbk=' . urlencode($server['pbk']) : '';
            $sidParam = !empty($server['sids']) ? '&sid=' . urlencode(explode(',', $server['sids'])[0]) : '';
            $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$vPort}?encryption=none&security=reality&sni={$sni}&fp=chrome&type=grpc&serviceName=grpc{$pbkParam}{$sidParam}#" . rawurlencode($remark);
        }
    }

    return [
        'success' => true,
        'email' => $finalEmail,
        'config_link' => $configLink
    ];
}

function xui_format_config_link($rawLink, $displayName, $server = []) {
    $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '');
    $targetPort = 0;
    if (!empty($server['port']) && (int)$server['port'] > 0) {
        $targetPort = (int)$server['port'];
    }
    $bugHost = !empty($server['bug_host']) ? trim($server['bug_host']) : '';

    // If VMess (vmess://<base64>)
    if (strpos($rawLink, 'vmess://') === 0) {
        $b64 = substr($rawLink, 8);
        $json = json_decode(base64_decode($b64), true);
        if (is_array($json)) {
            $json['ps'] = $displayName;
            if (!empty($targetAddress)) {
                $json['add'] = $targetAddress;
            }
            if ($targetPort > 0) {
                $json['port'] = $targetPort;
            }
            if (!empty($bugHost)) {
                $json['host'] = $bugHost;
                if (!empty($json['tls']) && $json['tls'] !== 'none') {
                    $json['sni'] = $bugHost;
                }
            }
            return 'vmess://' . base64_encode(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }

    // If VLESS or Trojan (vless://... or trojan://...)
    if (strpos($rawLink, 'vless://') === 0 || strpos($rawLink, 'trojan://') === 0) {
        $parsed = parse_url($rawLink);
        if ($parsed && !empty($parsed['scheme']) && !empty($parsed['user'])) {
            $scheme = $parsed['scheme'];
            $user = $parsed['user'];
            $host = !empty($targetAddress) ? $targetAddress : ($parsed['host'] ?? '');
            
            $port = $parsed['port'] ?? '';
            if ($scheme === 'vless' && !empty($server['vless_port']) && (int)$server['vless_port'] > 0) {
                $port = (int)$server['vless_port'];
            } elseif ($targetPort > 0) {
                $port = $targetPort;
            }

            $queryParams = [];
            if (!empty($parsed['query'])) {
                parse_str($parsed['query'], $queryParams);
            }

            if (!empty($bugHost)) {
                if (isset($queryParams['sni'])) $queryParams['sni'] = $bugHost;
                if (isset($queryParams['host'])) $queryParams['host'] = $bugHost;
            }
            if (!empty($server['pbk'])) {
                $queryParams['pbk'] = trim($server['pbk']);
            }
            if (!empty($server['sids'])) {
                $firstSid = trim(explode(',', $server['sids'])[0]);
                if ($firstSid !== '') $queryParams['sid'] = $firstSid;
            }

            $queryParts = [];
            foreach ($queryParams as $k => $v) {
                $queryParts[] = urlencode($k) . '=' . urlencode($v);
            }
            $newQuery = !empty($queryParts) ? '?' . implode('&', $queryParts) : '';

            return "{$scheme}://{$user}@{$host}:{$port}{$newQuery}#" . rawurlencode($displayName);
        }

        $parts = explode('#', $rawLink, 2);
        return $parts[0] . '#' . rawurlencode($displayName);
    }

    // If Shadowsocks (ss://<base64>#remark or ss://<user>@<host>:<port>#remark)
    if (strpos($rawLink, 'ss://') === 0) {
        $parts = explode('#', $rawLink, 2);
        $core = substr($parts[0], 5);
        if (strpos($core, '@') !== false) {
            $atParts = explode('@', $core, 2);
            $auth = $atParts[0];
            $hostPort = explode(':', $atParts[1]);
            $host = !empty($targetAddress) ? $targetAddress : $hostPort[0];
            $port = $targetPort > 0 ? $targetPort : ($hostPort[1] ?? '443');
            return 'ss://' . $auth . '@' . $host . ':' . $port . '#' . rawurlencode($displayName);
        } else {
            $decoded = base64_decode($core);
            if ($decoded && strpos($decoded, '@') !== false) {
                $atParts = explode('@', $decoded, 2);
                $auth = $atParts[0];
                $hostPort = explode(':', $atParts[1]);
                $host = !empty($targetAddress) ? $targetAddress : $hostPort[0];
                $port = $targetPort > 0 ? $targetPort : ($hostPort[1] ?? '443');
                return 'ss://' . base64_encode($auth . '@' . $host . ':' . $port) . '#' . rawurlencode($displayName);
            }
        }
        return $parts[0] . '#' . rawurlencode($displayName);
    }

    return $rawLink;
}

function xui_delete_client($server, $email) {
    if (empty($server['panel_url']) || empty($server['password']) || empty($email)) {
        return false;
    }
    $res = xui_request($server, '/panel/api/clients/del/' . rawurlencode($email), 'POST', new stdClass());
    return (!empty($res['data']['success']));
}

function xui_update_client($server, $uuid, $email, $expiryTimeStr, $newDisplayName = null) {
    if (empty($server['panel_url']) || empty($server['password']) || empty($email)) {
        return false;
    }
    $expiryMs = strtotime($expiryTimeStr) * 1000;
    $targetEmail = !empty($newDisplayName) ? xui_make_client_email($newDisplayName) : $email;

    $payload = [
        'id' => $uuid,
        'email' => $targetEmail,
        'expiryTime' => $expiryMs,
        'enable' => true
    ];
    $res = xui_request($server, '/panel/api/clients/update/' . rawurlencode($email), 'POST', $payload);

    // If update with new email failed (e.g. duplicate name), fallback to updating expiry with existing email
    if ((empty($res['data']['success']) || $res['code'] !== 200) && $targetEmail !== $email) {
        $payload['email'] = $email;
        $res = xui_request($server, '/panel/api/clients/update/' . rawurlencode($email), 'POST', $payload);
        if (!empty($res['data']['success'])) {
            return ['success' => true, 'email' => $email];
        }
        return false;
    }

    if (!empty($res['data']['success'])) {
        return ['success' => true, 'email' => $targetEmail];
    }
    return false;
}

function xui_get_client_traffic($server, $email) {
    if (empty($server['panel_url']) || empty($server['password']) || empty($email)) {
        return null;
    }
    $res = xui_request($server, '/panel/api/clients/traffic/' . rawurlencode($email), 'GET');
    if (!empty($res['data']['success']) && isset($res['data']['obj'])) {
        $obj = $res['data']['obj'];
        return [
            'up' => (int)($obj['up'] ?? 0),
            'down' => (int)($obj['down'] ?? 0),
            'expiry_time' => isset($obj['expiryTime']) ? (int)$obj['expiryTime'] : null,
            'enable' => !empty($obj['enable'])
        ];
    }
    return null;
}

function xui_test_server($server) {
    if (empty($server['panel_url'])) {
        return ['success' => false, 'message' => 'ยังไม่ได้กรอก Panel URL'];
    }
    $startTime = microtime(true);
    $res = xui_request($server, '/panel/api/inbounds/list', 'GET');
    $latencyMs = (int)(round((microtime(true) - $startTime) * 1000));

    if ($res['code'] === 200 && !empty($res['data']['success'])) {
        $inbounds = is_array($res['data']['obj']) ? $res['data']['obj'] : [];
        $count = count($inbounds);
        return [
            'success' => true,
            'latency_ms' => $latencyMs,
            'message' => "เชื่อมต่อ 3x-ui สำเร็จ! พบ {$count} Inbounds (Ping {$latencyMs}ms)"
        ];
    }

    $err = $res['data']['msg'] ?? $res['error'] ?? ('HTTP ' . $res['code']);
    return [
        'success' => false,
        'latency_ms' => $latencyMs,
        'message' => "เชื่อมต่อไม่สำเร็จ: {$err}"
    ];
}
