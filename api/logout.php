<?php
require_once __DIR__ . '/db.php';

if (!empty($_COOKIE['ekrom_remember_token'])) {
    try {
        $db = get_db();
        $tokenHash = hash('sha256', $_COOKIE['ekrom_remember_token']);
        $stmt = $db->prepare('DELETE FROM user_remember_tokens WHERE token_hash = ?');
        $stmt->execute([$tokenHash]);
    } catch (\Throwable $e) {}
    setcookie('ekrom_remember_token', '', [
        'expires' => time() - 42000,
        'path' => '/'
    ]);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}
session_destroy();
header('Location: ../login.php');
exit;
