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
    // 1. กำหนดอายุ Session Cookie 30 วัน
    $cookieParams = session_get_cookie_params();
    setcookie(session_name(), session_id(), [
        'expires' => time() + (86400 * 30),
        'path' => $cookieParams['path'] ?: '/',
        'domain' => $cookieParams['domain'] ?: '',
        'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    // 2. สร้าง Persistent Token เก็บใน Database ป้องกันกรณี PHP Session โดนระบบคลีนทิ้ง
    try {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expires = date('Y-m-d H:i:s', time() + (86400 * 30));

        $stmt = $db->prepare('INSERT INTO user_remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)');
        $stmt->execute([$user['id'], $tokenHash, $expires]);

        setcookie('ekrom_remember_token', $token, [
            'expires' => time() + (86400 * 30),
            'path' => '/',
            'secure' => (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } catch (\Throwable $e) {}
} else {
    // หากไม่ติ๊ก Remember me ให้ลบ Token เดิมออกถ้ามี
    if (!empty($_COOKIE['ekrom_remember_token'])) {
        try {
            $tokenHash = hash('sha256', $_COOKIE['ekrom_remember_token']);
            $stmt = $db->prepare('DELETE FROM user_remember_tokens WHERE token_hash = ?');
            $stmt->execute([$tokenHash]);
        } catch (\Throwable $e) {}
        setcookie('ekrom_remember_token', '', [
            'expires' => time() - 42000,
            'path' => '/'
        ]);
    }
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

$isDefaultPassword = false;
if ($user['role'] === 'admin' && password_verify('admin123', $user['password'])) {
    $isDefaultPassword = true;
} else if ($user['role'] === 'reseller' && password_verify('reseller123', $user['password'])) {
    $isDefaultPassword = true;
}

json_response([
    'status' => 'success',
    'message' => 'เข้าสู่ระบบสำเร็จ!',
    'role' => $user['role'],
    'must_change_password' => $isDefaultPassword,
    'user' => [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'role' => $user['role']
    ]
]);
