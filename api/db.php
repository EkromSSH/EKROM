<?php
// api/db.php - Core DB connection & Session handling
date_default_timezone_set('Asia/Bangkok');
require_once __DIR__ . '/xui.php';
require_once __DIR__ . '/ssh_vps.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.gc_maxlifetime', 86400 * 30);
    session_start();
}

function release_session_lock() {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}

function get_db() {
    static $db = null;
    if ($db === null) {
        $dbPath = dirname(__DIR__) . '/database.sqlite';
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec('PRAGMA journal_mode = WAL;');
        $db->exec('PRAGMA busy_timeout = 5000;');
        $db->exec('PRAGMA synchronous = NORMAL;');

        // ตารางสำหรับจัดเก็บ Remember Token (คงสถานะเข้าสู่ระบบได้ 30 วันแม้ PHP Session จะหมดอายุ)
        $db->exec('CREATE TABLE IF NOT EXISTS user_remember_tokens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            token_hash TEXT NOT NULL UNIQUE,
            expires_at DATETIME NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');

        // ตารางสำหรับจัดเก็บสถานะสนทนาของ LINE Bot (State Session)
        $db->exec('CREATE TABLE IF NOT EXISTS line_bot_sessions (
            user_id INTEGER PRIMARY KEY,
            state TEXT NOT NULL,
            data TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )');
    }
    return $db;
}

function json_response($data, $code = 200) {
    release_session_lock();
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function get_post_json() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function get_auth_user() {
    $db = get_db();
    if (!isset($_SESSION['user_id'])) {
        // ตรวจสอบคุกกี้ Remember Token หากเซสชัน PHP ขาดหาย
        if (!empty($_COOKIE['ekrom_remember_token'])) {
            $tokenHash = hash('sha256', $_COOKIE['ekrom_remember_token']);
            $stmt = $db->prepare('SELECT user_id, expires_at FROM user_remember_tokens WHERE token_hash = ?');
            $stmt->execute([$tokenHash]);
            $tokenRow = $stmt->fetch();

            if ($tokenRow && strtotime($tokenRow['expires_at']) > time()) {
                $userStmt = $db->prepare('SELECT id, username, role, balance, admin_pin, created_at FROM users WHERE id = ?');
                $userStmt->execute([$tokenRow['user_id']]);
                $user = $userStmt->fetch();

                if ($user) {
                    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    release_session_lock();
                    return $user;
                }
            } else {
                if ($tokenRow) {
                    $delStmt = $db->prepare('DELETE FROM user_remember_tokens WHERE token_hash = ?');
                    $delStmt->execute([$tokenHash]);
                }
                setcookie('ekrom_remember_token', '', [
                    'expires' => time() - 42000,
                    'path' => '/'
                ]);
            }
        }
        release_session_lock();
        return null;
    }
    $stmt = $db->prepare('SELECT id, username, role, balance, admin_pin, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    release_session_lock();
    return $user ?: null;
}

function require_auth() {
    $user = get_auth_user();
    if (!$user) {
        json_response(['status' => 'error', 'message' => 'กรุณาเข้าสู่ระบบก่อนดำเนินการ'], 401);
    }
    return $user;
}

function format_bytes($bytes) {
    if ($bytes <= 0) return '0 MB';
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    }
    if ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    }
    if ($bytes >= 1024) {
        return number_format($bytes / 1024, 1) . ' KB';
    }
    return $bytes . ' B';
}

/**
 * สร้างชื่อ Display Name สำหรับไฟล์ VPN / SSH
 * รูปแบบเมื่อลูกค้าใส่ชื่อกำกับ: customName serverName | หมดอายุ |dd-mm-yyyy hh:ii:ss
 * รูปแบบเมื่อไม่ใส่ชื่อกำกับ: serverName | หมดอายุ |dd-mm-yyyy hh:ii:ss
 */
function build_vpn_display_name($serverName, $expiryTime, $customName = '') {
    $cleanServer = trim((string)$serverName);
    // ลบธงที่ซ้ำซ้อนกันออก เช่น 🇹🇭🇹🇭 -> 🇹🇭
    $cleanServer = preg_replace('/([\x{1F1E6}-\x{1F1FF}]{2})\s*(?:[\x{1F1E6}-\x{1F1FF}]{2})+/u', '$1', $cleanServer);
    // ลบส่วน (หมดอายุ ...) หรือ | หมดอายุ |... เดิมออกถ้ามี
    $cleanServer = preg_replace('/\s*(?:[\(\[](?:หมดอายุ|EXP).*?[\)\]]|\|\s*(?:หมดอายุ|EXP)\s*\|.*$)/iu', '', $cleanServer);
    if ($cleanServer === '') {
        $cleanServer = 'VPN';
    }

    $customName = trim((string)$customName);
    if ($customName !== '') {
        // ลบวงเล็บครอบเดิมออกถ้าผู้ใช้พิมพ์วงเล็บมา เช่น (สมมุติ) หรือ [สมมุติ]
        $cleanCustom = trim($customName, "()[] \t\n\r\0\x0B");
        $cleanCustom = preg_replace('/([\x{1F1E6}-\x{1F1FF}]{2})\s*(?:[\x{1F1E6}-\x{1F1FF}]{2})+/u', '$1', $cleanCustom);
        if ($cleanCustom !== '') {
            $baseName = "{$cleanCustom} {$cleanServer}";
        } else {
            $baseName = $cleanServer;
        }
    } else {
        $baseName = $cleanServer;
    }

    $baseName = preg_replace('/([\x{1F1E6}-\x{1F1FF}]{2})\s*(?:[\x{1F1E6}-\x{1F1FF}]{2})+/u', '$1', $baseName);

    return format_vpn_config_name($baseName, $expiryTime);
}

function format_vpn_config_name($baseName, $expiryTime) {
    $cleanName = preg_replace('/\s*(?:[\(\[](?:หมดอายุ|EXP).*?[\)\]]|\|\s*(?:หมดอายุ|EXP)\s*\|.*$)/iu', '', trim((string)$baseName));
    $cleanName = preg_replace('/([\x{1F1E6}-\x{1F1FF}]{2})\s*(?:[\x{1F1E6}-\x{1F1FF}]{2})+/u', '$1', $cleanName);
    if (preg_match('/^\(\s*(.*?)\s*\)\s*(.*)$/u', $cleanName, $m)) {
        $cleanName = "{$m[1]} {$m[2]}";
    }
    if ($cleanName === '') {
        $cleanName = 'VPN';
    }
    $expTs = is_numeric($expiryTime) ? (int)$expiryTime : strtotime((string)$expiryTime);
    if (!$expTs) {
        return $cleanName;
    }
    $expFormatted = date('d-m-Y H:i:s', $expTs);
    return "{$cleanName} | หมดอายุ |{$expFormatted}";
}

function update_config_link_remark($configLink, $newDisplayName, $protocol = '') {
    if (empty($configLink)) {
        return $configLink;
    }
    
    // VMess (base64 json)
    if (strpos($configLink, 'vmess://') === 0) {
        $b64 = substr($configLink, 8);
        $json = json_decode(base64_decode($b64), true);
        if (is_array($json)) {
            $json['ps'] = $newDisplayName;
            return 'vmess://' . base64_encode(json_encode($json, JSON_UNESCAPED_UNICODE));
        }
    }
    
    // VLESS / Trojan / Shadowsocks
    if (strpos($configLink, 'vless://') === 0 || strpos($configLink, 'trojan://') === 0 || strpos($configLink, 'ss://') === 0) {
        $parts = explode('#', $configLink, 2);
        return $parts[0] . '#' . rawurlencode($newDisplayName);
    }
    
    // SSH JSON (NPV & NetMod payload)
    $sshJson = json_decode($configLink, true);
    if (is_array($sshJson) && (isset($sshJson['npv']) || isset($sshJson['netmod']))) {
        if (!empty($sshJson['npv']) && is_array($sshJson['npv'])) {
            foreach ($sshJson['npv'] as &$variant) {
                if (!empty($variant['config'])) {
                    $parts = explode('#', $variant['config'], 2);
                    $variant['config'] = $parts[0] . '#' . urlencode($newDisplayName);
                }
            }
        }
        return json_encode($sshJson, JSON_UNESCAPED_UNICODE);
    }
    
    // Raw NPV Tunnel SSH
    if (strpos($configLink, 'npvt-ssh://') === 0) {
        $parts = explode('#', $configLink, 2);
        return $parts[0] . '#' . urlencode($newDisplayName);
    }
    
    return $configLink;
}

function build_ssh_config_payload($server, $sshUser, $sshPass, $displayName) {
    $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '127.0.0.1');
    $targetPort = (int)($server['port'] ?: 22);

    $rawText = "IP: {$targetAddress}\nPort: {$targetPort}\nUser: {$sshUser}\nPass: {$sshPass}";

    // 1. Process NPV Tunnel Templates
    $npvTemplates = json_decode($server['ssh_templates'] ?? '', true) ?: [];
    $npvList = [];
    if (!empty($npvTemplates) && is_array($npvTemplates)) {
        foreach ($npvTemplates as $idx => $tpl) {
            $tplName = !empty($tpl['name']) ? $tpl['name'] : "NPV Tunnel " . ($idx + 1);
            $tplVal = trim($tpl['value'] ?? '');
            if ($tplVal === '') continue;

            if (strpos($tplVal, 'npvt-ssh://') === 0) {
                $b64 = substr($tplVal, strlen('npvt-ssh://'));
                $jsonStr = @base64_decode($b64);
                $jsonData = @json_decode($jsonStr, true);
                if (is_array($jsonData)) {
                    $jsonData['sshUsername'] = $sshUser;
                    $jsonData['sshPassword'] = $sshPass;
                    $jsonData['remarks'] = $displayName . ' (' . $tplName . ')';
                    $newB64 = base64_encode(json_encode($jsonData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    $npvList[] = [
                        'name' => $tplName,
                        'config' => 'npvt-ssh://' . $newB64
                    ];
                    continue;
                }
            }

            $replaced = str_replace(
                ['[user]', '[pass]', '[host]', '[port]', '[name]'],
                [$sshUser, $sshPass, $targetAddress, $targetPort, $displayName],
                $tplVal
            );
            $npvList[] = [
                'name' => $tplName,
                'config' => $replaced
            ];
        }
    }

    if (empty($npvList)) {
        $npvList[] = [
            'name' => 'NPV Tunnel (Direct SSL)',
            'config' => "npvt-ssh://{$sshUser}:{$sshPass}@{$targetAddress}:{$targetPort}#" . urlencode($displayName)
        ];
    }

    // 2. Process NetMod Templates
    $netmodTemplates = json_decode($server['netmod_templates'] ?? '', true) ?: [];
    $netmodList = [];
    if (!empty($netmodTemplates) && is_array($netmodTemplates)) {
        foreach ($netmodTemplates as $idx => $tpl) {
            $tplName = !empty($tpl['name']) ? $tpl['name'] : "NetMod " . ($idx + 1);
            $tplVal = trim($tpl['value'] ?? '');
            if ($tplVal === '') continue;

            if (strpos($tplVal, '[user]') !== false || strpos($tplVal, '[pass]') !== false) {
                $replaced = str_replace(
                    ['[user]', '[pass]', '[host]', '[port]', '[name]'],
                    [$sshUser, $sshPass, $targetAddress, $targetPort, $displayName],
                    $tplVal
                );
            } elseif (preg_match('/^([^@]+):([^@]+)@([^:]+):(.*)$/', $tplVal, $m)) {
                $replaced = "{$m[1]}:{$m[2]}@{$sshUser}:{$sshPass}";
            } else {
                $replaced = str_replace(
                    ['[user]', '[pass]', '[host]', '[port]', '[name]'],
                    [$sshUser, $sshPass, $targetAddress, $targetPort, $displayName],
                    $tplVal
                );
            }

            $netmodList[] = [
                'name' => $tplName,
                'config' => $replaced
            ];
        }
    }

    if (empty($netmodList)) {
        $netmodList[] = [
            'name' => 'NetMod Websocket',
            'config' => "{$targetAddress}:{$targetPort}@{$sshUser}:{$sshPass}"
        ];
    }

    return [
        'raw' => $rawText,
        'npv' => $npvList,
        'netmod' => $netmodList
    ];
}

function send_discord_webhook($event, $embed) {
    try {
        $db = get_db();
        $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "webhooks"');
        $stmt->execute();
        $raw = $stmt->fetchColumn();
        if (!$raw) return false;
        $webhooks = json_decode($raw, true);
        if (!is_array($webhooks) || empty($webhooks[$event])) return false;

        $url = trim($webhooks[$event]);
        if (!filter_var($url, FILTER_VALIDATE_URL)) return false;

        $payload = json_encode([
            'username' => 'EKROM Shop Alert',
            'embeds' => [$embed]
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        curl_exec($ch);
        curl_close($ch);
        return true;
    } catch (\Throwable $t) {
        return false;
    }
}

function send_system_error_alert($title, $message, $metadata = []) {
    $fields = [];
    foreach ($metadata as $k => $v) {
        $fields[] = [
            'name' => (string)$k,
            'value' => (string)$v,
            'inline' => true
        ];
    }
    $fields[] = [
        'name' => 'เวลา',
        'value' => date('Y-m-d H:i:s'),
        'inline' => false
    ];

    $embed = [
        'title' => '🚨 [แจ้งเตือนระบบผิดพลาด] ' . $title,
        'description' => $message,
        'color' => 0xef4444,
        'fields' => $fields,
        'footer' => [
            'text' => 'EKROM Shop System Monitor'
        ]
    ];

    try {
        $db = get_db();
        $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "webhooks"');
        $stmt->execute();
        $raw = $stmt->fetchColumn();
        if (!$raw) return false;
        $webhooks = json_decode($raw, true);
        if (!is_array($webhooks)) return false;

        $targetUrl = $webhooks['error'] ?? $webhooks['alert'] ?? $webhooks['buy'] ?? $webhooks['topup'] ?? null;
        if (!$targetUrl || !filter_var($targetUrl, FILTER_VALIDATE_URL)) return false;

        $payload = json_encode([
            'username' => 'EKROM System Alert',
            'embeds' => [$embed]
        ], JSON_UNESCAPED_UNICODE);

        $ch = curl_init($targetUrl);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 2,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        curl_exec($ch);
        curl_close($ch);
        return true;
    } catch (\Throwable $t) {
        return false;
    }
}

function get_turnstile_settings() {
    static $settings = null;
    if ($settings !== null) return $settings;
    $defaults = [
        'enabled' => 0,
        'site_key' => '',
        'secret_key' => ''
    ];
    try {
        $db = get_db();
        $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "turnstile_settings"');
        $stmt->execute();
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $dec = json_decode($raw, true);
            if (is_array($dec)) {
                $settings = array_merge($defaults, $dec);
                return $settings;
            }
        }
    } catch (\Throwable $t) {}
    $settings = $defaults;
    return $settings;
}

function verify_turnstile($token, $remoteIp = null) {
    $settings = get_turnstile_settings();
    if (empty($settings['enabled']) || empty($settings['site_key']) || empty($settings['secret_key'])) {
        return true;
    }
    if (empty($token) || $token === 'dev_token') {
        return false;
    }
    $secret = $settings['secret_key'];
    $postData = [
        'secret' => $secret,
        'response' => $token
    ];
    if ($remoteIp) {
        $ips = explode(',', $remoteIp);
        $cleanIp = trim($ips[0]);
        if (filter_var($cleanIp, FILTER_VALIDATE_IP)) {
            $postData['remoteip'] = $cleanIp;
        }
    }
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    $res = curl_exec($ch);
    curl_close($ch);
    if (!$res) return false;
    $json = json_decode($res, true);
    return !empty($json['success']);
}

function get_contact_settings() {
    static $settings = null;
    if ($settings !== null) return $settings;
    $defaults = [
        'work_hours' => '09:00 - 21:00 น.',
        'work_days' => 'เปิดบริการทุกวัน (จันทร์ - อาทิตย์)',
        'work_status' => 'online',
        'line_oa_id' => '',
        'line_oa_url' => '',
        'line_oa_name' => 'LINE Official Account',
        'line_personal_id' => '',
        'line_personal_url' => '',
        'line_personal_name' => 'LINE ส่วนตัวแอดมิน',
        'line_group_url' => '',
        'line_group_name' => 'กลุ่มพูดคุย แจ้งปัญหา',
        'line_group_desc' => 'กลุ่มพูดคุย แจ้งปัญหา และรับอัปเดตใหม่ล่าสุด',
        'facebook_page_url' => '',
        'facebook_page_name' => 'Facebook Fanpage',
        'messenger_group_url' => '',
        'messenger_group_name' => 'กลุ่มแชท Messenger',
        'contact_note' => 'หากทักแชทนอกเวลาทำการ แอดมินจะรีบตอบกลับให้เร็วที่สุดในเวลาทำการครับ'
    ];
    try {
        $db = get_db();
        $stmt = $db->prepare('SELECT value FROM system_settings WHERE key = "contact_settings"');
        $stmt->execute();
        $raw = $stmt->fetchColumn();
        if ($raw) {
            $dec = json_decode($raw, true);
            if (is_array($dec)) {
                $settings = array_merge($defaults, $dec);
                return $settings;
            }
        }
    } catch (\Throwable $t) {}
    $settings = $defaults;
    return $settings;
}

/**
 * ล้างไฟล์ VPN ที่หมดอายุเกินกำหนด (ค่าเริ่มต้น 3 วัน) ทั้งในระบบเว็บช็อปและเว็บ X-UI / VPS
 *
 * @param int $days จำนวนวันหลังหมดอายุ (default = 3)
 * @return array ข้อมูลสรุปการลบ ['count' => จำนวนไฟล์ที่ลบ, 'details' => [...]]
 */
function cleanup_expired_vpns($days = 3) {
    $db = get_db();
    $days = max(1, (int)$days);
    $timeLimit = date('Y-m-d H:i:s', strtotime("-{$days} days"));

    // ค้นหาไฟล์ที่หมดอายุเกิน $days วัน และยังไม่ได้ถูกลบ
    $stmt = $db->prepare("
        SELECT id, server_id, xui_email, uuid, ssh_user, protocol, server_name, user_id
        FROM vpn_configs 
        WHERE expiry_time < ? AND status_real != 'deleted'
    ");
    $stmt->execute([$timeLimit]);
    $expired = $stmt->fetchAll();
    $count = count($expired);
    $deletedDetails = [];

    foreach ($expired as $row) {
        $server = null;
        if (!empty($row['server_id'])) {
            $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
            $sStmt->execute([$row['server_id']]);
            $server = $sStmt->fetch();
        }

        // 1. ลบจาก X-UI (3x-ui)
        if ($server && !empty($row['xui_email']) && !empty($server['panel_url'])) {
            try {
                xui_delete_client($server, $row['xui_email'], $row['uuid'] ?? null);
            } catch (\Throwable $e) {
                error_log("Failed to delete xui client: " . $e->getMessage());
            }
        }

        // 2. ลบจาก SSH VPS (กรณีเป็น SSH)
        if ($server && ($server['type'] === 'ssh_script' || $server['type'] === 'udp_custom' || $row['protocol'] === 'ssh') && !empty($row['ssh_user'])) {
            try {
                ssh_vps_delete_user($server, $row['ssh_user']);
            } catch (\Throwable $e) {
                error_log("Failed to delete ssh user: " . $e->getMessage());
            }
        }

        // 3. ปรับสถานะในฐานข้อมูลเป็น 'deleted' (ทำให้ไม่แสดงในหน้าร้านค้าของลูกค้า)
        $db->prepare("UPDATE vpn_configs SET status_real = 'deleted' WHERE id = ?")->execute([$row['id']]);

        // 4. ลดจำนวนผู้ใช้งานในเซิร์ฟเวอร์
        if (!empty($row['server_id'])) {
            $db->prepare('UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id = ?')->execute([$row['server_id']]);
        }

        $deletedDetails[] = [
            'id' => $row['id'],
            'server_id' => $row['server_id'],
            'xui_email' => $row['xui_email'] ?? '',
            'ssh_user' => $row['ssh_user'] ?? ''
        ];
    }

    return [
        'count' => $count,
        'details' => $deletedDetails
    ];
}

/**
 * สั่งซื้อและสร้างบัญชี VPN / SSH อัตโนมัติ (ใช้ร่วมกันทั้งหน้าเว็บและ LINE Bot)
 *
 * @param array|int $user ข้อมูลผู้ใช้ หรือ user_id
 * @param int $serverId รหัสเซิร์ฟเวอร์
 * @param string $packageVal แพ็กเกจ ('trial', '1', '7', '15', '30')
 * @param string $customName ชื่อกำกับไฟล์ (ถ้ามี)
 * @param string $sshUser บัญชี SSH (ถ้ามี)
 * @param string $sshPass รหัสผ่าน SSH (ถ้ามี)
 * @param int $trialDuration ระยะเวลาทดลองใช้งาน (นาที)
 * @return array ผลลัพธ์การสร้างไฟล์
 */
function process_vpn_creation($user, $serverId, $packageVal = '30', $customName = '', $sshUser = '', $sshPass = '', $trialDuration = 60) {
    $db = get_db();
    if (is_numeric($user)) {
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([(int)$user]);
        $user = $stmt->fetch();
    }
    if (!$user) {
        return ['status' => 'error', 'message' => 'ไม่พบข้อมูลผู้ใช้งาน'];
    }

    $serverId = (int)$serverId;
    $stmt = $db->prepare('SELECT * FROM servers WHERE id = ? AND is_active = 1');
    $stmt->execute([$serverId]);
    $server = $stmt->fetch();

    if (!$server) {
        return ['status' => 'error', 'message' => 'ไม่พบเซิร์ฟเวอร์ที่เลือก หรือเซิร์ฟเวอร์ปิดปรับปรุง'];
    }

    // Check Tier Prices
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

    // Calculate Price and Duration
    $price = 0.00;
    $days = 0;
    $packageName = '';

    if ($packageVal === 'trial') {
        if ($trialDuration < 1 || $trialDuration > 1440) $trialDuration = 60;
        $price = 0.00;
        $days = 0;
        $packageName = 'ทดลองใช้งาน ' . $trialDuration . ' นาที';
    } elseif ($packageVal === '1') {
        $price = (float)$tierPrices[0];
        $days = 1;
        $packageName = 'แพ็กเกจ 1 วัน';
    } elseif ($packageVal === '7') {
        $price = (float)$tierPrices[1];
        $days = 7;
        $packageName = 'แพ็กเกจ 7 วัน';
    } elseif ($packageVal === '15') {
        $price = (float)$tierPrices[2];
        $days = 15;
        $packageName = 'แพ็กเกจ 15 วัน';
    } else {
        // 30 days default
        $packageVal = '30';
        $price = (float)$tierPrices[3];
        $days = 30;
        $packageName = 'แพ็กเกจ 30 วัน';
    }

    $isReseller = (isset($user['role']) && $user['role'] === 'reseller');
    $discountText = '';
    if ($isReseller && $packageVal !== 'trial' && $price > 0) {
        $price = round($price * 0.70, 2);
        $discountText = ' [ส่วนลดตัวแทน 30%]';
    }

    if ((float)$user['balance'] < $price) {
        return [
            'status' => 'error',
            'message' => 'ยอดเงินคงเหลือไม่พอ (ขาดอีก ฿' . number_format($price - (float)$user['balance'], 2) . ') กรุณาเติมเงิน'
        ];
    }

    // Generate UUID
    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff), mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    // Expiry
    if ($packageVal === 'trial') {
        $expiryTime = date('Y-m-d H:i:s', strtotime("+{$trialDuration} minutes"));
    } else {
        $expiryTime = date('Y-m-d 23:59:59', strtotime("+{$days} days"));
    }

    $displayName = build_vpn_display_name($server['name'], $expiryTime, $customName);

    $isSsh = ($server['type'] === 'ssh_script' || $server['type'] === 'udp_custom');
    $isXui = (!empty($server['panel_url']) && !empty($server['password']) && !$isSsh);
    $xuiEmail = null;
    $configLink = '';

    // Generate Config Link
    if ($isXui) {
        $xuiEmail = xui_make_client_email($displayName);
        $xuiRes = xui_add_client($server, $uuid, $xuiEmail, $expiryTime, $displayName);
        if (!$xuiRes['success']) {
            send_system_error_alert('เชื่อมต่อ X-UI ล้มเหลว', "ไม่สามารถสร้างบัญชี VPN บน X-UI ได้: {$server['name']}", [
                'เซิร์ฟเวอร์' => $server['name'],
                'ข้อความ Error' => $xuiRes['message'] ?? 'Unknown error',
                'ผู้ซื้อ' => $user['username']
            ]);
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์เพื่อสร้างบัญชีได้: ' . ($xuiRes['message'] ?? 'เกิดข้อผิดพลาด')
            ];
        }
        $xuiEmail = $xuiRes['email'];
        $configLink = $xuiRes['config_link'];
    } elseif ($isSsh) {
        if ($sshUser === '') $sshUser = 'u' . strtolower(bin2hex(random_bytes(3)));
        if ($sshPass === '') $sshPass = (string)rand(100000, 999999);

        $sshDays = max(1, (int)round((strtotime($expiryTime) - time()) / 86400));

        $sshRes = ssh_vps_add_user($server, $sshUser, $sshPass, $sshDays);
        if (!$sshRes['success']) {
            send_system_error_alert('เชื่อมต่อ SSH VPS ล้มเหลว', "ไม่สามารถสร้างบัญชี SSH บนเซิร์ฟเวอร์ได้: {$server['name']}", [
                'เซิร์ฟเวอร์' => $server['name'],
                'ข้อความ Error' => $sshRes['message'] ?? 'Unknown error',
                'ผู้ซื้อ' => $user['username']
            ]);
            return [
                'status' => 'error',
                'message' => 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ VPS เพื่อสร้างบัญชี SSH ได้: ' . ($sshRes['message'] ?? 'เกิดข้อผิดพลาด')
            ];
        }
        
        $sshPayload = build_ssh_config_payload($server, $sshUser, $sshPass, $displayName);
        $configLink = json_encode($sshPayload, JSON_UNESCAPED_UNICODE);
    } else {
        // V2Ray / VLESS Reality fallback
        $protocol = $server['protocol'] ?: 'vless';
        $serverType = $server['type'] ?? '';
        $targetAddress = !empty($server['domain']) ? trim($server['domain']) : (!empty($server['host']) ? trim($server['host']) : '127.0.0.1');
        $targetPort = (int)($server['port'] ?: 443);
        $gamingPort = (!empty($server['vless_port']) && (int)$server['vless_port'] > 0) ? (int)$server['vless_port'] : $targetPort;
        $sni = !empty($server['bug_host']) ? trim($server['bug_host']) : 'speedtest.net';
        $pbkParam = !empty($server['pbk']) ? '&pbk=' . urlencode($server['pbk']) : '';
        $sidParam = !empty($server['sids']) ? '&sid=' . urlencode(explode(',', $server['sids'])[0]) : '';

        if ($protocol === 'vmess') {
            $tlsVal = ($serverType === 'vmess_tls') ? 'tls' : 'none';
            $vPort = ($serverType === 'vmess_tls') ? $gamingPort : $targetPort;
            $vmessObj = [
                'v' => '2',
                'ps' => $displayName,
                'add' => $targetAddress,
                'port' => $vPort,
                'id' => $uuid,
                'aid' => 0,
                'scy' => 'auto',
                'net' => ($serverType === 'vmess_tls') ? 'tcp' : 'ws',
                'type' => 'none',
                'host' => ($serverType === 'vmess_tls') ? '' : $sni,
                'path' => ($serverType === 'vmess_tls') ? '' : '/',
                'tls' => $tlsVal
            ];
            if ($tlsVal !== 'none') {
                $vmessObj['sni'] = $sni;
                $vmessObj['fp'] = 'chrome';
                $vmessObj['alpn'] = 'h2,http/1.1';
                $vmessObj['allowInsecure'] = '1';
                $vmessObj['insecure'] = '1';
            }
            $configLink = 'vmess://' . base64_encode(json_encode($vmessObj, JSON_UNESCAPED_UNICODE));
        } else {
            $vPort = ($serverType === 'vless_tls' || !empty($server['vless_port'])) ? $gamingPort : $targetPort;
            if (!empty($server['pbk'])) {
                $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$vPort}?type=grpc&encryption=none&security=reality&sni={$sni}&fp=chrome&serviceName=grpc{$pbkParam}{$sidParam}#" . rawurlencode($displayName);
            } elseif ($serverType === 'vless_tls') {
                $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$vPort}?type=tcp&encryption=none&security=tls&sni={$sni}&fp=chrome&alpn=h2,http/1.1&allowInsecure=1&insecure=1#" . rawurlencode($displayName);
            } else {
                $hostParam = !empty($sni) ? "&host=" . urlencode($sni) : '';
                $configLink = "{$protocol}://{$uuid}@{$targetAddress}:{$vPort}?type=ws&encryption=none&path=/&security=none{$hostParam}#" . rawurlencode($displayName);
            }
        }
    }

    // Deduct balance
    $db->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$price, $user['id']]);

    // Insert VPN Config
    $actualProtocol = !empty($xuiRes['protocol']) ? $xuiRes['protocol'] : ($server['protocol'] ?: ($isXui ? 'vmess' : 'vless'));
    $nowStr = date('Y-m-d H:i:s');
    $stmt = $db->prepare("
        INSERT INTO vpn_configs (user_id, server_id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, ssh_user, ssh_pass, status_real, created_at, expiry_time, xui_email)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, ?)
    ");
    $stmt->execute([
        $user['id'], $serverId, $uuid, $displayName, $packageName, $packageVal, $price,
        $actualProtocol, $configLink, $sshUser, $sshPass, $nowStr, $expiryTime, $xuiEmail
    ]);
    $newConfigId = (int)$db->lastInsertId();

    // Log order
    $orderDesc = 'ซื้อ ' . $server['name'] . ' (' . $packageName . ')' . $discountText;
    $db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "buy", ?, ?, ?)')
       ->execute([$user['id'], $price, $orderDesc, $nowStr]);

    // Increase user count
    $db->prepare('UPDATE servers SET user_count = user_count + 1 WHERE id = ?')->execute([$serverId]);

    // Discord Webhook
    $priceWebhook = '฿' . number_format($price, 2) . ($isReseller && $packageVal !== 'trial' ? ' (ลด 30% ตัวแทน)' : '');
    send_discord_webhook('buy', [
        'title' => '🛒 มีการสั่งซื้อ VPN ใหม่!' . ($isReseller ? ' [ตัวแทนจำหน่าย]' : ''),
        'color' => 0xdb2777,
        'fields' => [
            ['name' => 'ผู้ซื้อ', 'value' => $user['username'] . ($isReseller ? ' (Reseller)' : ''), 'inline' => true],
            ['name' => 'เซิร์ฟเวอร์', 'value' => $server['name'], 'inline' => true],
            ['name' => 'แพ็กเกจ', 'value' => $packageName, 'inline' => true],
            ['name' => 'ราคา', 'value' => $priceWebhook, 'inline' => true],
            ['name' => 'หมดอายุ', 'value' => $expiryTime, 'inline' => true],
            ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
        ]
    ]);

    $newBalance = round((float)$user['balance'] - $price, 2);
    $successMsg = 'สั่งซื้อและสร้างไฟล์ VPN สำเร็จเรียบร้อยแล้ว! 🎉' . ($isReseller && $packageVal !== 'trial' ? ' (หัก ฿' . number_format($price, 2) . ' ลด 30% ตัวแทน)' : '');

    return [
        'status' => 'success',
        'message' => $successMsg,
        'config_id' => $newConfigId,
        'server_name' => $server['name'],
        'display_name' => $displayName,
        'package_name' => $packageName,
        'package_val' => $packageVal,
        'price' => $price,
        'expiry_time' => $expiryTime,
        'protocol' => $actualProtocol,
        'config_link' => $configLink,
        'uuid' => $uuid,
        'ssh_user' => $sshUser,
        'ssh_pass' => $sshPass,
        'new_balance' => $newBalance
    ];
}

/**
 * Process VPN Renewal atomically across DB, X-UI / SSH VPS, and Discord logs
 */
function process_vpn_renewal($user, $configId, $days) {
    $db = get_db();
    if (is_numeric($user)) {
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([(int)$user]);
        $user = $stmt->fetch();
    }
    if (!$user) {
        return ['status' => 'error', 'message' => 'ไม่พบข้อมูลผู้ใช้งาน'];
    }

    $configId = (int)$configId;
    $days = (int)$days;
    if ($configId <= 0 || $days <= 0) {
        return ['status' => 'error', 'message' => 'ข้อมูลการต่ออายุไม่ถูกต้อง'];
    }

    $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ?');
    $stmt->execute([$configId, $user['id']]);
    $vpn = $stmt->fetch();

    if (!$vpn) {
        return ['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN ในระบบ หรือไฟล์นี้ไม่ได้เป็นของคุณ'];
    }

    $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
    $sStmt->execute([$vpn['server_id']]);
    $server = $sStmt->fetch();

    // Check tier prices if available
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

    // Determine base renew price based on days
    if ($days === 1) {
        $basePrice = (float)$tierPrices[0];
    } elseif ($days === 7) {
        $basePrice = (float)$tierPrices[1];
    } elseif ($days === 15) {
        $basePrice = (float)$tierPrices[2];
    } elseif ($days === 30) {
        $basePrice = (float)$tierPrices[3];
    } else {
        $basePrice = max(5.00, round($days * 2.50, 2));
    }

    $isReseller = (isset($user['role']) && $user['role'] === 'reseller');
    $renewPrice = $basePrice;
    $discountText = '';

    if ($isReseller && $renewPrice > 0) {
        $renewPrice = round($basePrice * 0.70, 2);
        $discountText = ' [ส่วนลดตัวแทน 30%]';
    }

    if ((float)$user['balance'] < $renewPrice) {
        return [
            'status' => 'error',
            'message' => 'ยอดเงินคงเหลือไม่พอสำหรับการต่ออายุ ' . $days . ' วัน (ต้องใช้ ฿' . number_format($renewPrice, 2) . ' / ขาดอีก ฿' . number_format($renewPrice - (float)$user['balance'], 2) . ') กรุณาเติมเงิน'
        ];
    }

    // Calculate new expiry
    $currentExpiry = strtotime($vpn['expiry_time']);
    $baseTime = ($currentExpiry > time()) ? $currentExpiry : time();
    $newExpiry = date('Y-m-d 23:59:59', strtotime("+{$days} days", $baseTime));

    // Deduct balance
    $db->prepare('UPDATE users SET balance = balance - ? WHERE id = ?')->execute([$renewPrice, $user['id']]);

    $newDisplayName = format_vpn_config_name($vpn['server_name'], $newExpiry);
    $newConfigLink = update_config_link_remark($vpn['config_link'], $newDisplayName, $vpn['protocol']);

    // Update VPN
    $newPkgVal = is_numeric($vpn['package_val']) ? (string)((int)$vpn['package_val'] + $days) : (string)$days;
    $db->prepare('UPDATE vpn_configs SET server_name = ?, config_link = ?, expiry_time = ?, price_paid = price_paid + ?, package_val = ?, status_real = "active" WHERE id = ?')
       ->execute([$newDisplayName, $newConfigLink, $newExpiry, $renewPrice, $newPkgVal, $configId]);

    // Update remote panel/server
    if ($server) {
        if (!empty($vpn['xui_email']) && !empty($server['panel_url'])) {
            require_once __DIR__ . '/xui.php';
            $newXuiEmail = xui_make_client_email($newDisplayName);
            $updRes = xui_update_client($server, $vpn['uuid'], $vpn['xui_email'], $newExpiry, $newXuiEmail);
            if ($updRes && !empty($updRes['email'])) {
                $db->prepare('UPDATE vpn_configs SET xui_email = ? WHERE id = ?')->execute([$updRes['email'], $configId]);
            } elseif ($updRes && empty($updRes['success'])) {
                send_system_error_alert('ต่ออายุบน X-UI ไม่สำเร็จ', "ไม่สามารถอัปเดตวันหมดอายุบน X-UI ได้: {$server['name']}", [
                    'เซิร์ฟเวอร์' => $server['name'],
                    'UUID' => $vpn['uuid'],
                    'ผู้ใช้' => $user['username']
                ]);
            }
        } elseif (in_array($server['type'], ['ssh_script', 'udp_custom'], true) && !empty($vpn['ssh_user'])) {
            require_once __DIR__ . '/ssh_vps.php';
            $daysRemaining = max(1, (int)round((strtotime($newExpiry) - time()) / 86400));
            ssh_vps_renew_user($server, $vpn['ssh_user'], $daysRemaining);
        }
    }

    // Log order
    $orderDesc = 'ต่ออายุ ' . $vpn['server_name'] . ' +' . $days . ' วัน' . $discountText;
    $db->prepare('INSERT INTO orders_history (user_id, type, amount, description, created_at) VALUES (?, "renew", ?, ?, ?)')
       ->execute([$user['id'], $renewPrice, $orderDesc, date('Y-m-d H:i:s')]);

    // Discord Webhook
    $priceWebhook = '฿' . number_format($renewPrice, 2) . ($isReseller ? ' (ลด 30% ตัวแทน)' : '');
    send_discord_webhook('renew', [
        'title' => '♻️ มีการต่ออายุ VPN!' . ($isReseller ? ' [ตัวแทนจำหน่าย]' : ''),
        'color' => 0x8b5cf6,
        'fields' => [
            ['name' => 'ผู้ใช้งาน', 'value' => ($user['line_display_name'] ?: $user['username']) . ($isReseller ? ' (Reseller)' : ''), 'inline' => true],
            ['name' => 'เซิร์ฟเวอร์', 'value' => $vpn['server_name'], 'inline' => true],
            ['name' => 'จำนวนวัน', 'value' => "+{$days} วัน", 'inline' => true],
            ['name' => 'ยอดเงิน', 'value' => $priceWebhook, 'inline' => true],
            ['name' => 'หมดอายุใหม่', 'value' => $newExpiry, 'inline' => true],
            ['name' => 'เวลา', 'value' => date('Y-m-d H:i:s'), 'inline' => false]
        ]
    ]);

    $newBalStmt = $db->prepare('SELECT balance FROM users WHERE id = ?');
    $newBalStmt->execute([$user['id']]);
    $newBalance = (float)$newBalStmt->fetchColumn();

    $successMsg = "ต่ออายุสำเร็จ เพิ่มเวลาใช้งาน {$days} วัน เรียบร้อยแล้ว!" . ($isReseller ? " (หัก ฿" . number_format($renewPrice, 2) . " ลด 30% ตัวแทน)" : "");

    return [
        'status' => 'success',
        'message' => $successMsg,
        'config_id' => $configId,
        'server_name' => $vpn['server_name'],
        'display_name' => $newDisplayName,
        'days' => $days,
        'price' => $renewPrice,
        'new_expiry' => $newExpiry,
        'config_link' => $newConfigLink,
        'new_balance' => $newBalance,
        'ssh_user' => $vpn['ssh_user'] ?? '',
        'ssh_pass' => $vpn['ssh_pass'] ?? '',
        'protocol' => $vpn['protocol'] ?? ($server['protocol'] ?? 'vless')
    ];
}

/**
 * เปลี่ยนชื่อ Display Name / Custom Name ของ VPN Config
 */
function process_vpn_rename($user, $configId, $newCustomName) {
    global $db;
    $db = get_db();
    if (is_numeric($user)) {
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([(int)$user]);
        $user = $stmt->fetch();
    }
    if (!$user || !isset($user['id'])) {
        return ['status' => 'error', 'message' => 'User not authenticated'];
    }

    $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ?');
    $stmt->execute([$configId, $user['id']]);
    $vpn = $stmt->fetch();

    if (!$vpn) {
        return ['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN ในระบบ หรือไฟล์นี้ไม่ได้เป็นของคุณ'];
    }

    $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
    $sStmt->execute([$vpn['server_id']]);
    $server = $sStmt->fetch();

    $cleanCustom = trim((string)$newCustomName, "()[] \t\n\r\0\x0B");

    // Extract base server name from server table, or strip existing custom name / expiry from vpn['server_name']
    $serverBaseName = $server ? $server['name'] : $vpn['server_name'];
    // Strip expiry from serverBaseName
    $serverBaseName = preg_replace('/\s*(?:[\(\[](?:หมดอายุ|EXP).*?[\)\]]|\|\s*(?:หมดอายุ|EXP)\s*\|.*$)/iu', '', trim((string)$serverBaseName));
    // If serverBaseName has leading [something] or (something), and $server was not found, we can strip it
    if (!$server && preg_match('/^\[.*?\]\s*(.*)$/u', $serverBaseName, $m)) {
        $serverBaseName = $m[1];
    }

    if ($cleanCustom !== '') {
        $baseName = "{$cleanCustom} {$serverBaseName}";
    } else {
        $baseName = $serverBaseName;
    }

    $newDisplayName = format_vpn_config_name($baseName, $vpn['expiry_time']);
    $newConfigLink = update_config_link_remark($vpn['config_link'], $newDisplayName, $vpn['protocol']);

    // Update vpn_configs in database
    $db->prepare('UPDATE vpn_configs SET server_name = ?, config_link = ? WHERE id = ?')
       ->execute([$newDisplayName, $newConfigLink, $configId]);

    // If X-UI, update client email / remark if needed
    if ($server && !empty($vpn['xui_email']) && !empty($server['panel_url'])) {
        require_once __DIR__ . '/xui.php';
        $newXuiEmail = xui_make_client_email($newDisplayName);
        $updRes = xui_update_client($server, $vpn['uuid'], $vpn['xui_email'], $vpn['expiry_time'], $newXuiEmail);
        if ($updRes && !empty($updRes['email'])) {
            $db->prepare('UPDATE vpn_configs SET xui_email = ? WHERE id = ?')->execute([$updRes['email'], $configId]);
        }
    }

    return [
        'status' => 'success',
        'message' => 'เปลี่ยนชื่อไฟล์เรียบร้อยแล้ว!',
        'config_id' => $configId,
        'old_name' => $vpn['server_name'],
        'display_name' => $newDisplayName,
        'custom_name' => $cleanCustom,
        'config_link' => $newConfigLink,
        'expiry_time' => $vpn['expiry_time'],
        'protocol' => $vpn['protocol'] ?? ($server['protocol'] ?? 'vless'),
        'ssh_user' => $vpn['ssh_user'] ?? '',
        'ssh_pass' => $vpn['ssh_pass'] ?? ''
    ];
}

/**
 * ลบไฟล์ VPN และคืนเงินตามเงื่อนไข (100% ภายใน 10 นาที หรือคืนเงินตามชั่วโมงคงเหลือ)
 * @param array|int $user
 * @param int $configId
 * @param string $action 'preview' หรือ 'delete'
 * @return array
 */
function process_vpn_deletion($user, $configId, $action = 'delete') {
    global $db;
    $db = get_db();
    if (is_numeric($user)) {
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([(int)$user]);
        $user = $stmt->fetch();
    }
    if (!$user || !isset($user['id'])) {
        return ['status' => 'error', 'message' => 'User not authenticated'];
    }

    $configId = (int)$configId;
    $stmt = $db->prepare('SELECT * FROM vpn_configs WHERE id = ? AND user_id = ?');
    $stmt->execute([$configId, $user['id']]);
    $vpn = $stmt->fetch();

    if (!$vpn) {
        return ['status' => 'error', 'message' => 'ไม่พบไฟล์ VPN ที่ต้องการลบ'];
    }

    if ($vpn['status_real'] === 'deleted') {
        return ['status' => 'error', 'message' => 'ไฟล์ VPN นี้ถูกลบไปแล้ว'];
    }

    $now = time();
    $createdTime = strtotime($vpn['created_at']);
    $expiryTime = strtotime($vpn['expiry_time']);
    $pricePaid = (float)($vpn['price_paid'] ?? 0.00);

    // ตรวจสอบและแก้ไข Timezone กรณีข้อมูลเก่าถูกบันทึก created_at เป็น UTC
    $packageDays = is_numeric($vpn['package_val']) ? (int)$vpn['package_val'] : 0;
    if ($packageDays > 0) {
        $expectedSec = $packageDays * 86400;
        $actualSec = $expiryTime - $createdTime;
        if (abs($actualSec - ($expectedSec + 7 * 3600)) < 600) {
            $createdTime += 7 * 3600;
        }
    }

    $elapsedSeconds = max(0, $now - $createdTime);

    if ($packageDays > 0) {
        $totalHours = $packageDays * 24;
    } else {
        $totalDurationSeconds = max(3600, $expiryTime - $createdTime);
        $totalHours = max(1, (int)round($totalDurationSeconds / 3600));
    }

    $hourlyRate = ($totalHours > 0 && $pricePaid > 0) ? ($pricePaid / $totalHours) : 0.0;
    $isExpired = ($now >= $expiryTime);
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
        $usedHours = (int)ceil($elapsedSeconds / 3600);
        $usedHours = min($totalHours, max(1, $usedHours));
        $remainingHours = max(0, $totalHours - $usedHours);
        $refundAmount = round($remainingHours * $hourlyRate, 2);
        $refundAmount = min($pricePaid, max(0.00, $refundAmount));
    }

    if ($action === 'preview') {
        return [
            'status' => 'success',
            'preview' => true,
            'config' => $vpn,
            'is_grace' => $isGracePeriod,
            'is_expired' => $isExpired,
            'price_paid' => $pricePaid,
            'hourly_rate' => $hourlyRate,
            'total_hours' => $totalHours,
            'used_hours' => $usedHours,
            'remaining_hours' => $remainingHours,
            'refund_amount' => $refundAmount
        ];
    }

    // Process deletion atomically
    $db->beginTransaction();

    $delStmt = $db->prepare("UPDATE vpn_configs SET status_real = 'deleted' WHERE id = ? AND user_id = ? AND status_real != 'deleted'");
    $delStmt->execute([$configId, $user['id']]);

    if ($delStmt->rowCount() === 0) {
        $db->rollBack();
        return ['status' => 'error', 'message' => 'ไฟล์ VPN นี้ถูกลบไปแล้ว'];
    }

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

    $db->prepare('UPDATE servers SET user_count = MAX(0, user_count - 1) WHERE id = ?')->execute([$vpn['server_id']]);

    $db->commit();

    // ลบ client จาก X-UI หรือ VPS SSH
    $sStmt = $db->prepare('SELECT * FROM servers WHERE id = ?');
    $sStmt->execute([$vpn['server_id']]);
    $server = $sStmt->fetch();

    if ($server) {
        if (!empty($vpn['xui_email']) && !empty($server['panel_url'])) {
            require_once __DIR__ . '/xui.php';
            xui_delete_client($server, $vpn['xui_email'], $vpn['uuid'] ?? null);
        }
        if (($server['type'] === 'ssh_script' || $server['type'] === 'udp_custom') && !empty($vpn['ssh_user'])) {
            ssh_vps_delete_user($server, $vpn['ssh_user']);
        }
    }

    // Discord Webhook
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
            $message = 'ลบไฟล์เรียบร้อยแล้ว! คืนเงิน ฿' . number_format($refundAmount, 2) . ' (เต็มจำนวน 100%) เข้ากระเป๋าของคุณอัตโนมัติ';
        } else {
            $message = sprintf(
                'ลบไฟล์เรียบร้อยแล้ว! ใช้งานไป %d ชม. คืนเงินชั่วโมงคงเหลือ ฿%.2f (%d ชม.) เข้ากระเป๋าของคุณอัตโนมัติ',
                $usedHours,
                $refundAmount,
                $remainingHours
            );
        }
    } else {
        $message = 'ลบไฟล์เรียบร้อยแล้ว';
    }

    // Get updated balance
    $uStmt = $db->prepare('SELECT balance FROM users WHERE id = ?');
    $uStmt->execute([$user['id']]);
    $newBalance = (float)$uStmt->fetchColumn();

    return [
        'status' => 'success',
        'message' => $message,
        'config_id' => $configId,
        'server_name' => $vpn['server_name'],
        'refund_amount' => $refundAmount,
        'new_balance' => $newBalance,
        'is_grace' => $isGracePeriod
    ];
}
