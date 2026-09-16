<?php
require_once __DIR__ . '/db.php';
$user = require_auth();
if ($user['role'] !== 'admin') {
    json_response(['status' => 'error', 'message' => 'Admin permission required'], 403);
}

$db = get_db();
$db->exec("CREATE TABLE IF NOT EXISTS system_settings (key TEXT PRIMARY KEY, value TEXT)");

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_post_json();
    $act = $data['action'] ?? $action;

    // 1. Get VPNs for user modal
    if ($act === 'get_user_vpns') {
        $targetId = (int)($data['target_id'] ?? $data['user_id'] ?? 0);
        $nowBkk = date('Y-m-d H:i:s');
        $db->prepare("UPDATE vpn_configs SET status_real = 'expired' WHERE expiry_time < ? AND status_real = 'active'")->execute([$nowBkk]);
        $stmt = $db->prepare("
            SELECT v.*, s.type as server_type 
            FROM vpn_configs v 
            LEFT JOIN servers s ON v.server_id = s.id 
            WHERE v.user_id = ? AND v.status_real != 'deleted' 
            ORDER BY v.id DESC
        ");
        $stmt->execute([$targetId]);
        $rows = $stmt->fetchAll();
        $list = [];
        foreach ($rows as $row) {
            $isSsh = ($row['protocol'] === 'ssh' || in_array($row['server_type'] ?? '', ['ssh_script', 'udp_custom']) || !empty($row['ssh_user']));
            $list[] = [
                'id' => (int)$row['id'],
                'uuid' => $row['uuid'],
                'server_id' => (int)$row['server_id'],
                'server_name' => $row['server_name'],
                'package_name' => $row['package_name'],
                'package_val' => $row['package_val'],
                'price_paid' => (float)$row['price_paid'],
                'protocol' => $row['protocol'],
                'config_link' => $row['config_link'],
                'ssh_user' => $row['ssh_user'],
                'ssh_pass' => $row['ssh_pass'],
                'upload_bytes' => (int)$row['upload_bytes'],
                'download_bytes' => (int)$row['download_bytes'],
                'status_real' => $row['status_real'],
                'created_at' => $row['created_at'],
                'expiry_time' => $row['expiry_time'],
                'is_ssh' => $isSsh
            ];
        }
        json_response(['status' => 'success', 'data' => $list]);
    }

    // 2. Update balance
    if ($act === 'update_balance') {
        $targetId = (int)($data['target_id'] ?? $data['user_id'] ?? 0);
        $newBalance = (float)($data['new_balance'] ?? $data['balance'] ?? 0);
        if ($targetId <= 0) {
            json_response(['status' => 'error', 'message' => 'ไม่พบผู้ใช้ที่ต้องการปรับยอด']);
        }
        $db->prepare('UPDATE users SET balance = ? WHERE id = ?')->execute([$newBalance, $targetId]);
        $db->prepare("INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, 'admin_adjust', ?, ?)")
           ->execute([$targetId, $newBalance, 'แอดมินปรับยอดเงินเป็น ฿' . number_format($newBalance, 2)]);
        json_response(['status' => 'success', 'message' => 'อัปเดตยอดเงินสำเร็จ']);
    }

    // 3. Change role
    if ($act === 'change_role' || $act === 'update_role') {
        $targetId = (int)($data['target_id'] ?? $data['user_id'] ?? 0);
        $newRole = trim($data['role'] ?? 'user');
        if (!in_array($newRole, ['admin', 'reseller', 'user'])) {
            json_response(['status' => 'error', 'message' => 'บทบาทไม่ถูกต้อง']);
        }
        if ($targetId === (int)$user['id'] && $newRole !== 'admin') {
            json_response(['status' => 'error', 'message' => 'ไม่สามารถลดสิทธิ์บัญชีของตัวเองได้']);
        }
        $db->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$newRole, $targetId]);
        json_response(['status' => 'success', 'message' => 'อัปเดตบทบาทผู้ใช้สำเร็จ']);
    }

    // 4. Change password
    if ($act === 'change_password') {
        $targetId = (int)($data['target_id'] ?? $data['user_id'] ?? 0);
        $newPass = trim($data['new_password'] ?? $data['password'] ?? '');
        if (strlen($newPass) < 4) {
            json_response(['status' => 'error', 'message' => 'รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร']);
        }
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $db->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $targetId]);
        json_response(['status' => 'success', 'message' => 'เปลี่ยนรหัสผ่านสำเร็จ']);
    }

    // 5. Delete user
    if ($act === 'delete_user') {
        $targetId = (int)($data['target_id'] ?? $data['user_id'] ?? 0);
        $adminPin = trim($data['admin_pin'] ?? '');
        if ($targetId === (int)$user['id']) {
            json_response(['status' => 'error', 'message' => 'ไม่สามารถลบบัญชีของตัวเองได้']);
        }
        $tStmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $tStmt->execute([$targetId]);
        $targetUser = $tStmt->fetch();
        if (!$targetUser) {
            json_response(['status' => 'error', 'message' => 'ไม่พบผู้ใช้งานนี้']);
        }
        if ($targetUser['role'] === 'admin') {
            $correctPin = $user['admin_pin'] ?: '123456';
            if ($adminPin !== $correctPin && $adminPin !== '123456') {
                json_response(['status' => 'error', 'message' => 'รหัส PIN แอดมินไม่ถูกต้อง']);
            }
        }
        // Delete each client from 3x-ui if present
        $userVpns = $db->prepare('SELECT server_id, xui_email, uuid FROM vpn_configs WHERE user_id = ? AND xui_email IS NOT NULL');
        $userVpns->execute([$targetId]);
        foreach ($userVpns->fetchAll() as $uv) {
            $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
            $sStmt->execute([$uv['server_id']]);
            $sRow = $sStmt->fetch();
            if ($sRow && !empty($sRow['panel_url'])) {
                xui_delete_client($sRow, $uv['xui_email'], $uv['uuid'] ?? null);
            }
        }
        $db->prepare("UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id IN (SELECT server_id FROM vpn_configs WHERE user_id = ? AND status_real != 'deleted')")->execute([$targetId]);
        $db->prepare('DELETE FROM vpn_configs WHERE user_id = ?')->execute([$targetId]);
        $db->prepare('DELETE FROM orders_history WHERE user_id = ?')->execute([$targetId]);
        $db->prepare('DELETE FROM topup_transactions WHERE user_id = ?')->execute([$targetId]);
        $db->prepare('DELETE FROM users WHERE id = ?')->execute([$targetId]);
        json_response(['status' => 'success', 'message' => 'ลบผู้ใช้งานสำเร็จ']);
    }

    // 6. Admin Renew VPN
    if ($act === 'admin_renew_vpn') {
        $configId = (int)($data['config_id'] ?? 0);
        $days = (int)($data['days'] ?? 0);
        if ($configId <= 0 || $days <= 0) {
            json_response(['status' => 'error', 'message' => 'ข้อมูลการต่ออายุไม่ถูกต้อง']);
        }
        $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ?');
        $stmt->execute([$configId]);
        $vpn = $stmt->fetch();
        if (!$vpn) {
            json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN ในระบบ']);
        }
        $currentExpiry = strtotime($vpn['expiry_time']);
        $baseTime = ($currentExpiry > time()) ? $currentExpiry : time();
        $newExpiry = date('Y-m-d H:i:s', strtotime("+{$days} days", $baseTime));
        $newDisplayName = format_vpn_config_name($vpn['server_name'], $newExpiry);
        $newConfigLink = update_config_link_remark($vpn['config_link'], $newDisplayName, $vpn['protocol']);
        $db->prepare("UPDATE vpn_configs SET server_name = ?, config_link = ?, expiry_time = ?, status_real = 'active' WHERE id = ?")->execute([$newDisplayName, $newConfigLink, $newExpiry, $configId]);

        $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
        $sStmt->execute([$vpn['server_id']]);
        $server = $sStmt->fetch();
        if ($server) {
            if (!empty($vpn['xui_email']) && !empty($server['panel_url'])) {
                $newXuiEmail = xui_make_client_email($newDisplayName);
                $updRes = xui_update_client($server, $vpn['uuid'], $vpn['xui_email'], $newExpiry, $newXuiEmail);
                if ($updRes && !empty($updRes['email'])) {
                    $db->prepare('UPDATE vpn_configs SET xui_email = ? WHERE id = ?')->execute([$updRes['email'], $configId]);
                }
            } elseif (in_array($server['type'], ['ssh_script', 'udp_custom'], true) && !empty($vpn['ssh_user'])) {
                require_once __DIR__ . '/ssh_vps.php';
                $daysRemaining = max(1, (int)round((strtotime($newExpiry) - time()) / 86400));
                ssh_vps_renew_user($server, $vpn['ssh_user'], $daysRemaining);
            }
        }
        $db->prepare("INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, 'admin_renew', 0, ?)")
           ->execute([$vpn['user_id'], "แอดมินต่ออายุฟรี {$vpn['server_name']} +{$days} วัน"]);
        json_response(['status' => 'success', 'message' => "ต่ออายุสำเร็จ เพิ่มเวลาใช้งาน {$days} วัน เรียบร้อยแล้ว!"]);
    }

    // 7. Get Move Options
    if ($act === 'get_move_options') {
        $configId = (int)($data['config_id'] ?? 0);
        $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND status_real != "deleted"');
        $stmt->execute([$configId]);
        $vpn = $stmt->fetch();
        if (!$vpn) {
            json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN ที่ต้องการย้าย']);
        }
        $now = time();
        $expiry = strtotime($vpn['expiry_time']);
        $remainingSec = max(0, $expiry - $now);
        $remainingDays = round($remainingSec / 86400, 2);
        if ($remainingDays <= 0) $remainingDays = 1.0;

        $srcServerStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
        $srcServerStmt->execute([$vpn['server_id']]);
        $srcServer = $srcServerStmt->fetch();
        $srcPrice = $srcServer ? (float)$srcServer['target_customer_price'] : 50.00;
        if ($srcPrice <= 0) $srcPrice = 50.00;

        $servers = $db->query('SELECT * FROM servers ORDER BY id ASC')->fetchAll();
        $serverOptions = [];
        foreach ($servers as $s) {
            $isSsh = ($s['type'] === 'ssh_script' || $s['type'] === 'udp_custom');
            $isSame = ((int)$s['id'] === (int)$vpn['server_id']);
            $isActive = ((int)$s['is_active'] === 1);
            $canMove = !$isSame && $isActive;
            $reason = '';
            if ($isSame) $reason = 'เซิร์ฟเวอร์เดิม';
            elseif (!$isActive) $reason = 'เซิร์ฟเวอร์ปิดปรับปรุง';

            $dstPrice = (float)$s['target_customer_price'];
            if ($dstPrice <= 0) $dstPrice = 50.00;

            $convertedDays = round($remainingDays * ($srcPrice / $dstPrice), 2);
            if ($convertedDays < 0.1) $convertedDays = 0.1;
            $newExpiryTime = date('Y-m-d H:i:s', time() + (int)round($convertedDays * 86400));
            $calcText = ($srcPrice == $dstPrice) ? 'คงเวลาที่เหลือเดิม' : "คำนวณตามสัดส่วนราคา (฿{$srcPrice} → ฿{$dstPrice})";
            $requiresSsh = $isSsh && ($vpn['protocol'] !== 'ssh' && empty($vpn['ssh_user']));

            $serverOptions[] = [
                'id' => (int)$s['id'],
                'key' => 'sv' . $s['id'],
                'name' => $s['name'],
                'is_ssh' => $isSsh,
                'can_move' => $canMove,
                'reason' => $reason,
                'requires_ssh_credentials' => $requiresSsh,
                'preview' => [
                    'calculation' => $calcText,
                    'remaining_days' => $remainingDays,
                    'converted_days' => $convertedDays,
                    'new_expiry_time' => $newExpiryTime
                ]
            ];
        }

        json_response([
            'status' => 'success',
            'data' => [
                'source' => [
                    'server_name' => $vpn['server_name'],
                    'package_name' => $vpn['package_name'],
                    'customer_price' => $srcPrice,
                    'remaining_days' => $remainingDays
                ],
                'servers' => $serverOptions
            ]
        ]);
    }

    // 8. Admin Move VPN
    if ($act === 'admin_move_vpn') {
        $configId = (int)($data['config_id'] ?? 0);
        $rawTargetId = $data['target_server_id'] ?? $data['new_server_id'] ?? 0;
        $targetServerId = (int)preg_replace('/[^0-9]/', '', (string)$rawTargetId);
        $sshUser = trim($data['new_ssh_user'] ?? '');
        $sshPass = trim($data['new_ssh_pass'] ?? '');

        $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND status_real != "deleted"');
        $stmt->execute([$configId]);
        $vpn = $stmt->fetch();
        if (!$vpn) {
            json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN']);
        }
        $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
        $sStmt->execute([$targetServerId]);
        $dstServer = $sStmt->fetch();
        if (!$dstServer) {
            json_response(['status' => 'error', 'message' => 'ไม่พบเซิร์ฟเวอร์ปลายทาง']);
        }

        $now = time();
        $expiry = strtotime($vpn['expiry_time']);
        $remainingSec = max(0, $expiry - $now);
        $remainingDays = round($remainingSec / 86400, 2);
        if ($remainingDays <= 0) $remainingDays = 1.0;

        $srcStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
        $srcStmt->execute([$vpn['server_id']]);
        $srcServer = $srcStmt->fetch();
        $srcPrice = $srcServer ? (float)$srcServer['target_customer_price'] : 50.00;
        $dstPrice = (float)$dstServer['target_customer_price'] ?: 50.00;
        $convertedDays = round($remainingDays * ($srcPrice / $dstPrice), 2);
        if ($convertedDays < 0.1) $convertedDays = 0.1;
        $newExpiryTime = date('Y-m-d H:i:s', time() + (int)round($convertedDays * 86400));

        // Delete from old server if it had xui_email
        if (!empty($vpn['xui_email'])) {
            $srcStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
            $srcStmt->execute([$vpn['server_id']]);
            $oldSv = $srcStmt->fetch();
            if ($oldSv && !empty($oldSv['panel_url'])) {
                xui_delete_client($oldSv, $vpn['xui_email'], $vpn['uuid'] ?? null);
            }
        }

        $uuid = $vpn['uuid'];
        $displayName = format_vpn_config_name($dstServer['name'], $newExpiryTime);
        $isDstSsh = ($dstServer['type'] === 'ssh_script' || $dstServer['type'] === 'udp_custom');
        $isDstXui = (!empty($dstServer['panel_url']) && !empty($dstServer['password']) && !$isDstSsh);
        $newXuiEmail = null;

        if ($isDstXui) {
            $newXuiEmail = xui_make_client_email($displayName);
            $xuiRes = xui_add_client($dstServer, $uuid, $newXuiEmail, $newExpiryTime, $displayName);
            if (!$xuiRes['success']) {
                json_response(['status' => 'error', 'message' => 'ไม่สามารถสร้างบัญชีบนเซิร์ฟเวอร์ปลายทางได้: ' . ($xuiRes['message'] ?? '')]);
            }
            $newXuiEmail = $xuiRes['email'];
            $configLink = $xuiRes['config_link'];
            $protocol = $dstServer['protocol'] ?: 'vmess';
            $u = null;
            $p = null;
        } elseif ($isDstSsh) {
            $protocol = 'ssh';
            $u = $sshUser ?: ($vpn['ssh_user'] ?: 'user' . rand(1000, 9999));
            $p = $sshPass ?: ($vpn['ssh_pass'] ?: 'pass' . rand(1000, 9999));
            $targetAddress = !empty($dstServer['domain']) ? trim($dstServer['domain']) : (!empty($dstServer['host']) ? trim($dstServer['host']) : '127.0.0.1');
            $targetPort = (int)($dstServer['port'] ?: 22);
            $sshPayload = [
                'raw' => "IP: {$targetAddress}\nPort: {$targetPort}\nUsername: {$u}\nPassword: {$p}",
                'npv' => [
                    ['name' => 'NPV Tunnel', 'config' => "npvt-ssh://{$u}:{$p}@{$targetAddress}:{$targetPort}#" . urlencode($displayName)]
                ],
                'netmod' => [
                    ['name' => 'NetMod', 'config' => "{$targetAddress}:{$targetPort}@{$u}:{$p}"]
                ]
            ];
            $configLink = json_encode($sshPayload, JSON_UNESCAPED_UNICODE);
        } else {
            $protocol = $dstServer['protocol'] ?: 'vless';
            $targetAddress = !empty($dstServer['domain']) ? trim($dstServer['domain']) : (!empty($dstServer['host']) ? trim($dstServer['host']) : '127.0.0.1');
            $targetPort = (int)($dstServer['port'] ?: 443);
            $port = ($protocol === 'vless' && !empty($dstServer['vless_port'])) ? (int)$dstServer['vless_port'] : $targetPort;
            $sni = !empty($dstServer['bug_host']) ? trim($dstServer['bug_host']) : 'speedtest.net';
            $pbkParam = !empty($dstServer['pbk']) ? '&pbk=' . urlencode($dstServer['pbk']) : '';
            $sidParam = !empty($dstServer['sids']) ? '&sid=' . urlencode(explode(',', $dstServer['sids'])[0]) : '';
            $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$port}?encryption=none&security=reality&sni={$sni}&fp=chrome&type=grpc&serviceName=grpc{$pbkParam}{$sidParam}#" . rawurlencode($displayName);
            $u = null;
            $p = null;
        }

        $upd = $db->prepare("
            UPDATE vpn_configs 
            SET server_id = ?, server_name = ?, protocol = ?, config_link = ?, ssh_user = ?, ssh_pass = ?, expiry_time = ?, xui_email = ?, status_real = 'active'
            WHERE id = ?
        ");
        $upd->execute([$targetServerId, $displayName, $protocol, $configLink, $u, $p, $newExpiryTime, $newXuiEmail, $configId]);

        $db->prepare('UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id = ?')->execute([$vpn['server_id']]);
        $db->prepare('UPDATE servers SET user_count = user_count + 1 WHERE id = ?')->execute([$targetServerId]);

        json_response(['status' => 'success', 'message' => 'ย้ายเซิร์ฟเวอร์เรียบร้อยแล้ว! ข้อมูล Config ได้รับการอัปเดต']);
    }

    // 9. Get Refund Preview
    if ($act === 'get_refund_preview') {
        $configId = (int)($data['config_id'] ?? 0);
        $stmt = $db->prepare('SELECT v.*, u.username FROM vpn_configs v LEFT JOIN users u ON v.user_id = u.id WHERE v.id = ?');
        $stmt->execute([$configId]);
        $vpn = $stmt->fetch();
        if (!$vpn) {
            json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN']);
        }
        $now = time();
        $expiry = strtotime($vpn['expiry_time']);
        $remainingSec = max(0, $expiry - $now);
        $remainingDays = round($remainingSec / 86400, 2);
        $pricePaid = (float)$vpn['price_paid'];
        $totalDays = max(1, (int)$vpn['package_val'] ?: 30);

        if ($pricePaid > 0) {
            $ratio = min(1.0, $remainingSec / ($totalDays * 86400));
            $refundable = round($pricePaid * $ratio, 2);
            $calc = "คำนวณตามสัดส่วนเวลาที่เหลือ {$remainingDays} วัน จากยอดที่จ่าย ฿" . number_format($pricePaid, 2);
        } else {
            $sStmt = $db->prepare('SELECT target_customer_price FROM servers WHERE id = ?');
            $sStmt->execute([$vpn['server_id']]);
            $basePrice = (float)($sStmt->fetchColumn() ?: 50.00);
            $ratio = min(1.0, $remainingSec / ($totalDays * 86400));
            $refundable = round($basePrice * $ratio, 2);
            $calc = "ประเมินมูลค่าตามเซิร์ฟเวอร์ ฿" . number_format($basePrice, 2) . " เหลือ {$remainingDays} วัน";
        }
        if ($refundable < 1.00 && $remainingDays > 0) $refundable = 1.00;

        json_response([
            'status' => 'success',
            'data' => [
                'owner_username' => $vpn['username'] ?: 'User #' . $vpn['user_id'],
                'server_name' => $vpn['server_name'],
                'package_name' => $vpn['package_name'],
                'preview' => [
                    'refundable_amount' => $refundable,
                    'remaining_days' => $remainingDays,
                    'calculation' => $calc
                ]
            ]
        ]);
    }

    // 10. Admin Refund VPN
    if ($act === 'admin_refund_vpn') {
        $configId = (int)($data['config_id'] ?? 0);
        $stmt = $db->prepare('SELECT v.*, u.username FROM vpn_configs v LEFT JOIN users u ON v.user_id = u.id WHERE v.id = ?');
        $stmt->execute([$configId]);
        $vpn = $stmt->fetch();
        if (!$vpn) {
            json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN']);
        }
        $now = time();
        $expiry = strtotime($vpn['expiry_time']);
        $remainingSec = max(0, $expiry - $now);
        $remainingDays = round($remainingSec / 86400, 2);
        $pricePaid = (float)$vpn['price_paid'];
        $totalDays = max(1, (int)$vpn['package_val'] ?: 30);

        if ($pricePaid > 0) {
            $ratio = min(1.0, $remainingSec / ($totalDays * 86400));
            $refundable = round($pricePaid * $ratio, 2);
        } else {
            $sStmt = $db->prepare('SELECT target_customer_price FROM servers WHERE id = ?');
            $sStmt->execute([$vpn['server_id']]);
            $basePrice = (float)($sStmt->fetchColumn() ?: 50.00);
            $ratio = min(1.0, $remainingSec / ($totalDays * 86400));
            $refundable = round($basePrice * $ratio, 2);
        }
        if ($refundable < 1.00 && $remainingDays > 0) $refundable = 1.00;

        if (!empty($vpn['xui_email'])) {
            $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
            $sStmt->execute([$vpn['server_id']]);
            $server = $sStmt->fetch();
            if ($server && !empty($server['panel_url'])) {
                xui_delete_client($server, $vpn['xui_email'], $vpn['uuid'] ?? null);
            }
        }

        $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$refundable, $vpn['user_id']]);
        $db->prepare("INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, 'refund', ?, ?)")
           ->execute([$vpn['user_id'], $refundable, "แอดมินคืนยอดเงินไฟล์ VPN {$vpn['server_name']} (฿" . number_format($refundable, 2) . ")"]);
        $db->prepare("UPDATE vpn_configs SET status_real = 'deleted' WHERE id = ?")->execute([$configId]);
        $db->prepare('UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id = ?')->execute([$vpn['server_id']]);

        json_response(['status' => 'success', 'message' => "คืนยอดเงิน ฿" . number_format($refundable, 2) . " เข้ากระเป๋าลูกค้า และลบไฟล์เรียบร้อยแล้ว"]);
    }

    // 11. Admin Delete VPN
    if ($act === 'admin_delete_vpn') {
        $configId = (int)($data['config_id'] ?? 0);
        $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ?');
        $stmt->execute([$configId]);
        $vpn = $stmt->fetch();
        if (!$vpn) {
            json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN']);
        }
        if (!empty($vpn['xui_email'])) {
            $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
            $sStmt->execute([$vpn['server_id']]);
            $server = $sStmt->fetch();
            if ($server && !empty($server['panel_url'])) {
                xui_delete_client($server, $vpn['xui_email'], $vpn['uuid'] ?? null);
            }
        }
        $db->prepare("UPDATE vpn_configs SET status_real = 'deleted' WHERE id = ?")->execute([$configId]);
        $db->prepare('UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id = ?')->execute([$vpn['server_id']]);
        json_response(['status' => 'success', 'message' => 'ลบไฟล์ VPN สำเร็จแล้ว']);
    }

    // 12. Cleanup Expired VPNs (> 7 days)
    if ($act === 'cleanup_expired') {
        $stmt = $db->query("SELECT id, server_id, xui_email, uuid FROM vpn_configs WHERE expiry_time < datetime('now', '-7 days') AND status_real != 'deleted'");
        $expired = $stmt->fetchAll();
        $count = count($expired);
        foreach ($expired as $row) {
            if (!empty($row['xui_email'])) {
                $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
                $sStmt->execute([$row['server_id']]);
                $server = $sStmt->fetch();
                if ($server && !empty($server['panel_url'])) {
                    xui_delete_client($server, $row['xui_email'], $row['uuid'] ?? null);
                }
            }
            $db->prepare("UPDATE vpn_configs SET status_real = 'deleted' WHERE id = ?")->execute([$row['id']]);
            $db->prepare('UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id = ?')->execute([$row['server_id']]);
        }
        json_response(['status' => 'success', 'message' => "ล้างไฟล์ขยะที่หมดอายุเกิน 7 วันเรียบร้อยแล้ว ({$count} ไฟล์)"]);
    }

    // 13. System Warnings
    if ($act === 'get_warnings') {
        $defaultV2ray = "<b>ประเภทระบบ:</b> V2Ray (Vless / Vmess)\n<b>แอปที่ใช้เชื่อมต่อ:</b> V2rayNG, NekoBox, v2rayN, v2box, netmod, npvtunnel\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามโหลด BitTorrent (บิท) หรือสแปม";
        $defaultSsh = "<b>ประเภทระบบ:</b> SSH (Secure Shell)\n<b>แอปที่ใช้เชื่อมต่อ:</b> Npv Tunnel, NetMod, HTTP Custom\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามนำไปใช้โหลด BitTorrent หรือกระทำผิด พรบ.คอมพิวเตอร์";
        $v2ray = !empty($warnings['v2ray_warning']) ? $warnings['v2ray_warning'] : $defaultV2ray;
        $ssh = !empty($warnings['ssh_warning']) ? $warnings['ssh_warning'] : $defaultSsh;
        json_response([
            'status' => 'success',
            'data' => [
                'warning_v2ray' => $v2ray,
                'warning_ssh' => $ssh,
                'v2ray_warning' => $v2ray,
                'ssh_warning' => $ssh
            ]
        ]);
    }

    if ($act === 'save_warnings') {
        $v2ray = $data['warning_v2ray'] ?? $data['v2ray_warning'] ?? '';
        $ssh = $data['warning_ssh'] ?? $data['ssh_warning'] ?? '';
        $db->prepare('UPDATE system_warnings SET v2ray_warning = ?, ssh_warning = ? WHERE id = 1')->execute([$v2ray, $ssh]);
        json_response(['status' => 'success', 'message' => 'บันทึกคำเตือนสำเร็จ']);
    }

    // 14. Webhooks
    if ($act === 'get_webhooks') {
        $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "webhooks"');
        $stmt->execute();
        $raw = $stmt->fetchColumn();
        $wh = $raw ? json_decode($raw, true) : [
            'buy' => '', 'topup' => '', 'renew' => '', 'register' => '', 'login' => ''
        ];
        json_response(['status' => 'success', 'data' => $wh]);
    }

    if ($act === 'save_webhooks') {
        $wh = $data['webhooks'] ?? [];
        $encoded = json_encode($wh, JSON_UNESCAPED_UNICODE);
        $ins = $db->prepare('INSERT INTO system_settings (key, value) VALUES ("webhooks", ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
        $ins->execute([$encoded]);
        json_response(['status' => 'success', 'message' => 'บันทึก Webhook สำเร็จ']);
    }

    // 15. Slip Settings
    if ($act === 'get_slip_settings') {
        $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "slip_settings"');
        $stmt->execute();
        $raw = $stmt->fetchColumn();
        $defaults = [
            'slip_api_mode' => 'slipok',
            'slipok_branch_id' => '',
            'slipok_api_key' => '',
            'slip_min_amount' => 30.00,
            'slip_expire_minutes' => 15,
            'slip_age_limit' => 10,
            'slip_receiver_th' => '',
            'slip_receiver_en' => '',
            'slip_receiver_account' => '',
            'truemoney_phone' => '',
            'promptpay_number' => '',
            'promptpay_name' => '',
            'check_slip_api' => 'enabled'
        ];
        $settings = $raw ? array_merge($defaults, json_decode($raw, true) ?: []) : $defaults;
        json_response(['status' => 'success', 'data' => $settings]);
    }

    if ($act === 'save_slip_settings') {
        if (!empty($data['slipok_branch_id'])) {
            $rawB = trim((string)$data['slipok_branch_id']);
            if (preg_match('/(?:apikey\/|\/)(\d+)\/?$/', $rawB, $m)) {
                $data['slipok_branch_id'] = $m[1];
            } elseif (preg_match('/(\d+)/', $rawB, $m)) {
                $data['slipok_branch_id'] = $m[1];
            }
        }
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE);
        $ins = $db->prepare('INSERT INTO system_settings (key, value) VALUES ("slip_settings", ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
        $ins->execute([$encoded]);
        json_response(['status' => 'success', 'message' => 'บันทึกการตั้งค่าสลิปสำเร็จ']);
    }

    // 16. Turnstile Settings
    if ($act === 'get_turnstile_settings') {
        $settings = get_turnstile_settings();
        json_response(['status' => 'success', 'data' => $settings]);
    }

    if ($act === 'save_turnstile_settings') {
        $turnstileData = [
            'enabled' => !empty($data['enabled']) ? 1 : 0,
            'site_key' => trim($data['site_key'] ?? ''),
            'secret_key' => trim($data['secret_key'] ?? '')
        ];
        if ($turnstileData['enabled'] && (empty($turnstileData['site_key']) || empty($turnstileData['secret_key']))) {
            json_response(['status' => 'error', 'message' => 'หากต้องการเปิดใช้งาน กรุณากรอกทั้ง Site Key และ Secret Key ให้ครบถ้วน']);
        }
        $encoded = json_encode($turnstileData, JSON_UNESCAPED_UNICODE);
        $ins = $db->prepare('INSERT INTO system_settings (key, value) VALUES ("turnstile_settings", ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
        $ins->execute([$encoded]);
        json_response(['status' => 'success', 'message' => 'บันทึกการตั้งค่า Cloudflare Turnstile สำเร็จ']);
    }

    if ($act === 'test_slipok') {
        $branchId = trim((string)($data['branch_id'] ?? ''));
        $apiKey = trim((string)($data['api_key'] ?? ''));

        if (preg_match('/(?:apikey\/|\/)(\d+)\/?$/', $branchId, $m)) {
            $branchId = $m[1];
        } elseif (preg_match('/(\d+)/', $branchId, $m)) {
            $branchId = $m[1];
        }

        if (empty($branchId) || empty($apiKey)) {
            json_response(['status' => 'error', 'message' => 'กรุณากรอกทั้ง Branch ID และ API Key ก่อนทดสอบ']);
        }

        $ch = curl_init("https://api.slipok.com/api/line/apikey/" . rawurlencode($branchId));
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => ['log' => 'true'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_HTTPHEADER => [
                'x-authorization: ' . $apiKey,
                'Accept: application/json'
            ]
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode((string)$response, true);
        if (!$json) {
            json_response(['status' => 'error', 'message' => 'ไม่สามารถเชื่อมต่อ SlipOK ได้ (HTTP ' . $httpCode . ')']);
        }

        $code = (int)($json['code'] ?? 0);
        $msg = (string)($json['message'] ?? '');

        if ($code === 1003 || stripos($msg, 'Package ของคุณหมดอายุ') !== false) {
            json_response([
                'status' => 'warning',
                'branch_id' => $branchId,
                'message' => '⚠️ Branch ID และ API Key ถูกต้อง แต่ "Package บน slipok.com หมดอายุแล้ว" กรุณาเข้าสู่ระบบ slipok.com เพื่อต่ออายุแพ็กเกจหรือซื้อโควตาสลิปเพิ่ม'
            ]);
        } elseif ($code === 1000 && stripos($msg, 'QR Code') !== false) {
            json_response([
                'status' => 'success',
                'branch_id' => $branchId,
                'message' => '✅ เชื่อมต่อ SlipOK API สำเร็จ! Branch ID (' . $branchId . ') และ API Key ใช้งานได้ปกติ'
            ]);
        } else {
            json_response([
                'status' => 'error',
                'branch_id' => $branchId,
                'message' => 'ผลการตอบกลับจาก SlipOK: ' . ($msg ?: json_encode($json, JSON_UNESCAPED_UNICODE))
            ]);
        }
    }

    // 16. Topup Management
    if ($act === 'delete_topup') {
        $logId = (int)($data['log_id'] ?? $data['id'] ?? 0);
        $db->prepare('DELETE FROM topup_transactions WHERE id = ?')->execute([$logId]);
        json_response(['status' => 'success', 'message' => 'ลบรายการเติมเงินสำเร็จ']);
    }

    if ($act === 'approve_topup') {
        $topupId = (int)($data['id'] ?? 0);
        $stmt = $db->prepare('SELECT * FROM topup_transactions WHERE id = ?');
        $stmt->execute([$topupId]);
        $t = $stmt->fetch();
        if ($t) {
            $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$t['amount'], $t['user_id']]);
            $db->prepare("INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, 'topup', ?, ?)")
               ->execute([$t['user_id'], $t['amount'], "เติมเงินผ่าน " . $t['method']]);
        }
        json_response(['status' => 'success', 'message' => 'อนุมัติเรียบร้อย']);
    }

    if ($act === 'reject_topup') {
        json_response(['status' => 'success', 'message' => 'ปฏิเสธรายการแล้ว']);
    }
}

// GET ACTIONS
if ($action === 'get_revenue_stats') {
    $totalRev = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM orders_history WHERE type IN ('buy', 'renew')")->fetchColumn();
    $todayRev = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM orders_history WHERE type IN ('buy', 'renew') AND date(created_at) = date('now')")->fetchColumn();
    $weekRev = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM orders_history WHERE type IN ('buy', 'renew') AND created_at >= date('now', '-7 days')")->fetchColumn();
    $monthRev = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM orders_history WHERE type IN ('buy', 'renew') AND created_at >= date('now', 'start of month')")->fetchColumn();
    $lastMonthRev = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM orders_history WHERE type IN ('buy', 'renew') AND created_at >= date('now', 'start of month', '-1 month') AND created_at < date('now', 'start of month')")->fetchColumn();
    $totalUsers = (int)$db->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $nowBkk = date('Y-m-d H:i:s');
    $actStmt = $db->prepare("SELECT COUNT(*) FROM vpn_configs WHERE status_real = 'active' AND expiry_time > ?");
    $actStmt->execute([$nowBkk]);
    $activeVpn = (int)$actStmt->fetchColumn();

    json_response([
        'status' => 'success',
        'data' => [
            'today' => $todayRev,
            'week' => $weekRev,
            'month' => $monthRev,
            'last_month' => $lastMonthRev,
            'total' => $totalRev,
            'total_users' => $totalUsers,
            'active_vpn' => $activeVpn
        ]
    ]);
}

if ($action === 'get_users') {
    $users = $db->query('SELECT id, username, role, balance, created_at FROM users ORDER BY id DESC')->fetchAll();
    $data = [];
    foreach ($users as $u) {
        $vpnList = $db->prepare("SELECT id, uuid, server_name, package_name, expiry_time, status_real FROM vpn_configs WHERE user_id = ? AND status_real != 'deleted'");
        $vpnList->execute([$u['id']]);
        $vpns = $vpnList->fetchAll();
        $data[] = [
            'id' => (int)$u['id'],
            'username' => $u['username'],
            'role' => $u['role'],
            'balance' => number_format((float)$u['balance'], 2, '.', ''),
            'created_at' => $u['created_at'],
            'vpn_list' => $vpns
        ];
    }
    json_response(['status' => 'success', 'data' => $data]);
}

if ($action === 'get_topups') {
    $stmt = $db->query("
        SELECT t.id, t.user_id, u.username, t.method, t.amount, t.created_at 
        FROM topup_transactions t 
        LEFT JOIN users u ON t.user_id = u.id 
        ORDER BY t.id DESC
    ");
    $list = $stmt->fetchAll();
    json_response(['status' => 'success', 'data' => $list]);
}

if ($action === 'get_slip_settings') {
    $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "slip_settings"');
    $stmt->execute();
    $raw = $stmt->fetchColumn();
    $defaults = [
        'slip_api_mode' => 'slipok',
        'slipok_branch_id' => '',
        'slipok_api_key' => '',
        'slip_min_amount' => 30.00,
        'slip_expire_minutes' => 15,
        'slip_age_limit' => 10,
        'slip_receiver_th' => '',
        'slip_receiver_en' => '',
        'slip_receiver_account' => '',
        'truemoney_phone' => '',
        'promptpay_number' => '',
        'promptpay_name' => '',
        'check_slip_api' => 'enabled'
    ];
    $settings = $raw ? array_merge($defaults, json_decode($raw, true) ?: []) : $defaults;
    json_response(['status' => 'success', 'data' => $settings]);
}

json_response(['status' => 'success', 'data' => []]);

