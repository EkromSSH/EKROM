<?php
require_once __DIR__ . '/db.php';
$db = get_db();
$warnings = $db->query('SELECT * FROM system_warnings WHERE id = 1')->fetch();
$defaultV2ray = "<b>ประเภทระบบ:</b> V2Ray (Vless / Vmess)\n<b>แอปที่ใช้เชื่อมต่อ:</b> V2rayNG, NekoBox, v2rayN, v2box, netmod, npvtunnel\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามโหลด BitTorrent (บิท) หรือสแปม";
$defaultSsh = "<b>ประเภทระบบ:</b> SSH (Secure Shell)\n<b>แอปที่ใช้เชื่อมต่อ:</b> Npv Tunnel, NetMod, HTTP Custom\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามนำไปใช้โหลด BitTorrent หรือกระทำผิด พรบ.คอมพิวเตอร์";

$defaultAgreementTitle = "ข้อตกลงก่อนซื้อไฟล์";
$defaultAgreementText = "ก่อนยืนยันการซื้อ กรุณาอ่านเงื่อนไขให้ครบถ้วน\n\nหากไฟล์ถูกบล็อกหรือใช้งานไม่ได้ โดยสาเหตุไม่ได้เกิดจากระบบของทางร้าน ทางร้านจะรับผิดชอบโดยคืนเป็นเครดิตภายในเว็บไซต์เท่านั้น\nไม่มีการคืนเงินหรือโอนเงินสดคืนทุกกรณี";
$defaultAgreementCheckbox = "ฉันอ่านและยอมรับข้อตกลง เข้าใจว่าการชดเชย (ถ้ามี) จะเป็นเครดิตในเว็บไซต์ และไม่มีการคืนเงินสด";

$v2rayText = !empty($warnings['v2ray_warning']) ? $warnings['v2ray_warning'] : $defaultV2ray;
$sshText = !empty($warnings['ssh_warning']) ? $warnings['ssh_warning'] : $defaultSsh;
$agreementTitle = !empty($warnings['agreement_title']) ? $warnings['agreement_title'] : $defaultAgreementTitle;
$agreementText = !empty($warnings['agreement_text']) ? $warnings['agreement_text'] : $defaultAgreementText;
$agreementCheckbox = !empty($warnings['agreement_checkbox']) ? $warnings['agreement_checkbox'] : $defaultAgreementCheckbox;

json_response([
    'status' => 'success',
    'data' => [
        'v2ray' => $v2rayText,
        'ssh' => $sshText,
        'warning_v2ray' => $v2rayText,
        'warning_ssh' => $sshText,
        'agreement_title' => $agreementTitle,
        'agreement_text' => $agreementText,
        'agreement_checkbox' => $agreementCheckbox
    ]
]);
