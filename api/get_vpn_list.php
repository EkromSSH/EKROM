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
    SELECT vc.id, vc.server_id, vc.uuid, vc.server_name, vc.package_name, vc.package_val, vc.price_paid, vc.protocol, vc.config_link, 
           vc.ssh_user, vc.ssh_pass, vc.upload_bytes, vc.download_bytes, vc.status_real, vc.created_at, vc.expiry_time,
           s.tier_id, pt.prices AS tier_prices_json
    FROM vpn_configs vc
    LEFT JOIN servers s ON vc.server_id = s.id
    LEFT JOIN price_tiers pt ON s.tier_id = pt.id
    WHERE vc.user_id = ? AND vc.status_real != 'deleted'
    ORDER BY vc.id DESC
");
$stmt->execute([$user['id']]);
$configs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($configs as &$cfg) {
    $prices = null;
    if (!empty($cfg['tier_prices_json'])) {
        $prices = json_decode($cfg['tier_prices_json'], true);
    }
    if (!is_array($prices) || count($prices) < 4) {
        $prices = [5, 25, 45, 80];
    }
    $cfg['tier_prices'] = array_values(array_map('floatval', $prices));
    unset($cfg['tier_prices_json']);
}
unset($cfg);

json_response([
    'status' => 'success',
    'data' => $configs
]);
