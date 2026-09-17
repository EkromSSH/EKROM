<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$action = $_GET['action'] ?? 'get_store';
$db = get_db();

if ($action === 'get_stats') {
    $servers = $db->query('SELECT id, user_count, cpu FROM servers WHERE is_active = 1')->fetchAll();
    $stats = [];
    foreach ($servers as $s) {
        // slight jitter to make live stats feel organic
        $simulatedCpu = max(5, min(95, (int)$s['cpu'] + rand(-2, 3)));
        $stats['sv' . $s['id']] = [
            'user_count' => (int)$s['user_count'],
            'cpu' => $simulatedCpu
        ];
    }
    json_response(['status' => 'success', 'data' => $stats]);
}

// get_store
$tiers = $db->query('SELECT * FROM price_tiers')->fetchAll();
$formattedTiers = [];
$tierMap = [];
foreach ($tiers as $t) {
    $prices = json_decode($t['prices'], true) ?: [5, 25, 45, 80];
    $formattedTiers[] = [
        'id' => (int)$t['id'],
        'name' => $t['name'],
        'color_theme' => $t['color_theme'],
        'theme' => $t['color_theme'],
        'price_1' => $prices[0] ?? 5,
        'price_7' => $prices[1] ?? 25,
        'price_15' => $prices[2] ?? 45,
        'price_30' => $prices[3] ?? 80,
        'prices' => $prices
    ];
    $tierMap[$t['id']] = [
        'name' => $t['name'],
        'theme' => $t['color_theme'],
        'prices' => $prices
    ];
}

$addonsList = $db->query('SELECT * FROM addons')->fetchAll();
$allAddons = [];
foreach ($addonsList as $ad) {
    $allAddons[] = [
        'id' => (int)$ad['id'],
        'title' => $ad['title'],
        'theme_color' => $ad['theme_color'],
        'duration_text' => $ad['duration_text'],
        'description' => $ad['description'],
        'price' => (float)$ad['price'],
        'subscription_codes' => json_decode($ad['subscription_codes'], true) ?: []
    ];
}

$cats = $db->query('SELECT * FROM categories ORDER BY sort_order ASC')->fetchAll();
$servers = $db->query('SELECT * FROM servers WHERE is_active = 1')->fetchAll();

$catServers = [];
$uncategorized = [];

foreach ($cats as $c) {
    $catServers[$c['id']] = [
        'id' => (int)$c['id'],
        'name' => $c['name'],
        'color_theme' => $c['color_theme'],
        'servers' => []
    ];
}

foreach ($servers as $s) {
    $svKey = 'sv' . $s['id'];
    $tierId = $s['tier_id'];
    $tierInfo = $tierMap[$tierId] ?? ['name' => 'General', 'theme' => 'pink', 'prices' => [5, 25, 45, 80]];

    // Filter addons attached to this server (preserve configured order)
    $serverAddons = [];
    if (!empty($s['addon_id'])) {
        $addonIds = array_filter(array_map('trim', explode(',', (string)$s['addon_id'])));
        $addonMap = [];
        foreach ($allAddons as $ad) {
            $addonMap[(string)$ad['id']] = $ad;
        }
        foreach ($addonIds as $aid) {
            if (isset($addonMap[$aid])) {
                $serverAddons[] = $addonMap[$aid];
            }
        }
    }

    $catTheme = ($s['category_id'] && isset($catServers[$s['category_id']]['color_theme']))
        ? $catServers[$s['category_id']]['color_theme']
        : null;

    $svObj = [
        'id' => (int)$s['id'],
        'name' => $s['name'],
        'type' => $s['type'],
        'category_id' => (int)$s['category_id'],
        'category_theme' => $catTheme,
        'tier_id' => (int)$tierId,
        'price_tier' => (int)$tierId,
        'user_count' => (int)$s['user_count'],
        'cpu' => (int)$s['cpu'],
        'description' => $s['description'],
        'icon' => $s['type'] === 'ssh_script' ? '🔐' : '🚀',
        'theme' => $catTheme ?: ($tierInfo['theme'] ?? 'pink'),
        'target_customer_price' => (float)($s['target_customer_price'] ?? 0),
        'addons' => $serverAddons
    ];

    if ($s['category_id'] && isset($catServers[$s['category_id']])) {
        $catServers[$s['category_id']]['servers'][$svKey] = $svObj;
    } else {
        $uncategorized[$svKey] = $svObj;
    }
}

json_response([
    'status' => 'success',
    'data' => [
        'price_tiers' => $formattedTiers,
        'categories' => array_values($catServers),
        'uncategorized' => $uncategorized
    ]
]);
