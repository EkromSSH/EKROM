<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = require_auth();

json_response([
    'status' => 'success',
    'username' => $user['username'],
    'balance' => number_format((float)$user['balance'], 2, '.', ''),
    'role' => $user['role']
]);
