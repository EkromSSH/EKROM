<?php
require_once __DIR__ . '/db.php';
$user = require_auth();
if ($user['role'] !== 'admin') {
    json_response(['status' => 'error', 'message' => 'Admin required'], 403);
}

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: $_POST;
    $postAction = $body['action'] ?? $_POST['action'] ?? $_GET['action'] ?? '';

    if ($postAction === 'create') {
        $carrier = trim($body['carrier'] ?? 'AIS');
        $title = trim($body['title'] ?? '');
        $subtitle = trim($body['subtitle'] ?? '');
        $price_label = trim($body['price_label'] ?? '');
        $price_per = trim($body['price_per'] ?? '/ 30 วัน');
        $badge = trim($body['badge'] ?? 'ไม่จำกัด GB ✅');
        $duration = trim($body['duration_text'] ?? '30 วัน');
        $desc = trim($body['description'] ?? '');
        $desc_html = trim($body['desc_html'] ?? '');
        $warning = trim($body['warning'] ?? '');
        $warning_bg = trim($body['warning_bg'] ?? 'pink');
        $extra_html = trim($body['extra_html'] ?? '');
        $price = (float)($body['price'] ?? 0);
        $codes = is_array($body['codes'] ?? null) ? $body['codes'] : [];

        $theme = trim($body['theme_color'] ?? '');
        if (!$theme) {
            $lower = strtolower($carrier);
            if (strpos($lower, 'true') !== false) $theme = 'orange';
            else if (strpos($lower, 'dtac') !== false) $theme = 'purple';
            else if (strpos($lower, 'nt') !== false) $theme = 'yellow';
            else $theme = 'green';
        }

        if (!$title) {
            json_response(['status' => 'error', 'message' => 'กรุณาระบุชื่อแพ็กเกจ'], 400);
        }

        $stmt = $db->prepare("INSERT INTO addons (carrier, title, subtitle, price_label, price_per, badge, theme_color, duration_text, description, desc_html, warning, warning_bg, extra_html, price, subscription_codes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $carrier, $title, $subtitle, $price_label, $price_per, $badge, $theme, $duration,
            $desc, $desc_html, $warning, $warning_bg, $extra_html, $price, json_encode($codes, JSON_UNESCAPED_UNICODE)
        ]);

        json_response(['status' => 'success', 'message' => 'เพิ่มโปรเสริมใหม่สำเร็จ']);
    }

    if ($postAction === 'update') {
        $id = (int)($body['id'] ?? 0);
        $carrier = trim($body['carrier'] ?? 'AIS');
        $title = trim($body['title'] ?? '');
        $subtitle = trim($body['subtitle'] ?? '');
        $price_label = trim($body['price_label'] ?? '');
        $price_per = trim($body['price_per'] ?? '/ 30 วัน');
        $badge = trim($body['badge'] ?? 'ไม่จำกัด GB ✅');
        $duration = trim($body['duration_text'] ?? '30 วัน');
        $desc = trim($body['description'] ?? '');
        $desc_html = trim($body['desc_html'] ?? '');
        $warning = trim($body['warning'] ?? '');
        $warning_bg = trim($body['warning_bg'] ?? 'pink');
        $extra_html = trim($body['extra_html'] ?? '');
        $price = (float)($body['price'] ?? 0);
        $codes = is_array($body['codes'] ?? null) ? $body['codes'] : [];

        $theme = trim($body['theme_color'] ?? '');
        if (!$theme) {
            $lower = strtolower($carrier);
            if (strpos($lower, 'true') !== false) $theme = 'orange';
            else if (strpos($lower, 'dtac') !== false) $theme = 'purple';
            else if (strpos($lower, 'nt') !== false) $theme = 'yellow';
            else $theme = 'green';
        }

        if (!$id || !$title) {
            json_response(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน'], 400);
        }

        $stmt = $db->prepare("UPDATE addons SET carrier = ?, title = ?, subtitle = ?, price_label = ?, price_per = ?, badge = ?, theme_color = ?, duration_text = ?, description = ?, desc_html = ?, warning = ?, warning_bg = ?, extra_html = ?, price = ?, subscription_codes = ? WHERE id = ?");
        $stmt->execute([
            $carrier, $title, $subtitle, $price_label, $price_per, $badge, $theme, $duration,
            $desc, $desc_html, $warning, $warning_bg, $extra_html, $price, json_encode($codes, JSON_UNESCAPED_UNICODE), $id
        ]);

        json_response(['status' => 'success', 'message' => 'บันทึกโปรเสริมเรียบร้อยแล้ว']);
    }

    if ($postAction === 'delete') {
        $id = (int)($body['id'] ?? 0);
        $db->prepare("DELETE FROM addons WHERE id = ?")->execute([$id]);
        json_response(['status' => 'success', 'message' => 'ลบโปรเสริมแล้ว']);
    }

    json_response(['status' => 'error', 'message' => 'คำสั่งไม่ถูกต้อง'], 400);
}

$action = $_GET['action'] ?? 'list';
if ($action === 'list' || $action === 'get') {
    $addons = $db->query('SELECT * FROM addons ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    foreach ($addons as &$a) {
        $a['codes'] = json_decode($a['subscription_codes'], true) ?: [];
    }
    json_response(['status' => 'success', 'data' => $addons]);
}

json_response(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง'], 400);
