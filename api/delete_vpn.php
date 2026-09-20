<?php
require_once __DIR__ . '/db.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();
$data = get_post_json();
$configId = (int)($data['config_id'] ?? 0);
$action = $data['action'] ?? 'delete'; // 'preview' or 'delete'

$db = get_db();
$stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ?');
$stmt->execute([$configId, $user['id']]);
$vpn = $stmt->fetch();

if (!$vpn) {
    json_response(['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN ที่ต้องการลบ']);
}

if ($vpn['status_real'] === 'deleted') {
    json_response(['status' => 'error', 'message' => 'ไฟล์ VPN นี้ถูกลบไปแล้ว']);
}

$now = time();
$createdTime = strtotime($vpn['created_at']);
$expiryTime = strtotime($vpn['expiry_time']);
$pricePaid = (float)($vpn['price_paid'] ?? 0.00);

// ตรวจสอบและแก้ไข Timezone กรณีข้อมูลเก่าถูกบันทึก created_at เป็น UTC (ช้ากว่าเวลาไทย 7 ชม.)
$packageDays = is_numeric($vpn['package_val']) ? (int)$vpn['package_val'] : 0;
if ($packageDays > 0) {
    $expectedSec = $packageDays * 86400;
    $actualSec = $expiryTime - $createdTime;
    // หากส่วนต่างเวลามากกว่าจำนวนวันจริงประมาณ 7 ชั่วโมง แสดงว่า created_at เป็น UTC
    if (abs($actualSec - ($expectedSec + 7 * 3600)) < 600) {
        $createdTime += 7 * 3600;
    }
}

// คำนวณเวลาที่ผ่านไป
$elapsedSeconds = max(0, $now - $createdTime);

// คำนวณชั่วโมงทั้งหมดของแพ็กเกจ
if ($packageDays > 0) {
    $totalHours = $packageDays * 24;
} else {
    $totalDurationSeconds = max(3600, $expiryTime - $createdTime);
    $totalHours = max(1, (int)round($totalDurationSeconds / 3600));
}

// ราคาต่อชั่วโมง
$hourlyRate = ($totalHours > 0 && $pricePaid > 0) ? ($pricePaid / $totalHours) : 0.0;

// กรณีหมดอายุแล้ว
$isExpired = ($now >= $expiryTime);

// เงื่อนไขลบภายใน 10 นาที (Grace Period คืนเต็มจำนวน 100%)
$isGracePeriod = (!$isExpired && $elapsedSeconds <= 600 && $pricePaid > 0);

if ($isExpired || $pricePaid <= 0) {
    $usedHours = $totalHours;
    $remainingHours = 0;
    $refundAmount = 0.00;
} elseif ($isGracePeriod) {
    $usedHours = 0;
    $remainingHours = $totalHours;
    $refundAmount = $pricePaid;
} else {
    // ใช้งานเกิน 10 นาที: คิดเงินตามชั่วโมงที่ใช้จริง (เศษชั่วโมงปัดขึ้น 1 ชม.)
    $usedHours = (int)ceil($elapsedSeconds / 3600);
    $usedHours = min($totalHours, max(1, $usedHours));
    $remainingHours = max(0, $totalHours - $usedHours);

    // คืนเงินตามจำนวนชั่วโมงคงเหลือ
    $refundAmount = round($remainingHours * $hourlyRate, 2);
    $refundAmount = min($pricePaid, max(0.00, $refundAmount));
}

// หากเป็นการดูตัวอย่างยอดเงินคืนก่อนกดลบ (Preview)
if ($action === 'preview') {
    json_response([
        'status' => 'success',
        'preview' => true,
        'is_grace' => $isGracePeriod,
        'is_expired' => $isExpired,
        'price_paid' => $pricePaid,
        'hourly_rate' => $hourlyRate,
        'total_hours' => $totalHours,
        'used_hours' => $usedHours,
        'remaining_hours' => $remainingHours,
        'refund_amount' => $refundAmount
    ]);
}

// ดำเนินการลบและคืนเงินแบบ Atomic เพื่อป้องกัน race condition
$db->beginTransaction();

// อัปเดตสถานะเป็น deleted ทันที (หากถูกลบไปแล้ว rowCount จะเป็น 0)
$delStmt = $db->prepare("UPDATE vpn_configs SET status_real = 'deleted' WHERE id = ? AND user_id = ? AND status_real != 'deleted'");
$delStmt->execute([$configId, $user['id']]);

if ($delStmt->rowCount() === 0) {
    $db->rollBack();
    json_response(['status' => 'error', 'message' => 'ไฟล์ VPN นี้ถูกลบไปแล้ว']);
}

// ดำเนินการคืนเงิน (ถ้ามียอดคืน > 0)
if ($refundAmount > 0) {
    $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$refundAmount, $user['id']]);

    if ($isGracePeriod) {
        $desc = 'คืนเงิน 100% ลบไฟล์ ' . $vpn['server_name'] . ' ภายใน 10 นาที';
    } else {
        $desc = sprintf(
            'คืนเงินตามการใช้งาน (ใช้ %d ชม., คืน %d ชม.) ลบไฟล์ %s',
            $usedHours,
            $remainingHours,
            $vpn['server_name']
        );
    }

    $db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "refund", ?, ?, ?)')
       ->execute([$user['id'], $refundAmount, $desc, date('Y-m-d H:i:s')]);
}

// ลดจำนวนผู้ใช้ในเซิร์ฟเวอร์
$db->prepare('UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id = ?')->execute([$vpn['server_id']]);

$db->commit();

// ลบ client จาก 3x-ui หรือลบ SSH user จาก VPS (ทำนอก transaction เพื่อไม่ให้ lock database)
$sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
$sStmt->execute([$vpn['server_id']]);
$server = $sStmt->fetch();

if ($server) {
    if (!empty($vpn['xui_email']) && !empty($server['panel_url'])) {
        xui_delete_client($server, $vpn['xui_email'], $vpn['uuid'] ?? null);
    }
    if (($server['type'] === 'ssh_script' || $server['type'] === 'udp_custom') && !empty($vpn['ssh_user'])) {
        ssh_vps_delete_user($server, $vpn['ssh_user']);
    }
}

// ส่ง Discord Webhook
if ($refundAmount > 0) {
    send_discord_webhook('refund', [
        'title' => '💸 มีการคืนเงินจากการลบ VPN!',
        'color' => 0x10b981,
        'fields' => [
            ['name' => 'ผู้ใช้งาน', 'value' => $user['username'], 'inline' => true],
            ['name' => 'เซิร์ฟเวอร์', 'value' => $vpn['server_name'], 'inline' => true],
            ['name' => 'ยอดคืน', 'value' => '฿' . number_format($refundAmount, 2), 'inline' => true],
            ['name' => 'รูปแบบการคืน', 'value' => $isGracePeriod ? 'คืนเต็ม 100% (ภายใน 10 นาที)' : "ใช้ {$usedHours} ชม. / คืน {$remainingHours} ชม.", 'inline' => false],
            ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
        ]
    ]);
}

if ($refundAmount > 0) {
    if ($isGracePeriod) {
        $message = 'ลบไฟล์เรียบร้อยแล้ว! คืนเงิน ฿' . number_format($refundAmount, 2) . ' (เต็มจำนวน 100%) เข้าสู่กระเป๋าของคุณอัตโนมัติ';
    } else {
        $message = sprintf(
            'ลบไฟล์เรียบร้อยแล้ว! ใช้งานไป %d ชม. (ชั่วโมงละ ฿%.2f) คืนเงินชั่วโมงที่เหลือ ฿%.2f (%d ชม.) เข้าสู่กระเป๋าของคุณอัตโนมัติ',
            $usedHours,
            $hourlyRate,
            $refundAmount,
            $remainingHours
        );
    }
} else {
    $message = 'ลบไฟล์เรียบร้อยแล้ว';
}

json_response([
    'status' => 'success',
    'message' => $message,
    'refund_amount' => $refundAmount
]);
