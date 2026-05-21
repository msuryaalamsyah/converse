<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) session_start();

function requireAdminAuth() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

function adminLogin($username, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin_id']   = $user['id'];
        $_SESSION['admin_name'] = $user['name'];
        return true;
    }
    return false;
}

function adminLogout() {
    session_destroy();
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}

define('ADMIN_URL', SITE_URL . '/admin');
