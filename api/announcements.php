<?php
require_once __DIR__ . '/db.php';
$db = get_db();
$action = $_GET['action'] ?? 'list';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = require_auth();
    if ($user['role'] !== 'admin') json_response(['status' => 'error', 'message' => 'Admin required'], 403);

    $data = get_post_json();
    $act = $data['action'] ?? $action;

    if ($act === 'create') {
        $title = trim($data['title'] ?? '');
        $content = trim($data['message'] ?? ($data['content'] ?? ''));
        $type = trim($data['type'] ?? 'info');
        if (!$title || !$content) json_response(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน']);

        $stmt = $db->prepare('INSERT INTO announcements (title, content, type, is_active) VALUES (?, ?, ?, 1)');
        $stmt->execute([$title, $content, $type]);
        json_response(['status' => 'success', 'message' => 'สร้างประกาศสำเร็จ']);
    }

    if ($act === 'update') {
        $id = (int)($data['id'] ?? 0);
        $title = trim($data['title'] ?? '');
        $content = trim($data['message'] ?? ($data['content'] ?? ''));
        $type = trim($data['type'] ?? 'info');

        $stmt = $db->prepare('UPDATE announcements SET title = ?, content = ?, type = ? WHERE id = ?');
        $stmt->execute([$title, $content, $type, $id]);
        json_response(['status' => 'success', 'message' => 'แก้ไขประกาศสำเร็จ']);
    }

    if ($act === 'toggle') {
        $id = (int)($data['id'] ?? 0);
        $db->prepare('UPDATE announcements SET is_active = CASE WHEN is_active = 1 THEN 0 ELSE 1 END WHERE id = ?')->execute([$id]);
        json_response(['status' => 'success', 'message' => 'สลับสถานะสำเร็จ']);
    }

    if ($act === 'delete') {
        $id = (int)($data['id'] ?? 0);
        $db->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
        json_response(['status' => 'success', 'message' => 'ลบประกาศสำเร็จ']);
    }
}

if ($action === 'admin_list') {
    $user = require_auth();
    if ($user['role'] !== 'admin') json_response(['status' => 'error', 'message' => 'Admin required'], 403);
    $list = $db->query('SELECT id, title, content as message, type, is_active FROM announcements ORDER BY id DESC')->fetchAll();
    json_response(['status' => 'success', 'data' => $list]);
}

// buyer list
$list = $db->query('SELECT id, title, content, content as message, type FROM announcements WHERE is_active = 1 ORDER BY id DESC')->fetchAll();
json_response([
    'status' => 'success',
    'data' => $list
]);
