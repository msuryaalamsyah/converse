<?php
require_once __DIR__ . '/auth.php';
if (!empty($_SESSION['admin_id'])) { header('Location: ' . ADMIN_URL . '/index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    if (adminLogin($user, $pass)) {
        header('Location: ' . ADMIN_URL . '/index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — CONVERSE</title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@700;800;900&family=Inter:wght@400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f5f5f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-wrap { width: 440px; background: #fff; border: 3px solid #000; padding: 0; }
        .login-header { background: #000; color: #fff; padding: 32px 40px; }
        .login-logo { display: flex; align-items: center; gap: 10px; font-family: 'Barlow Condensed', sans-serif; font-weight: 900; font-size: 22px; letter-spacing: 4px; }
        .login-header p { font-size: 12px; color: #888; margin-top: 8px; letter-spacing: 1px; text-transform: uppercase; }
        .login-body { padding: 40px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-family: 'Barlow Condensed', sans-serif; font-weight: 700; font-size: 11px; letter-spacing: 2px; text-transform: uppercase; margin-bottom: 8px; }
        .form-group input { width: 100%; padding: 12px 14px; border: 2px solid #000; font-family: 'Inter', sans-serif; font-size: 14px; outline: none; }
        .form-group input:focus { border-color: #D80027; }
        .btn-login { width: 100%; padding: 14px; background: #000; color: #fff; border: 2px solid #000; font-family: 'Barlow Condensed', sans-serif; font-weight: 800; font-size: 16px; letter-spacing: 3px; text-transform: uppercase; cursor: pointer; transition: background 0.15s; }
        .btn-login:hover { background: #D80027; border-color: #D80027; }
        .error { background: #fff0f0; border-left: 4px solid #D80027; color: #D80027; padding: 12px 16px; font-size: 13px; margin-bottom: 20px; }
        .back-link { display: block; text-align: center; margin-top: 20px; font-size: 12px; color: #999; text-decoration: underline; }
        svg { flex-shrink: 0; }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-header">
        <div class="login-logo">
            <svg width="28" height="28" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="45" fill="none" stroke="white" stroke-width="4"/>
                <polygon points="50,15 62,40 88,40 67,58 75,85 50,68 25,85 33,58 12,40 38,40" fill="white"/>
            </svg>
            CONVERSE
        </div>
        <p>Admin Dashboard</p>
    </div>
    <div class="login-body">
        <?php if($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus placeholder="admin">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>
        <a href="<?= SITE_URL ?>" class="back-link">← Back to Store</a>
    </div>
</div>
</body>
</html>
