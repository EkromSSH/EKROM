<?php
// api/line_bot.php - Comprehensive LINE Messaging API & Bot Handler for EKROM Shop
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/slipok.php';

/**
 * Get LINE Bot settings from database
 */
function get_line_bot_settings(): array {
    $db = get_db();
    $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "line_bot_settings"');
    $stmt->execute();
    $raw = $stmt->fetchColumn();

    $defaults = [
        'enabled' => 0,
        'channel_secret' => '',
        'channel_access_token' => '',
        'bot_basic_id' => '',
        'bot_name' => 'EkromVPN',
        'webhook_url' => ''
    ];

    if ($raw) {
        $stored = json_decode($raw, true) ?: [];
        return array_merge($defaults, $stored);
    }
    return $defaults;
}

/**
 * Save LINE Bot settings
 */
function save_line_bot_settings(array $settings): bool {
    $db = get_db();
    $current = get_line_bot_settings();
    $merged = array_merge($current, $settings);
    $json = json_encode($merged, JSON_UNESCAPED_UNICODE);

    $stmt = $db->prepare('INSERT INTO system_settings (key, value) VALUES ("line_bot_settings", ?) ON CONFLICT(key) DO UPDATE SET value = excluded.value');
    return $stmt->execute([$json]);
}

/**
 * Session management for LINE Bot conversation state (Multi-turn conversations)
 */
function line_bot_set_user_session(int $userId, string $state, array $data = []): void {
    $db = get_db();
    $dataJson = json_encode($data, JSON_UNESCAPED_UNICODE);
    $now = date('Y-m-d H:i:s');
    $stmt = $db->prepare('
        INSERT INTO line_bot_sessions (user_id, state, data, updated_at)
        VALUES (?, ?, ?, ?)
        ON CONFLICT(user_id) DO UPDATE SET
            state = excluded.state,
            data = excluded.data,
            updated_at = excluded.updated_at
    ');
    $stmt->execute([$userId, $state, $dataJson, $now]);
}

function line_bot_get_user_session(int $userId): ?array {
    $db = get_db();
    $stmt = $db->prepare('SELECT state, data, updated_at FROM line_bot_sessions WHERE user_id = ?');
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    // Session expires after 10 minutes (600 seconds)
    if (strtotime($row['updated_at']) < time() - 600) {
        line_bot_clear_user_session($userId);
        return null;
    }
    return [
        'state' => $row['state'],
        'data' => !empty($row['data']) ? json_decode($row['data'], true) : [],
        'updated_at' => $row['updated_at']
    ];
}

function line_bot_clear_user_session(int $userId): void {
    $db = get_db();
    $stmt = $db->prepare('DELETE FROM line_bot_sessions WHERE user_id = ?');
    $stmt->execute([$userId]);
}

/**
 * Get active categories that have active servers
 */
function line_bot_get_active_categories(): array {
    $db = get_db();
    $stmt = $db->prepare('
        SELECT c.*, COUNT(s.id) as server_count 
        FROM categories c
        INNER JOIN servers s ON s.category_id = c.id
        WHERE s.is_active = 1
        GROUP BY c.id
        ORDER BY c.sort_order ASC, c.id ASC
    ');
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get single category by ID
 */
function line_bot_get_category_by_id(int $id): ?array {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM categories WHERE id = ?');
    $stmt->execute([$id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Get active servers by category ID
 */
function line_bot_get_servers_by_category(int $categoryId): array {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM servers WHERE is_active = 1 AND category_id = ? ORDER BY id ASC');
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll();
}

/**
 * Get active servers for LINE Bot
 */
function line_bot_get_active_servers(): array {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM servers WHERE is_active = 1 ORDER BY id ASC');
    $stmt->execute();
    return $stmt->fetchAll();
}

/**
 * Get single server by ID
 */
function line_bot_get_server_by_id(int $id): ?array {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
    $stmt->execute([$id]);
    $res = $stmt->fetch();
    return $res ?: null;
}

/**
 * Format server display title cleanly without duplicate flag emojis
 */
function line_bot_format_server_title(?string $flag, ?string $name): string {
    $flag = trim((string)$flag);
    $name = trim((string)$name);
    if ($name === '') {
        $name = 'VPN Server';
    }

    // 1. Remove duplicate adjacent flag emojis anywhere in name (e.g. 🇹🇭🇹🇭 -> 🇹🇭)
    $name = preg_replace('/([\x{1F1E6}-\x{1F1FF}]{2})\s*(?:[\x{1F1E6}-\x{1F1FF}]{2})+/u', '$1', $name);

    // 2. If $name already starts with a flag emoji (e.g. 🇹🇭, 🇸🇬), use $name directly without adding $flag
    if (preg_match('/^[\x{1F1E6}-\x{1F1FF}]{2}/u', $name)) {
        return $name;
    }

    // 3. If $name already starts with $flag, don't prepend $flag
    if ($flag !== '' && strpos($name, $flag) === 0) {
        return $name;
    }

    // 4. If $flag is provided and $name ends with a flag emoji, don't duplicate
    if ($flag !== '' && preg_match('/[\x{1F1E6}-\x{1F1FF}]{2}$/u', $name)) {
        return $name;
    }

    // 5. Otherwise prepend $flag
    if ($flag !== '') {
        return "{$flag} {$name}";
    }

    return $name;
}

/**
 * Clean config or server name to remove duplicate flags
 */
function line_bot_clean_config_name(?string $name): string {
    $name = trim((string)$name);
    if ($name === '') {
        return 'VPN Config';
    }
    // Remove duplicate flag emojis anywhere (e.g. 🇹🇭🇹🇭 -> 🇹🇭 or 🇹🇭 🇹🇭 -> 🇹🇭)
    $name = preg_replace('/([\x{1F1E6}-\x{1F1FF}]{2})\s*(?:[\x{1F1E6}-\x{1F1FF}]{2})+/u', '$1', $name);
    return $name;
}

function line_bot_clean_name_only(?string $name): string {
    return line_bot_clean_config_name($name);
}

/**
 * Verify LINE Webhook Signature (HMAC-SHA256)
 */
function line_bot_verify_signature(string $body, string $signature, ?string $channelSecret = null): bool {
    if (empty($signature)) return false;
    if ($channelSecret === null) {
        $settings = get_line_bot_settings();
        $channelSecret = $settings['channel_secret'] ?? '';
    }
    if (empty($channelSecret)) return false;

    $hash = hash_hmac('sha256', $body, $channelSecret, true);
    return hash_equals(base64_encode($hash), $signature);
}

/**
 * Call LINE Messaging API
 */
function line_bot_api_request(string $endpoint, array $postData, ?string $token = null): array {
    if ($token === null) {
        $settings = get_line_bot_settings();
        $token = $settings['channel_access_token'] ?? '';
    }

    $ch = curl_init('https://api.line.me/v2/bot/' . ltrim($endpoint, '/'));
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($postData, JSON_UNESCAPED_UNICODE),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json; charset=UTF-8',
            'Authorization: Bearer ' . $token
        ]
    ]);

    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr = curl_error($ch);
    curl_close($ch);

    $logFile = __DIR__ . '/../line_bot.log';
    if ($curlErr) {
        $logMsg = date('Y-m-d H:i:s') . " [ERROR] cURL ({$endpoint}): {$curlErr}\n";
        @file_put_contents($logFile, $logMsg, FILE_APPEND);
        return ['success' => false, 'error' => $curlErr];
    }

    $json = json_decode((string)$res, true);
    $isSuccess = ($httpCode >= 200 && $httpCode < 300);

    if (!$isSuccess) {
        $logMsg = date('Y-m-d H:i:s') . " [ERROR] LINE API ({$endpoint}) HTTP {$httpCode}: " . (string)$res . "\n";
        @file_put_contents($logFile, $logMsg, FILE_APPEND);
    } else {
        $logMsg = date('Y-m-d H:i:s') . " [INFO] LINE API ({$endpoint}) HTTP {$httpCode}: Success\n";
        @file_put_contents($logFile, $logMsg, FILE_APPEND);
    }

    return [
        'success' => $isSuccess,
        'http_code' => $httpCode,
        'data' => $json
    ];
}

/**
 * Send Reply Message via LINE API
 */
function line_bot_reply_message(string $replyToken, array $messages, ?string $token = null): bool {
    if (empty($replyToken)) return false;
    // Limit to max 5 messages per LINE specs
    $messages = array_slice($messages, 0, 5);
    $res = line_bot_api_request('message/reply', [
        'replyToken' => $replyToken,
        'messages' => $messages
    ], $token);
    return $res['success'];
}

/**
 * Send Push Message via LINE API
 */
function line_bot_push_message(string $toUserId, array $messages, ?string $token = null): bool {
    if (empty($toUserId)) return false;
    $messages = array_slice($messages, 0, 5);
    $res = line_bot_api_request('message/push', [
        'to' => $toUserId,
        'messages' => $messages
    ], $token);
    return $res['success'];
}

/**
 * Get User Profile from LINE API
 */
function line_bot_get_profile(string $lineUserId, ?string $token = null): ?array {
    if ($token === null) {
        $settings = get_line_bot_settings();
        $token = $settings['channel_access_token'] ?? '';
    }
    if (empty($token) || empty($lineUserId)) return null;

    $ch = curl_init('https://api.line.me/v2/bot/profile/' . urlencode($lineUserId));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token]
    ]);
    $res = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $res) {
        return json_decode($res, true);
    }
    return null;
}

/**
 * Download Message Content (e.g. uploaded slip image)
 */
function line_bot_get_message_content(string $messageId, ?string $token = null): ?string {
    if ($token === null) {
        $settings = get_line_bot_settings();
        $token = $settings['channel_access_token'] ?? '';
    }
    if (empty($token) || empty($messageId)) return null;

    $ch = curl_init("https://api-data.line.me/v2/bot/message/{$messageId}/content");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $token]
    ]);
    $binary = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $binary) {
        return $binary;
    }
    return null;
}

/**
 * Find or auto-register a user linked to a LINE user ID
 */
function line_bot_get_or_create_user(string $lineUserId): array {
    $db = get_db();
    $stmt = $db->prepare('SELECT * FROM users WHERE line_user_id = ?');
    $stmt->execute([$lineUserId]);
    $user = $stmt->fetch();

    if ($user) {
        return $user;
    }

    // New user -> Fetch LINE Profile
    $profile = line_bot_get_profile($lineUserId);
    $displayName = !empty($profile['displayName']) ? trim($profile['displayName']) : 'LINE User ' . substr($lineUserId, -4);
    $pictureUrl = !empty($profile['pictureUrl']) ? trim($profile['pictureUrl']) : null;

    // Generate unique username: line_<hash>
    $baseUsername = 'line_' . substr(md5($lineUserId), 0, 8);
    $username = $baseUsername;
    $suffix = 1;
    while (true) {
        $chk = $db->prepare('SELECT id FROM users WHERE username = ?');
        $chk->execute([$username]);
        if (!$chk->fetch()) break;
        $username = $baseUsername . '_' . $suffix++;
    }

    $randomPass = bin2hex(random_bytes(8));
    $hashedPass = password_hash($randomPass, PASSWORD_DEFAULT);

    $insert = $db->prepare('
        INSERT INTO users (username, password, role, balance, line_user_id, line_display_name, line_picture_url)
        VALUES (?, ?, "user", 0.00, ?, ?, ?)
    ');
    $insert->execute([$username, $hashedPass, $lineUserId, $displayName, $pictureUrl]);
    $userId = (int)$db->lastInsertId();

    // Log notification
    send_discord_webhook('system', [
        'title' => '👤 ลูกค้าใหม่สมัครผ่าน LINE Bot!',
        'color' => 0x06c755,
        'fields' => [
            ['name' => 'ชื่อใน LINE', 'value' => $displayName, 'inline' => true],
            ['name' => 'Username ในระบบ', 'value' => $username, 'inline' => true],
            ['name' => 'LINE User ID', 'value' => $lineUserId, 'inline' => false],
            ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
        ]
    ]);

    $fetch = $db->prepare('SELECT * FROM users WHERE id = ?');
    $fetch->execute([$userId]);
    return $fetch->fetch();
}

/**
 * Process Direct Slip Upload via LINE chat
 */
function line_bot_process_slip_image(array $user, string $imageBinary): array {
    $uploadDir = dirname(__DIR__) . '/uploads/slips';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $tempName = 'line_slip_' . $user['id'] . '_' . time() . '_' . uniqid() . '.jpg';
    $filePath = $uploadDir . '/' . $tempName;

    if (file_put_contents($filePath, $imageBinary) === false) {
        return [
            'success' => false,
            'message' => 'ไม่สามารถบันทึกไฟล์สลิปได้ กรุณาลองใหม่อีกครั้ง'
        ];
    }

    $settings = get_slip_settings();
    $slipokRes = call_slipok_api($filePath, null);

    if (!$slipokRes || empty($slipokRes['success']) || empty($slipokRes['data'])) {
        $code = (int)($slipokRes['code'] ?? 0);
        $rawMsg = (string)($slipokRes['message'] ?? '');

        if ($code === 1003 || stripos($rawMsg, 'Package') !== false) {
            $failMsg = 'ระบบตรวจสลิปของร้านค้ายังไม่พร้อมใช้งาน กรุณาแจ้งแอดมิน';
        } elseif ($code === 1001 || stripos($rawMsg, 'ไม่พบ') !== false) {
            $failMsg = 'ไม่พบ QR Code ในสลิป หรือรูปภาพไม่ใช่สลิปโอนเงินที่ถูกต้อง';
        } elseif (!empty($rawMsg)) {
            $failMsg = $rawMsg;
        } else {
            $failMsg = 'ตรวจสอบสลิปไม่สำเร็จ รูปภาพอาจไม่ชัดหรือไม่ใช่สลิปธนาคาร';
        }

        return [
            'success' => false,
            'message' => $failMsg
        ];
    }

    $slipData = $slipokRes['data'];
    $transRef = trim($slipData['transRef'] ?? '');
    $amount = (float)($slipData['amount'] ?? 0);
    $senderName = $slipData['sender']['displayName'] ?? ($slipData['sender']['name'] ?? 'ลูกค้า');
    $receiverAcc = $slipData['receiver']['account']['value'] ?? ($slipData['receiver']['proxy']['value'] ?? '');

    if (empty($transRef)) {
        return [
            'success' => false,
            'message' => 'ไม่พบรหัสอ้างอิงธุรกรรมในสลิป'
        ];
    }

    $minAmount = max(30.00, (float)($settings['slip_min_amount'] ?? 30.00));
    if ($amount < $minAmount) {
        return [
            'success' => false,
            'message' => 'ยอดเงินในสลิป (฿' . number_format($amount, 2) . ') น้อยกว่ายอดขั้นต่ำ ฿' . number_format($minAmount, 2) . ' บาท'
        ];
    }

    $db = get_db();

    // Check duplicate slip
    $chk = $db->prepare('SELECT id, user_id FROM topup_orders WHERE trans_ref = ? AND status = "paid"');
    $chk->execute([$transRef]);
    $existing = $chk->fetch();

    if ($existing) {
        return [
            'success' => false,
            'message' => 'สลิปนี้ (รหัส ' . $transRef . ') ถูกใช้งานเติมเงินไปแล้ว ไม่สามารถใช้ซ้ำได้ ❌'
        ];
    }

    // Verify Receiver Account
    $confReceiverAcc = preg_replace('/[^0-9]/', '', (string)$settings['slip_receiver_account']);
    $confPromptPay = preg_replace('/[^0-9]/', '', (string)($settings['promptpay_number'] ?? ''));
    $actualRec = preg_replace('/[^0-9]/', '', (string)$receiverAcc);

    if (!empty($confReceiverAcc) && !empty($actualRec)) {
        $match = (str_contains($actualRec, $confReceiverAcc) || str_contains($confReceiverAcc, $actualRec));
        if (!$match && !empty($confPromptPay)) {
            $match = (str_contains($actualRec, $confPromptPay) || str_contains($confPromptPay, $actualRec));
        }
        if (!$match) {
            return [
                'success' => false,
                'message' => 'บัญชีผู้รับในสลิปไม่ตรงกับบัญชีของทางร้าน (โอนผิดบัญชี)'
            ];
        }
    }

    // Success -> Credit user balance atomically
    $nowStr = date('Y-m-d H:i:s');
    $orderId = 'LINE-SLIP-' . date('YmdHis') . '-' . mt_rand(100, 999);
    $promptpayNum = (string)($settings['slip_receiver_account'] ?: ($settings['promptpay_number'] ?: ''));
    $promptpayName = (string)($settings['slip_receiver_th'] ?: ($settings['promptpay_name'] ?: 'ร้านค้า'));
    $slipDataJson = json_encode($slipData, JSON_UNESCAPED_UNICODE);

    try {
        $db->prepare('
            INSERT INTO topup_orders (order_id, user_id, amount, status, promptpay_number, promptpay_name, slip_path, trans_ref, slip_data, paid_at, expires_at, created_at)
            VALUES (?, ?, ?, "paid", ?, ?, ?, ?, ?, ?, ?, ?)
        ')->execute([$orderId, $user['id'], $amount, $promptpayNum, $promptpayName, $tempName, $transRef, $slipDataJson, $nowStr, $nowStr, $nowStr]);

        $db->prepare('
            INSERT INTO topup_transactions (user_id, method, amount, voucher_code, created_at)
            VALUES (?, "Slip Bank (LINE Bot)", ?, ?, ?)
        ')->execute([$user['id'], $amount, $transRef, $nowStr]);

        $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$amount, $user['id']]);

        $db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "topup", ?, ?, ?)')
           ->execute([$user['id'], $amount, "เติมเงินผ่านสลิปธนาคาร (LINE Bot: {$transRef})", $nowStr]);

        $newBalStmt = $db->prepare('SELECT balance FROM users WHERE id = ?');
        $newBalStmt->execute([$user['id']]);
        $newBalance = (float)$newBalStmt->fetchColumn();
    } catch (\Throwable $e) {
        $logFile = __DIR__ . '/../line_bot.log';
        @file_put_contents($logFile, date('Y-m-d H:i:s') . " [DB ERROR] Slip credit error: " . $e->getMessage() . "\n", FILE_APPEND);
        return [
            'success' => false,
            'message' => 'เกิดข้อผิดพลาดในการบันทึกยอดเงินเข้าบัญชี: ' . $e->getMessage()
        ];
    }

    // Send Discord Webhook
    send_discord_webhook('topup', [
        'title' => '💰 มีการเติมเงินผ่าน LINE Bot (สลิปโอนเงิน)',
        'color' => 0x06c755,
        'fields' => [
            ['name' => 'ผู้ใช้งาน', 'value' => ($user['line_display_name'] ?: $user['username']), 'inline' => true],
            ['name' => 'ยอดเงิน', 'value' => '฿' . number_format($amount, 2), 'inline' => true],
            ['name' => 'ยอดคงเหลือใหม่', 'value' => '฿' . number_format($newBalance, 2), 'inline' => true],
            ['name' => 'รหัสสลิป (TransRef)', 'value' => $transRef, 'inline' => false],
            ['name' => 'ชื่อผู้โอน', 'value' => $senderName, 'inline' => true],
            ['name' => 'เวลา', 'value' => $nowStr, 'inline' => true]
        ]
    ]);

    return [
        'success' => true,
        'amount' => $amount,
        'new_balance' => $newBalance,
        'trans_ref' => $transRef,
        'sender_name' => $senderName
    ];
}

/**
 * Process TrueMoney Voucher link in LINE chat
 */
function line_bot_process_truemoney_voucher(array $user, string $text): ?array {
    // Extract voucher code or link
    if (!preg_match('/gift\.truemoney\.com\/campaign\/\?v=([a-zA-Z0-9]+)/i', $text, $matches)) {
        return null;
    }

    $voucherCode = $matches[1];
    $redeemedAmount = 100.00; // Demo/standard voucher amount

    $db = get_db();
    $nowStr = date('Y-m-d H:i:s');

    $db->prepare('INSERT INTO topup_transactions (user_id, method, amount, voucher_code, created_at) VALUES (?, "TrueMoney Voucher (LINE)", ?, ?, ?)')
       ->execute([$user['id'], $redeemedAmount, $voucherCode, $nowStr]);

    $db->prepare('UPDATE users SET balance = balance + ? WHERE id = ?')->execute([$redeemedAmount, $user['id']]);

    $db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "topup", ?, ?, ?)')
       ->execute([$user['id'], $redeemedAmount, "เติมเงินซอง TrueMoney (LINE: {$voucherCode})", $nowStr]);

    $newBalStmt = $db->prepare('SELECT balance FROM users WHERE id = ?');
    $newBalStmt->execute([$user['id']]);
    $newBalance = (float)$newBalStmt->fetchColumn();

    // Discord Webhook
    send_discord_webhook('topup', [
        'title' => '🧧 มีการเติมเงินซองของขวัญ TrueMoney (LINE Bot)',
        'color' => 0xf97316,
        'fields' => [
            ['name' => 'ผู้ใช้งาน', 'value' => ($user['line_display_name'] ?: $user['username']), 'inline' => true],
            ['name' => 'จำนวนเงิน', 'value' => '฿' . number_format($redeemedAmount, 2), 'inline' => true],
            ['name' => 'ยอดคงเหลือใหม่', 'value' => '฿' . number_format($newBalance, 2), 'inline' => true],
            ['name' => 'รหัสซอง', 'value' => $voucherCode, 'inline' => false]
        ]
    ]);

    return [
        'success' => true,
        'amount' => $redeemedAmount,
        'new_balance' => $newBalance,
        'voucher_code' => $voucherCode
    ];
}

// ==========================================
// 🎨 LINE FLEX MESSAGES BUILDERS
// ==========================================

/**
 * Main Menu Flex Message
 */
function line_bot_build_main_menu(array $user): array {
    $displayName = $user['line_display_name'] ?: $user['username'];
    $balance = number_format((float)$user['balance'], 2);
    $avatar = $user['line_picture_url'] ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png';

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0f172a',
            'paddingAll' => '20px',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'spacing' => 'md',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => '⚡ EkromVPN Shop',
                            'weight' => 'bold',
                            'size' => 'lg',
                            'color' => '#38bdf8',
                            'flex' => 1
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'backgroundColor' => '#059669',
                            'cornerRadius' => '4px',
                            'paddingAll' => '2px',
                            'paddingStart' => '6px',
                            'paddingEnd' => '6px',
                            'flex' => 0,
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => 'BOT 24H',
                                    'size' => 'xxs',
                                    'color' => '#ffffff',
                                    'align' => 'center',
                                    'weight' => 'bold'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => 'ระบบสั่งซื้อและสร้างไฟล์ VPN / SSH อัตโนมัติ',
                    'size' => 'xs',
                    'color' => '#94a3b8',
                    'wrap' => true,
                    'margin' => 'sm'
                ]
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '18px',
            'contents' => [
                // User info card
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'backgroundColor' => '#f8fafc',
                    'cornerRadius' => '12px',
                    'paddingAll' => '12px',
                    'alignItems' => 'center',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'cornerRadius' => '100px',
                            'width' => '40px',
                            'height' => '40px',
                            'flex' => 0,
                            'contents' => [
                                [
                                    'type' => 'image',
                                    'url' => $avatar,
                                    'size' => 'full',
                                    'aspectMode' => 'cover',
                                    'aspectRatio' => '1:1'
                                ]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'margin' => 'md',
                            'flex' => 1,
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => $displayName,
                                    'weight' => 'bold',
                                    'size' => 'sm',
                                    'color' => '#0f172a',
                                    'wrap' => true,
                                    'maxLines' => 1
                                ],
                                [
                                    'type' => 'box',
                                    'layout' => 'horizontal',
                                    'contents' => [
                                        ['type' => 'text', 'text' => 'ยอดเงินคงเหลือ:', 'size' => 'xs', 'color' => '#64748b'],
                                        ['type' => 'text', 'text' => " ฿{$balance}", 'weight' => 'bold', 'size' => 'sm', 'color' => '#059669']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                // Menu Buttons
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'lg',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '🛒 สั่งซื้อ VPN', 'data' => 'action=buy_servers', 'displayText' => 'สั่งซื้อ VPN'],
                            'style' => 'primary',
                            'color' => '#2563eb',
                            'height' => 'sm'
                        ],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '⚡ ทดลองใช้ฟรี', 'data' => 'action=trial_servers', 'displayText' => 'ทดลองใช้ฟรี'],
                            'style' => 'secondary',
                            'color' => '#0284c7',
                            'height' => 'sm'
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'spacing' => 'sm',
                            'contents' => [
                                [
                                    'type' => 'button',
                                    'action' => ['type' => 'postback', 'label' => '♻️ ต่ออายุ', 'data' => 'action=renew_menu', 'displayText' => 'ต่ออายุ VPN'],
                                    'style' => 'primary',
                                    'color' => '#8b5cf6',
                                    'height' => 'sm',
                                    'flex' => 1
                                ],
                                [
                                    'type' => 'button',
                                    'action' => ['type' => 'postback', 'label' => '💰 เติมเงิน', 'data' => 'action=topup_menu', 'displayText' => 'เติมเงิน'],
                                    'style' => 'primary',
                                    'color' => '#059669',
                                    'height' => 'sm',
                                    'flex' => 1
                                ]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'spacing' => 'sm',
                            'contents' => [
                                [
                                    'type' => 'button',
                                    'action' => ['type' => 'postback', 'label' => '📂 ดูไฟล์', 'data' => 'action=my_vpns', 'displayText' => 'ดูไฟล์'],
                                    'style' => 'secondary',
                                    'color' => '#0284c7',
                                    'height' => 'sm',
                                    'flex' => 1
                                ],
                                [
                                    'type' => 'button',
                                    'action' => ['type' => 'postback', 'label' => '👤 โปรไฟล์', 'data' => 'action=my_profile', 'displayText' => 'โปรไฟล์'],
                                    'style' => 'secondary',
                                    'color' => '#0284c7',
                                    'height' => 'sm',
                                    'flex' => 1
                                ]
                            ]
                        ],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '❓ วิธีใช้งาน', 'data' => 'action=help_menu', 'displayText' => 'วิธีใช้งาน'],
                            'style' => 'link',
                            'height' => 'sm'
                        ]
                    ]
                ]
            ]
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => '⚡ เมนูหลัก EkromVPN Shop',
        'contents' => $bubble
    ];
}

/**
 * Category / Network Selection Flex Message (Step 1)
 */
function line_bot_build_category_selection(array $categories, array $user, bool $isTrial = false): array {
    if (empty($categories)) {
        return [
            'type' => 'flex',
            'altText' => '⚠️ ไม่พบเครือข่ายที่เปิดให้บริการ',
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '20px',
                    'contents' => [
                        ['type' => 'text', 'text' => '⚠️ ยังไม่มีเครือข่ายที่เปิดให้บริการ', 'weight' => 'bold', 'size' => 'md', 'color' => '#0f172a'],
                        ['type' => 'text', 'text' => 'ขออภัย ขณะนี้ยังไม่มีเซิร์ฟเวอร์หรือโปรโมชั่นที่เปิดใช้งาน กรุณาติดต่อแอดมิน', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'md'],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
                            'style' => 'secondary',
                            'height' => 'sm',
                            'margin' => 'lg'
                        ]
                    ]
                ]
            ]
        ];
    }

    $title = $isTrial ? '⚡ เลือกเครือข่าย (ทดลองใช้ฟรี)' : '🛒 เลือกเครือข่ายอินเทอร์เน็ต';
    $subtitle = $isTrial ? 'เลือกเครือข่ายซิมเพื่อทดลองใช้โปรโมชั่น VPN ฟรี' : 'เลือกเครือข่ายซิมเพื่อดูโปรโมชั่น VPN ทั้งหมด';

    $catCards = [];
    foreach ($categories as $cat) {
        $name = trim($cat['name'] ?? 'เครือข่าย');
        $lowerName = strtolower($name);
        $count = (int)($cat['server_count'] ?? 1);

        // Color & icon per carrier
        $themeColor = '#3b82f6';
        $icon = '📶';
        if (stripos($lowerName, 'ais') !== false) {
            $themeColor = '#059669';
            $icon = '🟢';
        } elseif (stripos($lowerName, 'dtac') !== false) {
            $themeColor = '#0284c7';
            $icon = '🔵';
        } elseif (stripos($lowerName, 'true') !== false) {
            $themeColor = '#e11d48';
            $icon = '🔴';
        }

        $btnLabel = $isTrial ? "ลอง {$name}" : "เลือก {$name}";
        $isTrialVal = $isTrial ? '1' : '0';

        $catCards[] = [
            'type' => 'box',
            'layout' => 'horizontal',
            'alignItems' => 'center',
            'backgroundColor' => '#f8fafc',
            'cornerRadius' => '10px',
            'paddingAll' => '12px',
            'margin' => 'sm',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'flex' => 1,
                    'contents' => [
                        ['type' => 'text', 'text' => "{$icon} {$name}", 'weight' => 'bold', 'size' => 'sm', 'color' => '#0f172a', 'wrap' => true],
                        ['type' => 'text', 'text' => "มี {$count} โปรโมชั่นพร้อมใช้งาน", 'size' => 'xxs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'xs']
                    ]
                ],
                [
                    'type' => 'button',
                    'action' => [
                        'type' => 'postback',
                        'label' => $btnLabel,
                        'data' => "action=select_category&category_id={$cat['id']}&is_trial={$isTrialVal}",
                        'displayText' => "เลือกเครือข่าย {$name}"
                    ],
                    'style' => 'primary',
                    'color' => $themeColor,
                    'height' => 'sm',
                    'flex' => 0
                ]
            ]
        ];
    }

    $catCards[] = [
        'type' => 'button',
        'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
        'style' => 'link',
        'height' => 'sm',
        'margin' => 'md'
    ];

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0f172a',
            'paddingAll' => '18px',
            'contents' => [
                ['type' => 'text', 'text' => $title, 'weight' => 'bold', 'size' => 'md', 'color' => '#38bdf8', 'wrap' => true],
                ['type' => 'text', 'text' => $subtitle, 'size' => 'xs', 'color' => '#94a3b8', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '14px',
            'contents' => $catCards
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => $title,
        'contents' => $bubble
    ];
}

/**
 * Servers / Promotions within Category Flex Message (Step 2)
 */
function line_bot_build_servers_in_category(array $category, array $servers, array $user, bool $isTrial = false): array {
    $catName = $category['name'] ?? 'เครือข่าย';
    $isTrialVal = $isTrial ? '1' : '0';

    if (empty($servers)) {
        return [
            'type' => 'flex',
            'altText' => "⚠️ ยังไม่มีโปรโมชั่นสำหรับ {$catName}",
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '20px',
                    'contents' => [
                        ['type' => 'text', 'text' => "⚠️ ยังไม่มีโปรโมชั่นสำหรับ {$catName}", 'weight' => 'bold', 'size' => 'md', 'color' => '#0f172a', 'wrap' => true],
                        ['type' => 'text', 'text' => 'ขณะนี้ยังไม่มีเซิร์ฟเวอร์เปิดใช้งานในหมวดนี้ กรุณาเลือกเครือข่ายอื่น', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'md'],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '◀️ เปลี่ยนเครือข่าย', 'data' => ($isTrial ? 'action=trial_servers' : 'action=buy_servers'), 'displayText' => 'เลือกเครือข่ายอื่น'],
                            'style' => 'primary',
                            'color' => '#2563eb',
                            'height' => 'sm',
                            'margin' => 'lg'
                        ]
                    ]
                ]
            ]
        ];
    }

    $serverCards = [];
    foreach ($servers as $s) {
        $flag = $s['icon'] ?? ($s['flag'] ?? '🇹🇭');
        $name = $s['name'] ?? 'VPN Server';
        $location = $s['location'] ?? 'ไทย';
        $userCount = (int)($s['user_count'] ?? 0);
        $protocol = strtoupper($s['protocol'] ?: ($s['type'] === 'ssh_script' ? 'SSH' : 'VLESS'));
        $desc = !empty($s['description']) ? $s['description'] : 'รองรับเล่นเกม ดูหนัง ทะลุบล็อก ไม่ลดสปีด 24 ชม.';

        $btnLabel = $isTrial ? '⚡ ขอทดลองใช้ฟรี' : '🛒 เลือกโปรนี้';
        $btnColor = $isTrial ? '#0284c7' : '#2563eb';

        $displayTitle = line_bot_format_server_title($flag, $name);
        $cleanName = line_bot_clean_name_only($name);

        $serverCards[] = [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#f8fafc',
            'cornerRadius' => '12px',
            'paddingAll' => '14px',
            'margin' => 'sm',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'alignItems' => 'center',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => $displayTitle,
                            'weight' => 'bold',
                            'size' => 'sm',
                            'color' => '#0f172a',
                            'wrap' => true,
                            'flex' => 1
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'backgroundColor' => '#e0f2fe',
                            'cornerRadius' => '4px',
                            'paddingAll' => '2px',
                            'paddingStart' => '6px',
                            'paddingEnd' => '6px',
                            'flex' => 0,
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => $protocol,
                                    'size' => 'xxs',
                                    'color' => '#0284c7',
                                    'weight' => 'bold'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => "⚡ {$desc}",
                    'size' => 'xs',
                    'color' => '#475569',
                    'wrap' => true,
                    'margin' => 'sm'
                ],
                [
                    'type' => 'text',
                    'text' => "🟢 ออนไลน์พร้อมใช้งาน • 📍 {$location}",
                    'size' => 'xxs',
                    'color' => '#059669',
                    'wrap' => true,
                    'margin' => 'xs'
                ],
                [
                    'type' => 'button',
                    'action' => [
                        'type' => 'postback',
                        'label' => $btnLabel,
                        'data' => "action=select_server&server_id={$s['id']}&is_trial={$isTrialVal}",
                        'displayText' => ($isTrial ? "ทดลองใช้ {$cleanName}" : "เลือก {$cleanName}")
                    ],
                    'style' => 'primary',
                    'color' => $btnColor,
                    'height' => 'sm',
                    'margin' => 'md'
                ]
            ]
        ];
    }

    $backAction = $isTrial ? 'action=trial_servers' : 'action=buy_servers';
    $navButtons = [
        'type' => 'box',
        'layout' => 'horizontal',
        'margin' => 'md',
        'spacing' => 'sm',
        'contents' => [
            [
                'type' => 'button',
                'action' => ['type' => 'postback', 'label' => '◀️ เปลี่ยนเครือข่าย', 'data' => $backAction, 'displayText' => 'เลือกเครือข่ายอื่น'],
                'style' => 'secondary',
                'color' => '#475569',
                'height' => 'sm',
                'flex' => 1
            ],
            [
                'type' => 'button',
                'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
                'style' => 'link',
                'height' => 'sm',
                'flex' => 1
            ]
        ]
    ];

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0f172a',
            'paddingAll' => '18px',
            'contents' => [
                ['type' => 'text', 'text' => "📶 โปรโมชั่นเครือข่าย: {$catName}", 'weight' => 'bold', 'size' => 'md', 'color' => '#38bdf8', 'wrap' => true],
                ['type' => 'text', 'text' => ($isTrial ? 'เลือกโปรโมชั่นที่ต้องการทดลองใช้ฟรี' : 'เลือกโปรโมชั่นที่ตรงกับแพ็กเกจเน็ตของคุณ'), 'size' => 'xs', 'color' => '#94a3b8', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '14px',
            'contents' => array_merge($serverCards, [$navButtons])
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => "📶 โปรโมชั่นเครือข่าย {$catName}",
        'contents' => $bubble
    ];
}

/**
 * Package Selection Flex Message (Step 3 - Buy Flow with Custom Name)
 */
function line_bot_build_package_selection(array $server, array $user, string $customName = ''): array {
    $db = get_db();
    $tierPrices = [5, 25, 45, 80]; // fallback [1d, 7d, 15d, 30d]
    if (!empty($server['tier_id'])) {
        $tStmt = $db->prepare('SELECT prices FROM price_tiers WHERE id = ?');
        $tStmt->execute([$server['tier_id']]);
        $tierJson = $tStmt->fetchColumn();
        if ($tierJson) {
            $arr = json_decode($tierJson, true);
            if (is_array($arr) && count($arr) >= 4) $tierPrices = $arr;
        }
    }

    $isReseller = (isset($user['role']) && $user['role'] === 'reseller');
    $userBal = (float)$user['balance'];

    $packages = [
        ['val' => '1', 'days' => '1 วัน', 'price' => (float)$tierPrices[0]],
        ['val' => '7', 'days' => '7 วัน', 'price' => (float)$tierPrices[1]],
        ['val' => '15', 'days' => '15 วัน', 'price' => (float)$tierPrices[2]],
        ['val' => '30', 'days' => '30 วัน', 'price' => (float)$tierPrices[3]]
    ];

    $customNameParam = urlencode($customName);

    $pkgRows = [];
    foreach ($packages as $pkg) {
        $p = $pkg['price'];
        if ($isReseller && $p > 0) $p = round($p * 0.70, 2);

        $hasEnough = ($userBal >= $p);
        $btnColor = $hasEnough ? '#2563eb' : '#94a3b8';

        $serverCleanName = line_bot_clean_name_only($server['name'] ?? 'VPN Server');
        $pkgRows[] = [
            'type' => 'box',
            'layout' => 'horizontal',
            'alignItems' => 'center',
            'backgroundColor' => '#f8fafc',
            'cornerRadius' => '8px',
            'paddingAll' => '10px',
            'margin' => 'sm',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'flex' => 1,
                    'contents' => [
                        ['type' => 'text', 'text' => "แพ็กเกจ {$pkg['days']}", 'weight' => 'bold', 'size' => 'sm', 'color' => '#0f172a', 'wrap' => true],
                        ['type' => 'text', 'text' => '฿' . number_format($p, 2) . ($isReseller ? ' (ลด 30%)' : ''), 'weight' => 'bold', 'size' => 'xs', 'color' => '#059669', 'wrap' => true]
                    ]
                ],
                [
                    'type' => 'button',
                    'action' => [
                        'type' => 'postback',
                        'label' => $hasEnough ? 'ซื้อเลย' : 'เงินไม่พอ',
                        'data' => "action=confirm_buy&server_id={$server['id']}&package={$pkg['val']}&custom_name={$customNameParam}",
                        'displayText' => "ซื้อแพ็กเกจ {$pkg['days']} ({$serverCleanName})"
                    ],
                    'style' => 'primary',
                    'color' => $btnColor,
                    'height' => 'sm',
                    'flex' => 0
                ]
            ]
        ];
    }

    $catId = (int)($server['category_id'] ?? 0);
    $backData = ($catId > 0) ? "action=select_category&category_id={$catId}&is_trial=0" : 'action=buy_servers';

    $serverCleanName = line_bot_clean_name_only($server['name'] ?? 'VPN Server');
    $headerContents = [
        ['type' => 'text', 'text' => "🛒 เลือกแพ็กเกจ: {$serverCleanName}", 'weight' => 'bold', 'size' => 'md', 'color' => '#38bdf8', 'wrap' => true],
        ['type' => 'text', 'text' => "ยอดเงินคงเหลือของคุณ: ฿" . number_format($userBal, 2), 'size' => 'xs', 'color' => '#94a3b8', 'wrap' => true, 'margin' => 'xs']
    ];

    if ($customName !== '') {
        $headerContents[] = [
            'type' => 'box',
            'layout' => 'horizontal',
            'margin' => 'sm',
            'backgroundColor' => '#1e293b',
            'cornerRadius' => '6px',
            'paddingAll' => '6px',
            'contents' => [
                ['type' => 'text', 'text' => "🏷️ ชื่อไฟล์ของคุณ: {$customName}", 'size' => 'xs', 'color' => '#38bdf8', 'weight' => 'bold', 'wrap' => true]
            ]
        ];
    }

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0f172a',
            'paddingAll' => '16px',
            'contents' => $headerContents
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '14px',
            'contents' => array_merge([
                ['type' => 'text', 'text' => 'เลือกระยะเวลาการใช้งานที่ต้องการ:', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'none']
            ], $pkgRows, [
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'md',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '💰 เติมเงินเพิ่ม', 'data' => 'action=topup_menu', 'displayText' => 'เติมเงิน'],
                            'style' => 'secondary',
                            'color' => '#059669',
                            'height' => 'sm',
                            'flex' => 1
                        ],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '◀️ เปลี่ยนโปร', 'data' => $backData, 'displayText' => 'เปลี่ยนโปรโมชั่น'],
                            'style' => 'secondary',
                            'color' => '#475569',
                            'height' => 'sm',
                            'flex' => 1
                        ]
                    ]
                ],
                [
                    'type' => 'button',
                    'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
                    'style' => 'link',
                    'height' => 'sm',
                    'margin' => 'xs'
                ]
            ])
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => "🛒 เลือกแพ็กเกจ {$server['name']}",
        'contents' => $bubble
    ];
}

/**
 * Trial Confirmation Flex Message (Step 3 - Trial Flow with Custom Name)
 */
function line_bot_build_trial_confirmation(array $server, array $user, string $customName = ''): array {
    $catId = (int)($server['category_id'] ?? 0);
    $backData = ($catId > 0) ? "action=select_category&category_id={$catId}&is_trial=1" : 'action=trial_servers';
    $protocol = strtoupper($server['protocol'] ?: ($server['type'] === 'ssh_script' ? 'SSH' : 'VLESS'));
    $customNameParam = urlencode($customName);
    $serverCleanName = line_bot_clean_name_only($server['name'] ?? 'VPN Server');
    $serverDisplayTitle = line_bot_format_server_title($server['icon'] ?? '🇹🇭', $server['name'] ?? 'VPN Server');

    $headerContents = [
        ['type' => 'text', 'text' => '⚡ ทดลองใช้งาน VPN ฟรี (60 นาที)', 'weight' => 'bold', 'size' => 'md', 'color' => '#38bdf8', 'wrap' => true],
        ['type' => 'text', 'text' => 'ทดลองเชื่อมต่อและทดสอบความเร็วฟรีทันที', 'size' => 'xs', 'color' => '#94a3b8', 'wrap' => true, 'margin' => 'xs']
    ];

    if ($customName !== '') {
        $headerContents[] = [
            'type' => 'box',
            'layout' => 'horizontal',
            'margin' => 'sm',
            'backgroundColor' => '#1e293b',
            'cornerRadius' => '6px',
            'paddingAll' => '6px',
            'contents' => [
                ['type' => 'text', 'text' => "🏷️ ชื่อไฟล์ของคุณ: {$customName}", 'size' => 'xs', 'color' => '#38bdf8', 'weight' => 'bold', 'wrap' => true]
            ]
        ];
    }

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0f172a',
            'paddingAll' => '16px',
            'contents' => $headerContents
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#f8fafc',
                    'cornerRadius' => '8px',
                    'paddingAll' => '12px',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'เซิร์ฟเวอร์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $serverDisplayTitle, 'weight' => 'bold', 'size' => 'xs', 'color' => '#0f172a', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'โปรโตคอล:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $protocol, 'size' => 'xs', 'color' => '#0284c7', 'weight' => 'bold', 'flex' => 4]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ระยะเวลาทดลอง:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => '60 นาที (1 ชั่วโมง)', 'weight' => 'bold', 'size' => 'xs', 'color' => '#0284c7', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ค่าบริการ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => '฿0.00 (ฟรี)', 'weight' => 'bold', 'size' => 'xs', 'color' => '#059669', 'flex' => 4]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'button',
                    'action' => [
                        'type' => 'postback',
                        'label' => '⚡ รับไฟล์ทดลองฟรี',
                        'data' => "action=confirm_trial&server_id={$server['id']}&custom_name={$customNameParam}",
                        'displayText' => "ยืนยันขอทดลองใช้ {$serverCleanName}"
                    ],
                    'style' => 'primary',
                    'color' => '#0284c7',
                    'height' => 'sm',
                    'margin' => 'md'
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'sm',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '◀️ เปลี่ยนโปร', 'data' => $backData, 'displayText' => 'เปลี่ยนโปรโมชั่น'],
                            'style' => 'secondary',
                            'color' => '#475569',
                            'height' => 'sm',
                            'flex' => 1
                        ],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
                            'style' => 'link',
                            'height' => 'sm',
                            'flex' => 1
                        ]
                    ]
                ]
            ]
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => "⚡ ทดลองใช้งานฟรี: {$serverCleanName}",
        'contents' => $bubble
    ];
}

/**
 * Server Carousel Flex Message (Fallback / Legacy)
 */
function line_bot_build_server_carousel(array $servers, array $user, bool $isTrial = false): array {
    if (empty($servers)) {
        return [
            'type' => 'text',
            'text' => 'ขออภัย ขณะนี้ไม่มีเซิร์ฟเวอร์ที่เปิดให้บริการ กรุณาติดต่อแอดมิน'
        ];
    }

    $bubbles = [];
    $servers = array_slice($servers, 0, 10); // LINE limit 10 bubbles

    foreach ($servers as $s) {
        $flag = $s['icon'] ?? ($s['flag'] ?? '🇹🇭');
        $name = $s['name'] ?? 'Server';
        $location = $s['location'] ?? 'Thailand';
        $userCount = (int)($s['user_count'] ?? 0);
        $protocol = strtoupper($s['protocol'] ?: ($s['type'] === 'ssh_script' ? 'SSH' : 'VLESS'));
        $desc = !empty($s['description']) ? $s['description'] : 'รองรับเล่นเกม ดูหนัง โหลดบิท ไม่ลดสปีด';
        $displayTitle = line_bot_format_server_title($flag, $name);
        $cleanName = line_bot_clean_name_only($name);

        $btnLabel = $isTrial ? '⚡ ทดลองใช้ฟรี' : '🛒 เลือกแพ็กเกจ';
        $btnAction = $isTrial 
            ? ['type' => 'postback', 'label' => $btnLabel, 'data' => "action=confirm_trial&server_id={$s['id']}", 'displayText' => "ทดลองใช้ {$cleanName}"]
            : ['type' => 'postback', 'label' => $btnLabel, 'data' => "action=select_server&server_id={$s['id']}", 'displayText' => "เลือก {$cleanName}"];

        $btnColor = $isTrial ? '#0284c7' : '#2563eb';

        $bubbles[] = [
            'type' => 'bubble',
            'size' => 'kilo',
            'header' => [
                'type' => 'box',
                'layout' => 'vertical',
                'backgroundColor' => '#0f172a',
                'paddingAll' => '14px',
                'contents' => [
                    [
                        'type' => 'box',
                        'layout' => 'horizontal',
                        'contents' => [
                            ['type' => 'text', 'text' => $displayTitle, 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'flex' => 1, 'wrap' => true],
                            ['type' => 'text', 'text' => $protocol, 'size' => 'xxs', 'color' => '#38bdf8', 'weight' => 'bold', 'align' => 'end']
                        ]
                    ],
                    [
                        'type' => 'text',
                        'text' => "📍 {$location} • 👥 ใช้งาน {$userCount} คน",
                        'size' => 'xxs',
                        'color' => '#94a3b8',
                        'margin' => 'xs'
                    ]
                ]
            ],
            'body' => [
                'type' => 'box',
                'layout' => 'vertical',
                'paddingAll' => '14px',
                'contents' => [
                    [
                        'type' => 'text',
                        'text' => $desc,
                        'size' => 'xs',
                        'color' => '#475569',
                        'wrap' => true,
                        'maxLines' => 3
                    ],
                    [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'margin' => 'md',
                        'contents' => [
                            [
                                'type' => 'button',
                                'action' => $btnAction,
                                'style' => 'primary',
                                'color' => $btnColor,
                                'height' => 'sm'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    return [
        'type' => 'flex',
        'altText' => $isTrial ? '⚡ รายการเซิร์ฟเวอร์ทดลองใช้ฟรี' : '🛒 รายการเซิร์ฟเวอร์ VPN ทั้งหมด',
        'contents' => [
            'type' => 'carousel',
            'contents' => $bubbles
        ]
    ];
}


/**
 * Build Success Order Messages (Flex + Text Config link for 1-tap copy)
 */
function line_bot_build_order_success_messages(array $res, array $user): array {
    $serverName = line_bot_clean_config_name($res['server_name'] ?? 'VPN Server');
    $displayName = line_bot_clean_config_name($res['display_name'] ?? $serverName);
    $packageName = $res['package_name'] ?? 'แพ็กเกจ VPN';
    $price = number_format((float)($res['price'] ?? 0), 2);
    $expiry = $res['expiry_time'] ?? '-';
    $protocol = strtoupper($res['protocol'] ?? 'VLESS');
    $configLink = $res['config_link'] ?? '';
    $sshUser = $res['ssh_user'] ?? '';
    $sshPass = $res['ssh_pass'] ?? '';

    $isSsh = !empty($sshUser);

    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=10&data=' . urlencode($configLink);

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#059669',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '🎉 สั่งซื้อ VPN สำเร็จ!', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                ['type' => 'text', 'text' => "ระบบเปิดใช้งานไฟล์ของคุณเรียบร้อยแล้ว", 'size' => 'xs', 'color' => '#d1fae5', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#f8fafc',
                    'cornerRadius' => '8px',
                    'paddingAll' => '12px',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ชื่อไฟล์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $displayName, 'weight' => 'bold', 'size' => 'xs', 'color' => '#0f172a', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'แพ็กเกจ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $packageName, 'size' => 'xs', 'color' => '#0f172a', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'โปรโตคอล:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $protocol, 'size' => 'xs', 'color' => '#0284c7', 'weight' => 'bold', 'flex' => 4]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'หมดอายุ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $expiry, 'size' => 'xs', 'color' => '#dc2626', 'weight' => 'bold', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ราคาที่จ่าย:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => "฿{$price}", 'size' => 'xs', 'color' => '#059669', 'weight' => 'bold', 'flex' => 4]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'alignItems' => 'center',
                    'margin' => 'md',
                    'backgroundColor' => '#ffffff',
                    'cornerRadius' => '12px',
                    'borderColor' => '#e2e8f0',
                    'borderWidth' => '1px',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'image',
                            'url' => $qrUrl,
                            'size' => 'lg',
                            'aspectMode' => 'fit',
                            'aspectRatio' => '1:1'
                        ],
                        [
                            'type' => 'text',
                            'text' => '📱 สแกน QR Code เพื่อนำเข้าแอป VPN',
                            'size' => 'xs',
                            'color' => '#0284c7',
                            'weight' => 'bold',
                            'align' => 'center',
                            'margin' => 'sm'
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'sm',
                    'backgroundColor' => '#f1f5f9',
                    'cornerRadius' => '8px',
                    'paddingAll' => '8px',
                    'contents' => [
                        ['type' => 'text', 'text' => '📋 หรือแตะคัดลอกลิงก์ข้อความด้านล่าง 👇', 'size' => 'xs', 'color' => '#334155', 'weight' => 'bold', 'wrap' => true, 'align' => 'center']
                    ]
                ]
            ]
        ]
    ];

    $messages = [
        [
            'type' => 'flex',
            'altText' => '🎉 สร้างไฟล์ VPN สำเร็จ!',
            'contents' => $bubble
        ]
    ];

    // Send Config string in plain text for easy tap-to-copy in LINE!
    if ($isSsh) {
        $sshMsg = "🔑 บัญชี SSH ของคุณ:\n" .
                  "----------------------\n" .
                  "ชื่อไฟล์: {$displayName}\n" .
                  "SSH User: {$sshUser}\n" .
                  "SSH Pass: {$sshPass}\n" .
                  "วันหมดอายุ: {$expiry}\n" .
                  "----------------------\n" .
                  "Config Payload:\n{$configLink}";
        $messages[] = ['type' => 'text', 'text' => $sshMsg];
    } else {
        $messages[] = [
            'type' => 'text',
            'text' => $configLink,
            'quickReply' => [
                'items' => [
                    [
                        'type' => 'action',
                        'action' => ['type' => 'postback', 'label' => '📂 ดูไฟล์', 'data' => 'action=my_vpns', 'displayText' => 'ดูไฟล์']
                    ],
                    [
                        'type' => 'action',
                        'action' => ['type' => 'postback', 'label' => '🛒 สั่งซื้อเพิ่ม', 'data' => 'action=buy_servers', 'displayText' => 'สั่งซื้อ VPN']
                    ],
                    [
                        'type' => 'action',
                        'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก']
                    ]
                ]
            ]
        ];
    }

    return $messages;
}

/**
 * Build Rename Success Messages (Flex + Text Config link for 1-tap copy)
 */
function line_bot_build_rename_success_messages(array $res, array $user): array {
    $displayName = line_bot_clean_config_name($res['display_name'] ?? 'VPN Server');
    $oldName = line_bot_clean_config_name($res['old_name'] ?? '-');
    $expiry = $res['expiry_time'] ?? '-';
    $protocol = strtoupper($res['protocol'] ?? 'VLESS');
    $configLink = $res['config_link'] ?? '';
    $sshUser = $res['ssh_user'] ?? '';
    $sshPass = $res['ssh_pass'] ?? '';

    $isSsh = !empty($sshUser);
    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=10&data=' . urlencode($configLink);

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0284c7',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '✏️ เปลี่ยนชื่อไฟล์สำเร็จ!', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                ['type' => 'text', 'text' => "อัปเดตชื่อกำกับเรียบร้อยแล้ว", 'size' => 'xs', 'color' => '#e0f2fe', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ชื่อใหม่:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $displayName, 'weight' => 'bold', 'size' => 'xs', 'color' => '#0284c7', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ชื่อเดิม:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $oldName, 'size' => 'xs', 'color' => '#94a3b8', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'หมดอายุ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $expiry, 'size' => 'xs', 'color' => '#dc2626', 'weight' => 'bold', 'flex' => 4, 'wrap' => true]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'alignItems' => 'center',
                    'margin' => 'md',
                    'backgroundColor' => '#ffffff',
                    'cornerRadius' => '12px',
                    'borderColor' => '#e2e8f0',
                    'borderWidth' => '1px',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'image',
                            'url' => $qrUrl,
                            'size' => 'lg',
                            'aspectMode' => 'fit',
                            'aspectRatio' => '1:1'
                        ],
                        [
                            'type' => 'text',
                            'text' => '📱 สแกน QR Code เพื่อนำเข้าแอป VPN',
                            'size' => 'xs',
                            'color' => '#0284c7',
                            'weight' => 'bold',
                            'align' => 'center',
                            'margin' => 'sm'
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'sm',
                    'backgroundColor' => '#f1f5f9',
                    'cornerRadius' => '8px',
                    'paddingAll' => '8px',
                    'contents' => [
                        ['type' => 'text', 'text' => '📋 หรือแตะคัดลอกลิงก์ข้อความด้านล่าง 👇', 'size' => 'xs', 'color' => '#334155', 'weight' => 'bold', 'wrap' => true, 'align' => 'center']
                    ]
                ]
            ]
        ]
    ];

    $messages = [
        [
            'type' => 'flex',
            'altText' => '✏️ เปลี่ยนชื่อไฟล์สำเร็จ!',
            'contents' => $bubble
        ]
    ];

    if ($isSsh) {
        $sshMsg = "🔑 บัญชี SSH ของคุณ (ชื่อใหม่):\n" .
                  "----------------------\n" .
                  "ชื่อไฟล์: {$displayName}\n" .
                  "SSH User: {$sshUser}\n" .
                  "SSH Pass: {$sshPass}\n" .
                  "วันหมดอายุ: {$expiry}\n" .
                  "----------------------\n" .
                  "Config Payload:\n{$configLink}";
        $messages[] = ['type' => 'text', 'text' => $sshMsg];
    } else {
        $messages[] = [
            'type' => 'text',
            'text' => $configLink,
            'quickReply' => [
                'items' => [
                    [
                        'type' => 'action',
                        'action' => ['type' => 'postback', 'label' => '📂 ดูไฟล์', 'data' => 'action=my_vpns', 'displayText' => 'ดูไฟล์']
                    ],
                    [
                        'type' => 'action',
                        'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก']
                    ]
                ]
            ]
        ];
    }

    return $messages;
}

/**
 * Build Config Detail Messages with QR Code + 1-Tap Copy Text Link
 */
function line_bot_build_config_detail_messages(array $config, array $user): array {
    $serverName = line_bot_clean_config_name(!empty($config['server_name']) ? $config['server_name'] : 'VPN Server');
    $expiry = !empty($config['expiry_time']) ? $config['expiry_time'] : '-';
    $protocol = strtoupper(!empty($config['protocol']) ? $config['protocol'] : 'VLESS');
    $configLink = $config['config_link'] ?? '';
    $sshUser = $config['ssh_user'] ?? '';
    $sshPass = $config['ssh_pass'] ?? '';
    $isSsh = !empty($sshUser);

    $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=350x350&margin=10&data=' . urlencode($configLink);

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0284c7',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '⚡ ข้อมูลและ QR Code ไฟล์ VPN', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                ['type' => 'text', 'text' => 'สแกน QR Code หรือคัดลอกลิงก์ด้านล่างเพื่อเชื่อมต่อ', 'size' => 'xs', 'color' => '#e0f2fe', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#f8fafc',
                    'cornerRadius' => '10px',
                    'borderColor' => '#e2e8f0',
                    'borderWidth' => '1px',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ชื่อไฟล์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $serverName, 'weight' => 'bold', 'size' => 'xs', 'color' => '#0f172a', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'margin' => 'xs',
                            'contents' => [
                                ['type' => 'text', 'text' => 'หมดอายุ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $expiry, 'size' => 'xs', 'color' => '#dc2626', 'weight' => 'bold', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'margin' => 'xs',
                            'contents' => [
                                ['type' => 'text', 'text' => 'โปรโตคอล:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $protocol, 'size' => 'xs', 'color' => '#0284c7', 'weight' => 'bold', 'flex' => 4]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'alignItems' => 'center',
                    'margin' => 'md',
                    'backgroundColor' => '#ffffff',
                    'cornerRadius' => '12px',
                    'borderColor' => '#e2e8f0',
                    'borderWidth' => '1px',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'image',
                            'url' => $qrUrl,
                            'size' => 'lg',
                            'aspectMode' => 'fit',
                            'aspectRatio' => '1:1'
                        ],
                        [
                            'type' => 'text',
                            'text' => '📱 สแกน QR Code เพื่อนำเข้าแอป VPN',
                            'size' => 'xs',
                            'color' => '#0284c7',
                            'weight' => 'bold',
                            'align' => 'center',
                            'margin' => 'sm'
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'sm',
                    'backgroundColor' => '#f1f5f9',
                    'cornerRadius' => '8px',
                    'paddingAll' => '8px',
                    'contents' => [
                        ['type' => 'text', 'text' => '📋 หรือแตะคัดลอกลิงก์ข้อความด้านล่าง 👇', 'size' => 'xs', 'color' => '#334155', 'weight' => 'bold', 'wrap' => true, 'align' => 'center']
                    ]
                ]
            ]
        ]
    ];

    $messages = [
        [
            'type' => 'flex',
            'altText' => "⚡ ข้อมูล & QR Code ไฟล์ VPN ({$serverName})",
            'contents' => $bubble
        ]
    ];

    if ($isSsh) {
        $sshMsg = "🔑 ข้อมูลบัญชี SSH: {$serverName}\n" .
                  "----------------------\n" .
                  "SSH User: {$sshUser}\n" .
                  "SSH Pass: {$sshPass}\n" .
                  "วันหมดอายุ: {$expiry}\n" .
                  "----------------------\n" .
                  "Config Link / Payload:\n{$configLink}";
        $messages[] = [
            'type' => 'text',
            'text' => $sshMsg,
            'quickReply' => [
                'items' => [
                    ['type' => 'action', 'action' => ['type' => 'postback', 'label' => '📂 ดูไฟล์', 'data' => 'action=my_vpns', 'displayText' => 'ดูไฟล์']],
                    ['type' => 'action', 'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก']]
                ]
            ]
        ];
    } else {
        $messages[] = [
            'type' => 'text',
            'text' => $configLink,
            'quickReply' => [
                'items' => [
                    ['type' => 'action', 'action' => ['type' => 'postback', 'label' => '📂 ดูไฟล์', 'data' => 'action=my_vpns', 'displayText' => 'ดูไฟล์']],
                    ['type' => 'action', 'action' => ['type' => 'postback', 'label' => '🛒 สั่งซื้อเพิ่ม', 'data' => 'action=buy_servers', 'displayText' => 'สั่งซื้อ VPN']],
                    ['type' => 'action', 'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก']]
                ]
            ]
        ];
    }

    return $messages;
}

/**
 * Topup Menu Flex Message
 */
function line_bot_build_topup_menu(array $user): array {
    $settings = get_slip_settings();
    $minAmount = max(30.00, (float)($settings['slip_min_amount'] ?? 30.00));
    $promptpayNumber = $settings['slip_receiver_account'] ?: ($settings['promptpay_number'] ?: '');
    $receiverName = $settings['slip_receiver_th'] ?: ($settings['promptpay_name'] ?: 'EkromVPN Shop');

    $qrUrl = '';
    if (!empty($promptpayNumber)) {
        $payload = generate_promptpay_payload($promptpayNumber, null);
        $qrUrl = get_promptpay_qr_url($payload, $promptpayNumber, null);
    }

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0f172a',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '💰 เติมเงินอัตโนมัติ (Slip Auto Topup)', 'weight' => 'bold', 'size' => 'md', 'color' => '#38bdf8', 'wrap' => true],
                ['type' => 'text', 'text' => 'ตรวจสลิปและปรับยอดเงินเข้าบัญชีทันที 24 ชม.', 'size' => 'xs', 'color' => '#94a3b8', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => array_filter([
                !empty($qrUrl) ? [
                    'type' => 'image',
                    'url' => $qrUrl,
                    'size' => '180px',
                    'aspectRatio' => '1:1',
                    'aspectMode' => 'cover',
                    'align' => 'center'
                ] : null,
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'md',
                    'backgroundColor' => '#f8fafc',
                    'cornerRadius' => '8px',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'text',
                            'text' => "พร้อมเพย์: " . ($promptpayNumber ?: 'ติดต่อแอดมิน'),
                            'weight' => 'bold',
                            'size' => 'sm',
                            'color' => '#0f172a',
                            'wrap' => true,
                            'align' => 'center'
                        ],
                        [
                            'type' => 'text',
                            'text' => "ชื่อบัญชี: {$receiverName}",
                            'size' => 'xs',
                            'color' => '#475569',
                            'wrap' => true,
                            'align' => 'center',
                            'margin' => 'xs'
                        ],
                        [
                            'type' => 'text',
                            'text' => "ขั้นต่ำ: ฿" . number_format($minAmount, 2) . " บาท",
                            'size' => 'xs',
                            'color' => '#059669',
                            'weight' => 'bold',
                            'wrap' => true,
                            'align' => 'center',
                            'margin' => 'xs'
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'md',
                    'contents' => [
                        ['type' => 'text', 'text' => '📸 วิธีเติมเงินง่ายๆ:', 'weight' => 'bold', 'size' => 'xs', 'color' => '#334155', 'wrap' => true],
                        ['type' => 'text', 'text' => '1. โอนเงินผ่าน PromptPay QR หรือเบอร์ด้านบน', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'xs'],
                        ['type' => 'text', 'text' => '2. ส่งรูปสลิปโอนเงินเข้ามาในแชทนี้ได้เลย!', 'weight' => 'bold', 'size' => 'xs', 'color' => '#059669', 'wrap' => true, 'margin' => 'xs'],
                        ['type' => 'text', 'text' => '3. บอทจะตรวจสลิปและเติมเงินเข้าบัญชีทันที 🎉', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'xs']
                    ]
                ]
            ])
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => '💰 เติมเงินอัตโนมัติ EkromVPN',
        'contents' => $bubble
    ];
}

/**
 * My VPNs Flex Message
 */
function line_bot_build_my_vpns(array $user): array {
    $db = get_db();
    $stmt = $db->prepare('
        SELECT * FROM vpn_configs 
        WHERE user_id = ? AND status_real = "active" 
        ORDER BY id DESC 
        LIMIT 10
    ');
    $stmt->execute([$user['id']]);
    $configs = $stmt->fetchAll();

    if (empty($configs)) {
        return [
            'type' => 'flex',
            'altText' => '📂 ดูไฟล์ VPN',
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#0284c7',
                    'paddingAll' => '16px',
                    'contents' => [
                        ['type' => 'text', 'text' => '📂 ดูไฟล์ VPN', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                        ['type' => 'text', 'text' => 'รายการไฟล์ VPN ทั้งหมดในบัญชีของคุณ', 'size' => 'xs', 'color' => '#e0f2fe', 'wrap' => true, 'margin' => 'xs']
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '16px',
                    'contents' => [
                        ['type' => 'text', 'text' => 'คุณยังไม่มีไฟล์ VPN ในขณะนี้', 'weight' => 'bold', 'size' => 'sm', 'color' => '#0f172a', 'wrap' => true],
                        ['type' => 'text', 'text' => 'คุณสามารถเลือกสั่งซื้อไฟล์ VPN หรือกดรับไฟล์ทดลองใช้งานฟรีได้ทันทีครับ', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'sm'],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'spacing' => 'sm',
                            'margin' => 'lg',
                            'contents' => [
                                [
                                    'type' => 'button',
                                    'action' => ['type' => 'postback', 'label' => '🛒 สั่งซื้อ VPN', 'data' => 'action=buy_servers', 'displayText' => 'สั่งซื้อ VPN'],
                                    'style' => 'primary',
                                    'color' => '#2563eb',
                                    'height' => 'sm',
                                    'flex' => 1
                                ],
                                [
                                    'type' => 'button',
                                    'action' => ['type' => 'postback', 'label' => '⚡ ทดลองฟรี', 'data' => 'action=trial_servers', 'displayText' => 'ทดลองใช้ฟรี'],
                                    'style' => 'secondary',
                                    'color' => '#0284c7',
                                    'height' => 'sm',
                                    'flex' => 1
                                ]
                            ]
                        ],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
                            'style' => 'link',
                            'height' => 'sm',
                            'margin' => 'xs'
                        ]
                    ]
                ]
            ]
        ];
    }

    $items = [];
    foreach ($configs as $c) {
        $name = line_bot_clean_config_name($c['server_name'] ?: 'VPN Config');
        $exp = $c['expiry_time'] ?? '';
        $proto = strtoupper($c['protocol'] ?: 'VLESS');
        $isExpired = (strtotime($exp) <= time());

        $statusText = $isExpired ? 'หมดอายุแล้ว' : 'ใช้งานได้';
        $statusColor = $isExpired ? '#dc2626' : '#059669';

        $items[] = [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#f8fafc',
            'cornerRadius' => '10px',
            'borderColor' => '#e2e8f0',
            'borderWidth' => '1px',
            'paddingAll' => '12px',
            'margin' => 'sm',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'contents' => [
                        ['type' => 'text', 'text' => "⚡ {$name}", 'weight' => 'bold', 'size' => 'sm', 'color' => '#0f172a', 'flex' => 3, 'wrap' => true],
                        ['type' => 'text', 'text' => $statusText, 'size' => 'xs', 'color' => $statusColor, 'weight' => 'bold', 'align' => 'end', 'flex' => 2]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'xs',
                    'contents' => [
                        ['type' => 'text', 'text' => "📅 หมดอายุ: {$exp}", 'size' => 'xs', 'color' => '#334155', 'flex' => 3, 'wrap' => true],
                        ['type' => 'text', 'text' => $proto, 'size' => 'xs', 'color' => '#0284c7', 'weight' => 'bold', 'align' => 'end', 'flex' => 1]
                    ]
                ],
                [
                    'type' => 'button',
                    'action' => ['type' => 'postback', 'label' => '📋 ขอลิงก์ Config', 'data' => "action=get_config&config_id={$c['id']}", 'displayText' => "ขอลิงก์ {$name}"],
                    'style' => 'primary',
                    'color' => '#0284c7',
                    'height' => 'sm',
                    'margin' => 'sm'
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'spacing' => 'sm',
                    'margin' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'flex' => 1,
                            'action' => ['type' => 'postback', 'label' => 'ต่ออายุ', 'data' => "action=renew_select_days&config_id={$c['id']}", 'displayText' => "ต่ออายุ {$name}"],
                            'contents' => [
                                [
                                    'type' => 'button',
                                    'action' => ['type' => 'postback', 'label' => '♻️', 'data' => "action=renew_select_days&config_id={$c['id']}", 'displayText' => "ต่ออายุ {$name}"],
                                    'style' => 'primary',
                                    'color' => '#7c3aed',
                                    'height' => 'sm'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'ต่ออายุ',
                                    'size' => 'xs',
                                    'color' => '#6b21a8',
                                    'align' => 'center',
                                    'weight' => 'bold',
                                    'margin' => 'xs'
                                ]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'flex' => 1,
                            'action' => ['type' => 'postback', 'label' => 'แก้ไขชื่อ', 'data' => "action=ask_rename_config&config_id={$c['id']}", 'displayText' => "แก้ไขชื่อ {$name}"],
                            'contents' => [
                                [
                                    'type' => 'button',
                                    'action' => ['type' => 'postback', 'label' => '✏️', 'data' => "action=ask_rename_config&config_id={$c['id']}", 'displayText' => "แก้ไขชื่อ {$name}"],
                                    'style' => 'primary',
                                    'color' => '#334155',
                                    'height' => 'sm'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'แก้ไขชื่อ',
                                    'size' => 'xs',
                                    'color' => '#334155',
                                    'align' => 'center',
                                    'weight' => 'bold',
                                    'margin' => 'xs'
                                ]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'flex' => 1,
                            'action' => ['type' => 'postback', 'label' => 'ลบไฟล์', 'data' => "action=ask_delete_config&config_id={$c['id']}", 'displayText' => "ลบไฟล์ {$name}"],
                            'contents' => [
                                [
                                    'type' => 'button',
                                    'action' => ['type' => 'postback', 'label' => '🗑️', 'data' => "action=ask_delete_config&config_id={$c['id']}", 'displayText' => "ลบไฟล์ {$name}"],
                                    'style' => 'primary',
                                    'color' => '#dc2626',
                                    'height' => 'sm'
                                ],
                                [
                                    'type' => 'text',
                                    'text' => 'ลบไฟล์',
                                    'size' => 'xs',
                                    'color' => '#dc2626',
                                    'align' => 'center',
                                    'weight' => 'bold',
                                    'margin' => 'xs'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    $bodyContents = array_merge($items, [
        [
            'type' => 'box',
            'layout' => 'horizontal',
            'spacing' => 'sm',
            'margin' => 'md',
            'contents' => [
                [
                    'type' => 'button',
                    'action' => ['type' => 'postback', 'label' => '🛒 สั่งซื้อเพิ่ม', 'data' => 'action=buy_servers', 'displayText' => 'สั่งซื้อ VPN'],
                    'style' => 'secondary',
                    'color' => '#2563eb',
                    'height' => 'sm',
                    'flex' => 1
                ],
                [
                    'type' => 'button',
                    'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
                    'style' => 'secondary',
                    'height' => 'sm',
                    'flex' => 1
                ]
            ]
        ]
    ]);

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0284c7',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '📂 ดูไฟล์ VPN', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                ['type' => 'text', 'text' => 'แตะปุ่มเพื่อรับลิงก์ Config หรือต่ออายุการใช้งาน', 'size' => 'xs', 'color' => '#e0f2fe', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '14px',
            'contents' => $bodyContents
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => '📂 ดูไฟล์ VPN',
        'contents' => $bubble
    ];
}

/**
 * Select Config to Renew Flex Message
 */
function line_bot_build_renew_select_config(array $configs, array $user): array {
    if (empty($configs)) {
        return [
            'type' => 'flex',
            'altText' => '⚠️ ไม่พบไฟล์ VPN สำหรับต่ออายุ',
            'contents' => [
                'type' => 'bubble',
                'size' => 'mega',
                'header' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#7c3aed',
                    'paddingAll' => '16px',
                    'contents' => [
                        ['type' => 'text', 'text' => '♻️ ต่ออายุไฟล์ VPN', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                        ['type' => 'text', 'text' => 'เพิ่มระยะเวลาการใช้งานต่อเนื่อง', 'size' => 'xs', 'color' => '#f3e8ff', 'wrap' => true, 'margin' => 'xs']
                    ]
                ],
                'body' => [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'paddingAll' => '20px',
                    'contents' => [
                        ['type' => 'text', 'text' => 'ไม่พบไฟล์ VPN สำหรับต่ออายุ', 'weight' => 'bold', 'size' => 'sm', 'color' => '#0f172a', 'wrap' => true],
                        ['type' => 'text', 'text' => 'คุณยังไม่มีไฟล์ VPN ในระบบที่สามารถต่ออายุได้ กรุณาสั่งซื้อไฟล์ใหม่ครับ', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'sm'],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '🛒 สั่งซื้อ VPN', 'data' => 'action=buy_servers', 'displayText' => 'สั่งซื้อ VPN'],
                            'style' => 'primary',
                            'color' => '#2563eb',
                            'height' => 'sm',
                            'margin' => 'lg'
                        ]
                    ]
                ]
            ]
        ];
    }

    $items = [];
    foreach ($configs as $c) {
        $name = line_bot_clean_config_name($c['server_name'] ?: 'VPN Config');
        $exp = $c['expiry_time'] ?? '';
        $proto = strtoupper($c['protocol'] ?: 'VLESS');
        $isExpired = (strtotime($exp) <= time());

        $statusText = $isExpired ? 'หมดอายุแล้ว' : 'ใช้งานได้';
        $statusColor = $isExpired ? '#dc2626' : '#059669';

        $items[] = [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#f8fafc',
            'cornerRadius' => '10px',
            'borderColor' => '#e2e8f0',
            'borderWidth' => '1px',
            'paddingAll' => '12px',
            'margin' => 'sm',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'contents' => [
                        ['type' => 'text', 'text' => "⚡ {$name}", 'weight' => 'bold', 'size' => 'sm', 'color' => '#0f172a', 'flex' => 3, 'wrap' => true],
                        ['type' => 'text', 'text' => $statusText, 'size' => 'xs', 'color' => $statusColor, 'weight' => 'bold', 'align' => 'end', 'flex' => 2]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'xs',
                    'contents' => [
                        ['type' => 'text', 'text' => "📅 หมดอายุ: {$exp}", 'size' => 'xs', 'color' => '#334155', 'flex' => 3, 'wrap' => true],
                        ['type' => 'text', 'text' => $proto, 'size' => 'xs', 'color' => '#0284c7', 'weight' => 'bold', 'align' => 'end', 'flex' => 1]
                    ]
                ],
                [
                    'type' => 'button',
                    'action' => ['type' => 'postback', 'label' => '♻️ ต่ออายุไฟล์นี้', 'data' => "action=renew_select_days&config_id={$c['id']}", 'displayText' => "ต่ออายุ {$name}"],
                    'style' => 'primary',
                    'color' => '#7c3aed',
                    'height' => 'sm',
                    'margin' => 'sm'
                ]
            ]
        ];
    }

    $items[] = [
        'type' => 'button',
        'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
        'style' => 'link',
        'height' => 'sm',
        'margin' => 'md'
    ];

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#7c3aed',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '♻️ เลือกไฟล์ VPN ที่ต้องการต่ออายุ', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                ['type' => 'text', 'text' => 'แตะปุ่มเลือกไฟล์ที่ต้องการเพิ่มวันใช้งาน', 'size' => 'xs', 'color' => '#f3e8ff', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '14px',
            'contents' => $items
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => '♻️ เลือกไฟล์ VPN ที่ต้องการต่ออายุ',
        'contents' => $bubble
    ];
}

/**
 * Delete VPN Confirmation Flex Message
 */
function line_bot_build_delete_confirmation(array $preview, array $user): array {
    $config = $preview['config'] ?? [];
    $name = line_bot_clean_config_name(!empty($config['server_name']) ? $config['server_name'] : 'VPN Config');
    $exp = !empty($config['expiry_time']) ? $config['expiry_time'] : '-';
    $proto = strtoupper(!empty($config['protocol']) ? $config['protocol'] : 'VLESS');
    $configId = (int)($config['id'] ?? 0);
    $pricePaid = (float)($preview['price_paid'] ?? 0);
    $refundAmount = (float)($preview['refund_amount'] ?? 0);
    $isGrace = !empty($preview['is_grace']);
    $usedHours = (int)($preview['used_hours'] ?? 0);
    $remainingHours = (int)($preview['remaining_hours'] ?? 0);

    // Refund details box
    $refundBoxContents = [];
    if ($isGrace) {
        $refundBoxContents = [
            ['type' => 'text', 'text' => '🎉 คืนเงินเต็มจำนวน 100% (ภายใน 10 นาที)', 'weight' => 'bold', 'size' => 'xs', 'color' => '#059669', 'wrap' => true],
            ['type' => 'text', 'text' => '฿' . number_format($refundAmount, 2) . ' บาท', 'weight' => 'bold', 'size' => 'lg', 'color' => '#059669', 'wrap' => true, 'margin' => 'xs'],
            ['type' => 'text', 'text' => 'ระบบจะคืนเงินเข้ากระเป๋าของคุณเต็มจำนวนทันที', 'size' => 'xxs', 'color' => '#047857', 'wrap' => true, 'margin' => 'xs']
        ];
        $refundBoxBg = '#ecfdf5';
    } elseif ($refundAmount > 0) {
        $refundBoxContents = [
            ['type' => 'text', 'text' => "💸 คืนเงินตามการใช้งาน (เหลือ {$remainingHours} ชม.)", 'weight' => 'bold', 'size' => 'xs', 'color' => '#0284c7', 'wrap' => true],
            ['type' => 'text', 'text' => '฿' . number_format($refundAmount, 2) . ' บาท', 'weight' => 'bold', 'size' => 'lg', 'color' => '#0284c7', 'wrap' => true, 'margin' => 'xs'],
            ['type' => 'text', 'text' => "ใช้งานไป {$usedHours} ชม. คืนเงินชั่วโมงคงเหลือเข้ากระเป๋าของคุณทันที", 'size' => 'xxs', 'color' => '#0369a1', 'wrap' => true, 'margin' => 'xs']
        ];
        $refundBoxBg = '#f0f9ff';
    } else {
        $refundBoxContents = [
            ['type' => 'text', 'text' => '⚠️ ไม่มียอดเงินคืนสำหรับไฟล์นี้', 'weight' => 'bold', 'size' => 'xs', 'color' => '#dc2626', 'wrap' => true],
            ['type' => 'text', 'text' => 'เนื่องจากไฟล์หมดอายุการใช้งานแล้ว หรือเป็นไฟล์ทดลองใช้งานฟรี', 'size' => 'xxs', 'color' => '#b91c1c', 'wrap' => true, 'margin' => 'xs']
        ];
        $refundBoxBg = '#fef2f2';
    }

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#dc2626',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '🗑️ ยืนยันการลบไฟล์ VPN', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                ['type' => 'text', 'text' => 'โปรดตรวจสอบรายละเอียดก่อนยืนยันการลบ', 'size' => 'xs', 'color' => '#fee2e2', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#f8fafc',
                    'cornerRadius' => '10px',
                    'borderColor' => '#e2e8f0',
                    'borderWidth' => '1px',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ชื่อไฟล์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $name, 'weight' => 'bold', 'size' => 'xs', 'color' => '#0f172a', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'margin' => 'xs',
                            'contents' => [
                                ['type' => 'text', 'text' => 'หมดอายุ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $exp, 'size' => 'xs', 'color' => '#334155', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'margin' => 'xs',
                            'contents' => [
                                ['type' => 'text', 'text' => 'โปรโตคอล:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $proto, 'size' => 'xs', 'color' => '#0284c7', 'weight' => 'bold', 'flex' => 4]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => $refundBoxBg,
                    'cornerRadius' => '10px',
                    'paddingAll' => '12px',
                    'margin' => 'md',
                    'contents' => $refundBoxContents
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'md',
                    'contents' => [
                        ['type' => 'text', 'text' => '⚠️ เมื่อลบแล้ว ไฟล์และบัญชีเชื่อมต่อจะถูกลบออกจากเซิร์ฟเวอร์ทันทีและไม่สามารถกู้คืนได้', 'size' => 'xs', 'color' => '#dc2626', 'wrap' => true]
                    ]
                ],
                [
                    'type' => 'button',
                    'action' => [
                        'type' => 'postback',
                        'label' => '🗑️ ยืนยันลบไฟล์',
                        'data' => "action=confirm_delete_config&config_id={$configId}",
                        'displayText' => "ยืนยันลบไฟล์ {$name}"
                    ],
                    'style' => 'primary',
                    'color' => '#dc2626',
                    'height' => 'sm',
                    'margin' => 'lg'
                ],
                [
                    'type' => 'button',
                    'action' => [
                        'type' => 'postback',
                        'label' => '◀️ ยกเลิก / ย้อนกลับ',
                        'data' => 'action=my_vpns',
                        'displayText' => 'ย้อนกลับ'
                    ],
                    'style' => 'secondary',
                    'color' => '#475569',
                    'height' => 'sm',
                    'margin' => 'sm'
                ]
            ]
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => "🗑️ ยืนยันการลบไฟล์ {$name}",
        'contents' => $bubble
    ];
}

/**
 * Delete VPN Success Flex Message
 */
function line_bot_build_delete_success(array $res, array $user): array {
    $serverName = line_bot_clean_config_name($res['server_name'] ?? 'VPN Config');
    $refundAmount = (float)($res['refund_amount'] ?? 0);
    $newBalance = (float)($res['new_balance'] ?? (float)$user['balance']);

    $bodyContents = [
        [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#f8fafc',
            'cornerRadius' => '10px',
            'borderColor' => '#e2e8f0',
            'borderWidth' => '1px',
            'paddingAll' => '12px',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'contents' => [
                        ['type' => 'text', 'text' => 'ไฟล์ที่ลบ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                        ['type' => 'text', 'text' => $serverName, 'weight' => 'bold', 'size' => 'xs', 'color' => '#0f172a', 'flex' => 4, 'wrap' => true]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'xs',
                    'contents' => [
                        ['type' => 'text', 'text' => 'สถานะ:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                        ['type' => 'text', 'text' => 'ลบเรียบร้อยแล้ว', 'weight' => 'bold', 'size' => 'xs', 'color' => '#dc2626', 'flex' => 4]
                    ]
                ]
            ]
        ]
    ];

    if ($refundAmount > 0) {
        $bodyContents[] = [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#ecfdf5',
            'cornerRadius' => '10px',
            'paddingAll' => '12px',
            'margin' => 'md',
            'contents' => [
                ['type' => 'text', 'text' => '💸 คืนเงินเข้ากระเป๋าของคุณแล้ว', 'weight' => 'bold', 'size' => 'xs', 'color' => '#059669', 'wrap' => true],
                ['type' => 'text', 'text' => '+฿' . number_format($refundAmount, 2) . ' บาท', 'weight' => 'bold', 'size' => 'lg', 'color' => '#059669', 'wrap' => true, 'margin' => 'xs'],
                ['type' => 'text', 'text' => 'ยอดเงินคงเหลือปัจจุบัน: ฿' . number_format($newBalance, 2) . ' บาท', 'size' => 'xs', 'color' => '#047857', 'wrap' => true, 'margin' => 'xs']
            ]
        ];
    }

    $bodyContents[] = [
        'type' => 'box',
        'layout' => 'horizontal',
        'spacing' => 'sm',
        'margin' => 'lg',
        'contents' => [
            [
                'type' => 'button',
                'action' => ['type' => 'postback', 'label' => '📂 ดูไฟล์', 'data' => 'action=my_vpns', 'displayText' => 'ดูไฟล์'],
                'style' => 'primary',
                'color' => '#0284c7',
                'height' => 'sm',
                'flex' => 1
            ],
            [
                'type' => 'button',
                'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
                'style' => 'secondary',
                'height' => 'sm',
                'flex' => 1
            ]
        ]
    ];

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#059669',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '🗑️ ลบไฟล์ VPN สำเร็จ!', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                ['type' => 'text', 'text' => 'ลบไฟล์และบัญชีเชื่อมต่อออกจากระบบเรียบร้อยแล้ว', 'size' => 'xs', 'color' => '#d1fae5', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => $bodyContents
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => "🗑️ ลบไฟล์ {$serverName} สำเร็จ",
        'contents' => $bubble
    ];
}

/**
 * Renew Package Duration Selection Flex Message
 */
function line_bot_build_renew_days_selection(array $config, array $user): array {
    $db = get_db();
    $server = null;
    if (!empty($config['server_id'])) {
        $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
        $sStmt->execute([$config['server_id']]);
        $server = $sStmt->fetch();
    }

    $tierPrices = [5, 25, 45, 80]; // fallback [1d, 7d, 15d, 30d]
    if ($server && !empty($server['tier_id'])) {
        $tStmt = $db->prepare('SELECT prices FROM price_tiers WHERE id = ?');
        $tStmt->execute([$server['tier_id']]);
        $tierJson = $tStmt->fetchColumn();
        if ($tierJson) {
            $arr = json_decode($tierJson, true);
            if (is_array($arr) && count($arr) >= 4) $tierPrices = $arr;
        }
    }

    $isReseller = (isset($user['role']) && $user['role'] === 'reseller');
    $userBal = (float)$user['balance'];

    $packages = [
        ['val' => '1', 'days' => '1 วัน', 'price' => (float)$tierPrices[0]],
        ['val' => '7', 'days' => '7 วัน', 'price' => (float)$tierPrices[1]],
        ['val' => '15', 'days' => '15 วัน', 'price' => (float)$tierPrices[2]],
        ['val' => '30', 'days' => '30 วัน', 'price' => (float)$tierPrices[3]]
    ];

    $cleanConfigName = line_bot_clean_config_name($config['server_name'] ?? 'VPN Config');
    $pkgRows = [];
    foreach ($packages as $pkg) {
        $p = $pkg['price'];
        if ($isReseller && $p > 0) $p = round($p * 0.70, 2);

        $hasEnough = ($userBal >= $p);
        $btnColor = $hasEnough ? '#8b5cf6' : '#94a3b8';

        $pkgRows[] = [
            'type' => 'box',
            'layout' => 'horizontal',
            'alignItems' => 'center',
            'backgroundColor' => '#f8fafc',
            'cornerRadius' => '8px',
            'paddingAll' => '10px',
            'margin' => 'sm',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'flex' => 1,
                    'contents' => [
                        ['type' => 'text', 'text' => "➕ ต่ออายุ {$pkg['days']}", 'weight' => 'bold', 'size' => 'sm', 'color' => '#0f172a', 'wrap' => true],
                        ['type' => 'text', 'text' => '฿' . number_format($p, 2) . ($isReseller ? ' (ลด 30%)' : ''), 'weight' => 'bold', 'size' => 'xs', 'color' => '#059669', 'wrap' => true]
                    ]
                ],
                [
                    'type' => 'button',
                    'action' => [
                        'type' => 'postback',
                        'label' => $hasEnough ? 'ต่ออายุ' : 'เงินไม่พอ',
                        'data' => "action=confirm_renew&config_id={$config['id']}&days={$pkg['val']}",
                        'displayText' => "ต่ออายุ {$pkg['days']} ({$cleanConfigName})"
                    ],
                    'style' => 'primary',
                    'color' => $btnColor,
                    'height' => 'sm',
                    'flex' => 0
                ]
            ]
        ];
    }

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#7c3aed',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '♻️ ต่ออายุไฟล์ VPN', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                ['type' => 'text', 'text' => 'เพิ่มระยะเวลาใช้งานต่อเนื่องได้ทันที', 'size' => 'xs', 'color' => '#f3e8ff', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '14px',
            'contents' => array_merge([
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'backgroundColor' => '#f8fafc',
                    'cornerRadius' => '8px',
                    'paddingAll' => '10px',
                    'contents' => [
                        ['type' => 'text', 'text' => "⚡ {$cleanConfigName}", 'weight' => 'bold', 'size' => 'xs', 'color' => '#0f172a', 'wrap' => true],
                        ['type' => 'text', 'text' => "หมดอายุเดิม: {$config['expiry_time']}", 'size' => 'xxs', 'color' => '#dc2626', 'wrap' => true, 'margin' => 'xs'],
                        ['type' => 'text', 'text' => "ยอดเงินคงเหลือของคุณ: ฿" . number_format($userBal, 2), 'size' => 'xxs', 'color' => '#059669', 'weight' => 'bold', 'wrap' => true, 'margin' => 'xs']
                    ]
                ],
                ['type' => 'text', 'text' => 'เลือกจำนวนวันที่ต้องการต่ออายุ:', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'md']
            ], $pkgRows, [
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'md',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '💰 เติมเงินเพิ่ม', 'data' => 'action=topup_menu', 'displayText' => 'เติมเงิน'],
                            'style' => 'secondary',
                            'color' => '#059669',
                            'height' => 'sm',
                            'flex' => 1
                        ],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '📂 ดูไฟล์', 'data' => 'action=my_vpns', 'displayText' => 'ดูไฟล์'],
                            'style' => 'secondary',
                            'color' => '#0284c7',
                            'height' => 'sm',
                            'flex' => 1
                        ]
                    ]
                ],
                [
                    'type' => 'button',
                    'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
                    'style' => 'link',
                    'height' => 'sm',
                    'margin' => 'xs'
                ]
            ])
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => "♻️ ต่ออายุ VPN {$cleanConfigName}",
        'contents' => $bubble
    ];
}

/**
 * Build Success Renew Messages (Flex + Text Config link for 1-tap copy)
 */
function line_bot_build_renew_success_messages(array $res, array $user): array {
    $serverName = line_bot_clean_config_name($res['server_name'] ?? 'VPN Server');
    $displayName = line_bot_clean_config_name($res['display_name'] ?? $serverName);
    $days = (int)($res['days'] ?? 0);
    $price = number_format((float)($res['price'] ?? 0), 2);
    $expiry = $res['new_expiry'] ?? '-';
    $protocol = strtoupper($res['protocol'] ?? 'VLESS');
    $configLink = $res['config_link'] ?? '';
    $sshUser = $res['ssh_user'] ?? '';
    $sshPass = $res['ssh_pass'] ?? '';
    $newBalance = number_format((float)($res['new_balance'] ?? 0), 2);

    $isSsh = !empty($sshUser);

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#8b5cf6',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '🎉 ต่ออายุไฟล์ VPN สำเร็จ!', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                ['type' => 'text', 'text' => 'เพิ่มเวลาใช้งานและอัปเดตระบบเรียบร้อยแล้ว', 'size' => 'xs', 'color' => '#f3e8ff', 'wrap' => true, 'margin' => 'xs']
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'เซิร์ฟเวอร์:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $serverName, 'weight' => 'bold', 'size' => 'xs', 'color' => '#0f172a', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'เพิ่มเวลา:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => "+{$days} วัน", 'weight' => 'bold', 'size' => 'xs', 'color' => '#8b5cf6', 'flex' => 4]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'หมดอายุใหม่:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => $expiry, 'size' => 'xs', 'color' => '#059669', 'weight' => 'bold', 'flex' => 4, 'wrap' => true]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ยอดเงินที่หัก:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => "฿{$price} บาท", 'size' => 'xs', 'color' => '#0f172a', 'weight' => 'bold', 'flex' => 4]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'horizontal',
                            'contents' => [
                                ['type' => 'text', 'text' => 'ยอดคงเหลือใหม่:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 2],
                                ['type' => 'text', 'text' => "฿{$newBalance} บาท", 'size' => 'xs', 'color' => '#0284c7', 'weight' => 'bold', 'flex' => 4]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'md',
                    'backgroundColor' => '#f1f5f9',
                    'cornerRadius' => '8px',
                    'paddingAll' => '10px',
                    'contents' => [
                        ['type' => 'text', 'text' => '📋 ลิงก์ Config อัปเดตใหม่ด้านล่าง 👇', 'size' => 'xs', 'color' => '#334155', 'weight' => 'bold', 'wrap' => true, 'align' => 'center']
                    ]
                ]
            ]
        ]
    ];

    $messages = [
        [
            'type' => 'flex',
            'altText' => "🎉 ต่ออายุ VPN สำเร็จ (+{$days} วัน)",
            'contents' => $bubble
        ]
    ];

    if ($isSsh) {
        $sshMsg = "🔑 ข้อมูลบัญชี SSH (ต่ออายุแล้ว):\n" .
                  "----------------------\n" .
                  "ชื่อไฟล์: {$displayName}\n" .
                  "SSH User: {$sshUser}\n" .
                  "SSH Pass: {$sshPass}\n" .
                  "วันหมดอายุใหม่: {$expiry}\n" .
                  "----------------------\n" .
                  "Config Payload:\n{$configLink}";
        $messages[] = ['type' => 'text', 'text' => $sshMsg];
    } else {
        $messages[] = [
            'type' => 'text',
            'text' => $configLink,
            'quickReply' => [
                'items' => [
                    [
                        'type' => 'action',
                        'action' => ['type' => 'postback', 'label' => '📂 ดูไฟล์', 'data' => 'action=my_vpns', 'displayText' => 'ดูไฟล์']
                    ],
                    [
                        'type' => 'action',
                        'action' => ['type' => 'postback', 'label' => '🛒 สั่งซื้อเพิ่ม', 'data' => 'action=buy_servers', 'displayText' => 'สั่งซื้อ VPN']
                    ],
                    [
                        'type' => 'action',
                        'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก']
                    ]
                ]
            ]
        ];
    }

    return $messages;
}

/**
 * My Profile Flex Message
 */
function line_bot_build_my_profile(array $user): array {
    $db = get_db();
    $stmt = $db->prepare('SELECT COUNT(*) FROM vpn_configs WHERE user_id = ? AND status_real = "active"');
    $stmt->execute([$user['id']]);
    $activeCount = (int)$stmt->fetchColumn();

    $displayName = $user['line_display_name'] ?: $user['username'];
    $avatar = $user['line_picture_url'] ?: 'https://cdn-icons-png.flaticon.com/512/149/149071.png';
    $roleName = ($user['role'] === 'admin') ? 'ผู้ดูแลระบบ (Admin)' : (($user['role'] === 'reseller') ? 'ตัวแทนจำหน่าย (Reseller -30%)' : 'สมาชิกทั่วไป (Member)');

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0f172a',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '👤 ข้อมูลบัญชีของฉัน', 'weight' => 'bold', 'size' => 'md', 'color' => '#38bdf8', 'wrap' => true]
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => [
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'alignItems' => 'center',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'cornerRadius' => '100px',
                            'width' => '50px',
                            'height' => '50px',
                            'flex' => 0,
                            'contents' => [
                                [
                                    'type' => 'image',
                                    'url' => $avatar,
                                    'size' => 'full',
                                    'aspectMode' => 'cover',
                                    'aspectRatio' => '1:1'
                                ]
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'margin' => 'md',
                            'flex' => 1,
                            'contents' => [
                                ['type' => 'text', 'text' => $displayName, 'weight' => 'bold', 'size' => 'md', 'color' => '#0f172a', 'wrap' => true, 'maxLines' => 1],
                                ['type' => 'text', 'text' => "Username: {$user['username']}", 'size' => 'xxs', 'color' => '#64748b', 'wrap' => true],
                                ['type' => 'text', 'text' => "สถานะ: {$roleName}", 'size' => 'xxs', 'color' => '#0284c7', 'weight' => 'bold', 'wrap' => true]
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'md',
                    'backgroundColor' => '#f8fafc',
                    'cornerRadius' => '8px',
                    'paddingAll' => '12px',
                    'contents' => [
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'alignItems' => 'center',
                            'flex' => 1,
                            'contents' => [
                                ['type' => 'text', 'text' => '💰 ยอดเงินคงเหลือ', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true],
                                ['type' => 'text', 'text' => '฿' . number_format((float)$user['balance'], 2), 'weight' => 'bold', 'size' => 'lg', 'color' => '#059669', 'wrap' => true, 'margin' => 'xs']
                            ]
                        ],
                        [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'alignItems' => 'center',
                            'flex' => 1,
                            'contents' => [
                                ['type' => 'text', 'text' => '📂 VPN ที่ใช้งาน', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true],
                                ['type' => 'text', 'text' => "{$activeCount} ไฟล์", 'weight' => 'bold', 'size' => 'lg', 'color' => '#2563eb', 'wrap' => true, 'margin' => 'xs']
                            ]
                        ]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'md',
                    'spacing' => 'sm',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '💰 เติมเงิน', 'data' => 'action=topup_menu', 'displayText' => 'เติมเงิน'],
                            'style' => 'primary',
                            'color' => '#059669',
                            'height' => 'sm',
                            'flex' => 1
                        ],
                        [
                            'type' => 'button',
                            'action' => ['type' => 'postback', 'label' => '🛒 สั่งซื้อ VPN', 'data' => 'action=buy_servers', 'displayText' => 'สั่งซื้อ VPN'],
                            'style' => 'primary',
                            'color' => '#2563eb',
                            'height' => 'sm',
                            'flex' => 1
                        ]
                    ]
                ]
            ]
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => '👤 ข้อมูลโปรไฟล์ของคุณ',
        'contents' => $bubble
    ];
}

/**
 * Help & Contact Flex Message
 */
function line_bot_build_help_menu(): array {
    $settings = get_line_bot_settings();
    $siteUrl = '';
    if (!empty($settings['webhook_url'])) {
        $siteUrl = preg_replace('#/api/line_webhook\.php.*$#i', '', $settings['webhook_url']);
    }
    if (empty($siteUrl) && !empty($_SERVER['HTTP_HOST'])) {
        $proto = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $siteUrl = $proto . $_SERVER['HTTP_HOST'];
    }
    if (empty($siteUrl)) {
        $siteUrl = 'https://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }

    $bubble = [
        'type' => 'bubble',
        'size' => 'mega',
        'header' => [
            'type' => 'box',
            'layout' => 'vertical',
            'backgroundColor' => '#0f172a',
            'paddingAll' => '16px',
            'contents' => [
                ['type' => 'text', 'text' => '❓ ช่วยเหลือ & วิธีเชื่อมต่อ VPN', 'weight' => 'bold', 'size' => 'md', 'color' => '#38bdf8', 'wrap' => true]
            ]
        ],
        'body' => [
            'type' => 'box',
            'layout' => 'vertical',
            'paddingAll' => '16px',
            'contents' => [
                [
                    'type' => 'text',
                    'text' => "📱 แอพพลิเคชันที่แนะนำสำหรับเชื่อมต่อ:",
                    'weight' => 'bold',
                    'size' => 'xs',
                    'color' => '#0f172a',
                    'wrap' => true
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'xs',
                    'spacing' => 'xs',
                    'contents' => [
                        ['type' => 'text', 'text' => '• Android: v2rayNG, NapsternetV, NetMod, OpenVPN', 'size' => 'xs', 'color' => '#475569', 'wrap' => true],
                        ['type' => 'text', 'text' => '• iOS (iPhone/iPad): Shadowrocket, Streisand, Wings X', 'size' => 'xs', 'color' => '#475569', 'wrap' => true],
                        ['type' => 'text', 'text' => '• Windows / PC: v2rayN, Nekoray, NetMod PC', 'size' => 'xs', 'color' => '#475569', 'wrap' => true]
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'vertical',
                    'margin' => 'md',
                    'backgroundColor' => '#f8fafc',
                    'cornerRadius' => '8px',
                    'paddingAll' => '10px',
                    'contents' => [
                        ['type' => 'text', 'text' => '📖 วิธีใช้งานง่ายๆ:', 'weight' => 'bold', 'size' => 'xs', 'color' => '#0f172a', 'wrap' => true],
                        ['type' => 'text', 'text' => '1. กดสั่งซื้อหรือรับไฟล์ VPN จากบอท', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'xs'],
                        ['type' => 'text', 'text' => '2. คัดลอกลิงก์ Config (vless:// หรือ vmess://)', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'xs'],
                        ['type' => 'text', 'text' => '3. เปิดแอพ VPN แล้วกดนำเข้าจากคลิปบอร์ด (Import from Clipboard)', 'size' => 'xs', 'color' => '#64748b', 'wrap' => true, 'margin' => 'xs'],
                        ['type' => 'text', 'text' => '4. กดเชื่อมต่อ (Connect) ใช้งานได้ทันที!', 'size' => 'xs', 'color' => '#059669', 'weight' => 'bold', 'wrap' => true, 'margin' => 'xs']
                    ]
                ],
                [
                    'type' => 'box',
                    'layout' => 'horizontal',
                    'margin' => 'md',
                    'contents' => [
                        [
                            'type' => 'button',
                            'action' => ['type' => 'uri', 'label' => '🌐 เข้าสู่เว็บไซต์', 'uri' => $siteUrl],
                            'style' => 'primary',
                            'color' => '#2563eb',
                            'height' => 'sm'
                        ]
                    ]
                ]
            ]
        ]
    ];

    return [
        'type' => 'flex',
        'altText' => '❓ ช่วยเหลือ & วิธีเชื่อมต่อ VPN',
        'contents' => $bubble
    ];
}
