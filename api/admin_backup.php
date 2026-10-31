<?php
require_once __DIR__ . '/db.php';

$user = require_auth();
if ($user['role'] !== 'admin') {
    json_response(['status' => 'error', 'message' => 'Admin required'], 403);
}

$jsonPath = dirname(__DIR__) . '/backup_config.json';
$aisPath = dirname(__DIR__) . '/backup_config_ais.txt';
$truePath = dirname(__DIR__) . '/backup_config_true.txt';
$bothPath = dirname(__DIR__) . '/backup_config.txt';

$defaultAis = "vless://3762c0f6-eaa5-4cc0-bae4-81c836030973@aisplay.ais.co.th:80?path=%2F&encryption=none&security=none&host=d2qcpub79wb31.cloudfront.net&type=ws#ais%20%E0%B9%83%E0%B8%8A%E0%B9%89%E0%B8%AA%E0%B8%B3%E0%B8%A3%E0%B8%AD%E0%B8%87%E0%B9%84%E0%B8%9B%E0%B8%81%E0%B9%88%E0%B8%AD%E0%B8%99";
$defaultTrue = "vless://3762c0f6-eaa5-4cc0-bae4-81c836030973@debugaccess.wetv.vip:80?type=ws&path=%2F&encryption=none&host=d2qcpub79wb31.cloudfront.net&security=none#%E0%B8%97%E0%B8%A3%E0%B8%B9%20%E0%B9%83%E0%B8%8A%E0%B9%89%E0%B8%AA%E0%B8%B3%E0%B8%A3%E0%B8%AD%E0%B8%87%E0%B9%84%E0%B8%9B%E0%B8%81%E0%B9%88%E0%B8%AD%E0%B8%99";

function get_current_backup_config($path, $defAis, $defTrue) {
    $data = [
        'enabled' => true,
        'ais_config' => $defAis,
        'true_config' => $defTrue,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    if (file_exists($path)) {
        $raw = @file_get_contents($path);
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $data['enabled'] = isset($decoded['enabled']) ? (bool)$decoded['enabled'] : true;
            if (!empty($decoded['ais_config'])) $data['ais_config'] = (string)$decoded['ais_config'];
            if (!empty($decoded['true_config'])) $data['true_config'] = (string)$decoded['true_config'];
            if (!empty($decoded['updated_at'])) $data['updated_at'] = (string)$decoded['updated_at'];
        }
    }
    return $data;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = get_current_backup_config($jsonPath, $defaultAis, $defaultTrue);
    json_response([
        'status' => 'success',
        'data' => $data
    ]);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: $_POST;

    $enabled = isset($body['enabled']) ? (bool)$body['enabled'] : true;
    $aisConfig = trim($body['ais_config'] ?? '');
    $trueConfig = trim($body['true_config'] ?? '');

    if (empty($aisConfig)) $aisConfig = $defaultAis;
    if (empty($trueConfig)) $trueConfig = $defaultTrue;

    $payload = [
        'enabled' => $enabled,
        'ais_config' => $aisConfig,
        'true_config' => $trueConfig,
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $encoded = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    @file_put_contents($jsonPath, $encoded);
    @chmod($jsonPath, 0666);

    @file_put_contents($aisPath, $aisConfig);
    @chmod($aisPath, 0666);

    @file_put_contents($truePath, $trueConfig);
    @chmod($truePath, 0666);

    $bothContent = "# AIS 5G Backup\n" . $aisConfig . "\n\n# TRUE 5G Backup\n" . $trueConfig . "\n";
    @file_put_contents($bothPath, $bothContent);
    @chmod($bothPath, 0666);

    json_response([
        'status' => 'success',
        'message' => 'บันทึกการตั้งค่าไฟล์สำรองฉุกเฉินเรียบร้อยแล้ว',
        'data' => $payload
    ]);
}
