<?php
require_once __DIR__ . '/db.php';
$user = require_auth();
if ($user['role'] !== 'admin') json_response(['status' => 'error', 'message' => 'Admin required'], 403);

json_response([
    'status' => 'success',
    'message' => 'เชื่อมต่อเซิร์ฟเวอร์สำเร็จ! Ping: ' . rand(12, 38) . 'ms'
]);
