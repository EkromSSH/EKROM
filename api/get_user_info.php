<?php
require_once __DIR__ . '/db.php';
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
$user = require_auth();

$db = get_db();
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$ordersCount = (int)$db->query("SELECT COUNT(*) FROM orders_history WHERE type IN ('buy', 'renew')")->fetchColumn();
$vpnCount = (int)$db->query("SELECT COUNT(*) FROM vpn_configs")->fetchColumn();
$totalSales = max($ordersCount, $vpnCount);

$trialCheck = check_user_trial_eligibility($user);

json_response([
    'status' => 'success',
    'username' => $user['username'],
    'balance' => number_format((float)$user['balance'], 2, '.', ''),
    'role' => $user['role'],
    'line_user_id' => $user['line_user_id'] ?? null,
    'line_display_name' => $user['line_display_name'] ?? null,
    'line_picture_url' => $user['line_picture_url'] ?? null,
    'is_line_user' => !empty($user['line_user_id']),
    'total_users' => $totalUsers,
    'total_sales' => $totalSales,
    'can_trial' => $trialCheck['allowed'],
    'trial_message' => $trialCheck['message'],
    'reseller_discount_percent' => get_reseller_discount_percent($user)
]);
