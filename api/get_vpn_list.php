<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = require_auth();

$db = get_db();
$nowBkk = date('Y-m-d H:i:s');

// 1. ตรวจสอบและเคลียร์ไฟล์ทดลองใช้ที่หมดอายุทันที (ลบออกจากหน้าเว็บและ X-UI / VPS ทันที)
$trialCheck = $db->prepare("
    SELECT COUNT(*) FROM vpn_configs 
    WHERE status_real != 'deleted' 
      AND (package_val = 'trial' OR package_name LIKE '%ทดลอง%') 
      AND expiry_time <= ?
");
$trialCheck->execute([$nowBkk]);
if ((int)$trialCheck->fetchColumn() > 0) {
    cleanup_expired_vpns(3);
}

// 2. ปรับสถานะไฟล์แพ็กเกจปกติที่หมดอายุเป็น 'expired' หากยังเป็น 'active'
$db->prepare("UPDATE vpn_configs SET status_real = 'expired' WHERE expiry_time < ? AND status_real = 'active'")->execute([$nowBkk]);

// Opportunistic auto-cleanup (ตรวจเช็กล้างไฟล์หมดอายุเกิน 3 วัน ในพื้นหลังแบบ Asynchronous ไม่บล็อกหน้าเว็บ)
$lastCleanupFile = sys_get_temp_dir() . '/ekrom_last_cleanup.txt';
if (!file_exists($lastCleanupFile) || (time() - filemtime($lastCleanupFile)) > 1800) {
    @touch($lastCleanupFile);
    @exec('/usr/bin/php ' . escapeshellarg(__DIR__ . '/cron_cleanup_expired.php') . ' >/dev/null 2>&1 &');
}

$stmt = $db->prepare("
    SELECT id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, 
           ssh_user, ssh_pass, upload_bytes, download_bytes, status_real, created_at, expiry_time
    FROM vpn_configs 
    WHERE user_id = ? AND status_real != 'deleted'
    ORDER BY id DESC
");
$stmt->execute([$user['id']]);
$configs = $stmt->fetchAll();

json_response([
    'status' => 'success',
    'data' => $configs
]);
