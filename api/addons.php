<?php
require_once __DIR__ . '/db.php';
$db = get_db();
$addons = $db->query('SELECT * FROM addons ORDER BY id ASC')->fetchAll();

$grouped = [];
foreach ($addons as $ad) {
    $carrier = $ad['carrier'];
    if (!isset($grouped[$carrier])) {
        $grouped[$carrier] = [
            'name' => $carrier,
            'items' => []
        ];
    }
    $grouped[$carrier]['items'][] = [
        'id' => (int)$ad['id'],
        'carrier' => $ad['carrier'],
        'title' => $ad['title'],
        'subtitle' => $ad['subtitle'] ?? '',
        'price_label' => $ad['price_label'] ?? '',
        'price_per' => $ad['price_per'] ?? '/ 30 วัน',
        'badge' => $ad['badge'] ?? 'ไม่จำกัด GB ✅',
        'theme_color' => $ad['theme_color'],
        'duration_text' => $ad['duration_text'],
        'description' => $ad['description'] ?? '',
        'desc_html' => $ad['desc_html'] ?? '',
        'warning' => $ad['warning'] ?? '',
        'warning_bg' => $ad['warning_bg'] ?? 'pink',
        'extra_html' => $ad['extra_html'] ?? '',
        'price' => (float)$ad['price'],
        'subscription_codes' => json_decode($ad['subscription_codes'], true) ?: []
    ];
}

json_response([
    'status' => 'success',
    'data' => array_values($grouped)
]);
