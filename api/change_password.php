<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();
$oldPass = $data['old_password'] ?? '';
$newPass = $data['new_password'] ?? '';
$newPin  = trim($data['new_pin'] ?? '');

if ($newPass === '') {
    json_response(['status' => 'error', 'message' => 'กรุณากรอกรหัสผ่านใหม่']);
}

$db = get_db();
$stmt = $db->prepare('SELECT password, admin_pin FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$userRow = $stmt->fetch();
$currentHash = $userRow['password'] ?? '';

// ตรวจสอบรหัสผ่านเดิม
if ($oldPass !== '') {
    if (!password_verify($oldPass, $currentHash)) {
        json_response(['status' => 'error', 'message' => 'รหัสผ่านเดิมไม่ถูกต้อง']);
    }
} else {
    // ถ้ารหัสผ่านเดิมเว้นว่างไว้ อนุญาตเฉพาะกรณีที่รหัสผ่านปัจจุบันยังคงเป็นรหัสเริ่มต้น
    $isDefault = password_verify('admin123', $currentHash) || password_verify('reseller123', $currentHash);
    if (!$isDefault) {
        json_response(['status' => 'error', 'message' => 'กรุณากรอกรหัสผ่านเดิม']);
    }
}

if (strlen($newPass) < 6) {
    json_response(['status' => 'error', 'message' => 'รหัสผ่านใหม่ต้องมีความยาวอย่างน้อย 6 ตัวอักษร']);
}

if ($newPass === 'admin123' || $newPass === 'reseller123') {
    json_response(['status' => 'error', 'message' => 'กรุณาตั้งรหัสผ่านใหม่ที่ไม่ใช่รหัสเริ่มต้น']);
}

$newHash = password_hash($newPass, PASSWORD_BCRYPT);
$db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$newHash, $user['id']]);

// อัปเดตรหัส PIN แอดมิน (ถ้ามีการระบุมา)
if ($newPin !== '') {
    if (!preg_match('/^\d{4,6}$/', $newPin)) {
        json_response(['status' => 'error', 'message' => 'รหัส PIN ต้องเป็นตัวเลข 4 - 6 หลัก']);
    }
    $db->prepare('UPDATE users SET admin_pin = ? WHERE id = ?')->execute([$newPin, $user['id']]);
}

json_response([
    'status' => 'success',
    'message' => 'เปลี่ยนรหัสผ่านเรียบร้อยแล้ว' . ($newPin !== '' ? ' พร้อมอัปเดตรหัส PIN ใหม่' : '')
]);
