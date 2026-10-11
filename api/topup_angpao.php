<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/slipok.php';
require_once __DIR__ . '/truemoney.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();

$link = trim($data['link'] ?? '');
if ($link === '') {
    json_response(['status' => 'error', 'message' => 'กรุณากรอกลิงก์ซองของขวัญ']);
}

$voucherCode = extract_truemoney_voucher_code($link);
if (!$voucherCode) {
    json_response(['status' => 'error', 'message' => 'ลิงก์ซองของขวัญไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง']);
}

$db = get_db();

// ป้องกันการเติมซ้ำจาก DB
$checkStmt = $db->prepare('SELECT id FROM topup_transactions WHERE voucher_code = ? OR voucher_code LIKE ? LIMIT 1');
$checkStmt->execute([$voucherCode, '%' . $voucherCode . '%']);
if ($checkStmt->fetch()) {
    json_response(['status' => 'error', 'message' => 'ซองของขวัญนี้ถูกใช้งานเติมเงินในระบบไปแล้ว']);
}

// ดึงเบอร์ TrueMoney ของเจ้าของร้าน
$slipSettings = get_slip_settings();
$phone = trim($slipSettings['truemoney_phone'] ?? '');
if (empty($phone)) {
    json_response(['status' => 'error', 'message' => 'ระบบยังไม่ได้ตั้งค่าเบอร์ TrueMoney สำหรับรับเงิน กรุณาติดต่อแอดมิน']);
}

// ทำการ Redeem ซองผ่าน TrueMoney Gateway
$redeemRes = redeem_truemoney_voucher($voucherCode, $phone);
if (!$redeemRes['success']) {
    json_response(['status' => 'error', 'message' => $redeemRes['message']]);
}

$redeemedAmount = floatval($redeemRes['amount']);
if ($redeemedAmount <= 0) {
    json_response(['status' => 'error', 'message' => 'ยอดเงินในซองของขวัญไม่ถูกต้อง']);
}

$nowStr = date('Y-m-d H:i:s');

// บันทึกธุรกรรมและเพิ่มยอดเงินเข้ากระเป๋าผู้ใช้จริง
$db->beginTransaction();
try {
    $db->prepare('INSERT INTO topup_transactions (user_id, method, amount, voucher_code, created_at) VALUES (?, "TrueMoney Voucher", ?, ?, ?)')
       ->execute([$user['id'], $redeemedAmount, $voucherCode, $nowStr]);

    $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$redeemedAmount, $user['id']]);

    $db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "topup", ?, ?, ?)')
       ->execute([$user['id'], $redeemedAmount, "เติมเงินซองของขวัญ TrueMoney (฿" . number_format($redeemedAmount, 2) . ")", $nowStr]);

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    json_response(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage()], 500);
}

// Discord Webhook
send_discord_webhook('topup', [
    'title' => '🧧 มีการเติมเงินใหม่ (ซองของขวัญ TrueMoney)',
    'color' => 0xf97316,
    'fields' => [
        ['name' => 'ผู้ใช้งาน', 'value' => $user['username'], 'inline' => true],
        ['name' => 'จำนวนเงิน', 'value' => '฿' . number_format($redeemedAmount, 2), 'inline' => true],
        ['name' => 'รหัสซอง', 'value' => $voucherCode, 'inline' => true],
        ['name' => 'ช่องทาง', 'value' => 'TrueMoney Voucher', 'inline' => true],
        ['name' => 'เวลา', 'value' => $nowStr, 'inline' => false]
    ]
]);

json_response([
    'status' => 'success',
    'amount' => $redeemedAmount,
    'message' => 'รับซองของขวัญสำเร็จ! เติมเงิน ฿' . number_format($redeemedAmount, 2) . ' เข้ากระเป๋าของคุณแล้ว 🎉'
]);
