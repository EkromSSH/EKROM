<?php
// api/db.php - Core DB connection & Session handling
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.gc_maxlifetime', 86400 * 30);
    session_start();
}

function get_db() {
    static $db = null;
    if ($db === null) {
        $dbPath = dirname(__DIR__) . '/database.sqlite';
        $db = new PDO('sqlite:' . $dbPath);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

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
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
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
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
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
        return null;
    }
    $stmt = $db->prepare('SELECT id, username, role, balance, admin_pin, created_at FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
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
    return number_format($bytes / 1048576, 2) . ' MB';
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

function verify_turnstile($token, $remoteIp = null) {
    if (empty($token) || $token === 'dev_token') {
        return false;
    }
    $secret = '0x4AAAAAAEGT6vuDV9CHlYrcuD7A2i_HBKY';
    $postData = [
        'secret' => $secret,
        'response' => $token
    ];
    if ($remoteIp) {
        $postData['remoteip'] = $remoteIp;
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
