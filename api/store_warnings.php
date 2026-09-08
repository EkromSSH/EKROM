<?php
require_once __DIR__ . '/db.php';
$db = get_db();
$warnings = $db->query('SELECT v2ray_warning, ssh_warning FROM system_warnings WHERE id = 1')->fetch();
$v2rayText = $warnings['v2ray_warning'] ?? "รองรับแอป v2rayNG, Shadowrocket, Streisand, Sing-box\nห้ามนำไปใช้ยิงหรือโจมตีเซิร์ฟเวอร์อื่น\nความเร็วขึ้นอยู่กับพื้นที่และแพ็กเกจเน็ตของผู้ใช้";
$sshText = $warnings['ssh_warning'] ?? "รองรับแอป NPV Tunnel, NetMod, HTTP Custom\nใส่ Username และ Password ตามที่ตั้งไว้\nห้ามดาวน์โหลดบิททอร์เรนต์ (BitTorrent)";

json_response([
    'status' => 'success',
    'data' => [
        'v2ray' => $v2rayText,
        'ssh' => $sshText,
        'warning_v2ray' => $v2rayText,
        'warning_ssh' => $sshText
    ]
]);
