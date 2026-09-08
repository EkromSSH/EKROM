<?php
require_once __DIR__ . '/db.php';
$user = require_auth();
if ($user['role'] !== 'admin') json_response(['status' => 'error', 'message' => 'Admin required'], 403);

$db = get_db();
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = get_post_json();
    $act = $data['action'] ?? $action;

    if ($act === 'delete') {
        $id = (int)($data['id'] ?? 0);
        $db->prepare('DELETE FROM servers WHERE id = ?')->execute([$id]);
        json_response(['status' => 'success', 'message' => 'ลบเซิร์ฟเวอร์สำเร็จ']);
    }

    if ($act === 'toggle') {
        $id = (int)($data['id'] ?? 0);
        $statusStr = trim($data['status'] ?? 'active');
        $statusVal = ($statusStr === 'active') ? 1 : 0;
        $db->prepare('UPDATE servers SET is_active = ? WHERE id = ?')->execute([$statusVal, $id]);
        json_response(['status' => 'success', 'message' => 'อัปเดตสถานะเซิร์ฟเวอร์สำเร็จ']);
    }

    // Save or Create server
    $id = (int)($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $catId = $data['category_id'] !== '' ? (int)$data['category_id'] : null;
    $tierId = (int)($data['price_tier'] ?? 1);
    $type = trim($data['type'] ?? 'v2ray');
    $panelUrl = trim($data['panel_url'] ?? '');
    $username = trim($data['username'] ?? '');
    $password = trim($data['password'] ?? '');
    $inboundId = trim($data['inbound_id'] ?? '');
    $domain = trim($data['domain'] ?? '');
    $bugHost = trim($data['bug_host'] ?? '');
    $port = (int)($data['port'] ?? 443);
    $vlessPort = (int)($data['vless_port'] ?? 443);
    $pbk = trim($data['pbk'] ?? '');
    $sids = trim($data['sids'] ?? '');
    $desc = trim($data['description'] ?? '');
    $addonId = is_array($data['addon_id'] ?? null) ? implode(',', $data['addon_id']) : (string)($data['addon_id'] ?? '');
    $sshTemplates = is_array($data['ssh_templates'] ?? null) ? json_encode($data['ssh_templates'], JSON_UNESCAPED_UNICODE) : '';
    $netmodTemplates = is_array($data['netmod_templates'] ?? null) ? json_encode($data['netmod_templates'], JSON_UNESCAPED_UNICODE) : '';
    $connectionMode = trim($data['connection_mode'] ?? 'api');

    if ($name === '') {
        json_response(['status' => 'error', 'message' => 'กรุณากรอกชื่อเซิร์ฟเวอร์']);
    }

    if ($id > 0) {
        // Update existing
        $stmt = $db->prepare("
            UPDATE servers SET 
                name = ?, category_id = ?, tier_id = ?, type = ?, panel_url = ?, 
                username = ?, password = ?, inbound_id = ?, domain = ?, bug_host = ?, 
                port = ?, vless_port = ?, pbk = ?, sids = ?, description = ?, 
                addon_id = ?, ssh_templates = ?, netmod_templates = ?, connection_mode = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $name, $catId, $tierId, $type, $panelUrl, $username, $password,
            $inboundId, $domain, $bugHost, $port, $vlessPort, $pbk, $sids,
            $desc, $addonId, $sshTemplates, $netmodTemplates, $connectionMode, $id
        ]);
        json_response(['status' => 'success', 'message' => 'แก้ไขเซิร์ฟเวอร์เรียบร้อยแล้ว']);
    } else {
        // Insert new
        $stmt = $db->prepare("
            INSERT INTO servers (
                name, category_id, tier_id, type, panel_url, username, password,
                inbound_id, domain, bug_host, port, vless_port, pbk, sids,
                description, addon_id, ssh_templates, netmod_templates, connection_mode, is_active
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
        ");
        $stmt->execute([
            $name, $catId, $tierId, $type, $panelUrl, $username, $password,
            $inboundId, $domain, $bugHost, $port, $vlessPort, $pbk, $sids,
            $desc, $addonId, $sshTemplates, $netmodTemplates, $connectionMode
        ]);
        json_response(['status' => 'success', 'message' => 'เพิ่มเซิร์ฟเวอร์ใหม่สำเร็จแล้ว']);
    }
}

// List servers
$query = "
    SELECT s.*, c.name as category_name, pt.name as price_tier_name
    FROM servers s
    LEFT JOIN categories c ON s.category_id = c.id
    LEFT JOIN price_tiers pt ON s.tier_id = pt.id
    ORDER BY s.id DESC
";
$servers = $db->query($query)->fetchAll();

$result = [];
foreach ($servers as $s) {
    $statusStr = ($s['is_active'] == 1) ? 'active' : 'inactive';
    $sshTpls = json_decode($s['ssh_templates'] ?? '', true) ?: [];
    $netmodTpls = json_decode($s['netmod_templates'] ?? '', true) ?: [];
    
    $result[] = [
        'id' => (int)$s['id'],
        'name' => $s['name'],
        'category_id' => $s['category_id'] !== null ? (int)$s['category_id'] : null,
        'category_name' => $s['category_name'] ?: 'ทั่วไป',
        'tier_id' => (int)$s['tier_id'],
        'price_tier' => (int)$s['tier_id'],
        'price_tier_name' => $s['price_tier_name'] ?: ('โซน ' . $s['tier_id']),
        'type' => $s['type'] ?: 'vless',
        'status' => $statusStr,
        'inbound_id' => $s['inbound_id'] ?: '1',
        'port' => $s['port'] ?: 443,
        'vless_port' => $s['vless_port'] ?: 443,
        'domain' => $s['domain'] ?: $s['host'],
        'bug_host' => $s['bug_host'],
        'pbk' => $s['pbk'],
        'sids' => $s['sids'],
        'panel_url' => $s['panel_url'],
        'username' => $s['username'],
        'password' => $s['password'],
        'description' => $s['description'],
        'addon_id' => $s['addon_id'],
        'ssh_templates' => $sshTpls,
        'netmod_templates' => $netmodTpls,
        'connection_mode' => $s['connection_mode'] ?: 'api'
    ];
}

json_response(['status' => 'success', 'data' => $result]);
