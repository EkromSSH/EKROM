<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();

$configId = (int)($data['config_id'] ?? 0);
$customName = trim($data['custom_name'] ?? '');

$res = process_vpn_rename($user, $configId, $customName);
if ($res['status'] !== 'success') {
    json_response($res, 400);
}

json_response($res);
