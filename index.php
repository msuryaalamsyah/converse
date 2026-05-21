<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
$featured   = getAllProducts(6, true);
$newArrivals = getAllProducts(3, false, true);
$allProducts = getAllProducts(6);
?>

<!-- HERO -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <span class="hero-label">The Icon</span>
        <h1 class="hero-title">Chuck<br>Taylor<br>All Star</h1>
        <p class="hero-sub">Timeless style. Relentless self-expression. The original canvas sneaker that started it all, built for the streets and beyond.</p>
        <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary">
            Shop Now
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
</section>

<!-- NEW & TRENDING -->
<section class="section">
    <div class="section-header">
        <h2 class="section-title">New &amp; Trending</h2>
        <a href="<?= SITE_URL ?>/products.php?filter=new" class="section-link">View All</a>
    </div>

    <div class="trending-row">
        <?php foreach(array_slice($newArrivals, 0, 2) as $p): ?>
        <a href="<?= SITE_URL ?>/product.php?slug=<?= $p['slug'] ?>" class="product-card">
            <div class="product-img">
                <img src="<?= getProductImage($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            </div>
            <div class="product-badge">New</div>
            <div class="product-info">
                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="product-category"><?= htmlspecialchars($p['category_name'] ?? '') ?></div>
                <div class="product-footer">
                    <span class="product-price">$<?= number_format($p['price'], 2) ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>

        <!-- CTA Block -->
        <div class="trending-cta">
            <h3>Custom Made</h3>
            <p>Design your own pair. Choose your colors, patterns, and materials.</p>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-secondary btn-sm">Start Customizing</a>
        </div>
    </div>
</section>

<!-- EDITORIAL STRIP -->
<div class="editorial">
    <div class="editorial-text">
        <span class="editorial-label">Create Next</span>
        <h2 class="editorial-title">We Are The Canvas</h2>
        <p class="editorial-body">From the court to the streets to the stage. Converse has always been a tool for self-expression. We celebrate the creatives, the rebels, and the misfits who use our sneakers to paint their own path.</p>
        <a href="<?= SITE_URL ?>/products.php" class="btn btn-secondary">
            Read Our Story
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
    </div>
    <div class="editorial-img">
        <svg viewBox="0 0 600 500" xmlns="http://www.w3.org/2000/svg" style="width:100%;height:100%;background:#111">
            <rect width="600" height="500" fill="#111"/>
            <text x="50%" y="50%" text-anchor="middle" dominant-baseline="middle"
                  font-family="'Barlow Condensed', sans-serif" font-weight="900"
                  font-size="80" fill="#222" text-transform="uppercase">STREET</text>
            <circle cx="300" cy="250" r="180" fill="none" stroke="#333" stroke-width="1"/>
            <circle cx="300" cy="250" r="120" fill="none" stroke="#2a2a2a" stroke-width="1"/>
        </svg>
    </div>
</div>

<!-- FEATURED PRODUCTS -->
<section class="section">
    <div class="section-header">
        <h2 class="section-title">Featured</h2>
        <a href="<?= SITE_URL ?>/products.php" class="section-link">All Products</a>
    </div>
    <div class="product-grid">
        <?php foreach($featured as $p):
            $stock = $p['total_stock'] ?? getProductTotalStock($p['id']);
        ?>
        <a href="<?= SITE_URL ?>/product.php?slug=<?= $p['slug'] ?>" class="product-card">
            <div class="product-img">
                <img src="<?= getProductImage($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>">
            </div>
            <?php if($p['is_new']): ?><div class="product-badge red">New</div><?php endif; ?>
            <div class="product-info">
                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="product-category"><?= htmlspecialchars($p['category_name'] ?? '') ?></div>
                <div class="product-footer">
                    <span class="product-price">$<?= number_format($p['price'], 2) ?></span>
                    <?php if($stock == 0): ?>
                        <span class="stock-dot out" title="Out of Stock"></span>
                    <?php elseif($stock < 15): ?>
                        <span class="stock-dot low" title="Low Stock"></span>
                    <?php else: ?>
                        <span class="stock-dot in" title="In Stock"></span>
                    <?php endif; ?>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
