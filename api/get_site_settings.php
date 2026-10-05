<?php
// api/get_site_settings.php - Public API for Site & Brand settings
require_once __DIR__ . '/db.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=60');
$settings = get_site_settings();
echo json_encode(['status' => 'success', 'data' => $settings], JSON_UNESCAPED_UNICODE);
