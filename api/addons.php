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
    $codes = [];
    $rawCodes = $ad['subscription_codes'] ?? '';
    if (!empty($rawCodes)) {
        if (is_array($rawCodes)) {
            $codes = $rawCodes;
        } else {
            $decoded = json_decode((string)$rawCodes, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $codes = $decoded;
            } elseif (preg_match('/\*\d+(?:\*\d+)*#/', (string)$rawCodes, $m)) {
                $codes = [['name' => 'รหัสสมัคร', 'code' => $m[0], 'price' => '']];
            }
        }
    }
    if (empty($codes) && !empty($ad['ussd_code'])) {
        $codes = [['name' => 'รหัสสมัครเดิม', 'code' => trim($ad['ussd_code']), 'price' => '']];
    }
    if (empty($codes)) {
        $searchCorpus = ($ad['warning'] ?? '') . ' ' . ($ad['extra_html'] ?? '') . ' ' . ($ad['description'] ?? '') . ' ' . ($ad['title'] ?? '');
        if (preg_match_all('/\*\d+(?:\*\d+)*#/', $searchCorpus, $matches)) {
            $uniqueCodes = array_values(array_unique($matches[0]));
            foreach ($uniqueCodes as $idx => $ucode) {
                $codes[] = [
                    'name' => 'รหัสสมัคร ' . ($idx + 1),
                    'code' => $ucode,
                    'price' => !empty($ad['price']) ? '฿' . number_format((float)$ad['price'], 2) : ''
                ];
            }
        }
    }

    $grouped[$carrier]['items'][] = [
        'id' => (int)$ad['id'],
        'carrier' => $ad['carrier'],
        'title' => $ad['title'],
        'subtitle' => $ad['subtitle'] ?? '',
        'price_label' => $ad['price_label'] ?? '',
        'price_per' => $ad['price_per'] ?? '/ 30 วัน',
        'badge' => $ad['badge'] ?? 'ไม่จำกัด GB ✅',
        'theme_color' => $ad['theme_color'] ?? 'green',
        'duration_text' => $ad['duration_text'] ?? '30 วัน',
        'description' => $ad['description'] ?? '',
        'desc_html' => $ad['desc_html'] ?? '',
        'warning' => $ad['warning'] ?? '',
        'warning_bg' => $ad['warning_bg'] ?? 'pink',
        'extra_html' => $ad['extra_html'] ?? '',
        'price' => (float)($ad['price'] ?? 0),
        'subscription_codes' => $codes,
        'ussd_code' => $ad['ussd_code'] ?? ''
    ];
}

json_response([
    'status' => 'success',
    'data' => array_values($grouped)
]);
