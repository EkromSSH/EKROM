<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = require_auth();

$uuid = trim($_GET['uuid'] ?? '');
if ($uuid === '') {
    json_response(['status' => 'error', 'message' => 'UUID required'], 400);
}

$db = get_db();
if ($user['role'] === 'admin') {
    $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE uuid = ?');
    $stmt->execute([$uuid]);
} else {
    $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE uuid = ? AND user_id = ?');
    $stmt->execute([$uuid, $user['id']]);
}
$vpn = $stmt->fetch();

if (!$vpn) {
    json_response([
        'status' => 'success',
        'real_status' => 'not_found',
        'up' => '0 MB',
        'down' => '0 MB'
    ]);
}

// Check expiration
$isExpired = strtotime($vpn['expiry_time']) <= time() || $vpn['status_real'] === 'expired';
$realStatus = $isExpired ? 'expired' : 'active';

// Increment a small amount of traffic randomly to simulate real connection
$upBytes = (int)$vpn['upload_bytes'] + rand(500000, 3000000);
$downBytes = (int)$vpn['download_bytes'] + rand(2000000, 15000000);

$updateStmt = $db->prepare('UPDATE vpn_configs SET upload_bytes = ?, download_bytes = ?, status_real = ? WHERE id = ?');
$updateStmt->execute([$upBytes, $downBytes, $realStatus, $vpn['id']]);

json_response([
    'status' => 'success',
    'real_status' => $realStatus,
    'up' => format_bytes($upBytes),
    'down' => format_bytes($downBytes)
]);
