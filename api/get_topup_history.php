<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = require_auth();

$db = get_db();
$stmt = $db->prepare('SELECT id, method, amount, created_at FROM topup_transactions WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([$user['id']]);
$list = $stmt->fetchAll();

json_response([
    'status' => 'success',
    'data' => $list
]);
