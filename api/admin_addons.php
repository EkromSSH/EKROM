<?php
require_once __DIR__ . '/db.php';
$user = require_auth();
if ($user['role'] !== 'admin') {
    json_response(['status' => 'error', 'message' => 'Admin required'], 403);
}

$db = get_db();
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

if ($action === 'list' || $action === 'get') {
    $addons = $db->query('SELECT * FROM addons ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($addons as &$a) {
        $a['codes'] = json_decode($a['subscription_codes'], true) ?: [];
    }
    json_response(['status' => 'success', 'data' => $addons]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: $_POST;
    $postAction = $body['action'] ?? $action;

    if ($postAction === 'create') {
        $carrier = trim($body['carrier'] ?? 'AIS 5G');
        $title = trim($body['title'] ?? '');
        $theme = trim($body['theme_color'] ?? 'green');
        $duration = trim($body['duration_text'] ?? '30 วัน');
        $desc = trim($body['description'] ?? '');
        $price = (float)($body['price'] ?? 0);
        $codes = $body['codes'] ?? [];

        if (!$title) {
            json_response(['status' => 'error', 'message' => 'กรุณาระบุชื่อแพ็กเกจ'], 400);
        }

        $stmt = $db->prepare("INSERT INTO addons (carrier, title, theme_color, duration_text, description, price, subscription_codes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$carrier, $title, $theme, $duration, $desc, $price, json_encode($codes)]);

        json_response(['status' => 'success', 'message' => 'เพิ่มโปรเสริมใหม่สำเร็จ']);
    }

    if ($postAction === 'update') {
        $id = (int)($body['id'] ?? 0);
        $carrier = trim($body['carrier'] ?? 'AIS 5G');
        $title = trim($body['title'] ?? '');
        $theme = trim($body['theme_color'] ?? 'green');
        $duration = trim($body['duration_text'] ?? '30 วัน');
        $desc = trim($body['description'] ?? '');
        $price = (float)($body['price'] ?? 0);
        $codes = $body['codes'] ?? [];

        if (!$id || !$title) {
            json_response(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน'], 400);
        }

        $stmt = $db->prepare("UPDATE addons SET carrier = ?, title = ?, theme_color = ?, duration_text = ?, description = ?, price = ?, subscription_codes = ? WHERE id = ?");
        $stmt->execute([$carrier, $title, $theme, $duration, $desc, $price, json_encode($codes), $id]);

        json_response(['status' => 'success', 'message' => 'บันทึกโปรเสริมเรียบร้อยแล้ว']);
    }

    if ($postAction === 'delete') {
        $id = (int)($body['id'] ?? 0);
        $db->prepare("DELETE FROM addons WHERE id = ?")->execute([$id]);
        json_response(['status' => 'success', 'message' => 'ลบโปรเสริมแล้ว']);
    }
}

json_response(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง'], 400);
