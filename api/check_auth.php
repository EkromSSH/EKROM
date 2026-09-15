<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = get_auth_user();
if ($user) {
    $db = get_db();
    $passStmt = $db->prepare('SELECT password FROM users WHERE id = ?');
    $passStmt->execute([$user['id']]);
    $userPassHash = $passStmt->fetchColumn() ?: '';

    $isDefaultPassword = false;
    if ($user['role'] === 'admin' && password_verify('admin123', $userPassHash)) {
        $isDefaultPassword = true;
    } else if ($user['role'] === 'reseller' && password_verify('reseller123', $userPassHash)) {
        $isDefaultPassword = true;
    }

    $userData = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'balance' => number_format((float)$user['balance'], 2, '.', ''),
        'must_change_password' => $isDefaultPassword
    ];
    json_response([
        'status' => 'logged_in',
        'user_id' => (int)$user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'balance' => number_format((float)$user['balance'], 2, '.', ''),
        'must_change_password' => $isDefaultPassword,
        'user' => $userData
    ]);
} else {
    json_response(['status' => 'guest', 'user' => null]);
}

