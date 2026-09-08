<?php
// init_db.php - Initializes SQLite database with tables and seed data

$dbFile = __DIR__ . '/database.sqlite';
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Create tables
$db->exec("
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'user',
    balance REAL DEFAULT 0.00,
    admin_pin TEXT DEFAULT '123456',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    color_theme TEXT DEFAULT 'blue',
    sort_order INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS price_tiers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    color_theme TEXT DEFAULT 'blue',
    prices TEXT NOT NULL -- JSON array: [1_day, 7_days, 15_days, 30_days]
);

CREATE TABLE IF NOT EXISTS servers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER,
    tier_id INTEGER,
    name TEXT NOT NULL,
    type TEXT DEFAULT 'v2ray', -- 'v2ray' or 'ssh_script'
    icon TEXT DEFAULT '🇹🇭',
    theme TEXT DEFAULT 'blue',
    host TEXT DEFAULT '127.0.0.1',
    port INTEGER DEFAULT 443,
    protocol TEXT DEFAULT 'vless',
    description TEXT,
    user_count INTEGER DEFAULT 0,
    cpu INTEGER DEFAULT 15,
    is_active INTEGER DEFAULT 1,
    target_customer_price REAL DEFAULT 50.00,
    config_template TEXT
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
    subscription_codes TEXT
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
    expiry_time DATETIME NOT NULL
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
");

// Check if admin already exists
$stmt = $db->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
if ($stmt->fetchColumn() == 0) {
    // Insert Admin user (pass: admin123, pin: 123456)
    $adminPass = password_hash('admin123', PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO users (username, password, role, balance, admin_pin) VALUES ('admin', ?, 'admin', 999.00, '123456')")
       ->execute([$adminPass]);

    // Insert Reseller user (pass: reseller123)
    $resellerPass = password_hash('reseller123', PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO users (username, password, role, balance, admin_pin) VALUES ('reseller', ?, 'reseller', 500.00, '123456')")
       ->execute([$resellerPass]);

    // Insert Demo buyer user (pass: buyer123)
    $buyerPass = password_hash('buyer123', PASSWORD_BCRYPT);
    $db->prepare("INSERT INTO users (username, password, role, balance) VALUES ('buyer', ?, 'user', 150.00)")
       ->execute([$buyerPass]);

    echo "Default users created (admin / reseller / buyer)
";
}

// Check Categories
$stmt = $db->query("SELECT COUNT(*) FROM categories");
if ($stmt->fetchColumn() == 0) {
    $db->exec("
    INSERT INTO categories (id, name, color_theme, sort_order) VALUES
    (1, 'เซิร์ฟเวอร์ไทย (Thailand 🇹🇭)', 'blue', 1),
    (2, 'เซิร์ฟเวอร์สิงคโปร์ (Singapore 🇸🇬)', 'emerald', 2),
    (3, 'เซิร์ฟเวอร์สำหรับสายเกมมิ่ง (Gaming 🎮)', 'purple', 3);
    ");
}

// Check Price Tiers
$stmt = $db->query("SELECT COUNT(*) FROM price_tiers");
if ($stmt->fetchColumn() == 0) {
    $db->exec("
    INSERT INTO price_tiers (id, name, color_theme, prices) VALUES
    (1, 'V2Ray Reality VIP', 'blue', '[5, 25, 45, 80]'),
    (2, 'SSH / Websocket Direct', 'emerald', '[5, 20, 40, 70]'),
    (3, 'Extreme Gaming Ultra', 'purple', '[10, 35, 65, 120]');
    ");
}

// Check Addons
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

    $stmt = $db->prepare("INSERT INTO addons (carrier, title, theme_color, duration_text, description, price, subscription_codes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['AIS 5G', 'AIS 15Mbps ไม่จำกัดปริมาณ', 'green', '30 วัน', 'ใช้งานร่วมกับ V2Ray ทะลุบล็อกได้ 100% เล่นเกม ดูวิดีโอ 4K ไม่สะดุด', 200.00, $aisCodes]);
    $stmt->execute(['True 5G', 'True Unlimited Max Speed', 'red', '30 วัน', 'แพ็กเกจเสริมแนะนำสำหรับเซิร์ฟเวอร์ไทยและสิงคโปร์ ความเร็วสูงสุดตามพื้นที่', 220.00, $trueCodes]);
    $stmt->execute(['DTAC', 'DTAC No Limit 10Mbps', 'blue', '30 วัน', 'ความเร็วคงที่ 10Mbps เหมาะกับการเปิด VPN ตลอดทั้งวัน', 180.00, $dtacCodes]);
}

// Check Servers
$stmt = $db->query("SELECT COUNT(*) FROM servers");
if ($stmt->fetchColumn() == 0) {
    $db->exec("
    INSERT INTO servers (id, category_id, tier_id, name, type, icon, theme, host, port, protocol, description, user_count, cpu, target_customer_price) VALUES
    (1, 1, 1, 'TH-Bypass-01 🇹🇭', 'v2ray', '🇹🇭', 'blue', 'th1.nexavpn.net', 443, 'vless', 'เซิร์ฟเวอร์ประเทศไทย ทะลุบล็อกทุกเว็บไซต์ สตรีมมิ่งลื่นไหล รองรับทุกเครือข่าย', 34, 18, 80.00),
    (2, 2, 1, 'SG-Fast-Route 🇸🇬', 'v2ray', '🇸🇬', 'emerald', 'sg1.nexavpn.net', 443, 'vless', 'เซิร์ฟเวอร์สิงคโปร์ แบนด์วิดท์ 1Gbps ดาวน์โหลดแรง เสถียรสูง', 52, 22, 80.00),
    (3, 3, 3, 'TH-Gaming-ZeroPing 🎮', 'v2ray', '🎮', 'purple', 'game.nexavpn.net', 443, 'vless', 'เซิร์ฟเวอร์เกมมิ่งโดยเฉพาะ ปิงต่ำ 10-15ms ไม่แลค ไม่หลุด', 41, 35, 120.00),
    (4, 1, 2, 'TH-SSH-Direct 🛡️', 'ssh_script', '🛡️', 'emerald', 'ssh1.nexavpn.net', 80, 'ssh', 'โปรโตคอล SSH/Websocket รองรับแอป NetMod และ NPV Tunnel', 19, 12, 70.00);
    ");
}

// Check Warnings
$stmt = $db->query("SELECT COUNT(*) FROM system_warnings");
if ($stmt->fetchColumn() == 0) {
    $db->exec("
    INSERT INTO system_warnings (id, v2ray_warning, ssh_warning) VALUES
    (1, 'รองรับแอป v2rayNG, Shadowrocket, Streisand, Sing-box
ห้ามนำไปใช้ยิงหรือโจมตีเซิร์ฟเวอร์อื่น
ความเร็วขึ้นอยู่กับพื้นที่และแพ็กเกจเน็ตของผู้ใช้',
        'รองรับแอป NPV Tunnel, NetMod, HTTP Custom
ใส่ Username และ Password ตามที่ตั้งไว้
ห้ามดาวน์โหลดบิททอร์เรนต์ (BitTorrent)');
    ");
}

// Check Announcements
$stmt = $db->query("SELECT COUNT(*) FROM announcements");
if ($stmt->fetchColumn() == 0) {
    $db->exec("
    INSERT INTO announcements (id, title, content, type, is_active) VALUES
    (1, 'ยินดีต้อนรับสู่ EKROM Shop 🚀', 'ระบบจำหน่ายและจัดการ VPN คุณภาพสูง ทะลุบล็อก เล่นเกม สตรีมมิ่ง รองรับตลอด 24 ชั่วโมง มีปัญหาสอบถามแอดมินได้ทันที', 'info', 1);
    ");
}

// Check sample VPN config for demo user
$buyerId = $db->query("SELECT id FROM users WHERE username = 'buyer'")->fetchColumn();
$stmt = $db->prepare("SELECT COUNT(*) FROM vpn_configs WHERE user_id = ?");
$stmt->execute([$buyerId]);
if ($stmt->fetchColumn() == 0) {
    $uuid1 = 'e4b2931a-65bc-488f-a9ce-192a8e8b0a01';
    $vless1 = "vless://$uuid1@th1.nexavpn.net:443?encryption=none&security=reality&sni=speedtest.net&fp=chrome&type=grpc&serviceName=th-grpc#TH-Bypass-01";
    $expire1 = date('Y-m-d H:i:s', strtotime('+28 days'));
    
    $uuid2 = 'a7c4125f-1490-4e31-863a-bb1234ef9999';
    $vless2 = "vless://$uuid2@sg1.nexavpn.net:443?encryption=none&security=reality&sni=sg.example.com&fp=chrome&type=ws#SG-Fast-Route";
    $expire2 = date('Y-m-d H:i:s', strtotime('+14 days'));

    $db->prepare("INSERT INTO vpn_configs (user_id, server_id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, upload_bytes, download_bytes, status_real, expiry_time) VALUES (?, 1, ?, 'TH-Bypass-01 🇹🇭', 'V2Ray Reality VIP 30 วัน', '30', 80.00, 'vless', ?, 452839210, 2489218490, 'active', ?)")
       ->execute([$buyerId, $uuid1, $vless1, $expire1]);

    $db->prepare("INSERT INTO vpn_configs (user_id, server_id, uuid, server_name, package_name, package_val, price_paid, protocol, config_link, upload_bytes, download_bytes, status_real, expiry_time) VALUES (?, 2, ?, 'SG-Fast-Route 🇸🇬', 'V2Ray Reality VIP 15 วัน', '15', 45.00, 'vless', ?, 128492000, 1102930219, 'active', ?)")
       ->execute([$buyerId, $uuid2, $vless2, $expire2]);

    $db->prepare("INSERT INTO topup_transactions (user_id, method, amount) VALUES (?, 'PromptPay Slip', 200.00)")
       ->execute([$buyerId]);
    $db->prepare("INSERT INTO topup_transactions (user_id, method, amount) VALUES (?, 'TrueMoney Voucher', 100.00)")
       ->execute([$buyerId]);

    $db->prepare("INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, 'buy', 80.00, 'สั่งซื้อ TH-Bypass-01 30 วัน')")
       ->execute([$buyerId]);
    $db->prepare("INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, 'buy', 45.00, 'สั่งซื้อ SG-Fast-Route 15 วัน')")
       ->execute([$buyerId]);
    $db->prepare("INSERT INTO orders_history (user_id, type, amount, description) VALUES (?, 'topup', 200.00, 'เติมเงิน PromptPay Slip')")
       ->execute([$buyerId]);
}

echo "Database initialized successfully at: " . $dbFile . "
";
