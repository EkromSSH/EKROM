<?php
// init_db.php - Initializes SQLite database with clean default tables and seed data

$dbFile = __DIR__ . '/database.sqlite';
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// 1. Create tables with full up-to-date schema
$db->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'user',
    balance REAL DEFAULT 0.00,
    admin_pin TEXT DEFAULT '123456',
    line_user_id TEXT DEFAULT NULL,
    line_display_name TEXT DEFAULT NULL,
    line_picture_url TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    color_theme TEXT DEFAULT 'emerald',
    sort_order INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS price_tiers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    color_theme TEXT DEFAULT 'indigo',
    prices TEXT NOT NULL -- JSON array: [1_day, 7_days, 15_days, 30_days]
);

CREATE TABLE IF NOT EXISTS servers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    tier_id INTEGER,
    name TEXT NOT NULL,
    type TEXT DEFAULT 'v2ray', -- 'v2ray' or 'ssh_script'
    icon TEXT DEFAULT '🇹🇭',
    theme TEXT DEFAULT 'emerald',
    host TEXT DEFAULT '127.0.0.1',
    port INTEGER DEFAULT 443,
    protocol TEXT DEFAULT 'vless',
    description TEXT,
    user_count INTEGER DEFAULT 0,
    cpu INTEGER DEFAULT 15,
    is_active INTEGER DEFAULT 1,
    target_customer_price REAL DEFAULT 50.00,
    config_template TEXT,
    panel_url TEXT,
    username TEXT,
    password TEXT,
    inbound_id TEXT,
    domain TEXT,
    bug_host TEXT,
    vless_port INTEGER,
    pbk TEXT,
    sids TEXT,
    ssh_templates TEXT,
    netmod_templates TEXT,
    addon_id TEXT,
    connection_mode TEXT DEFAULT 'legacy',
    ghost_cleanup_enabled INTEGER DEFAULT 1
);

CREATE TABLE IF NOT EXISTS server_addons (
    server_id INTEGER,
    addon_id INTEGER,
    PRIMARY KEY (server_id, addon_id)
);

CREATE TABLE IF NOT EXISTS addons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    carrier TEXT NOT NULL,
    title TEXT NOT NULL,
    theme_color TEXT DEFAULT 'green',
    duration_text TEXT DEFAULT '30 วัน',
    description TEXT,
    price REAL DEFAULT 150.00,
    subscription_codes TEXT,
    subtitle TEXT DEFAULT '',
    badge TEXT DEFAULT 'ไม่จำกัด GB ✅',
    desc_html TEXT DEFAULT '',
    warning TEXT DEFAULT '',
    warning_bg TEXT DEFAULT 'pink',
    extra_html TEXT DEFAULT '',
    price_label TEXT DEFAULT '',
    price_per TEXT DEFAULT '/ 30 วัน'
);

CREATE TABLE IF NOT EXISTS vpn_configs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    server_id INTEGER NOT NULL,
    uuid TEXT UNIQUE NOT NULL,
    server_name TEXT NOT NULL,
    package_name TEXT NOT NULL,
    package_val TEXT NOT NULL,
    price_paid REAL DEFAULT 0.00,
    protocol TEXT DEFAULT 'vless',
    config_link TEXT NOT NULL,
    ssh_user TEXT,
    ssh_pass TEXT,
    upload_bytes INTEGER DEFAULT 0,
    download_bytes INTEGER DEFAULT 0,
    status_real TEXT DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expiry_time DATETIME NOT NULL,
    xui_email TEXT DEFAULT NULL
);

CREATE TABLE IF NOT EXISTS topup_orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id TEXT NOT NULL UNIQUE,
    user_id INTEGER NOT NULL,
    amount REAL NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    promptpay_number TEXT NOT NULL,
    promptpay_name TEXT,
    qr_payload TEXT,
    slip_path TEXT,
    trans_ref TEXT,
    slip_data TEXT,
    fail_reason TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    paid_at DATETIME,
    FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS topup_transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    method TEXT NOT NULL,
    amount REAL NOT NULL,
    voucher_code TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders_history (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    type TEXT NOT NULL,
    amount REAL NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS announcements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    type TEXT DEFAULT 'info',
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS system_warnings (
    id INTEGER PRIMARY KEY,
    v2ray_warning TEXT,
    ssh_warning TEXT
);

CREATE TABLE IF NOT EXISTS tenant_shops (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    domain TEXT NOT NULL,
    owner_username TEXT NOT NULL,
    package_tier TEXT NOT NULL DEFAULT 'Standard',
    status TEXT NOT NULL DEFAULT 'active',
    monthly_fee REAL NOT NULL DEFAULT 299,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS system_settings (
    key TEXT PRIMARY KEY,
    value TEXT
);

CREATE TABLE IF NOT EXISTS user_remember_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    token_hash TEXT NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS line_bot_sessions (
    user_id INTEGER PRIMARY KEY,
    state TEXT NOT NULL,
    data TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
");

// 2. Safe Auto-Migration for existing databases (adds missing columns if not present)
$tableColumns = [
    'users' => [
        'admin_pin' => "TEXT DEFAULT '123456'",
        'line_user_id' => 'TEXT DEFAULT NULL',
        'line_display_name' => 'TEXT DEFAULT NULL',
        'line_picture_url' => 'TEXT DEFAULT NULL'
    ],
    'servers' => [
        'panel_url' => 'TEXT',
        'username' => 'TEXT',
        'password' => 'TEXT',
        'inbound_id' => 'TEXT',
        'domain' => 'TEXT',
        'bug_host' => 'TEXT',
        'vless_port' => 'INTEGER',
        'pbk' => 'TEXT',
        'sids' => 'TEXT',
        'ssh_templates' => 'TEXT',
        'netmod_templates' => 'TEXT',
        'addon_id' => 'TEXT',
        'connection_mode' => "TEXT DEFAULT 'legacy'",
        'ghost_cleanup_enabled' => 'INTEGER DEFAULT 1'
    ],
    'addons' => [
        'subtitle' => "TEXT DEFAULT ''",
        'badge' => "TEXT DEFAULT 'ไม่จำกัด GB ✅'",
        'desc_html' => "TEXT DEFAULT ''",
        'warning' => "TEXT DEFAULT ''",
        'warning_bg' => "TEXT DEFAULT 'pink'",
        'extra_html' => "TEXT DEFAULT ''",
        'price_label' => "TEXT DEFAULT ''",
        'price_per' => "TEXT DEFAULT '/ 30 วัน'"
    ],
    'vpn_configs' => [
        'xui_email' => 'TEXT DEFAULT NULL'
    ]
];

foreach ($tableColumns as $tableName => $cols) {
    try {
        $existingCols = [];
        $res = $db->query("PRAGMA table_info({$tableName})")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($res as $r) {
            $existingCols[] = strtolower($r['name']);
        }
        foreach ($cols as $colName => $colType) {
            if (!in_array(strtolower($colName), $existingCols)) {
                $db->exec("ALTER TABLE {$tableName} ADD COLUMN {$colName} {$colType}");
            }
        }
    } catch (\Throwable $t) {}
}

// 3. Seed Default Users (Admin: admin / admin123, PIN: 123456)
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
if ($stmt->fetchColumn() == 0) {
    $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO users (username, password, role, balance, admin_pin) VALUES ('admin', ?, 'admin', 0.00, '123456')")
       ->execute([$adminPass]);

    $resellerPass = password_hash('reseller123', PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO users (username, password, role, balance, admin_pin) VALUES ('reseller', ?, 'reseller', 0.00, '123456')")
       ->execute([$resellerPass]);

    $buyerPass = password_hash('buyer123', PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO users (username, password, role, balance) VALUES ('buyer', ?, 'user', 0.00)")
       ->execute([$buyerPass]);
}

// 4. Seed Default Categories
$stmt = $db->query("SELECT COUNT(*) FROM categories");
if ($stmt->fetchColumn() == 0) {
    $db->exec("
    INSERT INTO categories (id, name, color_theme, sort_order) VALUES
    (1, 'AIS 5G', 'emerald', 1),
    (2, 'TRUE 5G', 'rose', 2),
    (3, 'DTAC', 'cyan', 3);
    ");
}

// 5. Seed Default Price Tiers
$stmt = $db->query("SELECT COUNT(*) FROM price_tiers");
if ($stmt->fetchColumn() == 0) {
    $db->exec("
    INSERT INTO price_tiers (id, name, color_theme, prices) VALUES
    (1, 'VIP Reality (ทั่วไป)', 'indigo', '[5, 25, 45, 80]'),
    (2, 'Gaming & Streaming', 'purple', '[10, 35, 65, 120]');
    ");
}

// 6. Seed Default Addons
$stmt = $db->query("SELECT COUNT(*) FROM addons");
if ($stmt->fetchColumn() == 0) {
    $aisCodes = json_encode([
        ['name' => 'สมัคร 30 วัน ไม่อั้น', 'code' => '*777*7153*002447#'],
        ['name' => 'สมัคร 7 วัน 15Mbps', 'code' => '*777*7082#']
    ], JSON_UNESCAPED_UNICODE);

    $trueCodes = json_encode([
        ['name' => 'เน็ตทรู 30 วัน ไม่ลดสปีด', 'code' => '*900*8324*17336422#'],
        ['name' => 'เน็ตทรู 7 วัน ความเร็ว 10Mbps', 'code' => '*900*8888#']
    ], JSON_UNESCAPED_UNICODE);

    $dtacCodes = json_encode([
        ['name' => 'เน็ตดีแทค 30 วัน ไม่อั้น', 'code' => '*104*388*1234567#']
    ], JSON_UNESCAPED_UNICODE);

    $stmt = $db->prepare("INSERT INTO addons (carrier, title, theme_color, duration_text, description, price, subscription_codes, badge) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['AIS', 'AIS 15Mbps ไม่จำกัดปริมาณ', 'green', '30 วัน', 'ใช้งานร่วมกับ VPN ทะลุบล็อกได้ 100% เล่นเกม ดูวิดีโอ 4K ไม่สะดุด', 200.00, $aisCodes, 'ยอดนิยม 🔥']);
    $stmt->execute(['True', 'True Unlimited Max Speed', 'orange', '30 วัน', 'แพ็กเกจเสริมแนะนำ ความเร็วสูงสุดตามพื้นที่', 220.00, $trueCodes, 'แนะนำ ⭐']);
    $stmt->execute(['Dtac', 'DTAC No Limit 10Mbps', 'purple', '30 วัน', 'ความเร็วคงที่ 10Mbps เหมาะกับการเปิด VPN ตลอดทั้งวัน', 180.00, $dtacCodes, 'สุดคุ้ม 💎']);
}

// 7. Seed Default Warnings
$stmt = $db->query("SELECT COUNT(*) FROM system_warnings");
if ($stmt->fetchColumn() == 0) {
    $db->exec("
    INSERT INTO system_warnings (id, v2ray_warning, ssh_warning) VALUES
    (1, 'รองรับแอป v2rayNG, Shadowrocket, Streisand, Sing-box\nห้ามนำไปใช้ยิงหรือโจมตีเซิร์ฟเวอร์อื่น\nความเร็วขึ้นอยู่กับพื้นที่และแพ็กเกจเน็ตของผู้ใช้',
        'รองรับแอป NPV Tunnel, NetMod, HTTP Custom\nใส่ Username และ Password ตามที่ตั้งไว้\nห้ามดาวน์โหลดบิททอร์เรนต์ (BitTorrent)');
    ");
}

// 8. Seed Default Announcements
$stmt = $db->query("SELECT COUNT(*) FROM announcements");
if ($stmt->fetchColumn() == 0) {
    $db->exec("
    INSERT INTO announcements (id, title, content, type, is_active) VALUES
    (1, 'ยินดีต้อนรับสู่ EKROM Shop 🚀', 'ระบบจำหน่ายและจัดการ VPN คุณภาพสูง ทะลุบล็อก เล่นเกม สตรีมมิ่ง รองรับตลอด 24 ชั่วโมง มีปัญหาสอบถามแอดมินได้ทันที', 'info', 1);
    ");
}

// 9. Ensure default system settings exist
$stmt = $db->query("SELECT COUNT(*) FROM system_settings WHERE key = 'turnstile_settings'");
if ($stmt->fetchColumn() == 0) {
    $defaultTurnstile = json_encode([
        'enabled' => 0,
        'site_key' => '',
        'secret_key' => ''
    ], JSON_UNESCAPED_UNICODE);
    $db->prepare("INSERT INTO system_settings (key, value) VALUES ('turnstile_settings', ?)")->execute([$defaultTurnstile]);
}

$stmt = $db->query("SELECT COUNT(*) FROM system_settings WHERE key = 'slip_settings'");
if ($stmt->fetchColumn() == 0) {
    $defaultSlip = json_encode([
        'action' => 'save_slip_settings',
        'slip_api_mode' => 'manual',
        'slipok_branch_id' => '',
        'slipok_api_key' => '',
        'slip_min_amount' => 30,
        'slip_expire_minutes' => 15,
        'slip_receiver_th' => '',
        'slip_receiver_en' => '',
        'slip_receiver_account' => '',
        'promptpay_number' => '',
        'promptpay_name' => '',
        'truemoney_phone' => ''
    ], JSON_UNESCAPED_UNICODE);
    $db->prepare("INSERT INTO system_settings (key, value) VALUES ('slip_settings', ?)")->execute([$defaultSlip]);
}

$stmt = $db->query("SELECT COUNT(*) FROM system_settings WHERE key = 'webhooks'");
if ($stmt->fetchColumn() == 0) {
    $defaultWebhooks = json_encode([
        'buy' => '',
        'topup' => '',
        'renew' => '',
        'register' => '',
        'login' => ''
    ], JSON_UNESCAPED_UNICODE);
    $db->prepare("INSERT INTO system_settings (key, value) VALUES ('webhooks', ?)")->execute([$defaultWebhooks]);
}

$stmt = $db->query("SELECT COUNT(*) FROM system_settings WHERE key = 'line_bot_settings'");
if ($stmt->fetchColumn() == 0) {
    $defaultLineBot = json_encode([
        'enabled' => 0,
        'channel_secret' => '',
        'channel_access_token' => '',
        'bot_basic_id' => '',
        'bot_name' => 'EkromVPN',
        'webhook_url' => ''
    ], JSON_UNESCAPED_UNICODE);
    $db->prepare("INSERT INTO system_settings (key, value) VALUES ('line_bot_settings', ?)")->execute([$defaultLineBot]);
}

$stmt = $db->query("SELECT COUNT(*) FROM system_settings WHERE key = 'contact_settings'");
if ($stmt->fetchColumn() == 0) {
    $defaultContact = json_encode([
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
    ], JSON_UNESCAPED_UNICODE);
    $db->prepare("INSERT INTO system_settings (key, value) VALUES ('contact_settings', ?)")->execute([$defaultContact]);
}

echo "Database initialized successfully at: " . $dbFile . "\n";
