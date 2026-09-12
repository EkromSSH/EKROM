<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$data = get_post_json();
$username = trim($data['username'] ?? '');
$password = $data['password'] ?? '';
$rememberMe = !empty($data['remember_me']);

if ($username === '' || $password === '') {
    json_response(['status' => 'error', 'message' => 'กรุณากรอกชื่อผู้ใช้และรหัสผ่าน']);
}

$turnstileToken = trim($data['turnstile_token'] ?? '');
$clientIp = $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
if (!verify_turnstile($turnstileToken, $clientIp)) {
    json_response(['status' => 'error', 'message' => 'กรุณายืนยันว่าคุณไม่ใช่หุ่นยนต์ให้ถูกต้อง']);
}

$db = get_db();
$stmt = $db->prepare('SELECT * FROM users WHERE username = ? COLLATE NOCASE');
$stmt->execute([$username]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    json_response(['status' => 'error', 'message' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง']);
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

if ($rememberMe) {
    // Set 30-day session cookie
    session_set_cookie_params(86400 * 30);
}

// Discord Webhook
send_discord_webhook('login', [
    'title' => '🔑 สมาชิกเข้าสู่ระบบ',
    'color' => 0x64748b,
    'fields' => [
        ['name' => 'ชื่อผู้ใช้', 'value' => $user['username'], 'inline' => true],
        ['name' => 'ตำแหน่ง', 'value' => $user['role'], 'inline' => true],
        ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
    ]
]);

json_response([
    'status' => 'success',
    'message' => 'เข้าสู่ระบบสำเร็จ!',
    'role' => $user['role'],
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'role' => $user['role']
    ]
]);
