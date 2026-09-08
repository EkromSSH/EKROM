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
        'title' => $ad['title'],
        'theme_color' => $ad['theme_color'],
        'duration_text' => $ad['duration_text'],
        'description' => $ad['description'],
        'price' => (float)$ad['price'],
        'subscription_codes' => json_decode($ad['subscription_codes'], true) ?: []
    ];
}

json_response([
    'status' => 'success',
    'data' => array_values($grouped)
]);
