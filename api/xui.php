<?php
// api/xui.php - 3x-ui / X-UI Panel Integration for EKROM Shop
// Supports both Legacy 3x-ui (Session/Cookie + inbounds API) and New 3x-ui (Bearer API Token)

if (!defined('XUI_DEFAULT_LIMIT_IP')) {
    define('XUI_DEFAULT_LIMIT_IP', 2); // จำกัด 2 เครื่อง (2 IPs)
}
if (!defined('XUI_DEFAULT_TOTAL_BYTES')) {
    define('XUI_DEFAULT_TOTAL_BYTES', 214748364800); // จำกัด 200 GB (200 * 1024 * 1024 * 1024 bytes)
}

function xui_is_legacy($server) {
    if (isset($server['connection_mode'])) {
        $mode = strtolower(trim((string)$server['connection_mode']));
        if ($mode === 'legacy') return true;
        if ($mode === 'api') return false;
    }
    return !empty($server['username']);
}

function xui_get_cache_key($server) {
    $url = trim($server['panel_url'] ?? '');
    $user = trim($server['username'] ?? '');
    return md5($url . '|' . $user);
}

function xui_login($server) {
    if (empty($server['panel_url']) || empty($server['username']) || empty($server['password'])) {
        return ['success' => false, 'error' => 'เซิร์ฟเวอร์ยังไม่ได้ตั้งค่า Panel URL, Username หรือ Password'];
    }

    $panelUrl = rtrim(trim($server['panel_url']), '/');
    if (substr($panelUrl, -6) === '/panel') {
        $panelUrl = substr($panelUrl, 0, -6);
    }
    $loginUrl = $panelUrl . '/login';

    $username = trim($server['username']);
    $password = trim($server['password']);
    $payload = ['username' => $username, 'password' => $password];

    // 1. Try JSON login first
    $ch = curl_init($loginUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: EkromShop/1.0'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    if ($response === false || $httpCode === 0) {
        return ['success' => false, 'error' => $curlErr ?: 'เชื่อมต่อไปยังเซิร์ฟเวอร์ 3x-ui ไม่สำเร็จ'];
    }

    $headerStr = substr($response, 0, $headerSize);
    $bodyStr = substr($response, $headerSize);
    $json = json_decode($bodyStr, true);

    // 2. Fallback: If JSON login failed, try form-urlencoded
    if (empty($json['success'])) {
        $ch = curl_init($loginUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
                'User-Agent: EkromShop/1.0'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5
        ]);
        $response = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        if ($response !== false) {
            $headerStr = substr($response, 0, $headerSize);
            $bodyStr = substr($response, $headerSize);
            $json = json_decode($bodyStr, true);
        }
    }

    if (empty($json['success'])) {
        $msg = $json['msg'] ?? 'เข้าสู่ระบบ 3x-ui ไม่สำเร็จ (กรุณาตรวจสอบ Username / Password)';
        return ['success' => false, 'error' => $msg];
    }

    // Extract Set-Cookie
    preg_match_all('/^Set-Cookie:\s*([^;]+)/mi', $headerStr, $matches);
    $cookieStr = !empty($matches[1]) ? implode('; ', $matches[1]) : '';

    if (empty($cookieStr)) {
        return ['success' => false, 'error' => 'ไม่พบคุกกี้เซสชันหลังเข้าสู่ระบบ 3x-ui'];
    }

    $cacheFile = sys_get_temp_dir() . '/xui_sess_' . xui_get_cache_key($server) . '.txt';
    @file_put_contents($cacheFile, json_encode([
        'time' => time(),
        'cookie' => $cookieStr
    ]));

    return ['success' => true, 'cookie' => $cookieStr];
}

function xui_get_cookie($server, $forceRefresh = false) {
    $cacheFile = sys_get_temp_dir() . '/xui_sess_' . xui_get_cache_key($server) . '.txt';
    if (!$forceRefresh && file_exists($cacheFile)) {
        $cached = json_decode(@file_get_contents($cacheFile), true);
        if (!empty($cached['cookie']) && !empty($cached['time']) && (time() - $cached['time'] < 10800)) {
            return ['success' => true, 'cookie' => $cached['cookie']];
        }
    }
    return xui_login($server);
}

function xui_request($server, $path, $method = 'GET', $data = null, $isRetry = false) {
    if (empty($server['panel_url'])) {
        return ['code' => 0, 'data' => null, 'error' => 'No panel URL configured'];
    }

    $panelUrl = rtrim(trim($server['panel_url']), '/');
    if (substr($panelUrl, -6) === '/panel' && substr($path, 0, 6) === '/panel') {
        $panelUrl = substr($panelUrl, 0, -6);
    }
    $url = $panelUrl . $path;

    $isLegacy = xui_is_legacy($server);
    $headers = [
        'User-Agent: EkromShop/1.0',
        'Accept: application/json'
    ];

    if ($isLegacy) {
        $cookieRes = xui_get_cookie($server, $isRetry);
        if (!$cookieRes['success']) {
            return ['code' => 401, 'data' => null, 'error' => $cookieRes['error']];
        }
        $headers[] = 'Cookie: ' . $cookieRes['cookie'];
    } else {
        $token = trim($server['password'] ?? '');
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }
    }

    $ch = curl_init($url);
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

    // If legacy session expired (401, redirect, or login page HTML), refresh cookie and retry once
    if ($isLegacy && !$isRetry) {
        $needRelogin = false;
        if ($httpCode === 401 || $httpCode === 302 || $httpCode === 307) {
            $needRelogin = true;
        } elseif (is_string($response) && (stripos($response, '<!doctype html>') !== false || stripos($response, '<html') !== false) && stripos($response, 'login') !== false) {
            $needRelogin = true;
        } elseif (is_array($parsed) && isset($parsed['success']) && $parsed['success'] === false) {
            $msg = $parsed['msg'] ?? '';
            if (stripos($msg, 'login') !== false || stripos($msg, 'unauth') !== false || stripos($msg, 'session') !== false) {
                $needRelogin = true;
            }
        }
        if ($needRelogin) {
            return xui_request($server, $path, $method, $data, true);
        }
    }

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

function xui_build_client_config_link($server, $uuid, $displayName, $inbound = null) {
    $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '127.0.0.1');
    $port = (int)($server['port'] ?: 80);
    $protocol = strtolower(trim($server['protocol'] ?: ($inbound['protocol'] ?? 'vmess')));
    if ($protocol === 'v2ray') $protocol = 'vmess';
    $remark = $displayName ?: ($server['name'] ?? 'VPN');

    $streamSettings = [];
    if (!empty($inbound['streamSettings'])) {
        $streamSettings = is_array($inbound['streamSettings']) ? $inbound['streamSettings'] : json_decode($inbound['streamSettings'], true);
    }
    $network = $streamSettings['network'] ?? 'ws';
    $security = $streamSettings['security'] ?? 'none';
    $wsSettings = $streamSettings['wsSettings'] ?? [];
    $path = $wsSettings['path'] ?? '/';
    $wsHost = $wsSettings['headers']['host'] ?? ($wsSettings['host'] ?? '');

    if ($protocol === 'vmess') {
        $bug = !empty($server['bug_host']) ? trim($server['bug_host']) : ($wsHost ?: $targetAddress);
        $vmessObj = [
            'v' => '2',
            'ps' => $remark,
            'add' => $targetAddress,
            'port' => $port,
            'id' => $uuid,
            'aid' => 0,
            'scy' => 'auto',
            'net' => $network,
            'type' => 'none',
            'host' => $bug,
            'path' => $path,
            'tls' => $security
        ];
        if ($security !== 'none' && $security !== '') {
            $vmessObj['sni'] = $bug;
        }
        return 'vmess://' . base64_encode(json_encode($vmessObj, JSON_UNESCAPED_UNICODE));
    } elseif ($protocol === 'vless') {
        $sni = !empty($server['bug_host']) ? trim($server['bug_host']) : 'speedtest.net';
        $vPort = (!empty($server['vless_port']) && (int)$server['vless_port'] > 0) ? (int)$server['vless_port'] : $port;
        $pbkParam = !empty($server['pbk']) ? '&pbk=' . urlencode($server['pbk']) : '';
        $sidParam = !empty($server['sids']) ? '&sid=' . urlencode(explode(',', $server['sids'])[0]) : '';
        $typeParam = ($network === 'grpc' || !empty($server['pbk'])) ? 'grpc' : $network;
        $secParam = !empty($server['pbk']) ? 'reality' : ($security ?: 'none');
        return "vless://{$uuid}@{$targetAddress}:{$vPort}?encryption=none&security={$secParam}&sni={$sni}&fp=chrome&type={$typeParam}&serviceName=grpc{$pbkParam}{$sidParam}#" . rawurlencode($remark);
    } elseif ($protocol === 'trojan') {
        $sni = !empty($server['bug_host']) ? trim($server['bug_host']) : $targetAddress;
        return "trojan://{$uuid}@{$targetAddress}:{$port}?security={$security}&sni={$sni}&type={$network}#" . rawurlencode($remark);
    }

    return "vmess://{$uuid}@{$targetAddress}:{$port}#" . rawurlencode($remark);
}

function xui_add_client($server, $uuid, $email, $expiryTimeStr, $displayName = '', $limitIp = XUI_DEFAULT_LIMIT_IP, $totalGBBytes = XUI_DEFAULT_TOTAL_BYTES) {
    if (empty($server['panel_url'])) {
        return ['success' => false, 'message' => 'เซิร์ฟเวอร์ยังไม่ได้ตั้งค่า Panel URL'];
    }

    $isLegacy = xui_is_legacy($server);
    if ($isLegacy) {
        if (empty($server['username']) || empty($server['password'])) {
            return ['success' => false, 'message' => 'เซิร์ฟเวอร์แบบเดิม (Legacy 3x-ui) ต้องกรอกทั้ง Username และ Password'];
        }
    } else {
        if (empty($server['password'])) {
            return ['success' => false, 'message' => 'เซิร์ฟเวอร์แบบใหม่ (API Token) ยังไม่ได้ตั้งค่า Token'];
        }
    }

    $inboundId = (int)($server['inbound_id'] ?: 1);
    $expiryMs = strtotime($expiryTimeStr) * 1000;

    $baseName = !empty($displayName) ? $displayName : $email;
    $targetEmail = xui_make_client_email($baseName);
    $finalEmail = $targetEmail;

    $attempt = 1;
    $maxAttempts = 10;
    $res = null;

    while ($attempt <= $maxAttempts) {
        if ($isLegacy) {
            $clientObj = [
                'id' => $uuid,
                'alterId' => 0,
                'email' => $finalEmail,
                'limitIp' => (int)$limitIp,
                'totalGB' => (int)$totalGBBytes,
                'expiryTime' => $expiryMs,
                'enable' => true,
                'tgId' => 0,
                'subId' => substr(bin2hex(random_bytes(8)), 0, 16),
                'flow' => '',
                'password' => $uuid
            ];
            $payload = [
                'id' => $inboundId,
                'settings' => json_encode(['clients' => [$clientObj]], JSON_UNESCAPED_UNICODE)
            ];
            $res = xui_request($server, '/panel/api/inbounds/addClient', 'POST', $payload);
        } else {
            $payload = [
                'client' => [
                    'email' => $finalEmail,
                    'id' => $uuid,
                    'totalGB' => (int)$totalGBBytes,
                    'expiryTime' => $expiryMs,
                    'tgId' => 0,
                    'limitIp' => (int)$limitIp,
                    'enable' => true
                ],
                'inboundIds' => [$inboundId]
            ];
            $res = xui_request($server, '/panel/api/clients/add', 'POST', $payload);
        }

        if ($res['code'] === 200 && !empty($res['data']['success'])) {
            break;
        }

        $msg = $res['data']['msg'] ?? $res['error'] ?? '';
        if ((stripos($msg, 'already in use') !== false || stripos($msg, 'duplicate') !== false) && $attempt < $maxAttempts) {
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

    $configLink = '';
    if (!$isLegacy) {
        // In new 3x-ui, fetch config link from API
        $linkRes = xui_request($server, '/panel/api/clients/links/' . rawurlencode($finalEmail), 'GET');
        if (!empty($linkRes['data']['success']) && !empty($linkRes['data']['obj'][0])) {
            $rawLink = $linkRes['data']['obj'][0];
            $configLink = xui_format_config_link($rawLink, $displayName ?: $server['name'], $server);
        }
    }

    // If configLink is empty (always for legacy, or if API returned empty), build link from inbound settings or server config
    if (empty($configLink)) {
        $inbound = null;
        if ($isLegacy) {
            $inbRes = xui_request($server, '/panel/api/inbounds/get/' . $inboundId, 'GET');
            if (!empty($inbRes['data']['success']) && !empty($inbRes['data']['obj'])) {
                $inbound = $inbRes['data']['obj'];
            }
        }
        $configLink = xui_build_client_config_link($server, $uuid, $displayName ?: $server['name'], $inbound);
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

function xui_delete_client($server, $email, $uuid = null) {
    if (empty($server['panel_url']) || empty($email)) {
        return false;
    }

    $isLegacy = xui_is_legacy($server);
    if ($isLegacy) {
        $inboundId = (int)($server['inbound_id'] ?: 1);

        // If uuid not provided, lookup uuid by email from inbound clients
        if (empty($uuid)) {
            $inbRes = xui_request($server, '/panel/api/inbounds/get/' . $inboundId, 'GET');
            if (!empty($inbRes['data']['success']) && !empty($inbRes['data']['obj']['settings'])) {
                $settings = json_decode($inbRes['data']['obj']['settings'], true);
                foreach ($settings['clients'] ?? [] as $cl) {
                    if (($cl['email'] ?? '') === $email) {
                        $uuid = $cl['id'] ?? ($cl['password'] ?? null);
                        break;
                    }
                }
            }

            // Fallback: check all inbounds
            if (empty($uuid)) {
                $listRes = xui_request($server, '/panel/api/inbounds/list', 'GET');
                if (!empty($listRes['data']['success']) && is_array($listRes['data']['obj'])) {
                    foreach ($listRes['data']['obj'] as $inb) {
                        $settings = json_decode($inb['settings'] ?? '', true);
                        foreach ($settings['clients'] ?? [] as $cl) {
                            if (($cl['email'] ?? '') === $email) {
                                $uuid = $cl['id'] ?? ($cl['password'] ?? null);
                                $inboundId = (int)$inb['id'];
                                break 2;
                            }
                        }
                    }
                }
            }
        }

        if (empty($uuid)) {
            return false;
        }

        $res = xui_request($server, "/panel/api/inbounds/{$inboundId}/delClient/" . rawurlencode($uuid), 'POST', new stdClass());
        return (!empty($res['data']['success']));
    } else {
        $res = xui_request($server, '/panel/api/clients/del/' . rawurlencode($email), 'POST', new stdClass());
        return (!empty($res['data']['success']));
    }
}

function xui_update_client($server, $uuid, $email, $expiryTimeStr, $newDisplayName = null, $limitIp = XUI_DEFAULT_LIMIT_IP, $totalGBBytes = XUI_DEFAULT_TOTAL_BYTES) {
    if (empty($server['panel_url']) || empty($email)) {
        return false;
    }

    $expiryMs = strtotime($expiryTimeStr) * 1000;
    $targetEmail = !empty($newDisplayName) ? xui_make_client_email($newDisplayName) : $email;
    $isLegacy = xui_is_legacy($server);

    if ($isLegacy) {
        $inboundId = (int)($server['inbound_id'] ?: 1);
        $clientObj = [
            'id' => $uuid,
            'alterId' => 0,
            'email' => $targetEmail,
            'limitIp' => (int)$limitIp,
            'totalGB' => (int)$totalGBBytes,
            'expiryTime' => $expiryMs,
            'enable' => true,
            'flow' => '',
            'password' => $uuid
        ];
        $payload = [
            'id' => $inboundId,
            'settings' => json_encode(['clients' => [$clientObj]], JSON_UNESCAPED_UNICODE)
        ];
        $res = xui_request($server, '/panel/api/inbounds/updateClient/' . rawurlencode($uuid), 'POST', $payload);

        // If update failed (e.g. duplicate name), retry with existing email
        if ((empty($res['data']['success']) || $res['code'] !== 200) && $targetEmail !== $email) {
            $clientObj['email'] = $email;
            $payload['settings'] = json_encode(['clients' => [$clientObj]], JSON_UNESCAPED_UNICODE);
            $res = xui_request($server, '/panel/api/inbounds/updateClient/' . rawurlencode($uuid), 'POST', $payload);
            if (!empty($res['data']['success'])) {
                return ['success' => true, 'email' => $email];
            }
            return false;
        }

        if (!empty($res['data']['success'])) {
            return ['success' => true, 'email' => $targetEmail];
        }
        return false;
    } else {
        $payload = [
            'id' => $uuid,
            'email' => $targetEmail,
            'expiryTime' => $expiryMs,
            'limitIp' => (int)$limitIp,
            'totalGB' => (int)$totalGBBytes,
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
}

function xui_get_client_traffic($server, $email) {
    if (empty($server['panel_url']) || empty($email)) {
        return null;
    }

    $isLegacy = xui_is_legacy($server);
    if ($isLegacy) {
        $res = xui_request($server, '/panel/api/inbounds/getClientTraffics/' . rawurlencode($email), 'GET');
    } else {
        $res = xui_request($server, '/panel/api/clients/traffic/' . rawurlencode($email), 'GET');
    }

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
