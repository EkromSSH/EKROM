<?php
require_once __DIR__ . '/db.php';
$user = require_auth();
if ($user['role'] !== 'admin') {
    json_response(['status' => 'error', 'message' => 'Admin required'], 403);
}

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = get_post_json();
    if (empty($body)) {
        $body = $_POST;
    }
    $action = $body['action'] ?? $_GET['action'] ?? '';

    if ($action === 'adjust_balance') {
        $userId = (int)($body['user_id'] ?? 0);
        $amount = (float)($body['amount'] ?? 0);
        $note = trim($body['note'] ?? 'แอดมินปรับยอดเงินตัวแทน');

        if ($userId <= 0 || $amount == 0) {
            json_response(['status' => 'error', 'message' => 'จำนวนเงินและรหัสผู้ใช้ไม่ถูกต้อง'], 400);
        }

        $stmt = $db->prepare("SELECT id, username, balance FROM users WHERE id = ? AND role = 'reseller'");
        $stmt->execute([$userId]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$target) {
            json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลตัวแทนนี้ในระบบ'], 404);
        }

        $newBalance = max(0, (float)$target['balance'] + $amount);
        $db->prepare("UPDATE users SET balance = ? WHERE id = ?")->execute([$newBalance, $userId]);

        $db->prepare("INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, 'admin_adjust', ?, ?, datetime('now', 'localtime'))")
           ->execute([$userId, $amount, $note]);

        json_response([
            'status' => 'success',
            'message' => 'ปรับยอดเงินของ ' . $target['username'] . ' เรียบร้อยแล้ว (ยอดคงเหลือ ฿' . number_format($newBalance, 2) . ')',
            'new_balance' => $newBalance
        ]);
    }

    if ($action === 'save_user_discount' || $action === 'update_user_discount') {
        $userId = (int)($body['user_id'] ?? 0);
        $rawPercent = $body['discount_percent'] ?? $body['percent'] ?? null;

        if ($userId <= 0) {
            json_response(['status' => 'error', 'message' => 'รหัสผู้ใช้ไม่ถูกต้อง'], 400);
        }

        $stmt = $db->prepare("SELECT id, username, role, reseller_discount_percent FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$target) {
            json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลผู้ใช้นี้ในระบบ'], 404);
        }

        $sysPercent = get_reseller_discount_percent();
        $sysPercentStr = (round($sysPercent) == $sysPercent) ? (string)(int)$sysPercent : (string)$sysPercent;

        // ถ้าส่ง null, '', 'default', 'system' มา แสดงว่าต้องการใช้ค่าเริ่มต้นระบบ
        if ($rawPercent === null || $rawPercent === '' || $rawPercent === 'default' || $rawPercent === 'system') {
            $db->prepare("UPDATE users SET reseller_discount_percent = NULL WHERE id = ?")->execute([$userId]);
            json_response([
                'status' => 'success',
                'message' => 'รีเซ็ตส่วนลดของ "' . $target['username'] . '" ให้ใช้ค่าเริ่มต้นระบบ (' . $sysPercentStr . '%) แล้ว',
                'user_id' => $userId,
                'reseller_discount_percent' => null,
                'effective_discount_percent' => $sysPercent
            ]);
        }

        $percent = (float)$rawPercent;
        if ($percent < 0 || $percent > 100) {
            json_response(['status' => 'error', 'message' => 'เปอร์เซ็นต์ส่วนลดต้องอยู่ระหว่าง 0 ถึง 100%'], 400);
        }

        $db->prepare("UPDATE users SET reseller_discount_percent = ? WHERE id = ?")->execute([$percent, $userId]);
        $pctStr = (round($percent) == $percent) ? (string)(int)$percent : (string)$percent;

        json_response([
            'status' => 'success',
            'message' => 'บันทึกเปอร์เซ็นต์ส่วนลดของ "' . $target['username'] . '" เป็น ' . $pctStr . '% เรียบร้อยแล้ว',
            'user_id' => $userId,
            'reseller_discount_percent' => $percent,
            'effective_discount_percent' => $percent
        ]);
    }

    if ($action === 'promote') {
        $userId = (int)($body['user_id'] ?? 0);
        $rawDiscount = $body['discount_percent'] ?? $body['reseller_discount_percent'] ?? null;
        if ($userId <= 0) {
            json_response(['status' => 'error', 'message' => 'รหัสผู้ใช้ไม่ถูกต้อง'], 400);
        }

        $stmt = $db->prepare("SELECT id, username, role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$target) {
            json_response(['status' => 'error', 'message' => 'ไม่พบผู้ใช้นี้ในระบบ'], 404);
        }
        if ($target['role'] === 'admin') {
            json_response(['status' => 'error', 'message' => 'ไม่สามารถเปลี่ยนสถานะของผู้ดูแลระบบได้'], 400);
        }

        $customDiscount = null;
        if ($rawDiscount !== null && $rawDiscount !== '' && $rawDiscount !== 'default' && $rawDiscount !== 'system') {
            $customDiscount = max(0.0, min(100.0, (float)$rawDiscount));
            $db->prepare("UPDATE users SET role = 'reseller', reseller_discount_percent = ? WHERE id = ?")->execute([$customDiscount, $userId]);
        } else {
            $db->prepare("UPDATE users SET role = 'reseller', reseller_discount_percent = NULL WHERE id = ?")->execute([$userId]);
        }

        json_response(['status' => 'success', 'message' => 'แต่งตั้ง "' . $target['username'] . '" เป็นตัวแทนจำหน่ายเรียบร้อยแล้ว']);
    }

    if ($action === 'demote') {
        $userId = (int)($body['user_id'] ?? 0);
        if ($userId <= 0) {
            json_response(['status' => 'error', 'message' => 'รหัสผู้ใช้ไม่ถูกต้อง'], 400);
        }

        $stmt = $db->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'reseller'");
        $stmt->execute([$userId]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$target) {
            json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลตัวแทนนี้'], 404);
        }

        $db->prepare("UPDATE users SET role = 'user', reseller_discount_percent = NULL WHERE id = ?")->execute([$userId]);
        json_response(['status' => 'success', 'message' => 'ปลด "' . $target['username'] . '" กลับเป็นสมาชิกทั่วไปเรียบร้อยแล้ว']);
    }

    if ($action === 'create_reseller') {
        $username = trim($body['username'] ?? '');
        $password = trim($body['password'] ?? '');
        $initialBalance = max(0, (float)($body['initial_balance'] ?? 0));
        $rawDiscount = $body['discount_percent'] ?? $body['reseller_discount_percent'] ?? null;

        if (strlen($username) < 3 || strlen($password) < 4) {
            json_response(['status' => 'error', 'message' => 'ชื่อผู้ใช้ต้องมีอย่างน้อย 3 ตัวอักษร และรหัสผ่านอย่างน้อย 4 ตัวอักษร'], 400);
        }

        $exist = $db->prepare("SELECT id FROM users WHERE LOWER(username) = LOWER(?)");
        $exist->execute([$username]);
        if ($exist->fetch()) {
            json_response(['status' => 'error', 'message' => 'ชื่อผู้ใช้นี้มีในระบบแล้ว'], 400);
        }

        $customDiscount = null;
        if ($rawDiscount !== null && $rawDiscount !== '' && $rawDiscount !== 'default' && $rawDiscount !== 'system') {
            $customDiscount = max(0.0, min(100.0, (float)$rawDiscount));
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password, role, balance, admin_pin, reseller_discount_percent, created_at) VALUES (?, ?, 'reseller', ?, '123456', ?, datetime('now', 'localtime'))");
        $stmt->execute([$username, $hash, $initialBalance, $customDiscount]);

        $newId = (int)$db->lastInsertId();
        if ($initialBalance > 0) {
            $db->prepare("INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, 'admin_adjust', ?, 'ยอดเงินเริ่มต้นตัวแทน', datetime('now', 'localtime'))")
               ->execute([$newId, $initialBalance]);
        }

        json_response(['status' => 'success', 'message' => 'สร้างบัญชีตัวแทน "' . $username . '" สำเร็จ']);
    }

    if ($action === 'reset_password') {
        $userId = (int)($body['user_id'] ?? 0);
        $newPassword = trim($body['new_password'] ?? '');
        if ($userId <= 0 || strlen($newPassword) < 4) {
            json_response(['status' => 'error', 'message' => 'รหัสผ่านใหม่ต้องมีอย่างน้อย 4 ตัวอักษร'], 400);
        }

        $stmt = $db->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'reseller'");
        $stmt->execute([$userId]);
        $target = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$target) {
            json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลตัวแทนนี้'], 404);
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([$hash, $userId]);
        json_response(['status' => 'success', 'message' => 'เปลี่ยนรหัสผ่านของ "' . $target['username'] . '" เรียบร้อยแล้ว']);
    }

    if ($action === 'save_discount') {
        $percent = (float)($body['percent'] ?? 30);
        if ($percent < 0 || $percent > 100) {
            json_response(['status' => 'error', 'message' => 'เปอร์เซ็นต์ส่วนลดต้องอยู่ระหว่าง 0 ถึง 100%'], 400);
        }
        $stmt = $db->prepare("INSERT INTO system_settings (key, value) VALUES ('reseller_discount_percent', ?) 
            ON CONFLICT(key) DO UPDATE SET value = excluded.value");
        $stmt->execute([(string)$percent]);
        $pctStr = (round($percent) == $percent) ? (string)(int)$percent : (string)$percent;
        json_response([
            'status' => 'success', 
            'message' => 'บันทึกเปอร์เซ็นต์ส่วนลดตัวแทน (ค่าเริ่มต้นระบบ) เป็น ' . $pctStr . '% เรียบร้อยแล้ว',
            'percent' => $percent
        ]);
    }

    json_response(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง'], 400);
}

// GET Request: list
$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $discountPercent = get_reseller_discount_percent();

    $resellers = $db->query("
        SELECT u.id, u.username, u.role, u.balance, u.reseller_discount_percent, u.line_user_id, u.line_display_name, u.line_picture_url, u.created_at,
               COUNT(v.id) as total_vpns,
               COALESCE(SUM(CASE WHEN v.expiry_time > datetime('now', 'localtime') THEN 1 ELSE 0 END), 0) as active_vpns
        FROM users u
        LEFT JOIN vpn_configs v ON u.id = v.user_id
        WHERE u.role = 'reseller'
        GROUP BY u.id
        ORDER BY u.balance DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resellers as &$r) {
        $hasCustom = ($r['reseller_discount_percent'] !== null && is_numeric($r['reseller_discount_percent']));
        $r['reseller_discount_percent'] = $hasCustom ? (float)$r['reseller_discount_percent'] : null;
        $r['effective_discount_percent'] = $hasCustom ? (float)$r['reseller_discount_percent'] : (float)$discountPercent;
        $r['has_custom_discount'] = $hasCustom;
        $r['is_line_user'] = !empty($r['line_user_id']);
    }
    unset($r);

    // Fetch all non-resellers for promoting
    $eligible = $db->query("SELECT id, username, role, balance, reseller_discount_percent, line_user_id, line_display_name, line_picture_url FROM users WHERE role = 'user' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total stats
    $totalResellers = count($resellers);
    $totalBalance = array_sum(array_column($resellers, 'balance'));
    $totalVpns = array_sum(array_column($resellers, 'total_vpns'));

    // Recent reseller orders
    $orders = $db->query("
        SELECT o.*, u.username 
        FROM orders_history o
        JOIN users u ON o.user_id = u.id
        WHERE u.role = 'reseller'
        ORDER BY o.id DESC LIMIT 30
    ")->fetchAll(PDO::FETCH_ASSOC);

    json_response([
        'status' => 'success',
        'data' => [
            'resellers' => $resellers,
            'eligible_users' => $eligible,
            'reseller_discount_percent' => $discountPercent,
            'system_discount_percent' => $discountPercent,
            'stats' => [
                'total_resellers' => $totalResellers,
                'total_balance' => (float)$totalBalance,
                'total_vpns' => (int)$totalVpns,
                'reseller_discount_percent' => $discountPercent
            ],
            'recent_orders' => $orders
        ]
    ]);
}

json_response(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง'], 400);
