<?php
require_once __DIR__ . '/db.php';
$db = get_db();
$warnings = $db->query('SELECT * FROM system_warnings WHERE id = 1')->fetch();
$defaultV2ray = "<b>ประเภทระบบ:</b> V2Ray (Vless / Vmess)\n<b>แอปที่ใช้เชื่อมต่อ:</b> V2rayNG, NekoBox, v2rayN, v2box, netmod, npvtunnel\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามโหลด BitTorrent (บิท) หรือสแปม";
$defaultSsh = "<b>ประเภทระบบ:</b> SSH (Secure Shell)\n<b>แอปที่ใช้เชื่อมต่อ:</b> Npv Tunnel, NetMod, HTTP Custom\n<b>โปรเสริม:</b> สำหรับ Nopro ไม่ต้องสมัครโปรเสริมใดๆ หากเป็นนอกเหนือจากนี้ดูที่ชื่อของไฟลืที่จะสร้างว่าต้องการโปรเสริมอะไร เเล้วทำการสมัครโปรเสริมให้ครบถ้งนก่อนใช้งาน\n❌ ห้ามนำไปใช้โหลด BitTorrent หรือกระทำผิด พรบ.คอมพิวเตอร์";

$defaultAgreementTitle = "ข้อตกลงก่อนซื้อไฟล์";
$defaultAgreementText = "ก่อนยืนยันการซื้อ กรุณาอ่านเงื่อนไขให้ครบถ้วน\n\nหากไฟล์ถูกบล็อกหรือใช้งานไม่ได้ โดยสาเหตุไม่ได้เกิดจากระบบของทางร้าน ทางร้านจะรับผิดชอบโดยคืนเป็นเครดิตภายในเว็บไซต์เท่านั้น\nไม่มีการคืนเงินหรือโอนเงินสดคืนทุกกรณี";
$defaultAgreementCheckbox = "ฉันอ่านและยอมรับข้อตกลง เข้าใจว่าการชดเชย (ถ้ามี) จะเป็นเครดิตในเว็บไซต์ และไม่มีการคืนเงินสด";

if (!$warnings) { $warnings = []; }
$v2rayText = isset($warnings['v2ray_warning']) && $warnings['v2ray_warning'] !== null ? $warnings['v2ray_warning'] : $defaultV2ray;
$sshText = isset($warnings['ssh_warning']) && $warnings['ssh_warning'] !== null ? $warnings['ssh_warning'] : $defaultSsh;

// CRITICAL FIX: If column exists in DB, respect the exact value (even if blank "")
$agreementTitle = isset($warnings['agreement_title']) && $warnings['agreement_title'] !== null ? $warnings['agreement_title'] : $defaultAgreementTitle;
$agreementText = isset($warnings['agreement_text']) && $warnings['agreement_text'] !== null ? $warnings['agreement_text'] : $defaultAgreementText;
$agreementCheckbox = isset($warnings['agreement_checkbox']) && $warnings['agreement_checkbox'] !== null ? $warnings['agreement_checkbox'] : $defaultAgreementCheckbox;

$agreementEnabled = isset($warnings['agreement_enabled']) ? (int)$warnings['agreement_enabled'] : 1;
$agreementTitleColor = !empty($warnings['agreement_title_color']) ? $warnings['agreement_title_color'] : '#92400e';
$agreementTitleSize = !empty($warnings['agreement_title_size']) ? $warnings['agreement_title_size'] : '13px';
$agreementTitleWeight = !empty($warnings['agreement_title_weight']) ? $warnings['agreement_title_weight'] : 'bold';

$agreementTextColor = !empty($warnings['agreement_text_color']) ? $warnings['agreement_text_color'] : '#334155';
$agreementTextBoldColor = !empty($warnings['agreement_text_bold_color']) ? $warnings['agreement_text_bold_color'] : '#dc2626';
$agreementTextSize = !empty($warnings['agreement_text_size']) ? $warnings['agreement_text_size'] : '12px';
$agreementTextWeight = !empty($warnings['agreement_text_weight']) ? $warnings['agreement_text_weight'] : 'normal';

$agreementCheckboxColor = !empty($warnings['agreement_checkbox_color']) ? $warnings['agreement_checkbox_color'] : '#1e293b';
$agreementCheckboxSize = !empty($warnings['agreement_checkbox_size']) ? $warnings['agreement_checkbox_size'] : '12px';
$agreementCheckboxWeight = !empty($warnings['agreement_checkbox_weight']) ? $warnings['agreement_checkbox_weight'] : 'bold';

json_response([
    'status' => 'success',
    'data' => [
        'v2ray' => $v2rayText,
        'ssh' => $sshText,
        'warning_v2ray' => $v2rayText,
        'warning_ssh' => $sshText,
        'agreement_enabled' => $agreementEnabled,
        'agreement_title' => $agreementTitle,
        'agreement_text' => $agreementText,
        'agreement_checkbox' => $agreementCheckbox,
        'agreement_title_color' => $agreementTitleColor,
        'agreement_title_size' => $agreementTitleSize,
        'agreement_title_weight' => $agreementTitleWeight,
        'agreement_text_color' => $agreementTextColor,
        'agreement_text_bold_color' => $agreementTextBoldColor,
        'agreement_text_size' => $agreementTextSize,
        'agreement_text_weight' => $agreementTextWeight,
        'agreement_checkbox_color' => $agreementCheckboxColor,
        'agreement_checkbox_size' => $agreementCheckboxSize,
        'agreement_checkbox_weight' => $agreementCheckboxWeight
    ]
]);
