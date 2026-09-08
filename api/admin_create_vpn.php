<?php
require_once __DIR__ . '/db.php';
$user = require_auth();
if ($user['role'] !== 'admin') json_response(['status' => 'error', 'message' => 'Admin required'], 403);

$data = get_post_json();
$targetUserId = (int)($data['target_user_id'] ?? 0);
$rawServerId = $data['server_id'] ?? 1;
$serverId = (int)preg_replace('/[^0-9]/', '', (string)$rawServerId);
$days = (int)($data['days'] ?? $data['package'] ?? 30);
if ($days <= 0) $days = 30;

$customName = trim($data['custom_name'] ?? '');
$sshUser = trim($data['ssh_user'] ?? '');
$sshPass = trim($data['ssh_pass'] ?? '');

$db = get_db();
$stmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
$stmt->execute([$serverId]);
$server = $stmt->fetch();

if (!$server) json_response(['status' => 'error', 'message' => 'ไม่พบเซิร์ฟเวอร์']);

$uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);
$expiryTime = date('Y-m-d H:i:s', strtotime("+{$days} days"));
$displayName = $customName ?: $server['name'];

$isSsh = ($server['type'] === 'ssh_script' || $server['type'] === 'udp_custom');
if ($isSsh) {
    $protocol = 'ssh';
    if (!$sshUser) $sshUser = 'admin' . rand(1000, 9999);
    if (!$sshPass) $sshPass = 'pass' . rand(1000, 9999);
    
    $sshPayload = [
        'raw' => "IP: {$server['host']}\nPort: {$server['port']}\nUsername: {$sshUser}\nPassword: {$sshPass}",
        'npv' => [
            ['name' => 'NPV Tunnel', 'config' => "npvt-ssh://{$sshUser}:{$sshPass}@{$server['host']}:{$server['port']}#" . urlencode($displayName)]
        ],
        'netmod' => [
            ['name' => 'NetMod', 'config' => "{$server['host']}:{$server['port']}@{$sshUser}:{$sshPass}"]
        ]
    ];
    $configLink = json_encode($sshPayload, JSON_UNESCAPED_UNICODE);
} else {
    $protocol = $server['protocol'] ?: 'vless';
    $host = $server['domain'] ?: $server['host'];
    $port = $server['vless_port'] ?: $server['port'] ?: 443;
    $configLink = "{$protocol}://{$uuid}@{$host}:{$port}?encryption=none&security=reality&sni=speedtest.net&fp=chrome&type=grpc&serviceName=grpc#" . rawurlencode($displayName);
    $sshUser = null;
    $sshPass = null;
}

$packageName = $customName ? "แอดมินสร้าง ({$customName}) {$days} วัน" : "แอดมินสร้างให้ {$days} วัน";

$ins = $db->prepare("
    INSERT INTO vpn_configs (user_id, server_id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, ssh_user, ssh_pass, status_real, expiry_time)
    VALUES (?, ?, ?, ?, ?, ?, 0.00, ?, ?, ?, ?, 'active', ?)
");
$ins->execute([$targetUserId, $serverId, $uuid, $server['name'], $packageName, (string)$days, $protocol, $configLink, $sshUser, $sshPass, $expiryTime]);

$db->prepare('UPDATE servers SET user_count = user_count + 1 WHERE id = ?')->execute([$serverId]);

json_response(['status' => 'success', 'message' => 'สร้าง VPN ให้ผู้ใช้สำเร็จแล้ว']);
