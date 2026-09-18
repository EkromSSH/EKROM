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
        $isSsh = ($s['type'] === 'ssh_script' || $s['type'] === 'udp_custom');
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

    // Delete from old server if it had xui_email or was SSH
    $oldSvStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
    $oldSvStmt->execute([$vpn['server_id']]);
    $oldSv = $oldSvStmt->fetch();
    if ($oldSv) {
        if (!empty($vpn['xui_email']) && !empty($oldSv['panel_url'])) {
            xui_delete_client($oldSv, $vpn['xui_email'], $vpn['uuid'] ?? null);
        }
        if (($oldSv['type'] === 'ssh_script' || $oldSv['type'] === 'udp_custom') && !empty($vpn['ssh_user'])) {
            ssh_vps_delete_user($oldSv, $vpn['ssh_user']);
        }
    }

    // Update config link
    $uuid = $vpn['uuid'];
    $existingCustom = '';
    if (preg_match('/^\(\s*(.*?)\s*\)\s*/u', $vpn['server_name'], $pm)) {
        $existingCustom = $pm[1];
    }
    $displayName = build_vpn_display_name($newServer['name'], $newExpiry, $existingCustom);
    $isNewSsh = ($newServer['type'] === 'ssh_script' || $newServer['type'] === 'udp_custom');
    $isNewXui = (!empty($newServer['panel_url']) && !empty($newServer['password']) && !$isNewSsh);
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
    } elseif ($isNewSsh) {
        $sshU = $newSshUser ?: ($vpn['ssh_user'] ?: 'u' . strtolower(bin2hex(random_bytes(3))));
        $sshP = $newSshPass ?: ($vpn['ssh_pass'] ?: (string)rand(100000, 999999));
        $sshPass = $sshP;
        
        $days = max(1, (int)round((strtotime($newExpiry) - time()) / 86400));
        $sshRes = ssh_vps_add_user($newServer, $sshU, $sshP, $days);
        if (!$sshRes['success']) {
            json_response(['status' => 'error', 'message' => 'ไม่สามารถสร้างบัญชีบนเซิร์ฟเวอร์ SSH ใหม่ได้: ' . ($sshRes['message'] ?? '')]);
        }
        
        $sshPayload = build_ssh_config_payload($newServer, $sshU, $sshP, $displayName);
        $newConfigLink = json_encode($sshPayload, JSON_UNESCAPED_UNICODE);
        $protocol = 'ssh';
    } else {
        $protocol = $newServer['protocol'] ?: 'vless';
        $serverType = $newServer['type'] ?? '';
        $targetAddress = !empty($newServer['domain']) ? trim($newServer['domain']) : (!empty($newServer['host']) ? trim($newServer['host']) : '127.0.0.1');
        $targetPort = (int)($newServer['port'] ?: 443);
        $gamingPort = (!empty($newServer['vless_port']) && (int)$newServer['vless_port'] > 0) ? (int)$newServer['vless_port'] : $targetPort;
        $sni = !empty($newServer['bug_host']) ? trim($newServer['bug_host']) : 'speedtest.net';
        $pbkParam = !empty($newServer['pbk']) ? '&pbk=' . urlencode($newServer['pbk']) : '';
        $sidParam = !empty($newServer['sids']) ? '&sid=' . urlencode(explode(',', $newServer['sids'])[0]) : '';

        if ($protocol === 'vmess') {
            $tlsVal = ($serverType === 'vmess_tls') ? 'tls' : 'none';
            $vPort = ($serverType === 'vmess_tls') ? $gamingPort : $targetPort;
            $vmessObj = [
                'v' => '2',
                'ps' => $displayName,
                'add' => $targetAddress,
                'port' => $vPort,
                'id' => $uuid,
                'aid' => 0,
                'scy' => 'auto',
                'net' => 'ws',
                'type' => 'none',
                'host' => $sni,
                'path' => '/',
                'tls' => $tlsVal
            ];
            if ($tlsVal !== 'none') {
                $vmessObj['sni'] = $sni;
            }
            $newConfigLink = 'vmess://' . base64_encode(json_encode($vmessObj, JSON_UNESCAPED_UNICODE));
        } else {
            $secParam = !empty($newServer['pbk']) ? 'reality' : (($serverType === 'vless_tls') ? 'tls' : 'none');
            $vPort = ($serverType === 'vless_tls' || !empty($newServer['vless_port'])) ? $gamingPort : $targetPort;
            $newConfigLink = "{$protocol}://{$uuid}@{$targetAddress}:{$vPort}?encryption=none&security={$secParam}&sni={$sni}&fp=chrome&type=grpc&serviceName=grpc{$pbkParam}{$sidParam}#" . rawurlencode($displayName);
        }
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
