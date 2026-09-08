<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = get_auth_user();
if ($user) {
    $userData = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'balance' => number_format((float)$user['balance'], 2, '.', '')
    ];
    json_response([
        'status' => 'logged_in',
        'user_id' => (int)$user['id'],
        'username' => $user['username'],
        'role' => $user['role'],
        'balance' => number_format((float)$user['balance'], 2, '.', ''),
        'user' => $userData
    ]);
} else {
    json_response(['status' => 'guest', 'user' => null]);
}

