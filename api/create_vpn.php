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
if ($trialDuration < 1 || $trialDuration > 60) $trialDuration = 60;
$sshUser = trim($data['ssh_user'] ?? '');
$sshPass = trim($data['ssh_pass'] ?? '');

$db = get_db();
$stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND is_active = 1');
$stmt->execute([$serverId]);
$server = $stmt->fetch();

if (!$server) {
    json_response(['status' => 'error', 'message' => 'ไม่พบเซิร์ฟเวอร์ที่เลือก หรือเซิร์ฟเวอร์ปิดปรับปรุง']);
}

// Check Tier Prices
$tierPrices = [5, 25, 45, 80]; // fallback [1d, 7d, 15d, 30d]
if ($server['tier_id']) {
    $tStmt = $db->prepare('SELECT prices FROM price_tiers WHERE id = ?');
    $tStmt->execute([$server['tier_id']]);
    $tierJson = $tStmt->fetchColumn();
    if ($tierJson) {
        $arr = json_decode($tierJson, true);
        if (is_array($arr) && count($arr) >= 4) $tierPrices = $arr;
    }
}

// Calculate Price and Duration
$price = 0.00;
$days = 0;
$packageName = '';

if ($packageVal === 'trial') {
    $price = 0.00;
    $days = 0;
    $packageName = 'ทดลองใช้งาน ' . $trialDuration . ' นาที';
} elseif ($packageVal === '1') {
    $price = (float)$tierPrices[0];
    $days = 1;
    $packageName = 'แพ็กเกจ 1 วัน';
} elseif ($packageVal === '7') {
    $price = (float)$tierPrices[1];
    $days = 7;
    $packageName = 'แพ็กเกจ 7 วัน';
} elseif ($packageVal === '15') {
    $price = (float)$tierPrices[2];
    $days = 15;
    $packageName = 'แพ็กเกจ 15 วัน';
} else {
    // 30 days default
    $price = (float)$tierPrices[3];
    $days = 30;
    $packageName = 'แพ็กเกจ 30 วัน';
}

$isReseller = (isset($user['role']) && $user['role'] === 'reseller');
$discountText = '';
if ($isReseller && $packageVal !== 'trial' && $price > 0) {
    $price = round($price * 0.70, 2);
    $discountText = ' [ส่วนลดตัวแทน 30%]';
}

if ($user['balance'] < $price) {
    json_response([
        'status' => 'error', 
        'message' => 'ยอดเงินคงเหลือไม่พอ (ขาดอีก ฿' . number_format($price - $user['balance'], 2) . ') กรุณาเติมเงิน'
    ]);
}

// Generate UUID
$uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    mt_rand(0, 0xffff), mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0x0fff) | 0x4000,
    mt_rand(0, 0x3fff) | 0x8000,
    mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
);

// Expiry
if ($packageVal === 'trial') {
    $expiryTime = date('Y-m-d H:i:s', strtotime("+{$trialDuration} minutes"));
} else {
    $expiryTime = date('Y-m-d 23:59:59', strtotime("+{$days} days"));
}

$displayName = build_vpn_display_name($server['name'], $expiryTime, $customName);

$isSsh = ($server['type'] === 'ssh_script' || $server['type'] === 'udp_custom');
$isXui = (!empty($server['panel_url']) && !empty($server['password']) && !$isSsh);
$xuiEmail = null;

// Generate Config Link
if ($isXui) {
    $xuiEmail = xui_make_client_email($displayName);
    $xuiRes = xui_add_client($server, $uuid, $xuiEmail, $expiryTime, $displayName);
    if (!$xuiRes['success']) {
        send_system_error_alert('เชื่อมต่อ X-UI ล้มเหลว', "ไม่สามารถสร้างบัญชี VPN บน X-UI ได้: {$server['name']}", [
            'เซิร์ฟเวอร์' => $server['name'],
            'ข้อความ Error' => $xuiRes['message'] ?? 'Unknown error',
            'ผู้ซื้อ' => $user['username']
        ]);
        json_response([
            'status' => 'error',
            'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อสร้างบัญชีได้: ' . ($xuiRes['message'] ?? 'เกิดข้อผิดพลาด')
        ]);
    }
    $xuiEmail = $xuiRes['email'];
    $configLink = $xuiRes['config_link'];
} elseif ($isSsh) {
    if ($sshUser === '') $sshUser = 'u' . strtolower(bin2hex(random_bytes(3)));
    if ($sshPass === '') $sshPass = (string)rand(100000, 999999);

    $days = max(1, (int)round((strtotime($expiryTime) - time()) / 86400));

    // ส่งคำสั่งสร้างบัญชีบนเซิร์ฟเวอร์ VPS ผ่าน SSH
    $sshRes = ssh_vps_add_user($server, $sshUser, $sshPass, $days);
    if (!$sshRes['success']) {
        send_system_error_alert('เชื่อมต่อ SSH VPS ล้มเหลว', "ไม่สามารถสร้างบัญชี SSH บนเซิร์ฟเวอร์ได้: {$server['name']}", [
            'เซิร์ฟเวอร์' => $server['name'],
            'ข้อความ Error' => $sshRes['message'] ?? 'Unknown error',
            'ผู้ซื้อ' => $user['username']
        ]);
        json_response([
            'status' => 'error',
            'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ VPS เพื่อสร้างบัญชี SSH ได้: ' . ($sshRes['message'] ?? 'เกิดข้อผิดพลาด')
        ]);
    }
    
    $sshPayload = build_ssh_config_payload($server, $sshUser, $sshPass, $displayName);
    $configLink = json_encode($sshPayload, JSON_UNESCAPED_UNICODE);
} else {
    // V2Ray / VLESS Reality fallback
    $protocol = $server['protocol'] ?: 'vless';
    $serverType = $server['type'] ?? '';
    $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '127.0.0.1');
    $targetPort = (int)($server['port'] ?: 443);
    $gamingPort = (!empty($server['vless_port']) && (int)$server['vless_port'] > 0) ? (int)$server['vless_port'] : $targetPort;
    $sni = !empty($server['bug_host']) ? trim($server['bug_host']) : 'speedtest.net';
    $pbkParam = !empty($server['pbk']) ? '&pbk=' . urlencode($server['pbk']) : '';
    $sidParam = !empty($server['sids']) ? '&sid=' . urlencode(explode(',', $server['sids'])[0]) : '';

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
        $configLink = 'vmess://' . base64_encode(json_encode($vmessObj, JSON_UNESCAPED_UNICODE));
    } else {
        $vPort = ($serverType === 'vless_tls' || !empty($server['vless_port'])) ? $gamingPort : $targetPort;
        if (!empty($server['pbk'])) {
            $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$vPort}?type=grpc&encryption=none&security=reality&sni={$sni}&fp=chrome&serviceName=grpc{$pbkParam}{$sidParam}#" . rawurlencode($displayName);
        } elseif ($serverType === 'vless_tls') {
            $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$vPort}?type=tcp&encryption=none&security=tls&sni={$sni}&fp=chrome#" . rawurlencode($displayName);
        } else {
            $hostParam = !empty($sni) ? "&host=" . urlencode($sni) : '';
            $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$vPort}?type=ws&encryption=none&path=/&security=none{$hostParam}#" . rawurlencode($displayName);
        }
    }
}

// Deduct balance
$db->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$price, $user['id']]);

// Insert VPN Config
$actualProtocol = !empty($xuiRes['protocol']) ? $xuiRes['protocol'] : ($server['protocol'] ?: ($isXui ? 'vmess' : 'vless'));
$nowStr = date('Y-m-d H:i:s');
$stmt = $db->prepare("
    INSERT INTO vpn_configs (user_id, server_id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, ssh_user, ssh_pass, status_real, created_at, expiry_time, xui_email)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?)
");
$stmt->execute([
    $user['id'], $serverId, $uuid, $displayName, $packageName, $packageVal, $price,
    $actualProtocol, $configLink, $sshUser, $sshPass, $nowStr, $expiryTime, $xuiEmail
]);

// Log order
$orderDesc = 'ซื้อ ' . $server['name'] . ' (' . $packageName . ')' . $discountText;
$db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "buy", ?, ?, ?)')
   ->execute([$user['id'], $price, $orderDesc, $nowStr]);

// Increase user count
$db->prepare('UPDATE servers SET user_count = user_count + 1 WHERE id = ?')->execute([$serverId]);

// Discord Webhook
$priceWebhook = '฿' . number_format($price, 2) . ($isReseller && $packageVal !== 'trial' ? ' (ลด 30% ตัวแทน)' : '');
send_discord_webhook('buy', [
    'title' => '🛒 มีการสั่งซื้อ VPN ใหม่!' . ($isReseller ? ' [ตัวแทนจำหน่าย]' : ''),
    'color' => 0xdb2777,
    'fields' => [
        ['name' => 'ผู้ซื้อ', 'value' => $user['username'] . ($isReseller ? ' (Reseller)' : ''), 'inline' => true],
        ['name' => 'เซิร์ฟเวอร์', 'value' => $server['name'], 'inline' => true],
        ['name' => 'แพ็กเกจ', 'value' => $packageName, 'inline' => true],
        ['name' => 'ราคา', 'value' => $priceWebhook, 'inline' => true],
        ['name' => 'หมดอายุ', 'value' => $expiryTime, 'inline' => true],
        ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
    ]
]);

$successMsg = 'สั่งซื้อและสร้างไฟล์ VPN สำเร็จเรียบร้อยแล้ว! 🎉' . ($isReseller && $packageVal !== 'trial' ? ' (หัก ฿' . number_format($price, 2) . ' ลด 30% ตัวแทน)' : '');
json_response([
    'status' => 'success',
    'message' => $successMsg
]);
