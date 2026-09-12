<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = require_auth();

$db = get_db();
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$ordersCount = (int)$db->query("SELECT COUNT(*) FROM orders_history WHERE type IN ('buy', 'renew')")->fetchColumn();
$vpnCount = (int)$db->query("SELECT COUNT(*) FROM vpn_configs")->fetchColumn();
$totalSales = max($ordersCount, $vpnCount);

json_response([
    'status' => 'success',
    'username' => $user['username'],
    'balance' => number_format((float)$user['balance'], 2, '.', ''),
    'role' => $user['role'],
    'total_users' => $totalUsers,
    'total_sales' => $totalSales
]);
