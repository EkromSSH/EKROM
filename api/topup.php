<?php
// api/topup.php - Secure SlipOK & PromptPay Topup Handler for EKROM Shop
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/slipok.php';

$db = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $settings = get_slip_settings();
    $activeOrder = null;

    $user = get_auth_user();
    if ($user) {
        // Find latest active pending order for user
        $stmt = $db->prepare('SELECT * FROM topup_orders 
            WHERE user_id = ? AND status = "pending" AND expires_at > datetime("now", "localtime")
            ORDER BY id DESC LIMIT 1');
        $stmt->execute([$user['id']]);
        $ord = $stmt->fetch();
        if ($ord) {
            $remaining = strtotime($ord['expires_at']) - time();
            $activeOrder = [
                'id' => (int)$ord['id'],
                'order_id' => $ord['order_id'],
                'amount' => (float)$ord['amount'],
                'formatted_amount' => number_format((float)$ord['amount'], 2),
                'promptpay_number' => $ord['promptpay_number'],
                'promptpay_name' => $ord['promptpay_name'],
                'qr_payload' => $ord['qr_payload'],
                'qr_image_url' => get_promptpay_qr_url($ord['qr_payload'], $ord['promptpay_number'], (float)$ord['amount']),
                'created_at' => $ord['created_at'],
                'expires_at' => $ord['expires_at'],
                'expires_in_seconds' => max(0, $remaining)
            ];
        }
    }

    json_response([
        'status' => 'success',
        'data' => $settings,
        'active_order' => $activeOrder
    ]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['status' => 'error', 'message' => 'Method not allowed'], 405);
}

$user = require_auth();

// Support both JSON body and Multipart Form Data
$json = get_post_json();
$action = $_POST['action'] ?? $json['action'] ?? '';

// If action is not explicitly set, determine by presence of file or amount
if (empty($action)) {
    if (!empty($_FILES['slip']) || !empty($json['slip_base64'])) {
        $action = 'check_slip';
    } elseif (isset($_POST['amount']) || isset($json['amount'])) {
        $action = 'create_order';
    }
}

// -------------------------------------------------------------
// 1. ACTION: Create Topup Order
// -------------------------------------------------------------
if ($action === 'create_order') {
    $rawAmount = $_POST['amount'] ?? $json['amount'] ?? null;
    if ($rawAmount === null || !is_numeric($rawAmount)) {
        json_response([
            'status' => 'error',
            'code' => 'INVALID_AMOUNT',
            'message' => 'กรุณากรอกจำนวนเงินที่ต้องการเติม'
        ], 400);
    }

    $amount = round((float)$rawAmount, 2);
    $res = create_topup_order((int)$user['id'], $amount);

    if ($res['status'] !== 'success') {
        json_response($res, 400);
    }

    json_response($res, 200);
}

// -------------------------------------------------------------
// 2. ACTION: Cancel Order
// -------------------------------------------------------------
if ($action === 'cancel_order') {
    $orderId = trim((string)($_POST['order_id'] ?? $json['order_id'] ?? ''));
    if ($orderId) {
        $db->prepare('UPDATE topup_orders SET status = "cancelled" WHERE order_id = ? AND user_id = ? AND status = "pending"')
           ->execute([$orderId, $user['id']]);
    }
    json_response(['status' => 'success', 'message' => 'ยกเลิกรายการเรียบร้อยแล้ว']);
}

// -------------------------------------------------------------
// 3. ACTION: Check Slip via SlipOK API
// -------------------------------------------------------------
if ($action === 'check_slip') {
    $orderId = trim((string)($_POST['order_id'] ?? $json['order_id'] ?? ''));
    if (empty($orderId)) {
        json_response([
            'status' => 'error',
            'code' => 'ORDER_ID_REQUIRED',
            'message' => 'กรุณาระบุรหัสรายการเติมเงิน (Order ID)'
        ], 400);
    }

    $fileInfo = null;
    $tempFileToDelete = null;

    if (!empty($_FILES['slip'])) {
        $fileInfo = $_FILES['slip'];
    } elseif (!empty($json['slip_base64'])) {
        // Base64 slip handling
        $data = $json['slip_base64'];
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]);
        } else {
            $type = 'jpg';
        }
        $decoded = base64_decode($data);
        if ($decoded === false) {
            json_response(['status' => 'error', 'message' => 'รูปภาพ Base64 ไม่ถูกต้อง'], 400);
        }
        $tempPath = tempnam(sys_get_temp_dir(), 'slip_');
        file_put_contents($tempPath, $decoded);
        $tempFileToDelete = $tempPath;
        $fileInfo = [
            'name' => 'slip.' . $type,
            'tmp_name' => $tempPath,
            'error' => UPLOAD_ERR_OK,
            'size' => strlen($decoded)
        ];
    }

    if (!$fileInfo) {
        json_response([
            'status' => 'error',
            'code' => 'SLIP_REQUIRED',
            'message' => 'กรุณาแนบรูปภาพสลิปการโอนเงิน'
        ], 400);
    }

    // Optional simulation mode for test suites
    $options = [];
    $sim = $_POST['simulate'] ?? $json['simulate'] ?? null;
    if ($sim) {
        $options['simulate'] = $sim;
    }

    try {
        $result = verify_and_process_slip($orderId, (int)$user['id'], $fileInfo, $options);
        if ($tempFileToDelete && file_exists($tempFileToDelete)) {
            @unlink($tempFileToDelete);
        }

        $httpCode = ($result['status'] === 'success') ? 200 : 400;
        json_response($result, $httpCode);
    } catch (Exception $e) {
        if ($tempFileToDelete && file_exists($tempFileToDelete)) {
            @unlink($tempFileToDelete);
        }
        json_response([
            'status' => 'error',
            'code' => 'SYSTEM_ERROR',
            'message' => 'เกิดข้อผิดพลาดในการตรวจสอบ: ' . $e->getMessage()
        ], 500);
    }
}

json_response(['status' => 'error', 'message' => 'คำสั่งไม่ถูกต้อง'], 400);
