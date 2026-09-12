<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();
$configId = (int)($data['config_id'] ?? 0);

$db = get_db();
$stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ?');
$stmt->execute([$configId, $user['id']]);
$vpn = $stmt->fetch();

if (!$vpn) {
    json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN ที่ต้องการลบ']);
}

$createdTime = strtotime($vpn['created_at']);
$elapsedSeconds = time() - $createdTime;
$isRefundable = ($elapsedSeconds <= 600) && ((float)$vpn['price_paid'] > 0);
$refundAmount = $isRefundable ? (float)$vpn['price_paid'] : 0.00;

if ($isRefundable) {
    $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$refundAmount, $user['id']]);
    $db->prepare('INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, "refund", ?, ?)')
       ->execute([$user['id'], $refundAmount, 'คืนเงิน 100% ลบไฟล์ ' . $vpn['server_name'] . ' ภายใน 10 นาที']);
}

// Delete client from 3x-ui if applicable
if (!empty($vpn['xui_email'])) {
    $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
    $sStmt->execute([$vpn['server_id']]);
    $server = $sStmt->fetch();
    if ($server && !empty($server['panel_url'])) {
        xui_delete_client($server, $vpn['xui_email']);
    }
}

// Mark deleted
$db->prepare("UPDATE vpn_configs SET status_real = 'deleted' WHERE id = ?")->execute([$configId]);

// Decrease user count
$db->prepare('UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id = ?')->execute([$vpn['server_id']]);

if ($isRefundable) {
    json_response([
        'status' => 'success',
        'message' => 'ลบไฟล์เรียบร้อยแล้ว! คืนเงิน ฿' . number_format($refundAmount, 2) . ' เข้าสู่กระเป๋าของคุณอัตโนมัติ'
    ]);
} else {
    json_response([
        'status' => 'success',
        'message' => 'ลบไฟล์เรียบร้อยแล้ว'
    ]);
}
