<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
$categories = getAllCategories();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle . ' — CONVERSE' : 'CONVERSE' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@400;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <a href="<?= SITE_URL ?>/index.php" class="nav-logo">
            <svg width="32" height="32" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                <circle cx="50" cy="50" r="45" fill="none" stroke="currentColor" stroke-width="4"/>
                <polygon points="50,15 62,40 88,40 67,58 75,85 50,68 25,85 33,58 12,40 38,40" fill="currentColor"/>
            </svg>
            <span>CONVERSE</span>
        </a>

        <ul class="nav-links">
            <li><a href="<?= SITE_URL ?>/products.php?cat=men" class="<?= ($currentPage=='products' && ($_GET['cat']??'')=='men') ? 'active':'' ?>">Men</a></li>
            <li><a href="<?= SITE_URL ?>/products.php?cat=women" class="<?= ($currentPage=='products' && ($_GET['cat']??'')=='women') ? 'active':'' ?>">Women</a></li>
            <li><a href="<?= SITE_URL ?>/products.php?cat=kids" class="<?= ($currentPage=='products' && ($_GET['cat']??'')=='kids') ? 'active':'' ?>">Kids</a></li>
            <li><a href="<?= SITE_URL ?>/products.php" class="<?= ($currentPage=='products' && !isset($_GET['cat'])) ? 'active':'' ?>">All</a></li>
            <?php foreach($categories as $cat): ?>
            <li class="cat-link"><a href="<?= SITE_URL ?>/products.php?category=<?= $cat['slug'] ?>"><?= htmlspecialchars($cat['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="nav-actions">
            <form action="<?= SITE_URL ?>/products.php" method="GET" class="nav-search">
                <input type="text" name="q" placeholder="Search..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button type="submit"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></button>
            </form>
            <a href="#" class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
            <a href="#" class="nav-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></a>
        </div>

        <button class="nav-toggle" onclick="this.closest('.navbar').classList.toggle('open')">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>
