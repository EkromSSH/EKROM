<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();

$configId = (int)($data['config_id'] ?? 0);
$days = (int)($data['days'] ?? 0);

if ($configId <= 0 || $days <= 0) {
    json_response(['status' => 'error', 'message' => 'ข้อมูลการต่ออายุไม่ถูกต้อง']);
}

$db = get_db();
$stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ?');
$stmt->execute([$configId, $user['id']]);
$vpn = $stmt->fetch();

if (!$vpn) {
    json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN ในระบบ']);
}

// Daily rate approx 2.50฿ per day, min 5฿
$renewPrice = max(5.00, round($days * 2.50, 2));

if ($user['balance'] < $renewPrice) {
    json_response([
        'status' => 'error',
        'message' => 'ยอดเงินไม่พอสำหรับการต่ออายุ ' . $days . ' วัน (ต้องใช้ ฿' . number_format($renewPrice, 2) . ')'
    ]);
}

// Calculate new expiry
$currentExpiry = strtotime($vpn['expiry_time']);
$baseTime = ($currentExpiry > time()) ? $currentExpiry : time();
$newExpiry = date('Y-m-d H:i:s', strtotime("+{$days} days", $baseTime));

// Deduct balance
$db->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$renewPrice, $user['id']]);

$newDisplayName = format_vpn_config_name($vpn['server_name'], $newExpiry);
$newConfigLink = update_config_link_remark($vpn['config_link'], $newDisplayName, $vpn['protocol']);

// Update VPN
$db->prepare('UPDATE vpn_configs SET server_name = ?, config_link = ?, expiry_time = ?, status_real = "active" WHERE id = ?')
   ->execute([$newDisplayName, $newConfigLink, $newExpiry, $configId]);

if (!empty($vpn['xui_email'])) {
    $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
    $sStmt->execute([$vpn['server_id']]);
    $server = $sStmt->fetch();
    if ($server && !empty($server['panel_url'])) {
        xui_update_client($server, $vpn['uuid'], $vpn['xui_email'], $newExpiry);
    }
}

// Log order
$db->prepare('INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, "renew", ?, ?)')
   ->execute([$user['id'], $renewPrice, 'ต่ออายุ ' . $vpn['server_name'] . ' +' . $days . ' วัน']);

// Discord Webhook
send_discord_webhook('renew', [
    'title' => '♻️ มีการต่ออายุ VPN!',
    'color' => 0x8b5cf6,
    'fields' => [
        ['name' => 'ผู้ใช้งาน', 'value' => $user['username'], 'inline' => true],
        ['name' => 'เซิร์ฟเวอร์', 'value' => $vpn['server_name'], 'inline' => true],
        ['name' => 'จำนวนวัน', 'value' => "+{$days} วัน", 'inline' => true],
        ['name' => 'ยอดเงิน', 'value' => '฿' . number_format($renewPrice, 2), 'inline' => true],
        ['name' => 'หมดอายุใหม่', 'value' => $newExpiry, 'inline' => true],
        ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
    ]
]);

json_response([
    'status' => 'success',
    'message' => "ต่ออายุสำเร็จ เพิ่มเวลาใช้งาน {$days} วัน เรียบร้อยแล้ว!"
]);
