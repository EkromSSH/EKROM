<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = require_auth();

$db = get_db();
// Auto mark expired if expiry_time passed and status is still active
$db->exec("UPDATE vpn_configs SET status_real = 'expired' WHERE expiry_time < datetime('now') AND status_real = 'active'");

$stmt = $db->prepare("
    SELECT id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, 
           ssh_user, ssh_pass, upload_bytes, download_bytes, status_real, created_at, expiry_time
    FROM vpn_configs 
    WHERE user_id = ? AND status_real != 'deleted'
    ORDER BY id DESC
");
$stmt->execute([$user['id']]);
$configs = $stmt->fetchAll();

json_response([
    'status' => 'success',
    'data' => $configs
]);
