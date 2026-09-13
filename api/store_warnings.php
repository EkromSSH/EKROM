<?php
require_once __DIR__ . '/db.php';
$db = get_db();
$warnings = $db->query('SELECT v2ray_warning, ssh_warning FROM system_warnings WHERE id = 1')->fetch();
$defaultV2ray = "<b>ประเภทระบบ:</b> V2Ray (Vless / Vmess)\n<b>แอปที่ใช้เชื่อมต่อ:</b> V2rayNG, NekoBox, v2rayN, v2box, netmod, npvtunnel\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามโหลด BitTorrent (บิท) หรือสแปม";
$defaultSsh = "<b>ประเภทระบบ:</b> SSH (Secure Shell)\n<b>แอปที่ใช้เชื่อมต่อ:</b> Npv Tunnel, NetMod, HTTP Custom\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามนำไปใช้โหลด BitTorrent หรือกระทำผิด พรบ.คอมพิวเตอร์";

$v2rayText = !empty($warnings['v2ray_warning']) ? $warnings['v2ray_warning'] : $defaultV2ray;
$sshText = !empty($warnings['ssh_warning']) ? $warnings['ssh_warning'] : $defaultSsh;

json_response([
    'status' => 'success',
    'data' => [
        'v2ray' => $v2rayText,
        'ssh' => $sshText,
        'warning_v2ray' => $v2rayText,
        'warning_ssh' => $sshText
    ]
]);
