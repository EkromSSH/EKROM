<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/xui.php';
require_once __DIR__ . '/ssh_vps.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

function get_server_live_stats($server) {
    $type = $server['type'] ?? 'v2ray';
    $now = time();

    if ($type === 'ssh_script') {
        if (empty($server['host']) || empty($server['password'])) {
            return ['online' => false, 'user_count' => 0, 'cpu' => 0];
        }
        $cmd = "
online=\$(ps -eo user,comm 2>/dev/null | grep -E 'sshd|dropbear' | grep -v -E '^root|^nobody' | awk '{print \$1}' | sort -u | wc -l)
cpu=\$(top -bn1 2>/dev/null | grep 'Cpu(s)' | awk '{print int(\$2 + \$4)}' || awk '{print int(\$1 * 100)}' /proc/loadavg 2>/dev/null || echo 0)
echo \"\$online|\$cpu\"
";
        $res = ssh_vps_exec($server, $cmd, 3);
        if ($res['success'] && !empty($res['output'])) {
            $parts = explode('|', trim($res['output']));
            return [
                'online' => true,
                'user_count' => max(0, (int)($parts[0] ?? 0)),
                'cpu' => max(0, min(100, (int)($parts[1] ?? 0)))
            ];
        }
        return ['online' => false, 'user_count' => 0, 'cpu' => 0];
    } else {
        // 3x-ui / X-UI panel
        if (empty($server['panel_url'])) {
            return ['online' => false, 'user_count' => 0, 'cpu' => 0];
        }

        $ibRes = xui_request($server, '/panel/api/inbounds/list', 'GET');
        if (empty($ibRes['data']['success'])) {
            return ['online' => false, 'user_count' => 0, 'cpu' => 0];
        }

        // ดึงรายชื่อ Client ที่ออนไลน์แบบเรียลไทม์จาก 3x-ui โดยตรง
        // 3x-ui standard inbounds page uses /panel/api/inbounds/onlines
        $onlineRes = xui_request($server, '/panel/api/inbounds/onlines', 'POST');
        if (empty($onlineRes['data']['success']) || !is_array($onlineRes['data']['obj'])) {
            $onlineRes = xui_request($server, '/panel/api/clients/onlines', 'POST');
        }

        $onlineSet = null;
        if (!empty($onlineRes['data']['success']) && is_array($onlineRes['data']['obj'])) {
            $onlineSet = [];
            foreach ($onlineRes['data']['obj'] as $item) {
                $trimmed = trim((string)$item);
                if ($trimmed !== '') {
                    $onlineSet[$trimmed] = true;
                    $normalized = preg_replace('/\s+/u', ' ', $trimmed);
                    $onlineSet[$normalized] = true;
                }
            }
        }

        // กรองเฉพาะ Inbound ID ที่เซิร์ฟเวอร์นี้ใช้งานจริง
        $targetInboundIds = [];
        if (!empty($server['inbound_id'])) {
            $rawIds = explode(',', (string)$server['inbound_id']);
            foreach ($rawIds as $rid) {
                $trimmed = trim($rid);
                if ($trimmed !== '') {
                    $targetInboundIds[] = (int)$trimmed;
                }
            }
        }

        $onlineClients = [];
        // เกณฑ์เวลาเชื่อมต่อล่าสุดอิงตาม 3x-ui grace window (~20-25 วินาที)
        $thresholdMs = 25 * 1000;
        $nowMs = $now * 1000;

        if (!empty($ibRes['data']['obj']) && is_array($ibRes['data']['obj'])) {
            foreach ($ibRes['data']['obj'] as $ib) {
                $ibId = (int)($ib['id'] ?? 0);
                if (!empty($targetInboundIds) && !in_array($ibId, $targetInboundIds, true)) {
                    continue; // ข้าม Inbound อื่นๆ ที่ไม่ใช่ของเซิร์ฟเวอร์นี้
                }

                // รวบรวมรายชื่อ Client ทั้งหมดใน Inbound นี้ (ตรงตาม getClientCounts ใน 3x-ui)
                $inboundClients = [];
                if (!empty($ib['settings'])) {
                    $settings = is_array($ib['settings']) ? $ib['settings'] : json_decode($ib['settings'], true);
                    if (!empty($settings['clients']) && is_array($settings['clients'])) {
                        foreach ($settings['clients'] as $c) {
                            $email = trim((string)($c['email'] ?? ''));
                            $uuid = trim((string)($c['id'] ?? ($c['password'] ?? '')));
                            $key = $email ?: $uuid;
                            if ($key !== '') {
                                $inboundClients[$key] = [
                                    'email' => $email,
                                    'uuid' => $uuid,
                                    'enable' => !isset($c['enable']) || !empty($c['enable']),
                                    'lastOnline' => 0
                                ];
                            }
                        }
                    }
                }

                if (!empty($ib['clientStats']) && is_array($ib['clientStats'])) {
                    foreach ($ib['clientStats'] as $cs) {
                        $email = trim((string)($cs['email'] ?? ''));
                        $uuid = trim((string)($cs['uuid'] ?? ($cs['id'] ?? '')));
                        $key = $email ?: $uuid;
                        if ($key !== '') {
                            if (!isset($inboundClients[$key])) {
                                $inboundClients[$key] = [
                                    'email' => $email,
                                    'uuid' => $uuid,
                                    'enable' => !isset($cs['enable']) || !empty($cs['enable']),
                                    'lastOnline' => (int)($cs['lastOnline'] ?? 0)
                                ];
                            } else {
                                $inboundClients[$key]['lastOnline'] = (int)($cs['lastOnline'] ?? 0);
                                if (isset($cs['enable'])) {
                                    $inboundClients[$key]['enable'] = !empty($cs['enable']);
                                }
                            }
                        }
                    }
                }

                // ตรวจสอบสถานะออนไลน์: หากมี API onlines ให้เทียบตรงๆ เหมือน isClientOnline ของ 3x-ui
                // หากไม่มี API จึง fallback ใช้ lastOnline <= 25s
                foreach ($inboundClients as $key => $c) {
                    if (empty($c['enable'])) continue;

                    $isClientOnline = false;
                    if ($onlineSet !== null) {
                        $email = $c['email'];
                        $uuid = $c['uuid'];
                        $emailNorm = preg_replace('/\s+/u', ' ', $email);
                        if ((!empty($email) && (isset($onlineSet[$email]) || isset($onlineSet[$emailNorm])))
                            || (!empty($uuid) && isset($onlineSet[$uuid]))) {
                            $isClientOnline = true;
                        }
                    } else {
                        $lastOnline = $c['lastOnline'];
                        if ($lastOnline > 0 && ($nowMs - $lastOnline) <= $thresholdMs) {
                            $isClientOnline = true;
                        }
                    }

                    if ($isClientOnline) {
                        $onlineClients[$key] = true;
                    }
                }
            }
        }

        $statusRes = xui_request($server, '/panel/api/server/status', 'GET');
        $cpu = 0;
        if (!empty($statusRes['data']['obj']['cpu'])) {
            $cpu = round((float)$statusRes['data']['obj']['cpu']);
        }

        return [
            'online' => true,
            'user_count' => count($onlineClients),
            'cpu' => max(0, min(100, (int)$cpu))
        ];
    }
}

function get_all_servers_real_stats($db, $forceRefresh = false) {
    $cacheFile = sys_get_temp_dir() . '/ekrom_servers_stats_cache.json';
    $cacheTtl = 6;

    if (!$forceRefresh && file_exists($cacheFile)) {
        $raw = @file_get_contents($cacheFile);
        $cached = json_decode($raw, true);
        if (is_array($cached) && isset($cached['timestamp']) && (time() - $cached['timestamp']) < $cacheTtl && !empty($cached['data'])) {
            return $cached['data'];
        }
    }

    $lockFile = sys_get_temp_dir() . '/ekrom_servers_stats.lock';
    $fp = @fopen($lockFile, 'c+');
    if ($fp && !flock($fp, LOCK_EX | LOCK_NB)) {
        fclose($fp);
        if (isset($cached['data'])) {
            return $cached['data'];
        }
    }

    try {
        $servers = $db->query('SELECT * FROM servers WHERE is_active = 1')->fetchAll();
        $stats = [];
        $updateStmt = $db->prepare('UPDATE servers SET user_count = ?, cpu = ? WHERE id = ?');

        foreach ($servers as $s) {
            $st = get_server_live_stats($s);
            $stats['sv' . $s['id']] = [
                'user_count' => (int)$st['user_count'],
                'cpu' => (int)$st['cpu'],
                'is_online' => (bool)$st['online']
            ];
            $updateStmt->execute([(int)$st['user_count'], (int)$st['cpu'], $s['id']]);
        }

        @file_put_contents($cacheFile, json_encode([
            'timestamp' => time(),
            'data' => $stats
        ], JSON_UNESCAPED_UNICODE), LOCK_EX);

        if ($fp) {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
        return $stats;
    } catch (\Throwable $e) {
        if ($fp) {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
        return $cached['data'] ?? [];
    }
}

$action = $_GET['action'] ?? 'get_store';
$db = get_db();

if ($action === 'get_stats') {
    $stats = get_all_servers_real_stats($db);
    json_response(['status' => 'success', 'data' => $stats]);
}

// get_store
$liveStats = get_all_servers_real_stats($db);

$tiers = $db->query('SELECT * FROM price_tiers')->fetchAll();
$formattedTiers = [];
$tierMap = [];
foreach ($tiers as $t) {
    $prices = json_decode($t['prices'], true) ?: [5, 25, 45, 80];
    $formattedTiers[] = [
        'id' => (int)$t['id'],
        'name' => $t['name'],
        'color_theme' => $t['color_theme'],
        'theme' => $t['color_theme'],
        'price_1' => $prices[0] ?? 5,
        'price_7' => $prices[1] ?? 25,
        'price_15' => $prices[2] ?? 45,
        'price_30' => $prices[3] ?? 80,
        'prices' => $prices
    ];
    $tierMap[$t['id']] = [
        'name' => $t['name'],
        'theme' => $t['color_theme'],
        'prices' => $prices
    ];
}

$addonsList = $db->query('SELECT * FROM addons')->fetchAll();
$allAddons = [];
foreach ($addonsList as $ad) {
    $allAddons[] = [
        'id' => (int)$ad['id'],
        'title' => $ad['title'],
        'theme_color' => $ad['theme_color'],
        'duration_text' => $ad['duration_text'],
        'description' => $ad['description'],
        'price' => (float)$ad['price'],
        'subscription_codes' => json_decode($ad['subscription_codes'], true) ?: []
    ];
}

$cats = $db->query('SELECT * FROM categories ORDER BY sort_order ASC')->fetchAll();
$servers = $db->query('SELECT * FROM servers WHERE is_active = 1')->fetchAll();

$catServers = [];
$uncategorized = [];

foreach ($cats as $c) {
    $catServers[$c['id']] = [
        'id' => (int)$c['id'],
        'name' => $c['name'],
        'color_theme' => $c['color_theme'],
        'servers' => []
    ];
}

foreach ($servers as $s) {
    $svKey = 'sv' . $s['id'];
    $tierId = $s['tier_id'];
    $tierInfo = $tierMap[$tierId] ?? ['name' => 'General', 'theme' => 'pink', 'prices' => [5, 25, 45, 80]];

    // Filter addons attached to this server (preserve configured order)
    $serverAddons = [];
    if (!empty($s['addon_id'])) {
        $addonIds = array_filter(array_map('trim', explode(',', (string)$s['addon_id'])));
        $addonMap = [];
        foreach ($allAddons as $ad) {
            $addonMap[(string)$ad['id']] = $ad;
        }
        foreach ($addonIds as $aid) {
            if (isset($addonMap[$aid])) {
                $serverAddons[] = $addonMap[$aid];
            }
        }
    }

    $catTheme = ($s['category_id'] && isset($catServers[$s['category_id']]['color_theme']))
        ? $catServers[$s['category_id']]['color_theme']
        : null;

    $sStat = $liveStats[$svKey] ?? null;
    $liveUserCount = $sStat ? (int)$sStat['user_count'] : (int)$s['user_count'];
    $liveCpu = $sStat ? (int)$sStat['cpu'] : (int)$s['cpu'];
    $isServerOnline = $sStat ? (bool)$sStat['is_online'] : true;

    $svObj = [
        'id' => (int)$s['id'],
        'name' => $s['name'],
        'type' => $s['type'],
        'category_id' => (int)$s['category_id'],
        'category_theme' => $catTheme,
        'tier_id' => (int)$tierId,
        'price_tier' => (int)$tierId,
        'user_count' => $liveUserCount,
        'cpu' => $liveCpu,
        'is_server_online' => $isServerOnline,
        'description' => $s['description'],
        'icon' => $s['type'] === 'ssh_script' ? '🔐' : '🚀',
        'theme' => $catTheme ?: ($tierInfo['theme'] ?? 'pink'),
        'target_customer_price' => (float)($s['target_customer_price'] ?? 0),
        'addons' => $serverAddons
    ];

    if ($s['category_id'] && isset($catServers[$s['category_id']])) {
        $catServers[$s['category_id']]['servers'][$svKey] = $svObj;
    } else {
        $uncategorized[$svKey] = $svObj;
    }
}

json_response([
    'status' => 'success',
    'data' => [
        'price_tiers' => $formattedTiers,
        'categories' => array_values($catServers),
        'uncategorized' => $uncategorized
    ]
]);
