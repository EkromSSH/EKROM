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
    // ลบส่วน (หมดอายุ ...) หรือ | หมดอายุ |... เดิมออกถ้ามี
    $cleanServer = preg_replace('/\s*(?:[\(\[](?:หมดอายุ|EXP).*?[\)\]]|\|\s*(?:หมดอายุ|EXP)\s*\|.*$)/iu', '', $cleanServer);
    if ($cleanServer === '') {
        $cleanServer = 'VPN';
    }

    $customName = trim((string)$customName);
    if ($customName !== '') {
        // ลบวงเล็บครอบเดิมออกถ้าผู้ใช้พิมพ์วงเล็บมา เช่น (สมมุติ) หรือ [สมมุติ]
        $cleanCustom = trim($customName, "()[] \t\n\r\0\x0B");
        if ($cleanCustom !== '') {
            $baseName = "{$cleanCustom} {$cleanServer}";
        } else {
            $baseName = $cleanServer;
        }
    } else {
        $baseName = $cleanServer;
    }

    return format_vpn_config_name($baseName, $expiryTime);
}

function format_vpn_config_name($baseName, $expiryTime) {
    $cleanName = preg_replace('/\s*(?:[\(\[](?:หมดอายุ|EXP).*?[\)\]]|\|\s*(?:หมดอายุ|EXP)\s*\|.*$)/iu', '', trim((string)$baseName));
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
        'line_oa_id' => '@ekromshop',
        'line_oa_url' => 'https://line.me/R/ti/p/@ekromshop',
        'line_oa_name' => 'LINE Official Account',
        'line_personal_id' => 'ekrom_support',
        'line_personal_url' => 'https://line.me/ti/p/~ekrom_support',
        'line_personal_name' => 'LINE ส่วนตัวแอดมิน',
        'line_group_url' => 'https://line.me/ti/g2/ekrom-community',
        'line_group_name' => 'กลุ่ม LINE OpenChat',
        'line_group_desc' => 'กลุ่มพูดคุย แจ้งปัญหา และรับอัปเดตไฟล์ VPN ใหม่ล่าสุด',
        'facebook_page_url' => 'https://www.facebook.com/share/14Zq7rmBjpS/',
        'facebook_page_name' => 'Facebook Fanpage',
        'messenger_group_url' => 'https://m.me/j/AbbNfkaIlLvGwfKr/?send_source=gc%3Acopy_invite_link_c',
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


