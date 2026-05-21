<?php
// Admin layout header - include at top of every admin page
// Variables expected: $pageTitle (string)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — Admin' : 'Admin — CONVERSE' ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <svg width="22" height="22" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <circle cx="50" cy="50" r="45" fill="none" stroke="black" stroke-width="5"/>
            <polygon points="50,15 62,40 88,40 67,58 75,85 50,68 25,85 33,58 12,40 38,40" fill="black"/>
        </svg>
        <span>CONVERSE</span>
    </div>
    <nav class="sidebar-nav">
        <?php $cp = basename($_SERVER['PHP_SELF']); ?>
        <a href="<?= ADMIN_URL ?>/index.php" class="<?= $cp=='index.php'?'active':'' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            <span>Dashboard</span>
        </a>
        <div class="sidebar-section">Catalog</div>
        <a href="<?= ADMIN_URL ?>/products.php" class="<?= in_array($cp,['products.php','product-add.php','product-edit.php'])?'active':'' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <span>Products</span>
        </a>
        <a href="<?= ADMIN_URL ?>/categories.php" class="<?= in_array($cp,['categories.php'])?'active':'' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
            <span>Categories</span>
        </a>
        <a href="<?= ADMIN_URL ?>/stock.php" class="<?= $cp=='stock.php'?'active':'' ?>">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="20" x2="12" y2="10"/><line x1="18" y1="20" x2="18" y2="4"/><line x1="6" y1="20" x2="6" y2="16"/></svg>
            <span>Stock</span>
        </a>
    </nav>
    <div class="sidebar-bottom">
        <a href="<?= SITE_URL ?>" target="_blank">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            <span>View Store</span>
        </a>
        <a href="<?= ADMIN_URL ?>/logout.php" style="margin-top:8px;color:#D80027">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            <span>Logout</span>
        </a>
    </div>
</aside>

<!-- MAIN CONTENT -->
<main class="admin-main">
    <div class="admin-topbar">
        <div class="admin-page-title"><?= $pageTitle ?? 'Dashboard' ?></div>
        <div class="topbar-actions">
            <span class="topbar-user">👤 <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
        </div>
    </div>
    <div class="admin-content">
