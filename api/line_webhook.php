<?php
// api/line_webhook.php - Webhook Receiver for LINE Messaging API
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/line_bot.php';

// If GET request, return status check
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'status' => 'online',
        'service' => 'EkromVPN LINE Messaging API Webhook',
        'time' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Receive Webhook POST payload
$rawBody = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';

$logFile = __DIR__ . '/../line_bot.log';
$logEntry = date('Y-m-d H:i:s') . " [WEBHOOK INCOMING] IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\nPayload: " . $rawBody . "\n\n";
@file_put_contents($logFile, $logEntry, FILE_APPEND);

$settings = get_line_bot_settings();
if (empty($settings['enabled'])) {
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " [WEBHOOK] Bot is disabled\n", FILE_APPEND);
    http_response_code(200);
    echo json_encode(['status' => 'disabled', 'message' => 'LINE Bot is disabled']);
    exit;
}

// Verify signature if configured
if (!empty($settings['channel_secret']) && !empty($signature)) {
    if (!line_bot_verify_signature($rawBody, $signature, $settings['channel_secret'])) {
        @file_put_contents($logFile, date('Y-m-d H:i:s') . " [WEBHOOK ERROR] Signature verification failed\n", FILE_APPEND);
        http_response_code(400);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }
}

$data = json_decode($rawBody, true);
if (!$data || !isset($data['events']) || !is_array($data['events']) || empty($data['events'])) {
    @file_put_contents($logFile, date('Y-m-d H:i:s') . " [WEBHOOK] No events in payload (Verify test)\n", FILE_APPEND);
    http_response_code(200);
    echo json_encode(['status' => 'no_events']);
    exit;
}

foreach ($data['events'] as $event) {
    $type = $event['type'] ?? '';
    $replyToken = $event['replyToken'] ?? '';
    $userId = $event['source']['userId'] ?? null;

    if (empty($userId)) {
        continue;
    }

    // Get or auto-register user linked to this LINE user ID
    $user = line_bot_get_or_create_user($userId);

    // 1. Follow / Add Friend Event
    if ($type === 'follow') {
        $welcomeMsg = [
            'type' => 'text',
            'text' => "ยินดีต้อนรับคุณ " . ($user['line_display_name'] ?: 'ลูกค้า') . " สู่ EkromVPN Shop ครับ! ⚡\n\nระบบสั่งซื้อและจัดการไฟล์ VPN อัตโนมัติ 24 ชม. คุณสามารถเลือกสั่งซื้อ ทดลองใช้ฟรี หรือเติมเงินได้ทันทีจากเมนูด้านล่างนี้ได้เลยครับ 👇"
        ];
        $menuMsg = line_bot_build_main_menu($user);
        line_bot_reply_message($replyToken, [$welcomeMsg, $menuMsg]);
        continue;
    }

    // 2. Message Event (Text or Image)
    if ($type === 'message') {
        $msgType = $event['message']['type'] ?? '';

        // --- Image Message (Slip Upload) ---
        if ($msgType === 'image') {
            $msgId = $event['message']['id'] ?? '';

            // 1. Trigger animated loading indicator in LINE chat
            if (!empty($userId)) {
                line_bot_start_loading_animation($userId, 20);
                // 2. Instantly push elegant "กำลังตรวจสอบสลิป" card
                line_bot_push_message($userId, [line_bot_build_slip_checking_message($user)]);
            }

            $imageBinary = line_bot_get_message_content($msgId);

            if (empty($imageBinary)) {
                line_bot_reply_message($replyToken, [[
                    'type' => 'text',
                    'text' => '❌ ไม่สามารถดาวน์โหลดรูปภาพสลิปได้ กรุณาลองส่งใหม่อีกครั้งครับ'
                ]]);
                continue;
            }

            $res = line_bot_process_slip_image($user, $imageBinary);

            if ($res['success']) {
                // Fetch updated user
                $db = get_db();
                $uStmt = $db->prepare('SELECT * FROM users WHERE id = ?');
                $uStmt->execute([$user['id']]);
                $freshUser = $uStmt->fetch();

                $topupSuccessBubble = [
                    'type' => 'bubble',
                    'size' => 'mega',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'backgroundColor' => '#059669',
                        'paddingAll' => '16px',
                        'contents' => [
                            ['type' => 'text', 'text' => '🎉 ตรวจสอบสลิปสำเร็จ!', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true],
                            ['type' => 'text', 'text' => 'เติมเงินเข้ากระเป๋าเรียบร้อยแล้ว', 'size' => 'xs', 'color' => '#d1fae5', 'wrap' => true, 'margin' => 'xs']
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
                                            ['type' => 'text', 'text' => 'ยอดเงินที่เติม:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 3],
                                            ['type' => 'text', 'text' => '฿' . number_format($res['amount'], 2) . ' บาท', 'weight' => 'bold', 'size' => 'xs', 'color' => '#059669', 'flex' => 4]
                                        ]
                                    ],
                                    [
                                        'type' => 'box',
                                        'layout' => 'horizontal',
                                        'contents' => [
                                            ['type' => 'text', 'text' => 'ยอดคงเหลือใหม่:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 3],
                                            ['type' => 'text', 'text' => '฿' . number_format($res['new_balance'], 2) . ' บาท', 'weight' => 'bold', 'size' => 'xs', 'color' => '#0284c7', 'flex' => 4]
                                        ]
                                    ],
                                    [
                                        'type' => 'box',
                                        'layout' => 'horizontal',
                                        'contents' => [
                                            ['type' => 'text', 'text' => 'รหัสอ้างอิงสลิป:', 'size' => 'xs', 'color' => '#64748b', 'flex' => 3],
                                            ['type' => 'text', 'text' => (string)$res['trans_ref'], 'size' => 'xxs', 'color' => '#0f172a', 'flex' => 4, 'wrap' => true]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'margin' => 'lg',
                                'spacing' => 'sm',
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
                                        'action' => ['type' => 'postback', 'label' => '🏠 เมนูหลัก', 'data' => 'action=main_menu', 'displayText' => 'เมนูหลัก'],
                                        'style' => 'secondary',
                                        'height' => 'sm',
                                        'flex' => 1
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];

                line_bot_reply_message($replyToken, [[
                    'type' => 'flex',
                    'altText' => '🎉 เติมเงินสำเร็จ ฿' . number_format($res['amount'], 2),
                    'contents' => $topupSuccessBubble
                ]]);
            } else {
                $errorBubble = [
                    'type' => 'bubble',
                    'size' => 'mega',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'backgroundColor' => '#dc2626',
                        'paddingAll' => '16px',
                        'contents' => [
                            ['type' => 'text', 'text' => '❌ ตรวจสอบสลิปไม่สำเร็จ', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true]
                        ]
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'paddingAll' => '16px',
                        'contents' => [
                            ['type' => 'text', 'text' => $res['message'] ?? 'เกิดข้อผิดพลาดในการตรวจสอบสลิป', 'size' => 'xs', 'color' => '#334155', 'wrap' => true],
                            [
                                'type' => 'button',
                                'action' => ['type' => 'postback', 'label' => '💰 ดูวิธีเติมเงิน', 'data' => 'action=topup_menu', 'displayText' => 'ดูข้อมูลเติมเงิน'],
                                'style' => 'primary',
                                'color' => '#059669',
                                'height' => 'sm',
                                'margin' => 'lg'
                            ]
                        ]
                    ]
                ];

                line_bot_reply_message($replyToken, [[
                    'type' => 'flex',
                    'altText' => '❌ ตรวจสอบสลิปไม่สำเร็จ',
                    'contents' => $errorBubble
                ]]);
            }
            continue;
        }

        // --- Text Message ---
        if ($msgType === 'text') {
            $text = trim($event['message']['text'] ?? '');
            $cleanLower = strtolower($text);

            // 0. Check Session (Awaiting user text input for custom name or rename)
            $session = line_bot_get_user_session($user['id']);

            // If user typed cancel or back keyword
            if (in_array($cleanLower, ['ยกเลิก', 'cancel', '❌ ยกเลิก', 'ออก', 'กลับ', 'ย้อนกลับ', '◀️ ย้อนกลับ', 'back'])) {
                if ($session) {
                    $state = $session['state'] ?? '';
                    $sData = $session['data'] ?? [];
                    line_bot_clear_user_session($user['id']);

                    if ($state === 'awaiting_custom_name_for_server' || $state === 'awaiting_name_for_server' || $state === 'awaiting_custom_name_buy') {
                        $sId = (int)($sData['server_id'] ?? 0);
                        $isTr = !empty($sData['is_trial']);
                        $srv = line_bot_get_server_by_id($sId);
                        if ($srv && !empty($srv['category_id'])) {
                            $cat = line_bot_get_category_by_id((int)$srv['category_id']);
                            if ($cat) {
                                $srvs = line_bot_get_servers_by_category((int)$srv['category_id']);
                                line_bot_reply_message($replyToken, [line_bot_build_servers_in_category($cat, $srvs, $user, $isTr)]);
                                continue;
                            }
                        }
                        $cats = line_bot_get_active_categories();
                        line_bot_reply_message($replyToken, [line_bot_build_category_selection($cats, $user, $isTr)]);
                        continue;
                    } elseif ($state === 'awaiting_rename_config') {
                        line_bot_reply_message($replyToken, [line_bot_build_my_vpns($user)]);
                        continue;
                    }

                    line_bot_reply_message($replyToken, [
                        line_bot_build_main_menu($user)
                    ]);
                    continue;
                }
            }

            if ($session) {
                if ($session['state'] === 'awaiting_custom_name_for_server' || $session['state'] === 'awaiting_name_for_server' || $session['state'] === 'awaiting_custom_name_buy') {
                    $customName = trim($text);
                    if ($customName === '') {
                        line_bot_reply_message($replyToken, [[
                            'type' => 'text',
                            'text' => '📝กรุณาพิมพ์ชื่อของคุณ'
                        ]]);
                        continue;
                    }

                    line_bot_clear_user_session($user['id']);
                    $serverId = (int)($session['data']['server_id'] ?? 0);
                    $isTrial = !empty($session['data']['is_trial']);
                    $server = line_bot_get_server_by_id($serverId);

                    if ($server) {
                        if ($isTrial) {
                            line_bot_reply_message($replyToken, [line_bot_build_trial_confirmation($server, $user, $customName)]);
                        } else {
                            line_bot_reply_message($replyToken, [line_bot_build_package_selection($server, $user, $customName)]);
                        }
                    } else {
                        line_bot_reply_message($replyToken, [[
                            'type' => 'text',
                            'text' => '❌ ไม่พบเซิร์ฟเวอร์ที่เลือก กรุณาเลือกใหม่อีกครั้ง'
                        ]]);
                    }
                    continue;
                }

                if ($session['state'] === 'awaiting_rename_config') {
                    $newCustomName = trim($text);
                    if ($newCustomName === '') {
                        line_bot_reply_message($replyToken, [[
                            'type' => 'text',
                            'text' => '📝กรุณาพิมพ์ชื่อของคุณ'
                        ]]);
                        continue;
                    }

                    line_bot_clear_user_session($user['id']);
                    $configId = (int)($session['data']['config_id'] ?? 0);

                    $res = process_vpn_rename($user, $configId, $newCustomName);

                    if ($res['status'] === 'success') {
                        $successMsgs = line_bot_build_rename_success_messages($res, $user);
                        line_bot_reply_message($replyToken, $successMsgs);
                    } else {
                        $errMsg = $res['message'] ?? 'เกิดข้อผิดพลาดในการเปลี่ยนชื่อ';
                        line_bot_reply_message($replyToken, [[
                            'type' => 'text',
                            'text' => '❌ ' . $errMsg
                        ]]);
                    }
                    continue;
                }
            }

            // 1. Check TrueMoney Voucher Link
            if (stripos($text, 'gift.truemoney.com') !== false || stripos($text, 'truemoney.com') !== false) {
                $vRes = line_bot_process_truemoney_voucher($user, $text);
                if ($vRes && $vRes['success']) {
                    $voucherBubble = [
                        'type' => 'bubble',
                        'size' => 'mega',
                        'header' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'backgroundColor' => '#f97316',
                            'paddingAll' => '16px',
                            'contents' => [
                                ['type' => 'text', 'text' => '🧧 รับซองของขวัญ TrueMoney สำเร็จ!', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff', 'wrap' => true]
                            ]
                        ],
                        'body' => [
                            'type' => 'box',
                            'layout' => 'vertical',
                            'paddingAll' => '16px',
                            'contents' => [
                                [
                                    'type' => 'text',
                                    'text' => "เติมเงิน ฿" . number_format($vRes['amount'], 2) . " เข้ากระเป๋าของคุณแล้ว 🎉\nยอดคงเหลือใหม่: ฿" . number_format($vRes['new_balance'], 2) . " บาท",
                                    'size' => 'xs',
                                    'color' => '#334155',
                                    'wrap' => true
                                ],
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
                    ];
                    line_bot_reply_message($replyToken, [[
                        'type' => 'flex',
                        'altText' => '🧧 รับซอง TrueMoney สำเร็จ',
                        'contents' => $voucherBubble
                    ]]);
                } else {
                    line_bot_reply_message($replyToken, [[
                        'type' => 'text',
                        'text' => '❌ ไม่สามารถรับซองของขวัญได้ ลิงก์อาจหมดอายุ ถูกใช้งานแล้ว หรือไม่ถูกต้อง'
                    ]]);
                }
                continue;
            }

            // 2. Keyword Matching
            if (in_array($cleanLower, ['เมนู', 'menu', 'หน้าหลัก', 'home', 'เริ่ม', 'start', 'บอท', 'bot', 'สวัสดี', 'ดีครับ', 'ดีค่ะ', 'hi', 'hello'])) {
                line_bot_reply_message($replyToken, [line_bot_build_main_menu($user)]);
                continue;
            }

            if (in_array($cleanLower, ['ซื้อ', 'สั่งซื้อ', 'ซื้อ vpn', 'buy', 'vpn', 'ซื้อไฟล์', 'เน็ต', 'ดีแทค', 'dtac', 'เอไอเอส', 'ais', 'ทรู', 'true'])) {
                $categories = line_bot_get_active_categories();
                line_bot_reply_message($replyToken, [line_bot_build_category_selection($categories, $user, false)]);
                continue;
            }

            if (in_array($cleanLower, ['ทดลอง', 'ทดลองใช้', 'ทดลองฟรี', 'trial', 'ฟรี', 'free'])) {
                $categories = line_bot_get_active_categories();
                line_bot_reply_message($replyToken, [line_bot_build_category_selection($categories, $user, true)]);
                continue;
            }

            if (in_array($cleanLower, ['ต่ออายุ', 'renew', 'ต่อเวลา', 'เพิ่มวัน', 'ต่ออายุ vpn', 'ต่อเน็ต'])) {
                $db = get_db();
                $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE user_id = ? AND status_real = "active" ORDER BY id DESC LIMIT 10');
                $stmt->execute([$user['id']]);
                $configs = $stmt->fetchAll();

                if (count($configs) === 1) {
                    line_bot_reply_message($replyToken, [line_bot_build_renew_days_selection($configs[0], $user)]);
                } else {
                    line_bot_reply_message($replyToken, [line_bot_build_renew_select_config($configs, $user)]);
                }
                continue;
            }

            if (in_array($cleanLower, ['เติมเงิน', 'topup', 'สลิป', 'เติม', 'โอนเงิน', 'qr', 'พร้อมเพย์', 'promptpay'])) {
                line_bot_reply_message($replyToken, [line_bot_build_topup_menu($user)]);
                continue;
            }

            if (in_array($cleanLower, ['ไฟล์ของฉัน', 'vpn ของฉัน', 'my vpn', 'myvpns', 'config', 'ไฟล์', 'ดูไฟล์', 'ลบ', 'ลบไฟล์', 'ลบ vpn', 'delete'])) {
                line_bot_reply_message($replyToken, [line_bot_build_my_vpns($user)]);
                continue;
            }

            if (in_array($cleanLower, ['โปรไฟล์', 'ข้อมูลส่วนตัว', 'profile', 'ยอดเงิน', 'เงินเหลือ', 'balance', 'เช็คยอด', 'เงิน'])) {
                line_bot_reply_message($replyToken, [line_bot_build_my_profile($user)]);
                continue;
            }

            if (in_array($cleanLower, ['ช่วยเหลือ', 'help', 'วิธีใช้', 'ติดต่อ', 'contact', 'แอพ', 'แอป'])) {
                line_bot_reply_message($replyToken, [line_bot_build_help_menu()]);
                continue;
            }

            // Fallback: Show Main Menu
            line_bot_reply_message($replyToken, [
                [
                    'type' => 'text',
                    'text' => "สวัสดีครับคุณ " . ($user['line_display_name'] ?: 'ลูกค้า') . " ⚡\nคุณสามารถเลือกทำรายการผ่านปุ่มเมนูด้านล่างนี้ หรือส่งรูปภาพสลิปโอนเงินเข้ามาเพื่อเติมเงินอัตโนมัติได้ทันทีครับ 👇"
                ],
                line_bot_build_main_menu($user)
            ]);
            continue;
        }
    }

    // 3. Postback Event (Button Clicks)
    if ($type === 'postback') {
        $postbackData = $event['postback']['data'] ?? '';
        parse_str($postbackData, $params);
        $action = $params['action'] ?? '';

        // Clear any waiting session on postback actions
        line_bot_clear_user_session($user['id']);

        // Reload fresh user balance
        $db = get_db();
        $uStmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $uStmt->execute([$user['id']]);
        $user = $uStmt->fetch();

        switch ($action) {
            case 'main_menu':
                line_bot_reply_message($replyToken, [line_bot_build_main_menu($user)]);
                break;

            case 'buy_servers':
                $categories = line_bot_get_active_categories();
                line_bot_reply_message($replyToken, [line_bot_build_category_selection($categories, $user, false)]);
                break;

            case 'trial_servers':
                $categories = line_bot_get_active_categories();
                line_bot_reply_message($replyToken, [line_bot_build_category_selection($categories, $user, true)]);
                break;

            case 'renew_menu':
                $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE user_id = ? AND status_real = "active" ORDER BY id DESC LIMIT 10');
                $stmt->execute([$user['id']]);
                $configs = $stmt->fetchAll();

                if (count($configs) === 1) {
                    line_bot_reply_message($replyToken, [line_bot_build_renew_days_selection($configs[0], $user)]);
                } else {
                    line_bot_reply_message($replyToken, [line_bot_build_renew_select_config($configs, $user)]);
                }
                break;

            case 'renew_select_days':
                $configId = (int)($params['config_id'] ?? 0);
                $cStmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ?');
                $cStmt->execute([$configId, $user['id']]);
                $config = $cStmt->fetch();

                if ($config) {
                    line_bot_reply_message($replyToken, [line_bot_build_renew_days_selection($config, $user)]);
                } else {
                    line_bot_reply_message($replyToken, [[
                        'type' => 'text',
                        'text' => '❌ ไม่พบไฟล์ VPN นี้ในบัญชีของคุณ'
                    ]]);
                }
                break;

            case 'confirm_renew':
                $configId = (int)($params['config_id'] ?? 0);
                $days = (int)($params['days'] ?? 30);

                $res = process_vpn_renewal($user, $configId, $days);

                if ($res['status'] === 'success') {
                    $successMsgs = line_bot_build_renew_success_messages($res, $user);
                    line_bot_reply_message($replyToken, $successMsgs);
                } else {
                    $errMsg = $res['message'] ?? 'เกิดข้อผิดพลาดในการต่ออายุ';
                    $isBalErr = (stripos($errMsg, 'ยอดเงินคงเหลือไม่พอ') !== false || stripos($errMsg, 'ยอดเงินไม่พอ') !== false || stripos($errMsg, 'เงินไม่พอ') !== false);

                    if ($isBalErr) {
                        $insufficientBubble = [
                            'type' => 'bubble',
                            'size' => 'mega',
                            'header' => [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'backgroundColor' => '#dc2626',
                                'paddingAll' => '16px',
                                'contents' => [
                                    ['type' => 'text', 'text' => '⚠️ ยอดเงินในบัญชีไม่เพียงพอ', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff']
                                ]
                            ],
                            'body' => [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'paddingAll' => '16px',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => $errMsg . "\n\nยอดเงินคงเหลือปัจจุบันของคุณ: ฿" . number_format((float)$user['balance'], 2) . " บาท\nกรุณาเติมเงินเข้าระบบเพื่อดำเนินการต่ออายุครับ",
                                        'size' => 'xs',
                                        'color' => '#334155',
                                        'wrap' => true
                                    ],
                                    [
                                        'type' => 'box',
                                        'layout' => 'horizontal',
                                        'margin' => 'lg',
                                        'spacing' => 'sm',
                                        'contents' => [
                                            [
                                                'type' => 'button',
                                                'action' => ['type' => 'postback', 'label' => '💰 เติมเงินทันที', 'data' => 'action=topup_menu', 'displayText' => 'เติมเงิน'],
                                                'style' => 'primary',
                                                'color' => '#059669',
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
                                ]
                            ]
                        ];

                        line_bot_reply_message($replyToken, [[
                            'type' => 'flex',
                            'altText' => '⚠️ ยอดเงินในบัญชีไม่พอ กรุณาเติมเงิน',
                            'contents' => $insufficientBubble
                        ]]);
                    } else {
                        line_bot_reply_message($replyToken, [[
                            'type' => 'text',
                            'text' => '❌ ' . $errMsg
                        ]]);
                    }
                }
                break;

            case 'select_category':
                $categoryId = (int)($params['category_id'] ?? 0);
                $isTrial = (!empty($params['is_trial']) && $params['is_trial'] == '1');
                $category = line_bot_get_category_by_id($categoryId);
                if (!$category) {
                    $categories = line_bot_get_active_categories();
                    line_bot_reply_message($replyToken, [line_bot_build_category_selection($categories, $user, $isTrial)]);
                    break;
                }
                $servers = line_bot_get_servers_by_category($categoryId);
                line_bot_reply_message($replyToken, [line_bot_build_servers_in_category($category, $servers, $user, $isTrial)]);
                break;

            case 'select_server':
                $serverId = (int)($params['server_id'] ?? 0);
                $isTrial = (!empty($params['is_trial']) && $params['is_trial'] == '1');
                $server = line_bot_get_server_by_id($serverId);
                if (!$server) {
                    line_bot_reply_message($replyToken, [[
                        'type' => 'text',
                        'text' => '❌ ไม่พบเซิร์ฟเวอร์ที่เลือก กรุณาเลือกใหม่อีกครั้ง'
                    ]]);
                    break;
                }
                // Mandatory Name Input: Ask customer for their name before showing duration/payment
                line_bot_set_user_session($user['id'], 'awaiting_custom_name_for_server', [
                    'server_id' => $serverId,
                    'is_trial' => $isTrial ? 1 : 0
                ]);
                $backCategoryData = (!empty($server['category_id']))
                    ? "action=select_category&category_id={$server['category_id']}&is_trial=" . ($isTrial ? '1' : '0')
                    : ($isTrial ? 'action=trial_servers' : 'action=buy_servers');

                line_bot_reply_message($replyToken, [
                    [
                        'type' => 'text',
                        'text' => '📝กรุณาพิมพ์ชื่อของคุณ',
                        'quickReply' => [
                            'items' => [
                                [
                                    'type' => 'action',
                                    'action' => [
                                        'type' => 'postback',
                                        'label' => '◀️ ย้อนกลับ',
                                        'data' => $backCategoryData,
                                        'displayText' => 'ย้อนกลับ'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);
                break;

            case 'ask_rename_config':
                $configId = (int)($params['config_id'] ?? 0);
                $cStmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ?');
                $cStmt->execute([$configId, $user['id']]);
                $config = $cStmt->fetch();
                if (!$config) {
                    line_bot_reply_message($replyToken, [[
                        'type' => 'text',
                        'text' => '❌ ไม่พบไฟล์ VPN นี้ในบัญชีของคุณ'
                    ]]);
                    break;
                }
                line_bot_set_user_session($user['id'], 'awaiting_rename_config', [
                    'config_id' => $configId
                ]);
                line_bot_reply_message($replyToken, [
                    [
                        'type' => 'text',
                        'text' => '📝กรุณาพิมพ์ชื่อของคุณ',
                        'quickReply' => [
                            'items' => [
                                [
                                    'type' => 'action',
                                    'action' => [
                                        'type' => 'postback',
                                        'label' => '◀️ ย้อนกลับ',
                                        'data' => 'action=my_vpns',
                                        'displayText' => 'ย้อนกลับ'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]);
                break;

            case 'ask_delete_config':
                $configId = (int)($params['config_id'] ?? 0);
                $preview = process_vpn_deletion($user, $configId, 'preview');
                if ($preview['status'] !== 'success') {
                    line_bot_reply_message($replyToken, [[
                        'type' => 'text',
                        'text' => '❌ ' . ($preview['message'] ?? 'ไม่พบไฟล์ VPN ที่ต้องการลบ')
                    ]]);
                    break;
                }
                line_bot_reply_message($replyToken, [line_bot_build_delete_confirmation($preview, $user)]);
                break;

            case 'confirm_delete_config':
                $configId = (int)($params['config_id'] ?? 0);
                $res = process_vpn_deletion($user, $configId, 'delete');
                if ($res['status'] !== 'success') {
                    line_bot_reply_message($replyToken, [[
                        'type' => 'text',
                        'text' => '❌ ' . ($res['message'] ?? 'เกิดข้อผิดพลาดในการลบไฟล์')
                    ]]);
                    break;
                }
                line_bot_reply_message($replyToken, [line_bot_build_delete_success($res, $user)]);
                break;

            case 'confirm_buy':
                $serverId = (int)($params['server_id'] ?? 0);
                $packageVal = trim($params['package'] ?? '30');
                $customName = trim($params['custom_name'] ?? '');

                $res = process_vpn_creation($user, $serverId, $packageVal, $customName, '', '', 60);

                if ($res['status'] === 'success') {
                    $successMsgs = line_bot_build_order_success_messages($res, $user);
                    line_bot_reply_message($replyToken, $successMsgs);
                } else {
                    $errMsg = $res['message'] ?? 'เกิดข้อผิดพลาดในการสั่งซื้อ';
                    $isBalErr = (stripos($errMsg, 'ยอดเงินคงเหลือไม่พอ') !== false || stripos($errMsg, 'เงินไม่พอ') !== false);

                    if ($isBalErr) {
                        $insufficientBubble = [
                            'type' => 'bubble',
                            'size' => 'mega',
                            'header' => [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'backgroundColor' => '#dc2626',
                                'paddingAll' => '16px',
                                'contents' => [
                                    ['type' => 'text', 'text' => '⚠️ ยอดเงินในบัญชีไม่เพียงพอ', 'weight' => 'bold', 'size' => 'md', 'color' => '#ffffff']
                                ]
                            ],
                            'body' => [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'paddingAll' => '16px',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => $errMsg . "\n\nยอดเงินคงเหลือปัจจุบันของคุณ: ฿" . number_format((float)$user['balance'], 2) . " บาท\nกรุณาเติมเงินเข้าระบบเพื่อดำเนินการสั่งซื้อครับ",
                                        'size' => 'xs',
                                        'color' => '#334155',
                                        'wrap' => true
                                    ],
                                    [
                                        'type' => 'box',
                                        'layout' => 'horizontal',
                                        'margin' => 'lg',
                                        'spacing' => 'sm',
                                        'contents' => [
                                            [
                                                'type' => 'button',
                                                'action' => ['type' => 'postback', 'label' => '💰 เติมเงินทันที', 'data' => 'action=topup_menu', 'displayText' => 'เติมเงิน'],
                                                'style' => 'primary',
                                                'color' => '#059669',
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
                                ]
                            ]
                        ];

                        line_bot_reply_message($replyToken, [[
                            'type' => 'flex',
                            'altText' => '⚠️ ยอดเงินในบัญชีไม่พอ กรุณาเติมเงิน',
                            'contents' => $insufficientBubble
                        ]]);
                    } else {
                        line_bot_reply_message($replyToken, [[
                            'type' => 'text',
                            'text' => '❌ ' . $errMsg
                        ]]);
                    }
                }
                break;

            case 'confirm_trial':
                $serverId = (int)($params['server_id'] ?? 0);
                $customName = trim($params['custom_name'] ?? '');
                $res = process_vpn_creation($user, $serverId, 'trial', $customName, '', '', 60);

                if ($res['status'] === 'success') {
                    $successMsgs = line_bot_build_order_success_messages($res, $user);
                    line_bot_reply_message($replyToken, $successMsgs);
                } else {
                    $errMsg = $res['message'] ?? 'เกิดข้อผิดพลาดในการขอทดลองใช้';
                    line_bot_reply_message($replyToken, [[
                        'type' => 'text',
                        'text' => '❌ ' . $errMsg
                    ]]);
                }
                break;

            case 'topup_menu':
                line_bot_reply_message($replyToken, [line_bot_build_topup_menu($user)]);
                break;

            case 'my_vpns':
                line_bot_reply_message($replyToken, [line_bot_build_my_vpns($user)]);
                break;

            case 'my_profile':
                line_bot_reply_message($replyToken, [line_bot_build_my_profile($user)]);
                break;

            case 'help':
                line_bot_reply_message($replyToken, [line_bot_build_help_menu()]);
                break;

            case 'get_config':
                $configId = (int)($params['config_id'] ?? 0);
                $cStmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ?');
                $cStmt->execute([$configId, $user['id']]);
                $config = $cStmt->fetch();

                if (!$config) {
                    line_bot_reply_message($replyToken, [[
                        'type' => 'text',
                        'text' => '❌ ไม่พบไฟล์ VPN นี้ในบัญชีของคุณ'
                    ]]);
                    break;
                }

                $detailMsgs = line_bot_build_config_detail_messages($config, $user);
                line_bot_reply_message($replyToken, $detailMsgs);
                break;

            default:
                line_bot_reply_message($replyToken, [line_bot_build_main_menu($user)]);
                break;
        }
    }
}

http_response_code(200);
echo json_encode(['status' => 'success']);
