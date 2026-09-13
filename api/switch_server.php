<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();
$action = $data['action'] ?? 'get_options';
$configId = (int)($data['config_id'] ?? 0);

$db = get_db();
$stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ? AND status_real != "deleted"');
$stmt->execute([$configId, $user['id']]);
$vpn = $stmt->fetch();

if (!$vpn) {
    json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN ที่ต้องการ']);
}

if ($action === 'get_options') {
    $now = time();
    $expiry = strtotime($vpn['expiry_time']);
    $remainingSec = max(0, $expiry - $now);
    $remainingDays = round($remainingSec / 86400, 2);

    $servers = $db->query('SELECT id, name, type, target_customer_price, is_active FROM servers WHERE is_active = 1')->fetchAll();
    $serverOptions = [];
    foreach ($servers as $s) {
        $isSsh = ($s['type'] === 'ssh_script');
        $isSame = ($s['id'] == $vpn['server_id']);
        $serverOptions[] = [
            'id' => (int)$s['id'],
            'key' => 'sv' . $s['id'],
            'name' => $s['name'],
            'is_ssh' => $isSsh,
            'can_move' => !$isSame,
            'reason' => $isSame ? 'เซิร์ฟเวอร์เดิม' : '',
            'target_customer_price' => (float)$s['target_customer_price'],
            'requires_ssh_credentials' => $isSsh && ($vpn['protocol'] !== 'ssh')
        ];
    }

    json_response([
        'status' => 'success',
        'data' => [
            'source' => [
                'server_name' => $vpn['server_name'],
                'package_name' => $vpn['package_name'],
                'remaining_days' => $remainingDays,
                'is_paid' => ((float)$vpn['price_paid'] > 0)
            ],
            'servers' => $serverOptions
        ]
    ]);
} elseif ($action === 'migrate' || $action === 'switch') {
    $rawNewSv = $data['new_server_id'] ?? '';
    $newSvId = (int)preg_replace('/[^0-9]/', '', (string)$rawNewSv);
    $newSshUser = trim($data['new_ssh_user'] ?? '');
    $newSshPass = trim($data['new_ssh_pass'] ?? '');

    $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND is_active = 1');
    $sStmt->execute([$newSvId]);
    $newServer = $sStmt->fetch();

    if (!$newServer) {
        json_response(['status' => 'error', 'message' => 'ไม่พบเซิร์ฟเวอร์ปลายทาง']);
    }

    $salePrice = (float)($data['sale_price'] ?? 0);
    $targetPrice = (float)($newServer['target_customer_price'] ?? 0);
    $newExpiry = $vpn['expiry_time'];

    if ((float)$vpn['price_paid'] > 0 && $salePrice > 0 && $targetPrice > 0) {
        $remainingSec = max(0, strtotime($vpn['expiry_time']) - time());
        $convertedSec = (int)round($remainingSec * ($salePrice / $targetPrice));
        $newExpiry = date('Y-m-d H:i:s', time() + $convertedSec);
    }

    // Delete from old server if it had xui_email
    if (!empty($vpn['xui_email'])) {
        $oldSvStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
        $oldSvStmt->execute([$vpn['server_id']]);
        $oldSv = $oldSvStmt->fetch();
        if ($oldSv && !empty($oldSv['panel_url'])) {
            xui_delete_client($oldSv, $vpn['xui_email']);
        }
    }

    // Update config link
    $uuid = $vpn['uuid'];
    $displayName = format_vpn_config_name($newServer['name'], $newExpiry);
    $isNewXui = (!empty($newServer['panel_url']) && !empty($newServer['password']) && $newServer['type'] !== 'ssh_script');
    $newXuiEmail = null;

    if ($isNewXui) {
        $newXuiEmail = xui_make_client_email($displayName);
        $xuiRes = xui_add_client($newServer, $uuid, $newXuiEmail, $newExpiry, $displayName);
        if (!$xuiRes['success']) {
            json_response(['status' => 'error', 'message' => 'ไม่สามารถสร้างบัญชีบนเซิร์ฟเวอร์ใหม่ได้: ' . ($xuiRes['message'] ?? '')]);
        }
        $newXuiEmail = $xuiRes['email'];
        $newConfigLink = $xuiRes['config_link'];
        $protocol = $newServer['protocol'] ?: 'vmess';
        $sshU = null;
        $sshP = null;
    } elseif ($newServer['type'] === 'ssh_script') {
        $sshU = $newSshUser ?: ($vpn['ssh_user'] ?: 'user' . rand(1000, 9999));
        $sshP = $newSshPass ?: ($vpn['ssh_pass'] ?: 'pass' . rand(1000, 9999));
        $sshPass = $sshP;
        $targetAddress = !empty($newServer['domain']) ? trim($newServer['domain']) : (!empty($newServer['host']) ? trim($newServer['host']) : '127.0.0.1');
        $targetPort = (int)($newServer['port'] ?: 22);

        $sshPayload = [
            'raw' => "IP: {$targetAddress}\nPort: {$targetPort}\nUser: {$sshU}\nPass: {$sshP}",
            'npv' => [
                ['name' => 'NPV Tunnel', 'config' => "npvt-ssh://{$sshU}:{$sshPass}@{$targetAddress}:{$targetPort}#" . urlencode($displayName)]
            ],
            'netmod' => [
                ['name' => 'NetMod', 'config' => "{$targetAddress}:{$targetPort}@{$sshU}:{$sshPass}"]
            ]
        ];
        $newConfigLink = json_encode($sshPayload, JSON_UNESCAPED_UNICODE);
        $protocol = 'ssh';
    } else {
        $protocol = $newServer['protocol'] ?: 'vless';
        $targetAddress = !empty($newServer['domain']) ? trim($newServer['domain']) : (!empty($newServer['host']) ? trim($newServer['host']) : '127.0.0.1');
        $targetPort = (int)($newServer['port'] ?: 443);
        $port = ($protocol === 'vless' && !empty($newServer['vless_port'])) ? (int)$newServer['vless_port'] : $targetPort;
        $sni = !empty($newServer['bug_host']) ? trim($newServer['bug_host']) : 'speedtest.net';
        $pbkParam = !empty($newServer['pbk']) ? '&pbk=' . urlencode($newServer['pbk']) : '';
        $sidParam = !empty($newServer['sids']) ? '&sid=' . urlencode(explode(',', $newServer['sids'])[0]) : '';
        $newConfigLink = "{$protocol}://{$uuid}@{$targetAddress}:{$port}?encryption=none&security=reality&sni={$sni}&fp=chrome&type=grpc&serviceName=grpc{$pbkParam}{$sidParam}#" . rawurlencode($displayName);
        $sshU = null;
        $sshP = null;
    }

    $upd = $db->prepare("
        UPDATE vpn_configs 
        SET server_id = ?, server_name = ?, protocol = ?, config_link = ?, ssh_user = ?, ssh_pass = ?, expiry_time = ?, xui_email = ?
        WHERE id = ?
    ");
    $upd->execute([$newSvId, $displayName, $protocol, $newConfigLink, $sshU, $sshP, $newExpiry, $newXuiEmail, $configId]);

    // Update counts
    $db->prepare('UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id = ?')->execute([$vpn['server_id']]);
    $db->prepare('UPDATE servers SET user_count = user_count + 1 WHERE id = ?')->execute([$newSvId]);

    json_response(['status' => 'success', 'message' => 'ย้ายเซิร์ฟเวอร์เรียบร้อยแล้ว! ข้อมูล Config ได้รับการอัปเดต']);
}

json_response(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง'], 400);
