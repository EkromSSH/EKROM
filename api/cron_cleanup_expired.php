<?php
// api/cron_cleanup_expired.php - Auto cleanup expired VPN configs (> 3 days)
require_once __DIR__ . '/db.php';

$isCli = (php_sapi_name() === 'cli');
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '';
$isLocal = in_array($clientIp, ['127.0.0.1', '::1']);
$cronKey = $_GET['key'] ?? '';

// ตรวจสอบความปลอดภัยหากเรียกผ่านเว็บเบราว์เซอร์
if (!$isCli && !$isLocal) {
    $db = get_db();
    $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "cron_secret"');
    $stmt->execute();
    $storedKey = $stmt->fetchColumn();
    if (!empty($storedKey) && $cronKey !== $storedKey) {
        json_response(['status' => 'error', 'message' => 'Unauthorized'], 401);
    }
}

// สั่งล้างไฟล์: สมาชิกทดลองใช้งานเคลียร์ทันที, แพ็กเกจทั่วไปเคลียร์เมื่อหมดอายุเกิน 3 วัน
$result = cleanup_expired_vpns(3);

if ($isCli) {
    if ($result['count'] > 0) {
        echo "[" . date('Y-m-d H:i:s') . "] Auto Cleanup: {$result['count']} configs cleaned up (trials immediate, normal > 3 days).\n";
        foreach ($result['details'] as $det) {
            $typeStr = !empty($det['is_trial']) ? 'TRIAL' : 'NORMAL';
            echo "   - [{$typeStr}] ID: {$det['id']}, Server: {$det['server_id']}, XUI: {$det['xui_email']}, SSH: {$det['ssh_user']}\n";
        }
    } elseif (date('i') === '00' || isset($_GET['verbose'])) {
        echo "[" . date('Y-m-d H:i:s') . "] Auto Cleanup: 0 configs needed cleanup.\n";
    }
} else {
    json_response([
        'status' => 'success',
        'message' => "Auto Cleanup completed ({$result['count']} configs cleaned up: trials immediate, normal > 3 days)",
        'data' => $result
    ]);
}
