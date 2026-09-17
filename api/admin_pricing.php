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
        $theme = trim($body['color_theme'] ?? 'pink');
        $p1 = max(0, (float)($body['p1'] ?? 0));
        $p7 = max(0, (float)($body['p7'] ?? 0));
        $p15 = max(0, (float)($body['p15'] ?? 0));
        $p30 = max(0, (float)($body['p30'] ?? 0));
        $prices = [$p1, $p7, $p15, $p30];

        if (!$name) {
            json_response(['status' => 'error', 'message' => 'กรุณาระบุชื่อโซนราคา'], 400);
        }

        // Check duplicate name
        $chk = $db->prepare("SELECT id FROM price_tiers WHERE LOWER(name) = LOWER(?)");
        $chk->execute([$name]);
        if ($chk->fetch()) {
            json_response(['status' => 'error', 'message' => 'ชื่อโซนราคานี้มีอยู่ในระบบแล้ว'], 400);
        }

        $stmt = $db->prepare("INSERT INTO price_tiers (name, color_theme, prices) VALUES (?, ?, ?)");
        $stmt->execute([$name, $theme, json_encode($prices)]);

        json_response(['status' => 'success', 'message' => 'เพิ่มโซนราคาเรียบร้อยแล้ว']);
    }

    if ($action === 'update') {
        $id = (int)($body['id'] ?? 0);
        $name = trim($body['name'] ?? '');
        $theme = trim($body['color_theme'] ?? 'pink');
        $p1 = max(0, (float)($body['p1'] ?? 0));
        $p7 = max(0, (float)($body['p7'] ?? 0));
        $p15 = max(0, (float)($body['p15'] ?? 0));
        $p30 = max(0, (float)($body['p30'] ?? 0));
        $prices = [$p1, $p7, $p15, $p30];

        if (!$id || !$name) {
            json_response(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน'], 400);
        }

        $tier = $db->prepare("SELECT id FROM price_tiers WHERE id = ?");
        $tier->execute([$id]);
        if (!$tier->fetch()) {
            json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลโซนราคานี้'], 404);
        }

        // Check duplicate name on other tiers
        $chk = $db->prepare("SELECT id FROM price_tiers WHERE LOWER(name) = LOWER(?) AND id != ?");
        $chk->execute([$name, $id]);
        if ($chk->fetch()) {
            json_response(['status' => 'error', 'message' => 'ชื่อโซนราคานี้ซ้ำกับโซนราคาอื่น'], 400);
        }

        $stmt = $db->prepare("UPDATE price_tiers SET name = ?, color_theme = ?, prices = ? WHERE id = ?");
        $stmt->execute([$name, $theme, json_encode($prices), $id]);

        json_response(['status' => 'success', 'message' => 'บันทึกการแก้ไขราคาแล้ว']);
    }

    if ($action === 'delete') {
        $id = (int)($body['id'] ?? 0);
        if (!$id) {
            json_response(['status' => 'error', 'message' => 'ไม่พบรหัสโซนราคา'], 400);
        }

        $stmt = $db->prepare("SELECT COUNT(*) FROM servers WHERE tier_id = ?");
        $stmt->execute([$id]);
        $inUse = (int)$stmt->fetchColumn();
        if ($inUse > 0) {
            json_response(['status' => 'error', 'message' => "ไม่สามารถลบได้ เนื่องจากมี {$inUse} เซิร์ฟเวอร์กำลังใช้โซนนี้อยู่ กรุณาเปลี่ยนโซนราคาของเซิร์ฟเวอร์ก่อนลบ"], 400);
        }

        $stmt = $db->prepare("DELETE FROM price_tiers WHERE id = ?");
        $stmt->execute([$id]);
        json_response(['status' => 'success', 'message' => 'ลบโซนราคาเรียบร้อยแล้ว']);
    }

    json_response(['status' => 'error', 'message' => 'ไม่พบคำสั่งที่ต้องการ'], 400);
}

// GET handler
$action = $_GET['action'] ?? 'list';

if ($action === 'list' || $action === 'get') {
    $tiers = $db->query('SELECT * FROM price_tiers ORDER BY id ASC')->fetchAll(PDO::FETCH_ASSOC);
    $totalServers = 0;
    $minPrice = null;

    foreach ($tiers as &$t) {
        $prices = json_decode($t['prices'], true) ?: [5, 25, 45, 80];
        $t['prices'] = $prices;
        $t['price_1'] = $prices[0] ?? 5;
        $t['price_7'] = $prices[1] ?? 25;
        $t['price_15'] = $prices[2] ?? 45;
        $t['price_30'] = $prices[3] ?? 80;
        $t['theme'] = $t['color_theme'];

        $stmt = $db->prepare("SELECT COUNT(*) FROM servers WHERE tier_id = ?");
        $stmt->execute([$t['id']]);
        $serverCount = (int)$stmt->fetchColumn();
        $t['server_count'] = $serverCount;
        $totalServers += $serverCount;

        if (isset($prices[0])) {
            if ($minPrice === null || (float)$prices[0] < $minPrice) {
                $minPrice = (float)$prices[0];
            }
        }
    }

    json_response([
        'status' => 'success',
        'data' => $tiers,
        'stats' => [
            'total_tiers' => count($tiers),
            'total_servers' => $totalServers,
            'min_price' => $minPrice ?? 0
        ]
    ]);
}

json_response(['status' => 'error', 'message' => 'คำขอไม่ถูกต้อง'], 400);
