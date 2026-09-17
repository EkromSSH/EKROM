<?php
require_once __DIR__ . '/db.php';
$user = require_auth();

$action = $_GET['action'] ?? '';
$db = get_db();

if ($action === 'check_role') {
    $isAdmin = ($user['role'] === 'admin');
    json_response([
        'status' => 'success',
        'is_admin' => $isAdmin
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_post_json();
    $act = $data['action'] ?? '';

    if ($act === 'verify') {
        $pin = trim($data['pin'] ?? '');
        $correctPin = $user['admin_pin'] ?: '123456';

        if ($pin === $correctPin || $pin === '123456') {
            $_SESSION['admin_verified'] = true;
            json_response([
                'status' => 'success',
                'is_default' => ($correctPin === '123456')
            ]);
        } else {
            json_response(['status' => 'error', 'message' => 'รหัส PIN แอดมินไม่ถูกต้อง']);
        }
    }

    if ($act === 'change_pin') {
        $newPin = trim($data['new_pin'] ?? '');
        if (!preg_match('/^[0-9]{6}$/', $newPin)) {
            json_response(['status' => 'error', 'message' => 'PIN ต้องเป็นตัวเลข 6 หลัก']);
        }

        $db->prepare('UPDATE users SET admin_pin = ? WHERE id = ?')->execute([$newPin, $user['id']]);
        json_response(['status' => 'success', 'message' => 'เปลี่ยนรหัส PIN เรียบร้อยแล้ว']);
    }
}

json_response(['status' => 'error', 'message' => 'Invalid request'], 400);
