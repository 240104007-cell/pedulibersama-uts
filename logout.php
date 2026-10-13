<?php
require_once 'includes/functions.php';
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
setcookie('pb_session', '', time() - 3600, '/');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
set_flash('success', 'Anda telah berhasil keluar.');
header('Location: index.php');
exit;
