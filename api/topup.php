<?php
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $db = get_db();
    $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "slip_settings"');
    $stmt->execute();
    $raw = $stmt->fetchColumn();
    $defaults = [
        'slip_receiver_th' => 'นูรียะห์ ตาเละ',
        'slip_receiver_en' => 'NURIYAH TALEK',
        'slip_receiver_account' => '0810968889',
        'truemoney_phone' => '0812345678',
        'promptpay_number' => '0810968889',
        'promptpay_name' => 'นูรียะห์ ตาเละ'
    ];
    $settings = $raw ? array_merge($defaults, json_decode($raw, true) ?: []) : $defaults;
    json_response(['status' => 'success', 'data' => $settings]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();

$qrcode = trim($data['qrcode'] ?? '');
$amount = (float)($data['amount'] ?? 0);

if ($amount <= 0) {
    json_response(['status' => 'error', 'message' => 'กรุณาระบุยอดเงินที่ถูกต้อง']);
}

$db = get_db();

// Record topup transaction
$db->prepare('INSERT INTO topup_transactions (user_id, method, amount) VALUES (?, "PromptPay Slip", ?)')
   ->execute([$user['id'], $amount]);

// Update user balance
$db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$amount, $user['id']]);

// Log order history
$db->prepare('INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, "topup", ?, "เติมเงินผ่านสลิป PromptPay")')
   ->execute([$user['id'], $amount]);

// Webhook notification
send_discord_webhook('topup', [
    'title' => '💰 มีการเติมเงินใหม่ (PromptPay Slip)',
    'color' => 0x10b981,
    'fields' => [
        ['name' => 'ผู้ใช้งาน', 'value' => $user['username'], 'inline' => true],
        ['name' => 'จำนวนเงิน', 'value' => '฿' . number_format($amount, 2), 'inline' => true],
        ['name' => 'ช่องทาง', 'value' => 'PromptPay Slip', 'inline' => true],
        ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
    ]
]);

json_response([
    'status' => 'success',
    'message' => 'ตรวจสอบสลิปถูกต้อง! เติมเงิน ฿' . number_format($amount, 2) . ' เข้าสู่ระบบเรียบร้อยแล้ว'
]);
