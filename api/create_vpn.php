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

$displayName = $customName !== '' ? $customName : $server['name'];

// Generate Config Link
if ($server['type'] === 'ssh_script') {
    if ($sshUser === '') $sshUser = 'user' . rand(1000, 9999);
    if ($sshPass === '') $sshPass = 'pass' . rand(1000, 9999);
    
    $sshPayload = [
        'raw' => "IP: {$server['host']}
Port: {$server['port']}
User: {$sshUser}
Pass: {$sshPass}",
        'npv' => [
            [
                'name' => 'NPV Tunnel (Direct SSL)',
                'config' => "npvt-ssh://{$sshUser}:{$sshPass}@{$server['host']}:{$server['port']}#" . urlencode($displayName)
            ]
        ],
        'netmod' => [
            [
                'name' => 'NetMod Websocket',
                'config' => "{$server['host']}:{$server['port']}@{$sshUser}:{$sshPass}"
            ]
        ]
    ];
    $configLink = json_encode($sshPayload, JSON_UNESCAPED_UNICODE);
} else {
    // V2Ray / VLESS Reality
    $protocol = $server['protocol'] ?: 'vless';
    $configLink = "{$protocol}://{$uuid}@{$server['host']}:{$server['port']}?encryption=none&security=reality&sni=speedtest.net&fp=chrome&type=grpc&serviceName=grpc#" . rawurlencode($displayName);
}

// Deduct balance
$db->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$price, $user['id']]);

// Insert VPN Config
$stmt = $db->prepare("
    INSERT INTO vpn_configs (user_id, server_id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, ssh_user, ssh_pass, status_real, expiry_time)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)
");
$stmt->execute([
    $user['id'], $serverId, $uuid, $server['name'], $packageName, $packageVal, $price,
    $server['protocol'] ?: 'vless', $configLink, $sshUser, $sshPass, $expiryTime
]);

// Log order
$db->prepare('INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, "buy", ?, ?)')
   ->execute([$user['id'], $price, 'ซื้อ ' . $server['name'] . ' (' . $packageName . ')']);

// Increase user count
$db->prepare('UPDATE servers SET user_count = user_count + 1 WHERE id = ?')->execute([$serverId]);

// Discord Webhook
send_discord_webhook('buy', [
    'title' => '🛒 มีการสั่งซื้อ VPN ใหม่!',
    'color' => 0x3b82f6,
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
