<?php
// api/mobile_topup.php - Mobile Phone SIM Top-up API & Auto Refund System
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');

$user = require_auth();
$db = get_db();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'get_info');

// 1. Get info & package options
if ($action === 'get_info') {
    $operators = [
        [
            'code' => 'AIS',
            'name' => 'AIS 1-2-Call',
            'badge' => 'AIS',
            'color' => '#10b981',
            'bg_class' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'active_border' => 'border-emerald-500 ring-2 ring-emerald-500',
            'prefixes' => ['081', '082', '084', '087', '089', '092', '093', '098', '061', '062', '065']
        ],
        [
            'code' => 'TRUE',
            'name' => 'TrueMove H',
            'badge' => 'TRUE',
            'color' => '#ef4444',
            'bg_class' => 'bg-red-50 text-red-700 border-red-200',
            'active_border' => 'border-red-500 ring-2 ring-red-500',
            'prefixes' => ['083', '086', '088', '089', '090', '091', '095', '096', '097', '063', '064']
        ],
        [
            'code' => 'DTAC',
            'name' => 'DTAC',
            'badge' => 'DTAC',
            'color' => '#0284c7',
            'bg_class' => 'bg-sky-50 text-sky-700 border-sky-200',
            'active_border' => 'border-sky-500 ring-2 ring-sky-500',
            'prefixes' => ['080', '081', '082', '085', '087', '089', '094', '099', '066']
        ]
    ];

    $operatorAmounts = [
        'AIS'  => [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500],
        'DTAC' => [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500],
        'TRUE' => [5, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500]
    ];

    json_response([
        'status' => 'success',
        'operators' => $operators,
        'operator_amounts' => $operatorAmounts,
        'amounts' => $operatorAmounts['AIS'],
        'balance' => (float)$user['balance']
    ]);
}

// 2. Process Top-up
if ($action === 'topup') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['status' => 'error', 'message' => 'Invalid request method'], 405);
    }

    $rawPhone = trim((string)($_POST['phone_number'] ?? ''));
    $phone = preg_replace('/[^0-9]/', '', $rawPhone);
    $operator = strtoupper(trim((string)($_POST['operator'] ?? '')));
    $amount = (float)($_POST['amount'] ?? 0);

    // Validate phone number (must be 10 digits starting with 06, 08, 09)
    if (!preg_match('/^(06|08|09)[0-9]{8}$/', $phone)) {
        json_response([
            'status' => 'error',
            'message' => 'หมายเลขโทรศัพท์ไม่ถูกต้อง (ต้องขึ้นต้นด้วย 06, 08, 09 และมีครบ 10 หลัก)'
        ]);
    }

    // Validate operator
    $validOperators = ['AIS', 'TRUE', 'DTAC'];
    if (!in_array($operator, $validOperators, true)) {
        json_response(['status' => 'error', 'message' => 'กรุณาเลือกเครือข่ายที่ถูกต้อง']);
    }

    // Validate amount per operator
    $operatorAmounts = [
        'AIS'  => [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500],
        'DTAC' => [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500],
        'TRUE' => [5, 10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500]
    ];
    $validAmounts = $operatorAmounts[$operator] ?? [];
    if (!in_array((int)$amount, $validAmounts, true)) {
        json_response(['status' => 'error', 'message' => "จำนวนเงินที่ต้องการเติมไม่ถูกต้องสำหรับเครือข่าย {$operator}"]);
    }

    // Check user balance
    $uStmt = $db->prepare('SELECT id, balance FROM users WHERE id = ?');
    $uStmt->execute([$user['id']]);
    $currentUser = $uStmt->fetch();
    $currentBalance = (float)($currentUser['balance'] ?? 0);

    if ($currentBalance < $amount) {
        json_response([
            'status' => 'error',
            'message' => 'ยอดเงินคงเหลือไม่พอ (มี ฿' . number_format($currentBalance, 2) . ' / ขาดอีก ฿' . number_format($amount - $currentBalance, 2) . ') กรุณาเติมเงินเข้าเว็บก่อนครับ'
        ]);
    }

    $formattedPhone = substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
    $transId = 'WP' . date('YmdHis') . rand(1000, 9999);

    // Database transaction
    $db->beginTransaction();
    try {
        // 1. Deduct balance
        $upd = $db->prepare('UPDATE users SET balance = balance - ? WHERE id = ?');
        $upd->execute([$amount, $user['id']]);

        // 2. Record order in orders_history
        $desc = "เติมเงินมือถือ {$operator} เบอร์ {$formattedPhone} (อ้างอิง: {$transId})";
        $oStmt = $db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "mobile_topup", ?, ?, datetime("now", "localtime"))');
        $oStmt->execute([$user['id'], -$amount, $desc]);

        // 3. Insert into mobile_topups
        $ins = $db->prepare('INSERT INTO mobile_topups (user_id, phone_number, operator, amount, price_paid, status, trans_id, refund_status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, "success", ?, "none", datetime("now", "localtime"), datetime("now", "localtime"))');
        $ins->execute([$user['id'], $phone, $operator, $amount, $amount, $transId]);
        $topupId = $db->lastInsertId();

        $db->commit();

        $newBal = $currentBalance - $amount;

        json_response([
            'status' => 'success',
            'message' => "เติมเงินเข้าเบอร์ {$formattedPhone} ({$operator}) จำนวน ฿" . number_format($amount, 2) . " สำเร็จเรียบร้อยแล้ว!",
            'data' => [
                'topup_id' => $topupId,
                'trans_id' => $transId,
                'phone' => $formattedPhone,
                'operator' => $operator,
                'amount' => $amount,
                'new_balance' => round($newBal, 2)
            ]
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        json_response(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการทำรายการ: ' . $e->getMessage()]);
    }
}

// 3. Get Top-up History
if ($action === 'get_history') {
    $stmt = $db->prepare('SELECT * FROM mobile_topups WHERE user_id = ? ORDER BY id DESC LIMIT 50');
    $stmt->execute([$user['id']]);
    $rows = $stmt->fetchAll();

    $nowTs = time();
    $items = [];

    $refundWindow = 900; // 15 นาที (ป้องกันกรณีปลายทางนำยอดเงินไปสมัครเน็ตแล้วดึงคืนไม่เต็มจำนวน)
    foreach ($rows as $r) {
        $createdTs = strtotime($r['created_at']);
        $diffSeconds = $nowTs - $createdTs;
        $secondsLeft = max(0, $refundWindow - $diffSeconds);
        $minutesLeft = floor($secondsLeft / 60);
        $secsLeft = $secondsLeft % 60;

        $phone = $r['phone_number'];
        $formattedPhone = (strlen($phone) === 10) ? (substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6)) : $phone;

        $canRefund = ($r['status'] === 'success' && $r['refund_status'] !== 'success' && $secondsLeft > 0);

        if ($secondsLeft > 0) {
            $timeLeftText = ($minutesLeft > 0) ? "เหลือเวลา {$minutesLeft} นาที {$secsLeft} วินาที" : "เหลือเวลา {$secsLeft} วินาที";
        } else {
            $timeLeftText = 'หมดเวลาขอคืนเงินแล้ว (ครบกำหนด 15 นาที)';
        }

        $items[] = [
            'id' => (int)$r['id'],
            'phone' => $formattedPhone,
            'operator' => $r['operator'],
            'amount' => (float)$r['amount'],
            'status' => $r['status'],
            'trans_id' => $r['trans_id'],
            'refund_status' => $r['refund_status'],
            'refund_amount' => (float)$r['refund_amount'],
            'created_at' => $r['created_at'],
            'can_refund' => $canRefund,
            'seconds_left' => $secondsLeft,
            'time_left_text' => $timeLeftText
        ];
    }

    json_response(['status' => 'success', 'history' => $items]);
}

// 4. Request Refund (ดึงเงินคืนกรณีเติมผิดเบอร์อัตโนมัติ)
if ($action === 'refund') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_response(['status' => 'error', 'message' => 'Invalid request method'], 405);
    }

    $topupId = (int)($_POST['topup_id'] ?? 0);
    if ($topupId <= 0) {
        json_response(['status' => 'error', 'message' => 'ไม่พบรหัสรายการที่ต้องการดึงเงินคืน']);
    }

    $stmt = $db->prepare('SELECT * FROM mobile_topups WHERE id = ? AND user_id = ?');
    $stmt->execute([$topupId, $user['id']]);
    $order = $stmt->fetch();

    if (!$order) {
        json_response(['status' => 'error', 'message' => 'ไม่พบข้อมูลรายการเติมเงินนี้']);
    }

    if ($order['refund_status'] === 'success') {
        json_response(['status' => 'error', 'message' => 'รายการนี้ได้รับการดึงเงินคืนเรียบร้อยแล้ว']);
    }

    $createdTs = strtotime($order['created_at']);
    if (time() - $createdTs > 900) {
        json_response(['status' => 'error', 'message' => 'รายการนี้เกินกำหนดเวลาคุ้มครอง 15 นาทีแล้ว จึงไม่สามารถดึงเงินคืนได้']);
    }

    // Process Refund (In test mode, we retrieve the full amount; when live wePAY is connected, it uses actual recovered amount from operator)
    $recoveredAmount = (float)$order['amount'];

    $db->beginTransaction();
    try {
        // 1. Credit balance back to user's wallet
        $upd = $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
        $upd->execute([$recoveredAmount, $user['id']]);

        // 2. Record refund in orders_history
        $phone = $order['phone_number'];
        $formattedPhone = (strlen($phone) === 10) ? (substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6)) : $phone;
        $desc = "ดึงเงินคืนสำเร็จจากการเติมเงินผิดเบอร์ {$formattedPhone} ({$order['operator']}) เลขอ้างอิง: {$order['trans_id']}";
        $oStmt = $db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "mobile_refund", ?, ?, datetime("now", "localtime"))');
        $oStmt->execute([$user['id'], $recoveredAmount, $desc]);

        // 3. Update mobile_topups record
        $mUpd = $db->prepare('UPDATE mobile_topups SET status = "refunded", refund_status = "success", refund_amount = ?, refund_requested_at = datetime("now", "localtime"), updated_at = datetime("now", "localtime") WHERE id = ?');
        $mUpd->execute([$recoveredAmount, $topupId]);

        $db->commit();

        // Get new balance
        $bStmt = $db->prepare('SELECT balance FROM users WHERE id = ?');
        $bStmt->execute([$user['id']]);
        $newBal = (float)$bStmt->fetchColumn();

        json_response([
            'status' => 'success',
            'message' => "ดึงเงินคืนสำเร็จ! ยอดเงิน ฿" . number_format($recoveredAmount, 2) . " ได้ถูกโอนกลับเข้ากระเป๋าของคุณเรียบร้อยแล้ว",
            'refund_amount' => $recoveredAmount,
            'new_balance' => round($newBal, 2)
        ]);
    } catch (Exception $e) {
        $db->rollBack();
        json_response(['status' => 'error', 'message' => 'เกิดข้อผิดพลาดในการดึงเงินคืน: ' . $e->getMessage()]);
    }
}

json_response(['status' => 'error', 'message' => 'Action not supported']);
