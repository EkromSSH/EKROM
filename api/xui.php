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

function xui_clean_panel_url($url) {
    $url = trim((string)$url);
    if ($url === '') return '';
    $url = rtrim($url, '/');
    $url = preg_replace('#/(panel|inbounds?|login)(/.*)?$#i', '', $url);
    return rtrim($url, '/');
}

function xui_get_cache_key($server) {
    $url = xui_clean_panel_url($server['panel_url'] ?? '');
    $user = trim($server['username'] ?? '');
    return md5($url . '|' . $user);
}

function xui_login($server) {
    if (empty($server['panel_url']) || empty($server['username']) || empty($server['password'])) {
        return ['success' => false, 'error' => 'เซิร์ฟเวอร์ยังไม่ได้ตั้งค่า Panel URL, Username หรือ Password'];
    }

    $panelUrl = xui_clean_panel_url($server['panel_url']);
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

function xui_request($server, $path, $method = 'GET', $data = null, $isRetry = false, $timeout = 10, $connectTimeout = 5) {
    if (empty($server['panel_url'])) {
        return ['code' => 0, 'data' => null, 'error' => 'No panel URL configured'];
    }

    $panelUrl = xui_clean_panel_url($server['panel_url']);
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
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => $connectTimeout
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
            return xui_request($server, $path, $method, $data, true, $timeout, $connectTimeout);
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

function xui_get_target_address($server) {
    $domain = !empty($server['domain']) ? trim($server['domain']) : '';
    $host = !empty($server['host']) ? trim($server['host']) : '';
    $panelHost = '';
    if (!empty($server['panel_url'])) {
        $parsed = parse_url($server['panel_url']);
        if (!empty($parsed['host'])) {
            $panelHost = trim($parsed['host']);
        }
    }

    if ($domain !== '') {
        // If domain is an IP or resolves in DNS, use domain
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return $domain;
        }
        $resolved = @gethostbyname($domain);
        if ($resolved !== $domain) {
            return $domain;
        }
        // Domain failed DNS lookup, fall back to panelHost if available
        if ($panelHost !== '') {
            return $panelHost;
        }
        return $domain;
    }

    if ($host !== '') {
        return $host;
    }

    if ($panelHost !== '') {
        return $panelHost;
    }

    return '127.0.0.1';
}

function xui_build_client_config_link($server, $uuid, $displayName, $inbound = null) {
    $targetAddress = xui_get_target_address($server);
    $port = (!empty($inbound['port']) && (int)$inbound['port'] > 0) ? (int)$inbound['port'] : (int)($server['port'] ?: 80);
    $protocol = strtolower(trim($inbound['protocol'] ?? ($server['protocol'] ?? 'vmess')));
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

    $serverType = $server['type'] ?? '';
    $gamingPort = (!empty($server['vless_port']) && (int)$server['vless_port'] > 0) ? (int)$server['vless_port'] : $port;

    $tcpSettings = $streamSettings['tcpSettings'] ?? [];
    $kcpSettings = $streamSettings['kcpSettings'] ?? [];
    $grpcSettings = $streamSettings['grpcSettings'] ?? [];
    $httpupgradeSettings = $streamSettings['httpupgradeSettings'] ?? [];
    $xhttpSettings = $streamSettings['xhttpSettings'] ?? [];
    $tlsSettings = $streamSettings['tlsSettings'] ?? [];
    $realitySettings = $streamSettings['realitySettings'] ?? [];

    $allowInsecure = !empty($tlsSettings['settings']['allowInsecure'])
        || !empty($tlsSettings['settings']['allow_insecure'])
        || !empty($tlsSettings['allowInsecure'])
        || !empty($tlsSettings['allow_insecure'])
        || (isset($tlsSettings['settings']['allowInsecure']) && $tlsSettings['settings']['allowInsecure'] === true)
        || ($serverType === 'vmess_tls' || $serverType === 'vless_tls');

    $fp = !empty($tlsSettings['settings']['fingerprint']) ? $tlsSettings['settings']['fingerprint'] : (!empty($realitySettings['settings']['fingerprint']) ? $realitySettings['settings']['fingerprint'] : 'chrome');
    $tlsSni = !empty($tlsSettings['serverName']) ? $tlsSettings['serverName'] : (!empty($tlsSettings['sni']) ? $tlsSettings['sni'] : '');
    $bugHost = !empty($server['bug_host']) ? trim($server['bug_host']) : '';
    $effectiveSni = $bugHost ?: ($tlsSni ?: $targetAddress);

    $alpnStr = '';
    if (!empty($tlsSettings['alpn'])) {
        $alpnStr = is_array($tlsSettings['alpn']) ? implode(',', $tlsSettings['alpn']) : (string)$tlsSettings['alpn'];
    }

    if ($protocol === 'vmess') {
        $vPort = (!empty($inbound['port']) && (int)$inbound['port'] > 0) ? (int)$inbound['port'] : (($serverType === 'vmess_tls') ? $gamingPort : $port);
        $tlsVal = ($security === 'tls' || $serverType === 'vmess_tls') ? 'tls' : $security;
        $vmessObj = [
            'v' => '2',
            'ps' => $remark,
            'add' => $targetAddress,
            'port' => $vPort,
            'id' => $uuid,
            'aid' => 0,
            'scy' => 'auto',
            'net' => $network,
            'type' => 'none',
            'host' => '',
            'path' => '',
            'tls' => $tlsVal
        ];

        if ($network === 'tcp') {
            $tcpHeaderType = $tcpSettings['header']['type'] ?? ($tcpSettings['type'] ?? 'none');
            $vmessObj['type'] = $tcpHeaderType;
            if ($tcpHeaderType === 'http') {
                $tcpPath = $tcpSettings['header']['request']['path'][0] ?? ($tcpSettings['request']['path'][0] ?? '/');
                $tcpHost = $bugHost ?: ($tcpSettings['header']['request']['headers']['Host'][0] ?? ($tcpSettings['request']['headers']['host'][0] ?? ''));
                $vmessObj['path'] = $tcpPath;
                if ($tcpHost !== '') $vmessObj['host'] = $tcpHost;
            }
        } elseif ($network === 'kcp') {
            $vmessObj['type'] = $kcpSettings['type'] ?? 'none';
            $vmessObj['path'] = $kcpSettings['seed'] ?? '';
        } elseif ($network === 'ws') {
            $vmessObj['path'] = $path ?: '/';
            $h = $bugHost ?: ($wsHost ?: $targetAddress);
            if ($h !== '') $vmessObj['host'] = $h;
        } elseif ($network === 'grpc') {
            $vmessObj['path'] = $grpcSettings['serviceName'] ?? 'grpc';
            if (!empty($grpcSettings['authority'])) $vmessObj['authority'] = $grpcSettings['authority'];
            if (!empty($grpcSettings['multiMode'])) $vmessObj['type'] = 'multi';
        } elseif ($network === 'httpupgrade') {
            $vmessObj['path'] = $httpupgradeSettings['path'] ?? ($path ?: '/');
            $h = $bugHost ?: ($httpupgradeSettings['headers']['host'] ?? ($httpupgradeSettings['host'] ?? ''));
            if ($h !== '') $vmessObj['host'] = $h;
        } elseif ($network === 'xhttp') {
            $vmessObj['path'] = $xhttpSettings['path'] ?? ($path ?: '/');
            $h = $bugHost ?: ($xhttpSettings['headers']['host'] ?? ($xhttpSettings['host'] ?? ''));
            if ($h !== '') $vmessObj['host'] = $h;
            if (!empty($xhttpSettings['mode'])) $vmessObj['type'] = $xhttpSettings['mode'];
        }

        if ($tlsVal === 'tls') {
            if ($effectiveSni !== '') $vmessObj['sni'] = $effectiveSni;
            if ($fp !== '') $vmessObj['fp'] = $fp;
            if ($alpnStr !== '') {
                $vmessObj['alpn'] = $alpnStr;
            }
            if ($allowInsecure) {
                $vmessObj['allowInsecure'] = '1';
                $vmessObj['insecure'] = '1';
            }
        }
        return 'vmess://' . base64_encode(json_encode($vmessObj, JSON_UNESCAPED_UNICODE));
    } elseif ($protocol === 'vless') {
        $vPort = (!empty($inbound['port']) && (int)$inbound['port'] > 0) ? (int)$inbound['port'] : (($serverType === 'vless_tls' || (!empty($server['vless_port']) && (int)$server['vless_port'] > 0)) ? $gamingPort : $port);

        if (!empty($server['pbk']) || $security === 'reality') {
            $effectiveSecurity = 'reality';
        } elseif ($security === 'tls' || $serverType === 'vless_tls') {
            $effectiveSecurity = 'tls';
        } else {
            $effectiveSecurity = $security ?: 'none';
        }

        $params = [];
        $params['type'] = $network;
        $params['encryption'] = 'none';

        if ($network === 'ws') {
            $params['path'] = $path ?: '/';
            $wsH = $bugHost ?: ($wsHost ?: $targetAddress);
            if ($wsH !== '') $params['host'] = $wsH;
        } elseif ($network === 'grpc') {
            $params['serviceName'] = $grpcSettings['serviceName'] ?? 'grpc';
            if (!empty($grpcSettings['authority'])) $params['authority'] = $grpcSettings['authority'];
            if (!empty($grpcSettings['multiMode'])) $params['mode'] = 'multi';
        } elseif ($network === 'tcp') {
            $tcpHeaderType = $tcpSettings['header']['type'] ?? ($tcpSettings['type'] ?? 'none');
            if ($tcpHeaderType === 'http') {
                $params['headerType'] = 'http';
                $tcpPath = $tcpSettings['header']['request']['path'][0] ?? ($tcpSettings['request']['path'][0] ?? '/');
                $tcpHost = $bugHost ?: ($tcpSettings['header']['request']['headers']['Host'][0] ?? ($tcpSettings['request']['headers']['host'][0] ?? ''));
                $params['path'] = $tcpPath;
                if ($tcpHost !== '') $params['host'] = $tcpHost;
            }
        } elseif ($network === 'kcp') {
            if (!empty($kcpSettings['type']) && $kcpSettings['type'] !== 'none') $params['headerType'] = $kcpSettings['type'];
            if (!empty($kcpSettings['seed'])) $params['seed'] = $kcpSettings['seed'];
        } elseif ($network === 'httpupgrade') {
            $params['path'] = $httpupgradeSettings['path'] ?? ($path ?: '/');
            $huHost = $bugHost ?: ($httpupgradeSettings['headers']['host'] ?? ($httpupgradeSettings['host'] ?? ''));
            if ($huHost !== '') $params['host'] = $huHost;
        } elseif ($network === 'xhttp') {
            $params['path'] = $xhttpSettings['path'] ?? ($path ?: '/');
            $xhHost = $bugHost ?: ($xhttpSettings['headers']['host'] ?? ($xhttpSettings['host'] ?? ''));
            if ($xhHost !== '') $params['host'] = $xhHost;
            if (!empty($xhttpSettings['mode'])) $params['mode'] = $xhttpSettings['mode'];
        }

        $params['security'] = $effectiveSecurity;
        if ($effectiveSecurity === 'tls') {
            if ($fp !== '') $params['fp'] = $fp;
            if ($alpnStr !== '') {
                $params['alpn'] = $alpnStr;
            }
            if ($allowInsecure) {
                $params['allowInsecure'] = '1';
                $params['insecure'] = '1';
            }
            if ($effectiveSni !== '') $params['sni'] = $effectiveSni;
            if (!empty($tlsSettings['settings']['echConfigList'])) {
                $params['ech'] = $tlsSettings['settings']['echConfigList'];
            }
        } elseif ($effectiveSecurity === 'reality') {
            $pbk = !empty($server['pbk']) ? trim($server['pbk']) : ($realitySettings['settings']['publicKey'] ?? '');
            $sids = !empty($server['sids']) ? trim($server['sids']) : (is_array($realitySettings['shortIds'] ?? null) ? implode(',', $realitySettings['shortIds']) : ($realitySettings['shortIds'] ?? ''));
            $firstSid = trim(explode(',', $sids)[0] ?? '');
            $realSni = $bugHost ?: (!empty($realitySettings['serverNames']) ? (is_array($realitySettings['serverNames']) ? $realitySettings['serverNames'][0] : explode(',', $realitySettings['serverNames'])[0]) : 'speedtest.net');

            if ($pbk !== '') $params['pbk'] = $pbk;
            if ($fp !== '') $params['fp'] = $fp;
            if ($realSni !== '') $params['sni'] = $realSni;
            if ($firstSid !== '') $params['sid'] = $firstSid;
            if (!empty($realitySettings['settings']['spiderX'])) $params['spx'] = $realitySettings['settings']['spiderX'];
            if (!empty($realitySettings['settings']['mldsa65Verify'])) $params['pqv'] = $realitySettings['settings']['mldsa65Verify'];
        }

        $queryStr = !empty($params) ? '?' . str_replace('%2F', '/', http_build_query($params, '', '&', PHP_QUERY_RFC3986)) : '';
        $hashRemark = strtr(rawurlencode($remark), ['%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')']);

        return "vless://{$uuid}@{$targetAddress}:{$vPort}{$queryStr}#{$hashRemark}";
    } elseif ($protocol === 'trojan') {
        $vPort = (!empty($inbound['port']) && (int)$inbound['port'] > 0) ? (int)$inbound['port'] : $port;
        $params = [];
        $params['type'] = $network;
        if ($network === 'ws') {
            $params['path'] = $path ?: '/';
            $wsH = $bugHost ?: ($wsHost ?: $targetAddress);
            if ($wsH !== '') $params['host'] = $wsH;
        } elseif ($network === 'grpc') {
            $params['serviceName'] = $grpcSettings['serviceName'] ?? 'grpc';
            if (!empty($grpcSettings['authority'])) $params['authority'] = $grpcSettings['authority'];
            if (!empty($grpcSettings['multiMode'])) $params['mode'] = 'multi';
        } elseif ($network === 'tcp') {
            $tcpHeaderType = $tcpSettings['header']['type'] ?? ($tcpSettings['type'] ?? 'none');
            if ($tcpHeaderType === 'http') {
                $params['headerType'] = 'http';
                $tcpPath = $tcpSettings['header']['request']['path'][0] ?? ($tcpSettings['request']['path'][0] ?? '/');
                $tcpHost = $bugHost ?: ($tcpSettings['header']['request']['headers']['Host'][0] ?? ($tcpSettings['request']['headers']['host'][0] ?? ''));
                $params['path'] = $tcpPath;
                if ($tcpHost !== '') $params['host'] = $tcpHost;
            }
        } elseif ($network === 'kcp') {
            if (!empty($kcpSettings['type']) && $kcpSettings['type'] !== 'none') $params['headerType'] = $kcpSettings['type'];
            if (!empty($kcpSettings['seed'])) $params['seed'] = $kcpSettings['seed'];
        } elseif ($network === 'httpupgrade') {
            $params['path'] = $httpupgradeSettings['path'] ?? ($path ?: '/');
            $huHost = $bugHost ?: ($httpupgradeSettings['headers']['host'] ?? ($httpupgradeSettings['host'] ?? ''));
            if ($huHost !== '') $params['host'] = $huHost;
        } elseif ($network === 'xhttp') {
            $params['path'] = $xhttpSettings['path'] ?? ($path ?: '/');
            $xhHost = $bugHost ?: ($xhttpSettings['headers']['host'] ?? ($xhttpSettings['host'] ?? ''));
            if ($xhHost !== '') $params['host'] = $xhHost;
            if (!empty($xhttpSettings['mode'])) $params['mode'] = $xhttpSettings['mode'];
        }

        $params['security'] = $security ?: 'none';
        if ($security === 'tls') {
            if ($fp !== '') $params['fp'] = $fp;
            if ($alpnStr !== '') {
                $params['alpn'] = $alpnStr;
            }
            if ($allowInsecure) {
                $params['allowInsecure'] = '1';
                $params['insecure'] = '1';
            }
            if (!empty($tlsSettings['settings']['echConfigList'])) {
                $params['ech'] = $tlsSettings['settings']['echConfigList'];
            }
            if ($effectiveSni !== '') $params['sni'] = $effectiveSni;
        }

        $queryStr = !empty($params) ? '?' . str_replace('%2F', '/', http_build_query($params, '', '&', PHP_QUERY_RFC3986)) : '';
        $hashRemark = strtr(rawurlencode($remark), ['%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')']);
        return "trojan://{$uuid}@{$targetAddress}:{$vPort}{$queryStr}#{$hashRemark}";
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

    // Always fetch inbound settings to determine actual protocol and ensure full config parameters
    $inbound = null;
    $inbRes = xui_request($server, '/panel/api/inbounds/get/' . $inboundId, 'GET');
    if (!empty($inbRes['data']['success']) && !empty($inbRes['data']['obj'])) {
        $inbound = $inbRes['data']['obj'];
    }

    if (empty($configLink)) {
        $configLink = xui_build_client_config_link($server, $uuid, $displayName ?: $server['name'], $inbound);
    }

    $actualProtocol = strtolower(trim($inbound['protocol'] ?? ($server['protocol'] ?? 'vmess')));
    if ($actualProtocol === 'v2ray') $actualProtocol = 'vmess';

    return [
        'success' => true,
        'email' => $finalEmail,
        'config_link' => $configLink,
        'protocol' => $actualProtocol
    ];
}

function xui_format_config_link($rawLink, $displayName, $server = []) {
    if (empty($rawLink)) return '';

    $targetAddress = xui_get_target_address($server);
    $bugHost = !empty($server['bug_host']) ? trim($server['bug_host']) : '';
    $serverType = $server['type'] ?? '';
    $hashRemark = strtr(rawurlencode($displayName), ['%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')']);

    // If VMess (vmess://<base64>)
    if (strpos($rawLink, 'vmess://') === 0) {
        $b64 = substr($rawLink, 8);
        $json = json_decode(base64_decode($b64), true);
        if (is_array($json)) {
            $json['ps'] = $displayName;
            if (!empty($targetAddress) && (empty($json['add']) || $json['add'] === '0.0.0.0' || $json['add'] === '127.0.0.1')) {
                $json['add'] = $targetAddress;
            }
            if (!empty($json['tls']) && ($json['tls'] === 'tls' || $serverType === 'vmess_tls')) {
                $json['tls'] = 'tls';
                if ($bugHost !== '') {
                    $json['sni'] = $bugHost;
                }
                $json['allowInsecure'] = '1';
                $json['insecure'] = '1';
            }
            return 'vmess://' . base64_encode(json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
    }

    // If VLESS, Trojan, or Shadowsocks:
    $parts = explode('#', $rawLink, 2);
    $baseLink = $parts[0];

    // If address is 0.0.0.0 or 127.0.0.1, replace with targetAddress
    if (!empty($targetAddress)) {
        $baseLink = preg_replace('/@(?:0\.0\.0\.0|127\.0\.0\.1):/', '@' . $targetAddress . ':', $baseLink);
    }

    if ($bugHost !== '' && (strpos($baseLink, 'security=tls') !== false || strpos($baseLink, 'security=reality') !== false)) {
        if (strpos($baseLink, 'sni=') === false) {
            $baseLink .= (strpos($baseLink, '?') === false ? '?' : '&') . 'sni=' . rawurlencode($bugHost);
        }
    }

    return $baseLink . '#' . $hashRemark;
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
