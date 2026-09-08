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
        $theme = trim($body['color_theme'] ?? 'blue');
        $order = (int)($body['sort_order'] ?? 0);

        if (!$name) {
            json_response(['status' => 'error', 'message' => 'กรุณาระบุชื่อหมวดหมู่'], 400);
        }

        // Check duplicate name
        $chk = $db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?)");
        $chk->execute([$name]);
        if ($chk->fetch()) {
            json_response(['status' => 'error', 'message' => 'ชื่อหมวดหมู่นี้มีอยู่ในระบบแล้ว'], 400);
        }

        $stmt = $db->prepare("INSERT INTO categories (name, color_theme, sort_order) VALUES (?, ?, ?)");
        $stmt->execute([$name, $theme, $order]);

        json_response(['status' => 'success', 'message' => 'สร้างหมวดหมู่ใหม่เรียบร้อยแล้ว']);
    }

    if ($action === 'update') {
        $id = (int)($body['id'] ?? 0);
        $name = trim($body['name'] ?? '');
        $theme = trim($body['color_theme'] ?? 'blue');
        $order = (int)($body['sort_order'] ?? 0);

        if (!$id || !$name) {
            json_response(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน'], 400);
        }

        $cat = $db->prepare("SELECT id FROM categories WHERE id = ?");
        $cat->execute([$id]);
        if (!$cat->fetch()) {
            json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลหมวดหมู่นี้'], 404);
        }

        // Check duplicate name on other categories
        $chk = $db->prepare("SELECT id FROM categories WHERE LOWER(name) = LOWER(?) AND id != ?");
        $chk->execute([$name, $id]);
        if ($chk->fetch()) {
            json_response(['status' => 'error', 'message' => 'ชื่อหมวดหมู่นี้ซ้ำกับหมวดหมู่อื่น'], 400);
        }

        $stmt = $db->prepare("UPDATE categories SET name = ?, color_theme = ?, sort_order = ? WHERE id = ?");
        $stmt->execute([$name, $theme, $order, $id]);

        json_response(['status' => 'success', 'message' => 'บันทึกการแก้ไขหมวดหมู่แล้ว']);
    }

    if ($action === 'delete') {
        $id = (int)($body['id'] ?? 0);
        if (!$id) {
            json_response(['status' => 'error', 'message' => 'ไม่พบรหัสหมวดหมู่'], 400);
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM servers WHERE category_id = ?");
        $stmt->execute([$id]);
        $inUse = (int)$stmt->fetchColumn();
        if ($inUse > 0) {
            json_response(['status' => 'error', 'message' => "ไม่สามารถลบได้ เนื่องจากมี {$inUse} เซิร์ฟเวอร์กำลังอยู่ในหมวดหมู่นี้ กรุณาย้ายเซิร์ฟเวอร์ก่อนลบ"], 400);
        }

        $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        json_response(['status' => 'success', 'message' => 'ลบหมวดหมู่เรียบร้อยแล้ว']);
    }

    json_response(['status' => 'error', 'message' => 'ไม่พบคำสั่งที่ต้องการ'], 400);
}

// GET handler
$action = $_GET['action'] ?? 'list';

if ($action === 'list' || $action === 'get') {
    $cats = $db->query('SELECT * FROM categories ORDER BY sort_order ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
    $totalServers = 0;
    foreach ($cats as &$c) {
        $stmt = $db->prepare("SELECT COUNT(*) FROM servers WHERE category_id = ?");
        $stmt->execute([$c['id']]);
        $cnt = (int)$stmt->fetchColumn();
        $c['server_count'] = $cnt;
        $totalServers += $cnt;
    }

    $unassigned = (int)$db->query("SELECT COUNT(*) FROM servers WHERE category_id IS NULL OR category_id = 0")->fetchColumn();

    json_response([
        'status' => 'success',
        'data' => $cats,
        'stats' => [
            'total_categories' => count($cats),
            'total_assigned_servers' => $totalServers,
            'unassigned_servers' => $unassigned
        ]
    ]);
}

json_response(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง'], 400);
