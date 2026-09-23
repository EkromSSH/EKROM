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
$basePrice = max(5.00, round($days * 2.50, 2));
$isReseller = (isset($user['role']) && $user['role'] === 'reseller');
$renewPrice = $basePrice;
$discountText = '';

if ($isReseller) {
    $renewPrice = round($basePrice * 0.70, 2);
    $discountText = ' [ส่วนลดตัวแทน 30%]';
}

if ($user['balance'] < $renewPrice) {
    json_response([
        'status' => 'error',
        'message' => 'ยอดเงินไม่พอสำหรับการต่ออายุ ' . $days . ' วัน (ต้องใช้ ฿' . number_format($renewPrice, 2) . ')'
    ]);
}

// Calculate new expiry
$currentExpiry = strtotime($vpn['expiry_time']);
$baseTime = ($currentExpiry > time()) ? $currentExpiry : time();
$newExpiry = date('Y-m-d 23:59:59', strtotime("+{$days} days", $baseTime));

// Deduct balance
$db->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$renewPrice, $user['id']]);

$newDisplayName = format_vpn_config_name($vpn['server_name'], $newExpiry);
$newConfigLink = update_config_link_remark($vpn['config_link'], $newDisplayName, $vpn['protocol']);

// Update VPN
$newPkgVal = is_numeric($vpn['package_val']) ? (string)((int)$vpn['package_val'] + $days) : $vpn['package_val'];
$db->prepare('UPDATE vpn_configs SET server_name = ?, config_link = ?, expiry_time = ?, price_paid = price_paid + ?, package_val = ?, status_real = "active" WHERE id = ?')
   ->execute([$newDisplayName, $newConfigLink, $newExpiry, $renewPrice, $newPkgVal, $configId]);

$sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
$sStmt->execute([$vpn['server_id']]);
$server = $sStmt->fetch();

if ($server) {
    if (!empty($vpn['xui_email']) && !empty($server['panel_url'])) {
        $newXuiEmail = xui_make_client_email($newDisplayName);
        $updRes = xui_update_client($server, $vpn['uuid'], $vpn['xui_email'], $newExpiry, $newXuiEmail);
        if ($updRes && !empty($updRes['email'])) {
            $db->prepare('UPDATE vpn_configs SET xui_email = ? WHERE id = ?')->execute([$updRes['email'], $configId]);
        }
    } elseif (in_array($server['type'], ['ssh_script', 'udp_custom'], true) && !empty($vpn['ssh_user'])) {
        require_once __DIR__ . '/ssh_vps.php';
        $daysRemaining = max(1, (int)round((strtotime($newExpiry) - time()) / 86400));
        ssh_vps_renew_user($server, $vpn['ssh_user'], $daysRemaining);
    }
}

// Log order
$orderDesc = 'ต่ออายุ ' . $vpn['server_name'] . ' +' . $days . ' วัน' . $discountText;
$db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "renew", ?, ?, ?)')
   ->execute([$user['id'], $renewPrice, $orderDesc, date('Y-m-d H:i:s')]);

// Discord Webhook
$priceWebhook = '฿' . number_format($renewPrice, 2) . ($isReseller ? ' (ลด 30% ตัวแทน)' : '');
send_discord_webhook('renew', [
    'title' => '♻️ มีการต่ออายุ VPN!' . ($isReseller ? ' [ตัวแทนจำหน่าย]' : ''),
    'color' => 0x8b5cf6,
    'fields' => [
        ['name' => 'ผู้ใช้งาน', 'value' => $user['username'] . ($isReseller ? ' (Reseller)' : ''), 'inline' => true],
        ['name' => 'เซิร์ฟเวอร์', 'value' => $vpn['server_name'], 'inline' => true],
        ['name' => 'จำนวนวัน', 'value' => "+{$days} วัน", 'inline' => true],
        ['name' => 'ยอดเงิน', 'value' => $priceWebhook, 'inline' => true],
        ['name' => 'หมดอายุใหม่', 'value' => $newExpiry, 'inline' => true],
        ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
    ]
]);

$successMsg = "ต่ออายุสำเร็จ เพิ่มเวลาใช้งาน {$days} วัน เรียบร้อยแล้ว!" . ($isReseller ? " (หัก ฿" . number_format($renewPrice, 2) . " ลด 30% ตัวแทน)" : "");
json_response([
    'status' => 'success',
    'message' => $successMsg
]);
