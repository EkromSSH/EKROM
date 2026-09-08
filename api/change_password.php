<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();
$oldPass = $data['old_password'] ?? '';
$newPass = $data['new_password'] ?? '';

if ($oldPass === '' || $newPass === '') {
    json_response(['status' => 'error', 'message' => 'กรุณากรอกรหัสผ่านเดิมและรหัสผ่านใหม่']);
}

$db = get_db();
$stmt = $db->prepare('SELECT password FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$currentHash = $stmt->fetchColumn();

if (!password_verify($oldPass, $currentHash)) {
    json_response(['status' => 'error', 'message' => 'รหัสผ่านเดิมไม่ถูกต้อง']);
}

if (strlen($newPass) < 4) {
    json_response(['status' => 'error', 'message' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 4 ตัวอักษร']);
}

$newHash = password_hash($newPass, PASSWORD_BCRYPT);
$db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$newHash, $user['id']]);

json_response(['status' => 'success', 'message' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว']);
