<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();

$rawServerId = $data['server_id'] ?? '';
$serverId = (int)preg_replace('/[^0-9]/', '', (string)$rawServerId);
$packageVal = trim($data['package'] ?? '30');
$customName = trim($data['custom_name'] ?? '');
$trialDuration = (int)($data['trial_duration'] ?? 60);
$sshUser = trim($data['ssh_user'] ?? '');
$sshPass = trim($data['ssh_pass'] ?? '');

$res = process_vpn_creation($user, $serverId, $packageVal, $customName, $sshUser, $sshPass, $trialDuration);

if ($res['status'] !== 'success') {
    json_response($res, 400);
}

json_response($res);
