<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$data = get_post_json();
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';

if ($username === '' || $password === '') {
    json_response(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน']);
}

$turnstileToken = trim($data['turnstile_token'] ?? '');
$clientIp = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
if (!verify_turnstile($turnstileToken, $clientIp)) {
    json_response(['status' => 'error', 'message' => 'กรุณายืนยันว่าคุณไม่ใช่หุ่นยนต์ให้ถูกต้อง']);
}

if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username)) {
    json_response(['status' => 'error', 'message' => 'ชื่อผู้ใช้ต้องเป็นตัวอักษรภาษาอังกฤษหรือตัวเลข 3-20 ตัวอักษร']);
}

if (strlen($password) < 4) {
    json_response(['status' => 'error', 'message' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 4 ตัวอักษร']);
}

$db = get_db();
$stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ? COLLATE NOCASE');
$stmt->execute([$username]);
if ($stmt->fetchColumn() > 0) {
    json_response(['status' => 'error', 'message' => 'ชื่อผู้ใช้นี้มีคนใช้งานแล้ว']);
}

$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $db->prepare('INSERT INTO users (username, password, role, balance) VALUES (?, ?, "user", 0.00)');
$stmt->execute([$username, $hash]);
$newId = $db->lastInsertId();

// Discord Webhook
send_discord_webhook('register', [
    'title' => '✨ มีสมาชิกใหม่สมัครใช้งาน!',
    'color' => 0x06b6d4,
    'fields' => [
        ['name' => 'ชื่อผู้ใช้', 'value' => $username, 'inline' => true],
        ['name' => 'User ID', 'value' => '#' . $newId, 'inline' => true],
        ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
    ]
]);

json_response([
    'status' => 'success',
    'message' => 'สมัครสมาชิกสำเร็จเรียบร้อยแล้ว กรุณาเข้าสู่ระบบ'
]);
