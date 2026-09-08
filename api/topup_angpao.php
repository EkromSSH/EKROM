<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();

$link = trim($data['link'] ?? '');
if ($link === '') {
    json_response(['status' => 'error', 'message' => 'กรุณากรอกลิงก์ซองของขวัญ']);
}

// Validate link pattern
if (!preg_match('/(gift\.truemoney\.com|truemoney\.com)/i', $link) && !preg_match('/^[a-zA-Z0-9]{15,40}$/', $link)) {
    json_response(['status' => 'error', 'message' => 'ลิงก์ซองของขวัญไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง']);
}

// In real production, this integrates with TrueMoney Voucher API.
// For testing/simulation, we accept and credit a generous demo amount (e.g. 100.00 THB)
$redeemedAmount = 100.00;

$db = get_db();
$db->prepare('INSERT INTO topup_transactions (user_id, method, amount, voucher_code) VALUES (?, "TrueMoney Voucher", ?, ?)')
   ->execute([$user['id'], $redeemedAmount, substr($link, 0, 80)]);

$db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$redeemedAmount, $user['id']]);

$db->prepare('INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, "topup", ?, "เติมเงินซองของขวัญ TrueMoney")')
   ->execute([$user['id'], $redeemedAmount]);

// Discord Webhook
send_discord_webhook('topup', [
    'title' => '🧧 มีการเติมเงินใหม่ (ซองของขวัญ TrueMoney)',
    'color' => 0xf97316,
    'fields' => [
        ['name' => 'ผู้ใช้งาน', 'value' => $user['username'], 'inline' => true],
        ['name' => 'จำนวนเงิน', 'value' => '฿' . number_format($redeemedAmount, 2), 'inline' => true],
        ['name' => 'ช่องทาง', 'value' => 'TrueMoney Voucher', 'inline' => true],
        ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
    ]
]);

json_response([
    'status' => 'success',
    'message' => 'รับซองของขวัญสำเร็จ! เติมเงิน ฿' . number_format($redeemedAmount, 2) . ' เข้ากระเป๋าของคุณแล้ว 🎉'
]);
