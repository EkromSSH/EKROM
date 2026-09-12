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
$baseDisplayName = $customName ?: $server['name'];
$displayName = format_vpn_config_name($baseDisplayName, $expiryTime);

$isSsh = ($server['type'] === 'ssh_script' || $server['type'] === 'udp_custom');
$isXui = (!empty($server['panel_url']) && !empty($server['password']) && !$isSsh);
$xuiEmail = null;

if ($isXui) {
    $targetUsername = 'user';
    if ($targetUserId > 0) {
        $uStmt = $db->prepare('SELECT username FROM users WHERE id = ?');
        $uStmt->execute([$targetUserId]);
        $targetUsername = $uStmt->fetchColumn() ?: ('user' . $targetUserId);
    }
    $cleanUser = preg_replace('/[^a-zA-Z0-9]/', '', $targetUsername);
    $xuiEmail = strtolower($cleanUser ?: 'user') . '_' . substr(str_replace('-', '', $uuid), 0, 8);

    $xuiRes = xui_add_client($server, $uuid, $xuiEmail, $expiryTime, $displayName);
    if (!$xuiRes['success']) {
        json_response([
            'status' => 'error',
            'message' => 'ไม่สามารถสร้างบัญชีบนเซิร์ฟเวอร์ 3x-ui ได้: ' . ($xuiRes['message'] ?? 'เกิดข้อผิดพลาด')
        ]);
    }
    $configLink = $xuiRes['config_link'];
    $protocol = $server['protocol'] ?: 'vmess';
    $sshUser = null;
    $sshPass = null;
} elseif ($isSsh) {
    $protocol = 'ssh';
    if (!$sshUser) $sshUser = 'admin' . rand(1000, 9999);
    if (!$sshPass) $sshPass = 'pass' . rand(1000, 9999);
    
    $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '127.0.0.1');
    $targetPort = (int)($server['port'] ?: 22);

    $sshPayload = [
        'raw' => "IP: {$targetAddress}\nPort: {$targetPort}\nUsername: {$sshUser}\nPassword: {$sshPass}",
        'npv' => [
            ['name' => 'NPV Tunnel', 'config' => "npvt-ssh://{$sshUser}:{$sshPass}@{$targetAddress}:{$targetPort}#" . urlencode($displayName)]
        ],
        'netmod' => [
            ['name' => 'NetMod', 'config' => "{$targetAddress}:{$targetPort}@{$sshUser}:{$sshPass}"]
        ]
    ];
    $configLink = json_encode($sshPayload, JSON_UNESCAPED_UNICODE);
} else {
    $protocol = $server['protocol'] ?: 'vless';
    $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '127.0.0.1');
    $targetPort = (int)($server['port'] ?: 443);
    $port = ($protocol === 'vless' && !empty($server['vless_port'])) ? (int)$server['vless_port'] : $targetPort;
    $sni = !empty($server['bug_host']) ? trim($server['bug_host']) : 'speedtest.net';
    $pbkParam = !empty($server['pbk']) ? '&pbk=' . urlencode($server['pbk']) : '';
    $sidParam = !empty($server['sids']) ? '&sid=' . urlencode(explode(',', $server['sids'])[0]) : '';
    $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$port}?encryption=none&security=reality&sni={$sni}&fp=chrome&type=grpc&serviceName=grpc{$pbkParam}{$sidParam}#" . rawurlencode($displayName);
    $sshUser = null;
    $sshPass = null;
}

$packageName = $customName ? "แอดมินสร้าง ({$customName}) {$days} วัน" : "แอดมินสร้างให้ {$days} วัน";

$ins = $db->prepare("
    INSERT INTO vpn_configs (user_id, server_id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, ssh_user, ssh_pass, status_real, expiry_time, xui_email)
    VALUES (?, ?, ?, ?, ?, ?, 0.00, ?, ?, ?, ?, 'active', ?, ?)
");
$ins->execute([$targetUserId, $serverId, $uuid, $displayName, $packageName, (string)$days, $protocol, $configLink, $sshUser, $sshPass, $expiryTime, $xuiEmail]);

$db->prepare('UPDATE servers SET user_count = user_count + 1 WHERE id = ?')->execute([$serverId]);

json_response(['status' => 'success', 'message' => 'สร้าง VPN ให้ผู้ใช้สำเร็จแล้ว']);
