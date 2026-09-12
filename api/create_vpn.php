<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();

$rawServerId = $data['server_id'] ?? '';
$serverId = (int)preg_replace('/[^0-9]/', '', (string)$rawServerId);
$packageVal = trim($data['package'] ?? '30');
$customName = trim($data['custom_name'] ?? '');
$trialDuration = (int)($data['trial_duration'] ?? 60);
if ($trialDuration < 1 || $trialDuration > 60) $trialDuration = 60;
$sshUser = trim($data['ssh_user'] ?? '');
$sshPass = trim($data['ssh_pass'] ?? '');

$db = get_db();
$stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND is_active = 1');
$stmt->execute([$serverId]);
$server = $stmt->fetch();

if (!$server) {
    json_response(['status' => 'error', 'message' => 'ไม่พบเซิร์ฟเวอร์ที่เลือก หรือเซิร์ฟเวอร์ปิดปรับปรุง']);
}

// Check Tier Prices
$tierPrices = [5, 25, 45, 80]; // fallback [1d, 7d, 15d, 30d]
if ($server['tier_id']) {
    $tStmt = $db->prepare('SELECT prices FROM price_tiers WHERE id = ?');
    $tStmt->execute([$server['tier_id']]);
    $tierJson = $tStmt->fetchColumn();
    if ($tierJson) {
        $arr = json_decode($tierJson, true);
        if (is_array($arr) && count($arr) >= 4) $tierPrices = $arr;
    }
}

// Calculate Price and Duration
$price = 0.00;
$days = 0;
$packageName = '';

if ($packageVal === 'trial') {
    $price = 0.00;
    $days = 0;
    $packageName = 'ทดลองใช้งาน ' . $trialDuration . ' นาที';
} elseif ($packageVal === '1') {
    $price = (float)$tierPrices[0];
    $days = 1;
    $packageName = 'แพ็กเกจ 1 วัน';
} elseif ($packageVal === '7') {
    $price = (float)$tierPrices[1];
    $days = 7;
    $packageName = 'แพ็กเกจ 7 วัน';
} elseif ($packageVal === '15') {
    $price = (float)$tierPrices[2];
    $days = 15;
    $packageName = 'แพ็กเกจ 15 วัน';
} else {
    // 30 days default
    $price = (float)$tierPrices[3];
    $days = 30;
    $packageName = 'แพ็กเกจ 30 วัน';
}

if ($user['balance'] < $price) {
    json_response([
        'status' => 'error', 
        'message' => 'ยอดเงินคงเหลือไม่พอ (ขาดอีก ฿' . number_format($price - $user['balance'], 2) . ') กรุณาเติมเงิน'
    ]);
}

// Generate UUID
$uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

// Expiry
if ($packageVal === 'trial') {
    $expiryTime = date('Y-m-d H:i:s', strtotime("+{$trialDuration} minutes"));
} else {
    $expiryTime = date('Y-m-d H:i:s', strtotime("+{$days} days"));
}

$baseDisplayName = $customName !== '' ? $customName : $server['name'];
$displayName = format_vpn_config_name($baseDisplayName, $expiryTime);

// Check if 3x-ui server
$isXui = (!empty($server['panel_url']) && !empty($server['password']) && $server['type'] !== 'ssh_script');
$xuiEmail = null;

// Generate Config Link
if ($isXui) {
    $cleanUser = preg_replace('/[^a-zA-Z0-9]/', '', $user['username'] ?? '');
    if (empty($cleanUser)) $cleanUser = 'user' . $user['id'];
    $xuiEmail = strtolower($cleanUser) . '_' . substr(str_replace('-', '', $uuid), 0, 8);
    
    $xuiRes = xui_add_client($server, $uuid, $xuiEmail, $expiryTime, $displayName);
    if (!$xuiRes['success']) {
        json_response([
            'status' => 'error',
            'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อสร้างบัญชีได้: ' . ($xuiRes['message'] ?? 'เกิดข้อผิดพลาด')
        ]);
    }
    $configLink = $xuiRes['config_link'];
} elseif ($server['type'] === 'ssh_script') {
    if ($sshUser === '') $sshUser = 'user' . rand(1000, 9999);
    if ($sshPass === '') $sshPass = 'pass' . rand(1000, 9999);
    
    $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '127.0.0.1');
    $targetPort = (int)($server['port'] ?: 22);

    $sshPayload = [
        'raw' => "IP: {$targetAddress}
Port: {$targetPort}
User: {$sshUser}
Pass: {$sshPass}",
        'npv' => [
            [
                'name' => 'NPV Tunnel (Direct SSL)',
                'config' => "npvt-ssh://{$sshUser}:{$sshPass}@{$targetAddress}:{$targetPort}#" . urlencode($displayName)
            ]
        ],
        'netmod' => [
            [
                'name' => 'NetMod Websocket',
                'config' => "{$targetAddress}:{$targetPort}@{$sshUser}:{$sshPass}"
            ]
        ]
    ];
    $configLink = json_encode($sshPayload, JSON_UNESCAPED_UNICODE);
} else {
    // V2Ray / VLESS Reality fallback
    $protocol = $server['protocol'] ?: 'vless';
    $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '127.0.0.1');
    $targetPort = (int)($server['port'] ?: 443);
    $vPort = ($protocol === 'vless' && !empty($server['vless_port'])) ? (int)$server['vless_port'] : $targetPort;
    $sni = !empty($server['bug_host']) ? trim($server['bug_host']) : 'speedtest.net';
    $pbkParam = !empty($server['pbk']) ? '&pbk=' . urlencode($server['pbk']) : '';
    $sidParam = !empty($server['sids']) ? '&sid=' . urlencode(explode(',', $server['sids'])[0]) : '';
    $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$vPort}?encryption=none&security=reality&sni={$sni}&fp=chrome&type=grpc&serviceName=grpc{$pbkParam}{$sidParam}#" . rawurlencode($displayName);
}

// Deduct balance
$db->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$price, $user['id']]);

// Insert VPN Config
$actualProtocol = $server['protocol'] ?: ($isXui ? 'vmess' : 'vless');
$stmt = $db->prepare("
    INSERT INTO vpn_configs (user_id, server_id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, ssh_user, ssh_pass, status_real, expiry_time, xui_email)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?)
");
$stmt->execute([
    $user['id'], $serverId, $uuid, $displayName, $packageName, $packageVal, $price,
    $actualProtocol, $configLink, $sshUser, $sshPass, $expiryTime, $xuiEmail
]);

// Log order
$db->prepare('INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, "buy", ?, ?)')
   ->execute([$user['id'], $price, 'ซื้อ ' . $server['name'] . ' (' . $packageName . ')']);

// Increase user count
$db->prepare('UPDATE servers SET user_count = user_count + 1 WHERE id = ?')->execute([$serverId]);

// Discord Webhook
send_discord_webhook('buy', [
    'title' => '🛒 มีการสั่งซื้อ VPN ใหม่!',
    'color' => 0xdb2777,
    'fields' => [
        ['name' => 'ผู้ซื้อ', 'value' => $user['username'], 'inline' => true],
        ['name' => 'เซิร์ฟเวอร์', 'value' => $server['name'], 'inline' => true],
        ['name' => 'แพ็กเกจ', 'value' => $packageName, 'inline' => true],
        ['name' => 'ราคา', 'value' => '฿' . number_format($price, 2), 'inline' => true],
        ['name' => 'หมดอายุ', 'value' => $expiryTime, 'inline' => true],
        ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
    ]
]);

json_response([
    'status' => 'success',
    'message' => 'สั่งซื้อและสร้างไฟล์ VPN สำเร็จเรียบร้อยแล้ว! 🎉'
]);
