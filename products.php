<?php
$pageTitle = 'Products';
require_once __DIR__ . '/includes/header.php';

$db = getDB();
$q = trim($_GET['q'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$sort = $_GET['sort'] ?? 'featured';
$filter = $_GET['filter'] ?? '';

// Build query
$sql = "SELECT p.*, c.name AS category_name, c.slug AS category_slug,
               COALESCE(SUM(pv.stock), 0) AS total_stock
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_variants pv ON p.id = pv.product_id";

$params = [];
$where  = [];

if ($q)            { $where[] = "(p.name LIKE ? OR p.sku LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($categorySlug) { $where[] = "c.slug = ?"; $params[] = $categorySlug; }
if ($filter === 'new') { $where[] = "p.is_new = 1"; }

if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " GROUP BY p.id";

switch($sort) {
    case 'price_asc':  $sql .= " ORDER BY p.price ASC"; break;
    case 'price_desc': $sql .= " ORDER BY p.price DESC"; break;
    case 'new':        $sql .= " ORDER BY p.created_at DESC"; break;
    default:           $sql .= " ORDER BY p.is_featured DESC, p.created_at DESC";
}

$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
$total = count($products);

$categories = getAllCategories();
$currentCat = $categorySlug ? getCategoryBySlug($categorySlug) : null;
$pageTitle = $currentCat ? htmlspecialchars($currentCat['name']) : ($q ? 'Search: ' . htmlspecialchars($q) : 'All Sneakers');
?>

<div class="products-page">
    <div class="products-header">
        <h1><?= $pageTitle ?></h1>
        <?php if($q): ?>
        <p>Search results for "<strong><?= htmlspecialchars($q) ?></strong>" — <?= $total ?> result<?= $total != 1 ? 's' : '' ?></p>
        <?php elseif($currentCat): ?>
        <p><?= htmlspecialchars($currentCat['description'] ?? '') ?></p>
        <?php else: ?>
        <p>Explore our latest collection of high-top, low-top, and platform sneakers designed for the street.</p>
        <?php endif; ?>
        <hr>
    </div>

    <div class="products-layout">

        <!-- Sidebar Filters -->
        <aside class="filters-sidebar">
            <div class="filter-group">
                <h4>Size</h4>
                <div class="size-grid">
                    <?php foreach(['6','7','8','9','10','11','12','13'] as $sz): ?>
                    <button class="size-btn" onclick="this.classList.toggle('active')"><?= $sz ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="filter-group">
                <h4>Color</h4>
                <div class="color-swatches">
                    <div class="swatch" style="background:#000" onclick="this.classList.toggle('active')" title="Black"></div>
                    <div class="swatch" style="background:#fff;border:2px solid #ddd" onclick="this.classList.toggle('active')" title="White"></div>
                    <div class="swatch" style="background:#D80027" onclick="this.classList.toggle('active')" title="Red"></div>
                    <div class="swatch" style="background:#1a3a8f" onclick="this.classList.toggle('active')" title="Navy"></div>
                    <div class="swatch" style="background:#b8a070" onclick="this.classList.toggle('active')" title="Tan"></div>
                </div>
            </div>

            <div class="filter-group">
                <h4>Style</h4>
                <?php foreach($categories as $cat): ?>
                <div class="filter-check">
                    <input type="checkbox" id="cat_<?= $cat['id'] ?>"
                        <?= $categorySlug === $cat['slug'] ? 'checked' : '' ?>
                        onchange="window.location='<?= SITE_URL ?>/products.php?category=<?= $cat['slug'] ?>'">
                    <label for="cat_<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></label>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if($categorySlug || $q || $filter): ?>
            <a href="<?= SITE_URL ?>/products.php" class="btn btn-secondary btn-sm">Clear Filters</a>
            <?php endif; ?>
        </aside>

        <!-- Products Content -->
        <div>
            <div class="products-toolbar">
                <span class="products-count">Showing <?= $total ?> Result<?= $total != 1 ? 's' : '' ?></span>
                <form method="GET">
                    <?php if($categorySlug): ?><input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug) ?>"><?php endif; ?>
                    <?php if($q): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
                    <select name="sort" class="sort-select" onchange="this.form.submit()">
                        <option value="featured"   <?= $sort=='featured'   ?'selected':'' ?>>Sort By: Featured</option>
                        <option value="new"        <?= $sort=='new'        ?'selected':'' ?>>Newest</option>
                        <option value="price_asc"  <?= $sort=='price_asc'  ?'selected':'' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort=='price_desc' ?'selected':'' ?>>Price: High to Low</option>
                    </select>
                </form>
            </div>

            <?php if(empty($products)): ?>
            <div style="text-align:center;padding:80px 20px;border:2px dashed var(--grey2)">
                <div style="font-family:var(--font-display);font-size:32px;font-weight:800;text-transform:uppercase;margin-bottom:12px">No Products Found</div>
                <p style="color:var(--grey3);margin-bottom:24px">Try adjusting your filters or search terms.</p>
                <a href="<?= SITE_URL ?>/products.php" class="btn btn-primary">Browse All</a>
            </div>
            <?php else: ?>
            <div class="product-grid">
                <?php foreach($products as $p): ?>
                <a href="<?= SITE_URL ?>/product.php?slug=<?= $p['slug'] ?>" class="product-card">
                    <div class="product-img">
                        <img src="<?= getProductImage($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    </div>
                    <?php if($p['is_new']): ?><div class="product-badge red">New</div>
                    <?php elseif($p['is_featured']): ?><div class="product-badge">Bestseller</div><?php endif; ?>
                    <div class="product-info">
                        <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div class="product-category"><?= htmlspecialchars($p['category_name'] ?? '') ?></div>
                        <div class="product-footer">
                            <span class="product-price">$<?= number_format($p['price'], 2) ?></span>
                            <?php
                                $stock = $p['total_stock'];
                                if($stock == 0) echo '<span class="stock-dot out"></span>';
                                elseif($stock < 15) echo '<span class="stock-dot low" title="Low Stock"></span>';
                                else echo '<span class="stock-dot in"></span>';
                            ?>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
