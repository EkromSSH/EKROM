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

    if ($action === 'create') {
        $name = trim($body['name'] ?? '');
        $domain = trim($body['domain'] ?? '');
        $owner = trim($body['owner_username'] ?? 'reseller');
        $tier = trim($body['package_tier'] ?? 'Standard');
        $fee = (float)($body['monthly_fee'] ?? 299);
        $days = (int)($body['days'] ?? 30);
        $status = in_array($body['status'] ?? '', ['active', 'suspended', 'trial']) ? $body['status'] : 'active';

        // Clean domain
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = rtrim(trim($domain), '/');

        if (!$name || !$domain) {
            json_response(['status' => 'error', 'message' => 'กรุณาระบุชื่อร้านและโดเมน'], 400);
        }

        // Check duplicate domain
        $chk = $db->prepare("SELECT id FROM tenant_shops WHERE LOWER(domain) = LOWER(?)");
        $chk->execute([$domain]);
        if ($chk->fetch()) {
            json_response(['status' => 'error', 'message' => 'โดเมนนี้มีอยู่ในระบบแล้ว กรุณาใช้โดเมนอื่น'], 400);
        }

        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$days} days"));
        $stmt = $db->prepare("INSERT INTO tenant_shops (name, domain, owner_username, package_tier, status, monthly_fee, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $domain, $owner, $tier, $status, $fee, $expiresAt]);

        json_response(['status' => 'success', 'message' => 'สร้างร้านค้าเช่าเรียบร้อยแล้ว']);
    }

    if ($action === 'update') {
        $id = (int)($body['id'] ?? 0);
        $name = trim($body['name'] ?? '');
        $domain = trim($body['domain'] ?? '');
        $owner = trim($body['owner_username'] ?? 'reseller');
        $tier = trim($body['package_tier'] ?? 'Standard');
        $fee = (float)($body['monthly_fee'] ?? 299);
        $status = in_array($body['status'] ?? '', ['active', 'suspended', 'trial']) ? $body['status'] : 'active';
        $expiresAt = trim($body['expires_at'] ?? '');

        // Clean domain
        $domain = preg_replace('#^https?://#i', '', $domain);
        $domain = rtrim(trim($domain), '/');

        if (!$id || !$name || !$domain) {
            json_response(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบถ้วน'], 400);
        }

        $shop = $db->query("SELECT * FROM tenant_shops WHERE id = {$id}")->fetch(PDO::FETCH_ASSOC);
        if (!$shop) {
            json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลร้านค้านี้'], 404);
        }

        // Check duplicate domain on other shops
        $chk = $db->prepare("SELECT id FROM tenant_shops WHERE LOWER(domain) = LOWER(?) AND id != ?");
        $chk->execute([$domain, $id]);
        if ($chk->fetch()) {
            json_response(['status' => 'error', 'message' => 'โดเมนนี้ถูกใช้โดยร้านค้าอื่นแล้ว'], 400);
        }

        $newExpiresAt = !empty($expiresAt) ? date('Y-m-d H:i:s', strtotime($expiresAt)) : $shop['expires_at'];

        $stmt = $db->prepare("UPDATE tenant_shops SET name = ?, domain = ?, owner_username = ?, package_tier = ?, status = ?, monthly_fee = ?, expires_at = ? WHERE id = ?");
        $stmt->execute([$name, $domain, $owner, $tier, $status, $fee, $newExpiresAt, $id]);

        json_response(['status' => 'success', 'message' => 'แก้ไขข้อมูลร้านค้าสำเร็จ']);
    }

    if ($action === 'renew') {
        $id = (int)($body['id'] ?? 0);
        $days = (int)($body['days'] ?? 30);
        if ($days <= 0) $days = 30;

        $shop = $db->query("SELECT * FROM tenant_shops WHERE id = {$id}")->fetch(PDO::FETCH_ASSOC);
        if (!$shop) {
            json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลร้านค้านี้'], 404);
        }

        $baseTime = max(time(), strtotime($shop['expires_at']));
        $newExpiry = date('Y-m-d H:i:s', strtotime("+{$days} days", $baseTime));
        $newStatus = ($shop['status'] === 'suspended' || $shop['status'] === 'trial') ? 'active' : $shop['status'];

        $db->prepare("UPDATE tenant_shops SET expires_at = ?, status = ? WHERE id = ?")->execute([$newExpiry, $newStatus, $id]);

        json_response(['status' => 'success', 'message' => "ต่ออายุร้านค้า +{$days} วัน เรียบร้อยแล้ว", 'expires_at' => $newExpiry]);
    }

    if ($action === 'toggle_status') {
        $id = (int)($body['id'] ?? 0);
        $shop = $db->query("SELECT status FROM tenant_shops WHERE id = {$id}")->fetch(PDO::FETCH_ASSOC);
        if (!$shop) json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลร้านค้านี้'], 404);

        $newStatus = ($shop['status'] === 'active') ? 'suspended' : 'active';
        $db->prepare("UPDATE tenant_shops SET status = ? WHERE id = ?")->execute([$newStatus, $id]);
        $statusText = ($newStatus === 'active') ? 'เปิดใช้งาน' : 'ระงับชั่วคราว';
        json_response(['status' => 'success', 'message' => "เปลี่ยนสถานะร้านเป็น {$statusText} แล้ว", 'new_status' => $newStatus]);
    }

    if ($action === 'delete') {
        $id = (int)($body['id'] ?? 0);
        $db->prepare("DELETE FROM tenant_shops WHERE id = ?")->execute([$id]);
        json_response(['status' => 'success', 'message' => 'ลบร้านค้าเช่าแล้ว']);
    }

    json_response(['status' => 'error', 'message' => 'ไม่พบคำสั่งที่ต้องการ'], 400);
}

// GET handler
$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
    $shops = $db->query("SELECT * FROM tenant_shops ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    $totalShops = count($shops);
    $activeShops = 0;
    $totalRev = 0;

    foreach ($shops as &$s) {
        $isExp = (strtotime($s['expires_at']) < time());
        $s['is_expired'] = $isExp;
        if (($s['status'] === 'active' || $s['status'] === 'trial') && !$isExp) {
            $activeShops++;
        }
        $totalRev += (float)$s['monthly_fee'];
    }

    $users = $db->query("SELECT username, role FROM users ORDER BY role DESC, username ASC")->fetchAll(PDO::FETCH_ASSOC);

    json_response([
        'status' => 'success',
        'data' => [
            'shops' => $shops,
            'users' => $users,
            'stats' => [
                'total_shops' => $totalShops,
                'active_shops' => $activeShops,
                'total_monthly_rev' => $totalRev
            ]
        ]
    ]);
}

json_response(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง'], 400);
