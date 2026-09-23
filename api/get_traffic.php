<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/xui.php';
require_once __DIR__ . '/ssh_vps.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = require_auth();

$uuid = trim($_GET['uuid'] ?? '');
if ($uuid === '') {
    json_response(['status' => 'error', 'message' => 'UUID required'], 400);
}

$db = get_db();
if ($user['role'] === 'admin') {
    $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE uuid = ? AND status_real != "deleted"');
    $stmt->execute([$uuid]);
} else {
    $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE uuid = ? AND user_id = ? AND status_real != "deleted"');
    $stmt->execute([$uuid, $user['id']]);
}
$vpn = $stmt->fetch();

if (!$vpn || $vpn['status_real'] === 'deleted') {
    json_response([
        'status' => 'success',
        'real_status' => 'not_found',
        'up' => '0 MB',
        'down' => '0 MB',
        'up_bytes' => 0,
        'down_bytes' => 0,
        'is_online' => false,
        'last_online' => 0
    ]);
}

// 1. Check expiration
$isExpired = strtotime($vpn['expiry_time']) <= time() || $vpn['status_real'] === 'expired';
$upBytes = (int)($vpn['upload_bytes'] ?? 0);
$downBytes = (int)($vpn['download_bytes'] ?? 0);

if ($isExpired) {
    json_response([
        'status' => 'success',
        'real_status' => 'expired',
        'up' => format_bytes($upBytes),
        'down' => format_bytes($downBytes),
        'up_bytes' => $upBytes,
        'down_bytes' => $downBytes,
        'is_online' => false,
        'last_online' => 0
    ]);
}

$realStatus = 'active';

// Release session lock before any remote server call so other requests don't block
release_session_lock();

// 2. Fetch server details
$serverStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
$serverStmt->execute([$vpn['server_id']]);
$server = $serverStmt->fetch();

$isOnline = false;
$lastOnline = 0;

if ($server && !empty($server['is_active'])) {
    $serverType = $server['type'] ?? 'v2ray';

    if ($serverType === 'ssh_script') {
        // SSH VPS server
        $sshUser = trim($vpn['ssh_user'] ?? '');
        if ($sshUser !== '') {
            $sshCacheKey = md5(($server['host'] ?? '') . '_' . $sshUser);
            $sshCacheFile = sys_get_temp_dir() . '/ssh_traffic_' . $sshCacheKey . '.json';
            $sshCached = null;
            if (file_exists($sshCacheFile) && (time() - filemtime($sshCacheFile) < 15)) {
                $sshCached = json_decode(@file_get_contents($sshCacheFile), true);
            }

            if (is_array($sshCached)) {
                $isOnline = !empty($sshCached['online']);
                $downBytes = (int)($sshCached['bytes'] ?? 0);
                if ($isOnline) {
                    $lastOnline = time() * 1000;
                }
            } else {
                $cmd = "
uid=\$(id -u '{$sshUser}' 2>/dev/null)
online=0
bytes=0
if [ -n \"\$uid\" ]; then
    iptables -C OUTPUT -m owner --uid-owner \"\$uid\" -j ACCEPT 2>/dev/null || iptables -I OUTPUT 1 -m owner --uid-owner \"\$uid\" -j ACCEPT 2>/dev/null
    bytes=\$(iptables -nvx -L OUTPUT 2>/dev/null | grep \"owner UID match \$uid\" | awk '{print \$2}' | head -n1)
    [ -z \"\$bytes\" ] && bytes=0
    proc=\$(ps -u '{$sshUser}' 2>/dev/null | grep -v 'PID' | wc -l)
    [ \"\$proc\" -gt 0 ] && online=1
fi
echo \"\$online|\$bytes\"
";
                $res = ssh_vps_exec($server, $cmd, 4);
                if ($res['success'] && !empty($res['output'])) {
                    $parts = explode('|', trim($res['output']));
                    $isOnline = ((int)($parts[0] ?? 0)) > 0;
                    $sshBytes = max(0, (int)($parts[1] ?? 0));
                    if ($sshBytes > 0) {
                        $downBytes = $sshBytes;
                    }
                    if ($isOnline) {
                        $lastOnline = time() * 1000;
                    }
                    @file_put_contents($sshCacheFile, json_encode(['online' => $isOnline ? 1 : 0, 'bytes' => $downBytes]), LOCK_EX);
                }
            }
        }
    } else {
        // 3x-ui / X-UI server
        if (!empty($server['panel_url'])) {
            $cacheKey = md5($server['panel_url']);
            $inboundsCacheFile = sys_get_temp_dir() . '/xui_inbounds_' . $cacheKey . '.json';
            $inboundsObj = null;

            if (file_exists($inboundsCacheFile) && (time() - filemtime($inboundsCacheFile) < 15)) {
                $inboundsObj = json_decode(@file_get_contents($inboundsCacheFile), true);
            }

            if (!is_array($inboundsObj)) {
                $ibRes = xui_request($server, '/panel/api/inbounds/list', 'GET');
                if (!empty($ibRes['data']['success']) && is_array($ibRes['data']['obj'])) {
                    $inboundsObj = $ibRes['data']['obj'];
                    @file_put_contents($inboundsCacheFile, json_encode($inboundsObj), LOCK_EX);
                }
            }

            if (is_array($inboundsObj)) {
                $cleanUuid = strtolower(trim($vpn['uuid']));
                $cleanEmail = trim($vpn['xui_email'] ?? '');
                $foundClient = null;

                foreach ($inboundsObj as $ib) {
                    if (!empty($ib['clientStats'])) {
                        foreach ($ib['clientStats'] as $cs) {
                            $csUuid = strtolower(trim($cs['uuid'] ?? ''));
                            $csEmail = trim($cs['email'] ?? '');

                            if ($cleanUuid !== '' && $csUuid === $cleanUuid) {
                                $foundClient = $cs;
                                break 2;
                            }
                            if ($cleanEmail !== '' && $csEmail === $cleanEmail) {
                                $foundClient = $cs;
                                break 2;
                            }
                            if ($cleanUuid !== '' && strlen($cleanUuid) >= 8 && strpos($csEmail, substr($cleanUuid, 0, 8)) !== false) {
                                $foundClient = $cs;
                                break 2;
                            }
                        }
                    }

                    // Fallback to settings.clients
                    if (!$foundClient) {
                        $settings = is_string($ib['settings'] ?? null) ? json_decode($ib['settings'], true) : ($ib['settings'] ?? []);
                        if (!empty($settings['clients'])) {
                            foreach ($settings['clients'] as $c) {
                                $cUuid = strtolower(trim($c['id'] ?? ''));
                                if ($cleanUuid !== '' && $cUuid === $cleanUuid) {
                                    $foundClient = [
                                        'uuid' => $c['id'],
                                        'email' => $c['email'] ?? '',
                                        'up' => 0,
                                        'down' => 0,
                                        'enable' => $c['enable'] ?? true,
                                        'lastOnline' => 0
                                    ];
                                    break 2;
                                }
                            }
                        }
                    }
                }

                if ($foundClient) {
                    $upBytes = (int)($foundClient['up'] ?? 0);
                    $downBytes = (int)($foundClient['down'] ?? 0);
                    $lastOnline = (int)($foundClient['lastOnline'] ?? 0);

                    // ตรวจสอบออนไลน์: อิงตามเกณฑ์ 30 วินาที หรือตรวจสอบกับ API onlines ของ 3x-ui
                    $isOnline = ($lastOnline > 0 && ((time() * 1000) - $lastOnline) <= 30000);
                    if (!$isOnline) {
                        $onlinesCacheFile = sys_get_temp_dir() . '/xui_onlines_' . $cacheKey . '.json';
                        $onlList = null;
                        if (file_exists($onlinesCacheFile) && (time() - filemtime($onlinesCacheFile) < 3)) {
                            $onlList = json_decode(@file_get_contents($onlinesCacheFile), true);
                        }
                        if (!is_array($onlList)) {
                            $onlRes = xui_request($server, '/panel/api/clients/onlines', 'POST');
                            if (!empty($onlRes['data']['success']) && is_array($onlRes['data']['obj'])) {
                                $onlList = $onlRes['data']['obj'];
                                @file_put_contents($onlinesCacheFile, json_encode($onlList), LOCK_EX);
                            } else {
                                $onlList = [];
                            }
                        }
                        if (is_array($onlList)) {
                            $cEmail = trim($foundClient['email'] ?? '');
                            $cUuid = trim($foundClient['uuid'] ?? '');
                            if (($cEmail !== '' && in_array($cEmail, $onlList, true)) || ($cUuid !== '' && in_array($cUuid, $onlList, true))) {
                                $isOnline = true;
                            }
                        }
                    }

                    if (empty($foundClient['enable']) && !$isExpired) {
                        $realStatus = 'disabled';
                    }
                } else {
                    if (!$isExpired) {
                        $realStatus = 'not_found';
                    }
                }
            }
        }
    }
}

// Persist real values into database
$updateStmt = $db->prepare('UPDATE vpn_configs SET upload_bytes = ?, download_bytes = ?, status_real = ? WHERE id = ? AND status_real != "deleted"');
$updateStmt->execute([$upBytes, $downBytes, $realStatus, $vpn['id']]);

json_response([
    'status' => 'success',
    'real_status' => $realStatus,
    'up' => format_bytes($upBytes),
    'down' => format_bytes($downBytes),
    'up_bytes' => $upBytes,
    'down_bytes' => $downBytes,
    'is_online' => $isOnline,
    'last_online' => $lastOnline
]);
