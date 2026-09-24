<?php
// api/slipok.php - SlipOK API & Dynamic PromptPay Engine for EKROM Shop
require_once __DIR__ . '/db.php';

/**
 * Calculate CRC16-CCITT checksum for EMVCo QR Code
 */
function promptpay_crc16(string $data): string {
    $crc = 0xFFFF;
    $length = strlen($data);
    for ($i = 0; $i < $length; $i++) {
        $x = (($crc >> 8) ^ ord($data[$i])) & 0xFF;
        $x ^= $x >> 4;
        $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xFFFF;
    }
    return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
}

/**
 * Generate EMVCo standard PromptPay QR Payload (supports dynamic amount)
 */
function generate_promptpay_payload(string $target, ?float $amount = null): string {
    $cleanTarget = preg_replace('/[^0-9]/', '', $target);
    
    // Determine target type
    if (strlen($cleanTarget) >= 10 && substr($cleanTarget, 0, 1) === '0') {
        // Mobile phone number -> 0066 + 9 digits
        $phone = '0066' . substr($cleanTarget, 1);
        $subTag = '01' . sprintf('%02d', strlen($phone)) . $phone;
    } elseif (strlen($cleanTarget) === 13) {
        // Citizen ID / Tax ID -> 13 digits
        $subTag = '02' . sprintf('%02d', strlen($cleanTarget)) . $cleanTarget;
    } elseif (strlen($cleanTarget) === 15) {
        // E-Wallet ID -> 15 digits
        $subTag = '03' . sprintf('%02d', strlen($cleanTarget)) . $cleanTarget;
    } else {
        $subTag = '01' . sprintf('%02d', strlen($cleanTarget)) . $cleanTarget;
    }

    $merchantInfo = '0016A000000677010111' . $subTag;
    $tag29 = '29' . sprintf('%02d', strlen($merchantInfo)) . $merchantInfo;

    $payload = '000201';
    // 010212 = dynamic amount; 010211 = static
    $payload .= ($amount !== null && $amount > 0) ? '010212' : '010211';
    $payload .= $tag29;
    $payload .= '5802TH';
    $payload .= '5303764'; // THB currency code

    if ($amount !== null && $amount > 0) {
        $amountStr = number_format($amount, 2, '.', '');
        $payload .= '54' . sprintf('%02d', strlen($amountStr)) . $amountStr;
    }

    $payload .= '6304';
    $payload .= promptpay_crc16($payload);

    return $payload;
}

/**
 * Get QR Code image URL for display
 */
function get_promptpay_qr_url(string $payload, string $target, ?float $amount = null): string {
    // Generate high-resolution QR image using standard QR server
    return 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=10&data=' . urlencode($payload);
}

/**
 * Get combined Slip & SlipOK configuration
 */
function get_slip_settings(): array {
    $db = get_db();
    $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "slip_settings"');
    $stmt->execute();
    $raw = $stmt->fetchColumn();

    $defaults = [
        'slip_api_mode' => 'slipok',
        'slipok_branch_id' => getenv('SLIPOK_BRANCH_ID') ?: '',
        'slipok_api_key' => getenv('SLIPOK_API_KEY') ?: '',
        'slip_min_amount' => 30.00,
        'slip_expire_minutes' => 15,
        'slip_receiver_th' => '',
        'slip_receiver_en' => '',
        'slip_receiver_account' => '',
        'promptpay_number' => '',
        'promptpay_name' => '',
        'truemoney_phone' => ''
    ];

    if ($raw) {
        $stored = json_decode($raw, true) ?: [];
        $merged = array_merge($defaults, $stored);
    } else {
        $merged = $defaults;
    }

    // Ensure numeric types
    $merged['slip_min_amount'] = max(30.00, (float)($merged['slip_min_amount'] ?? 30.00));
    $merged['slip_expire_minutes'] = max(5, (int)($merged['slip_expire_minutes'] ?? 15));

    // Fallback environment variables
    if (empty($merged['slipok_branch_id']) && getenv('SLIPOK_BRANCH_ID')) {
        $merged['slipok_branch_id'] = getenv('SLIPOK_BRANCH_ID');
    }
    if (empty($merged['slipok_api_key']) && getenv('SLIPOK_API_KEY')) {
        $merged['slipok_api_key'] = getenv('SLIPOK_API_KEY');
    }

    // Auto-clean branch ID if URL was passed (extract digits)
    if (!empty($merged['slipok_branch_id'])) {
        $rawB = trim((string)$merged['slipok_branch_id']);
        if (preg_match('/(?:apikey\/|\/)(\d+)\/?$/', $rawB, $m)) {
            $merged['slipok_branch_id'] = $m[1];
        } elseif (preg_match('/(\d+)/', $rawB, $m)) {
            $merged['slipok_branch_id'] = $m[1];
        }
    }

    return $merged;
}

/**
 * Call SlipOK API to verify slip
 *
 * @param string $filePath Absolute path to slip image
 * @param float|null $expectedAmount
 * @param array $options Optional override / testing options
 * @return array Normalized SlipOK response
 */
function call_slipok_api(string $filePath, ?float $expectedAmount = null, array $options = []): array {
    $settings = get_slip_settings();
    $rawBranch = trim($options['branch_id'] ?? $settings['slipok_branch_id'] ?? '');
    if (preg_match('/(?:apikey\/|\/)(\d+)\/?$/', $rawBranch, $m)) {
        $branchId = $m[1];
    } elseif (preg_match('/(\d+)/', $rawBranch, $m)) {
        $branchId = $m[1];
    } else {
        $branchId = $rawBranch;
    }
    $apiKey = trim($options['api_key'] ?? $settings['slipok_api_key'] ?? '');

    // Allow mock / simulated responses for automated test suites
    if (!empty($options['simulate'])) {
        return simulate_slipok_response($options['simulate'], $expectedAmount, $settings, $options);
    }

    if (empty($branchId) || empty($apiKey)) {
        return [
            'success' => false,
            'code' => 'CONFIG_MISSING',
            'message' => 'ระบบยังไม่ได้ตั้งค่า SlipOK Branch ID หรือ API Key กรุณาติดต่อผู้ดูแลระบบ'
        ];
    }

    if (!file_exists($filePath)) {
        return [
            'success' => false,
            'code' => 'FILE_NOT_FOUND',
            'message' => 'ไม่พบไฟล์สลิปในระบบ'
        ];
    }

    $url = "https://api.slipok.com/api/line/apikey/" . rawurlencode($branchId);

    $mime = mime_content_type($filePath) ?: 'image/jpeg';
    $cfile = new CURLFile($filePath, $mime, basename($filePath));

    $postData = [
        'files' => $cfile,
        'log' => 'true'
    ];
    if ($expectedAmount !== null && $expectedAmount > 0) {
        $postData['amount'] = number_format($expectedAmount, 2, '.', '');
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'x-authorization: ' . $apiKey,
            'Accept: application/json'
        ]
    ]);

    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    $curlErrNo = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErrNo !== 0) {
        $isTimeout = ($curlErrNo === CURLE_OPERATION_TIMEDOUT || $curlErrNo === CURLE_COULDNT_CONNECT);
        return [
            'success' => false,
            'code' => $isTimeout ? 'TIMEOUT' : 'NETWORK_ERROR',
            'message' => $isTimeout 
                ? 'การเชื่อมต่อกับ SlipOK API หมดเวลา (Timeout) กรุณาลองใหม่อีกครั้ง' 
                : 'ไม่สามารถเชื่อมต่อกับ SlipOK API ได้ (' . $curlErr . ')'
        ];
    }

    if ($httpCode >= 500) {
        return [
            'success' => false,
            'code' => 'API_ERROR',
            'http_code' => $httpCode,
            'message' => 'เซิร์ฟเวอร์ SlipOK เกิดข้อผิดพลาด (HTTP ' . $httpCode . ') กรุณาลองใหม่อีกครั้ง'
        ];
    }

    $json = json_decode((string)$response, true);
    if (!is_array($json)) {
        return [
            'success' => false,
            'code' => 'INVALID_RESPONSE',
            'message' => 'คำตอบจาก SlipOK ไม่ถูกต้อง'
        ];
    }

    return $json;
}

/**
 * Simulate SlipOK response for test suites
 */
function simulate_slipok_response(string $simulate, ?float $expectedAmount, array $settings, array $options = []): array {
    $now = date('Y-m-d H:i:s');
    $receiverAcc = $settings['slip_receiver_account'] ?: '0812345678';
    $receiverName = $settings['slip_receiver_th'] ?: 'ทดสอบ บัญชี';

    switch ($simulate) {
        case 'valid':
            $ref = $options['trans_ref'] ?? ('TEST-' . date('YmdHis') . '-' . mt_rand(1000, 9999));
            return [
                'success' => true,
                'data' => [
                    'success' => true,
                    'message' => 'ตรวจสอบสลิปสำเร็จ',
                    'transRef' => $ref,
                    'sendingBank' => '004',
                    'receivingBank' => '014',
                    'transDate' => date('Ymd'),
                    'transTime' => date('His'),
                    'transTimestamp' => date('c'),
                    'sender' => [
                        'displayName' => 'ผู้ทดสอบ เติมเงิน',
                        'name' => 'TESTER TOPUP',
                        'account' => ['value' => 'xxx-x-xx123-x']
                    ],
                    'receiver' => [
                        'displayName' => $receiverName,
                        'name' => $receiverName,
                        'proxy' => ['type' => 'MSISDN', 'value' => $receiverAcc],
                        'account' => ['type' => 'BANKAC', 'value' => $receiverAcc]
                    ],
                    'amount' => (float)$expectedAmount,
                    'ref1' => '',
                    'ref2' => '',
                    'ref3' => ''
                ]
            ];

        case 'amount_mismatch':
            return [
                'success' => true,
                'data' => [
                    'success' => true,
                    'message' => 'ตรวจสอบสลิปสำเร็จ',
                    'transRef' => 'MISMATCH-' . mt_rand(100000, 999999),
                    'receiver' => [
                        'displayName' => $receiverName,
                        'proxy' => ['value' => $receiverAcc],
                        'account' => ['value' => $receiverAcc]
                    ],
                    'amount' => 20.00 // Intentionally 20 instead of 30
                ]
            ];

        case 'receiver_mismatch':
            return [
                'success' => true,
                'data' => [
                    'success' => true,
                    'message' => 'ตรวจสอบสลิปสำเร็จ',
                    'transRef' => 'RECMIS-' . mt_rand(100000, 999999),
                    'receiver' => [
                        'displayName' => 'นาย บัญชีอื่น มิจฉาชีพ',
                        'proxy' => ['value' => '0999999999'],
                        'account' => ['value' => '9999999999']
                    ],
                    'amount' => (float)$expectedAmount
                ]
            ];

        case 'invalid_slip':
            return [
                'success' => false,
                'code' => 1001,
                'message' => 'ไม่พบ QR Code ในสลิป หรือรูปภาพไม่ใช่สลิปโอนเงินที่ถูกต้อง'
            ];

        case 'api_error':
            return [
                'success' => false,
                'code' => 'API_ERROR',
                'message' => 'เซิร์ฟเวอร์ SlipOK เกิดข้อผิดพลาด (HTTP 500)'
            ];

        case 'timeout':
            return [
                'success' => false,
                'code' => 'TIMEOUT',
                'message' => 'การเชื่อมต่อกับ SlipOK API หมดเวลา (Timeout) กรุณาลองใหม่อีกครั้ง'
            ];

        default:
            return [
                'success' => false,
                'code' => 'UNKNOWN_SIMULATION',
                'message' => 'สถานการณ์จำลองไม่ถูกต้อง'
            ];
    }
}

/**
 * Create a new Topup Order
 * Enforces minimum 30 THB, builds dynamic PromptPay QR, and registers pending order
 */
function create_topup_order(int $userId, float $amount): array {
    $settings = get_slip_settings();
    $minAmount = (float)$settings['slip_min_amount'];

    // Enforce 30 THB minimum
    if ($amount < $minAmount) {
        return [
            'status' => 'error',
            'code' => 'AMOUNT_BELOW_MINIMUM',
            'message' => 'จำนวนเงินขั้นต่ำที่สามารถเติมได้คือ ' . number_format($minAmount, 2) . ' บาท'
        ];
    }

    $promptpayAcc = trim($settings['slip_receiver_account'] ?: ($settings['promptpay_number'] ?: ''));
    $promptpayName = trim($settings['slip_receiver_th'] ?: ($settings['promptpay_name'] ?: 'ผู้ดูแลระบบ'));
    if (empty($promptpayAcc)) {
        return [
            'status' => 'error',
            'code' => 'PROMPTPAY_NOT_CONFIGURED',
            'message' => 'ร้านค้ายังไม่ได้ตั้งค่าหมายเลขพร้อมเพย์สำหรับรับเงิน กรุณาแจ้งผู้ดูแลระบบ'
        ];
    }
    $expireMinutes = (int)$settings['slip_expire_minutes'];

    // Generate unique Order ID
    $orderId = 'TOPUP-' . date('YmdHis') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

    // Generate dynamic PromptPay QR payload with exact order amount
    $qrPayload = generate_promptpay_payload($promptpayAcc, $amount);
    $qrImageUrl = get_promptpay_qr_url($qrPayload, $promptpayAcc, $amount);

    $nowStr = date('Y-m-d H:i:s');
    $expiresAt = date('Y-m-d H:i:s', time() + ($expireMinutes * 60));

    $db = get_db();
    $stmt = $db->prepare('INSERT INTO topup_orders 
        (order_id, user_id, amount, status, promptpay_number, promptpay_name, qr_payload, created_at, expires_at)
        VALUES (?, ?, ?, "pending", ?, ?, ?, ?, ?)');
    $stmt->execute([
        $orderId,
        $userId,
        $amount,
        $promptpayAcc,
        $promptpayName,
        $qrPayload,
        $nowStr,
        $expiresAt
    ]);

    $insertedId = (int)$db->lastInsertId();

    return [
        'status' => 'success',
        'data' => [
            'id' => $insertedId,
            'order_id' => $orderId,
            'amount' => $amount,
            'formatted_amount' => number_format($amount, 2),
            'promptpay_number' => $promptpayAcc,
            'promptpay_name' => $promptpayName,
            'qr_payload' => $qrPayload,
            'qr_image_url' => $qrImageUrl,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expiresAt,
            'expires_in_seconds' => $expireMinutes * 60,
            'min_amount' => $minAmount
        ]
    ];
}

/**
 * Verify slip with SlipOK and process topup order atomically
 */
function verify_and_process_slip(string $orderId, int $userId, array $fileInfo, array $options = []): array {
    $db = get_db();
    $settings = get_slip_settings();

    // 1. Fetch order from DB
    $stmt = $db->prepare('SELECT * FROM topup_orders WHERE order_id = ? AND user_id = ?');
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch();

    if (!$order) {
        return [
            'status' => 'error',
            'code' => 'ORDER_NOT_FOUND',
            'message' => 'ไม่พบรายการเติมเงินนี้ในระบบ'
        ];
    }

    // Check if already paid (idempotency)
    if ($order['status'] === 'paid') {
        return [
            'status' => 'success',
            'code' => 'ALREADY_PAID',
            'message' => 'รายการนี้เติมเงินสำเร็จเรียบร้อยแล้ว',
            'data' => [
                'order_id' => $order['order_id'],
                'amount' => (float)$order['amount'],
                'status' => 'paid'
            ]
        ];
    }

    // Check if order expired
    if (strtotime($order['expires_at']) <= time()) {
        $db->prepare('UPDATE topup_orders SET status = "expired", fail_reason = "รายการหมดอายุ" WHERE id = ? AND status = "pending"')
           ->execute([$order['id']]);
        return [
            'status' => 'error',
            'code' => 'ORDER_EXPIRED',
            'message' => 'รายการเติมเงินนี้หมดอายุแล้ว กรุณาสร้างรายการใหม่'
        ];
    }

    if ($order['status'] !== 'pending') {
        return [
            'status' => 'error',
            'code' => 'INVALID_STATUS',
            'message' => 'สถานะของรายการไม่ถูกต้อง (' . $order['status'] . ')'
        ];
    }

    // 2. Validate uploaded file
    $tmpPath = $fileInfo['tmp_name'] ?? '';
    $fileError = $fileInfo['error'] ?? UPLOAD_ERR_NO_FILE;
    $fileSize = $fileInfo['size'] ?? 0;

    if ($fileError !== UPLOAD_ERR_OK || empty($tmpPath) || !file_exists($tmpPath)) {
        return [
            'status' => 'error',
            'code' => 'UPLOAD_FAILED',
            'message' => 'การอัปโหลดไฟล์สลิปไม่สมบูรณ์ กรุณาลองใหม่อีกครั้ง'
        ];
    }

    // Max 10MB
    if ($fileSize > 10 * 1024 * 1024) {
        return [
            'status' => 'error',
            'code' => 'FILE_TOO_LARGE',
            'message' => 'ขนาดไฟล์สลิปใหญ่เกินไป (จำกัดไม่เกิน 10MB)'
        ];
    }

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpPath);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!in_array($mime, $allowedMimes, true)) {
        return [
            'status' => 'error',
            'code' => 'INVALID_MIME_TYPE',
            'message' => 'รูปแบบไฟล์ไม่ถูกต้อง รองรับเฉพาะรูปภาพ JPG, PNG หรือ WEBP เท่านั้น'
        ];
    }

    // Save slip permanently in uploads/slips/
    $uploadDir = dirname(__DIR__) . '/uploads/slips';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext = 'jpg';
    if ($mime === 'image/png') $ext = 'png';
    elseif ($mime === 'image/webp') $ext = 'webp';

    $safeFileName = 'slip_' . preg_replace('/[^a-zA-Z0-9_-]/', '', $orderId) . '_' . uniqid() . '.' . $ext;
    $targetPath = $uploadDir . '/' . $safeFileName;

    if (!copy($tmpPath, $targetPath)) {
        return [
            'status' => 'error',
            'code' => 'STORAGE_ERROR',
            'message' => 'ไม่สามารถจัดเก็บไฟล์สลิปได้'
        ];
    }

    // 3. Send to SlipOK API
    $orderAmount = (float)$order['amount'];
    $slipokRes = call_slipok_api($targetPath, $orderAmount, $options);

    // 4. Inspect SlipOK Response
    if (!$slipokRes || empty($slipokRes['success']) || empty($slipokRes['data'])) {
        $code = (int)($slipokRes['code'] ?? 0);
        $rawMsg = (string)($slipokRes['message'] ?? '');

        if ($code === 1003 || stripos($rawMsg, 'Package ของคุณหมดอายุ') !== false) {
            $failMsg = 'แพ็กเกจ SlipOK ของร้านค้าหมดอายุแล้ว (Package บนเว็บ slipok.com หมดอายุ กรุณาเข้าสู่ระบบ slipok.com เพื่อต่ออายุแพ็กเกจ)';
        } elseif ($code === 1004 || stripos($rawMsg, 'เครดิต') !== false) {
            $failMsg = 'เครดิตตรวจสลิปของ SlipOK หมดแล้ว (กรุณาเติมเครดิตบน slipok.com)';
        } elseif ($code === 1001 || stripos($rawMsg, 'ไม่พบ') !== false) {
            $failMsg = 'ไม่พบ QR Code ในสลิป หรือรูปภาพไม่ใช่สลิปโอนเงินที่ถูกต้อง';
        } elseif ($code === 1000) {
            $failMsg = 'ข้อมูลการเชื่อมต่อ SlipOK ไม่ถูกต้อง (กรุณาตรวจสอบ Branch ID และ API Key ในหน้าตั้งค่า)';
        } elseif (!empty($rawMsg)) {
            $failMsg = $rawMsg;
        } else {
            $failMsg = 'ไม่สามารถตรวจสอบสลิปได้ กรุณาลองใหม่อีกครั้ง';
        }

        $db->prepare('UPDATE topup_orders SET slip_path = ?, fail_reason = ? WHERE id = ?')
           ->execute([$safeFileName, $failMsg, $order['id']]);

        return [
            'status' => 'error',
            'code' => $slipokRes['code'] ?? 'SLIP_INVALID',
            'message' => $failMsg
        ];
    }

    $slipData = $slipokRes['data'];
    $transRef = trim((string)($slipData['transRef'] ?? ''));

    if (empty($transRef)) {
        $failMsg = 'ไม่พบรหัสอ้างอิงธุรกรรม (transRef) ในสลิป';
        $db->prepare('UPDATE topup_orders SET slip_path = ?, fail_reason = ? WHERE id = ?')
           ->execute([$safeFileName, $failMsg, $order['id']]);

        return [
            'status' => 'error',
            'code' => 'NO_TRANS_REF',
            'message' => $failMsg
        ];
    }

    // 5. Verify Amount (exact match)
    $slipAmount = (float)($slipData['amount'] ?? 0);
    if (abs($slipAmount - $orderAmount) > 0.01) {
        $failMsg = 'ยอดเงินในสลิป (฿' . number_format($slipAmount, 2) . ') ไม่ตรงกับยอดรายการเติมเงิน (฿' . number_format($orderAmount, 2) . ')';
        $db->prepare('UPDATE topup_orders SET slip_path = ?, trans_ref = ?, slip_data = ?, fail_reason = ? WHERE id = ?')
           ->execute([$safeFileName, $transRef, json_encode($slipData, JSON_UNESCAPED_UNICODE), $failMsg, $order['id']]);

        return [
            'status' => 'error',
            'code' => 'AMOUNT_MISMATCH',
            'message' => $failMsg,
            'data' => [
                'expected_amount' => $orderAmount,
                'slip_amount' => $slipAmount
            ]
        ];
    }

    // 6. Verify Receiver Account
    $expectedAccount = preg_replace('/[^0-9]/', '', $settings['slip_receiver_account'] ?: $order['promptpay_number']);
    if (!empty($expectedAccount)) {
        $receiverAcc = preg_replace('/[^0-9]/', '', (string)($slipData['receiver']['account']['value'] ?? ''));
        $receiverProxy = preg_replace('/[^0-9]/', '', (string)($slipData['receiver']['proxy']['value'] ?? ''));
        $receiverName = (string)($slipData['receiver']['displayName'] ?? $slipData['receiver']['name'] ?? '');

        $matched = false;
        // Check if phone or account ends with or contains expected account digits
        if (!empty($receiverAcc) && (strpos($receiverAcc, $expectedAccount) !== false || strpos($expectedAccount, $receiverAcc) !== false)) {
            $matched = true;
        }
        if (!empty($receiverProxy) && (strpos($receiverProxy, $expectedAccount) !== false || strpos($expectedAccount, $receiverProxy) !== false)) {
            $matched = true;
        }
        // Also check partial matching (last 4 digits) if full account is masked by bank
        if (!$matched && strlen($expectedAccount) >= 4) {
            $last4 = substr($expectedAccount, -4);
            if ((!empty($receiverAcc) && substr($receiverAcc, -4) === $last4) || 
                (!empty($receiverProxy) && substr($receiverProxy, -4) === $last4)) {
                $matched = true;
            }
        }
        // Also check receiver name match if set
        if (!$matched && !empty($settings['slip_receiver_th'])) {
            $expectedNameWords = array_filter(explode(' ', trim($settings['slip_receiver_th'])));
            foreach ($expectedNameWords as $word) {
                if (mb_strlen($word) >= 3 && mb_strpos($receiverName, $word) !== false) {
                    $matched = true;
                    break;
                }
            }
        }

        if (!$matched) {
            $failMsg = 'ข้อมูลบัญชีผู้รับเงินในสลิปไม่ตรงกับบัญชีของทางร้าน';
            $db->prepare('UPDATE topup_orders SET slip_path = ?, trans_ref = ?, slip_data = ?, fail_reason = ? WHERE id = ?')
               ->execute([$safeFileName, $transRef, json_encode($slipData, JSON_UNESCAPED_UNICODE), $failMsg, $order['id']]);

            return [
                'status' => 'error',
                'code' => 'RECEIVER_MISMATCH',
                'message' => $failMsg
            ];
        }
    }

    // 7. Check Duplicate Slip (transRef must never be used in any successful topup)
    $chkStmt = $db->prepare('SELECT id, order_id, user_id FROM topup_orders WHERE trans_ref = ? AND status = "paid" AND id != ?');
    $chkStmt->execute([$transRef, $order['id']]);
    $existing = $chkStmt->fetch();

    if ($existing) {
        $failMsg = 'สลิปนี้ถูกใช้งานไปแล้วในระบบ (รหัสธุรกรรมซ้ำ: ' . $transRef . ')';
        $db->prepare('UPDATE topup_orders SET slip_path = ?, trans_ref = ?, slip_data = ?, fail_reason = ? WHERE id = ?')
           ->execute([$safeFileName, $transRef, json_encode($slipData, JSON_UNESCAPED_UNICODE), $failMsg, $order['id']]);

        return [
            'status' => 'error',
            'code' => 'DUPLICATE_SLIP',
            'message' => $failMsg
        ];
    }

    // 8. ATOMIC DATABASE TRANSACTION: Lock & Credit Customer Balance
    try {
        $db->beginTransaction();

        // Double-check transRef under transaction lock
        $doubleCheck = $db->prepare('SELECT id FROM topup_orders WHERE trans_ref = ? AND status = "paid" AND id != ?');
        $doubleCheck->execute([$transRef, $order['id']]);
        if ($doubleCheck->fetch()) {
            $db->rollBack();
            return [
                'status' => 'error',
                'code' => 'DUPLICATE_SLIP',
                'message' => 'สลิปนี้ถูกใช้งานไปแล้วในระบบ'
            ];
        }

        $nowStr = date('Y-m-d H:i:s');
        $updateStmt = $db->prepare('UPDATE topup_orders SET 
            status = "paid", 
            trans_ref = ?, 
            slip_path = ?, 
            slip_data = ?, 
            fail_reason = NULL,
            paid_at = ?
            WHERE id = ? AND status = "pending"');
        $updateStmt->execute([
            $transRef,
            $safeFileName,
            json_encode($slipData, JSON_UNESCAPED_UNICODE),
            $nowStr,
            $order['id']
        ]);

        if ($updateStmt->rowCount() === 0) {
            // Concurrently processed already by another request
            $db->rollBack();
            return [
                'status' => 'error',
                'code' => 'CONCURRENT_CONFLICT',
                'message' => 'รายการนี้ได้รับการประมวลผลไปแล้ว หรือสถานะเปลี่ยนแปลง'
            ];
        }

        // Increase user balance
        $creditStmt = $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?');
        $creditStmt->execute([$orderAmount, $userId]);

        // Record in topup_transactions
        $txStmt = $db->prepare('INSERT INTO topup_transactions (user_id, method, amount, voucher_code, created_at) 
            VALUES (?, "PromptPay (SlipOK)", ?, ?, ?)');
        $txStmt->execute([$userId, $orderAmount, $transRef, $nowStr]);

        // Record in orders_history
        $orderDesc = "เติมเงินผ่านพร้อมเพย์ SlipOK (Ref: {$transRef})";
        $histStmt = $db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) 
            VALUES (?, "topup", ?, ?, ?)');
        $histStmt->execute([$userId, $orderAmount, $orderDesc, $nowStr]);

        $db->commit();

        // Get updated user balance
        $uStmt = $db->prepare('SELECT balance, username FROM users WHERE id = ?');
        $uStmt->execute([$userId]);
        $updatedUser = $uStmt->fetch();
        $newBalance = (float)($updatedUser['balance'] ?? 0);

        // Send Discord notification
        send_discord_webhook('topup', [
            'title' => '💰 เติมเงินสำเร็จผ่าน SlipOK API (PromptPay)',
            'color' => 0x10b981,
            'fields' => [
                ['name' => 'ผู้ใช้งาน', 'value' => $updatedUser['username'] ?? ('ID #' . $userId), 'inline' => true],
                ['name' => 'จำนวนเงิน', 'value' => '฿' . number_format($orderAmount, 2), 'inline' => true],
                ['name' => 'ยอดเงินคงเหลือใหม่', 'value' => '฿' . number_format($newBalance, 2), 'inline' => true],
                ['name' => 'เลขอ้างอิงสลิป', 'value' => $transRef, 'inline' => false],
                ['name' => 'รหัสรายการ', 'value' => $orderId, 'inline' => true],
                ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => true]
            ]
        ]);

        return [
            'status' => 'success',
            'message' => 'ตรวจสอบสลิปถูกต้อง! เติมเงิน ฿' . number_format($orderAmount, 2) . ' เข้าสู่ระบบเรียบร้อยแล้ว',
            'data' => [
                'order_id' => $orderId,
                'amount' => $orderAmount,
                'new_balance' => $newBalance,
                'trans_ref' => $transRef,
                'paid_at' => date('Y-m-d H:i:s')
            ]
        ];

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        return [
            'status' => 'error',
            'code' => 'TRANSACTION_FAILED',
            'message' => 'เกิดข้อผิดพลาดในการบันทึกยอดเงิน: ' . $e->getMessage()
        ];
    }
}
