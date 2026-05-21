<?php
require_once __DIR__ . '/includes/header.php';

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { header('Location: ' . SITE_URL . '/products.php'); exit; }

$product = getProductBySlug($slug);
if (!$product) { http_response_code(404); die('<h1 style="text-align:center;padding:100px;font-family:sans-serif">Product not found.</h1>'); }

$variants = getProductVariants($product['id']);
$totalStock = array_sum(array_column($variants, 'stock'));

// Group sizes
$sizeStocks = [];
foreach($variants as $v) {
    $sizeStocks[$v['size']] = ($sizeStocks[$v['size']] ?? 0) + $v['stock'];
}

$pageTitle = $product['name'];

// Related products
$db = getDB();
$related = [];
if ($product['category_id']) {
    $stmt = $db->prepare("SELECT p.*, COALESCE(SUM(pv.stock),0) AS total_stock
                          FROM products p
                          LEFT JOIN product_variants pv ON p.id = pv.product_id
                          WHERE p.category_id = ? AND p.id != ?
                          GROUP BY p.id LIMIT 4");
    $stmt->execute([$product['category_id'], $product['id']]);
    $related = $stmt->fetchAll();
}
?>

<div class="product-detail">

    <!-- Gallery -->
    <div class="detail-gallery">
        <div class="detail-main-img">
            <img src="<?= getProductImage($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" id="mainImg">
        </div>
        <div class="detail-thumbs">
            <div class="detail-thumb active">
                <img src="<?= getProductImage($product['image']) ?>" alt="">
            </div>
        </div>
    </div>

    <!-- Info -->
    <div class="detail-info">

        <div class="detail-category"><?= htmlspecialchars($product['category_name'] ?? '') ?></div>
        <h1 class="detail-name"><?= htmlspecialchars($product['name']) ?></h1>
        <div class="detail-sku">SKU: <?= htmlspecialchars($product['sku']) ?></div>
        <div class="detail-price">$<?= number_format($product['price'], 2) ?></div>

        <!-- Stock Status -->
        <div style="margin-bottom:24px;display:flex;align-items:center;gap:8px">
            <?php if($totalStock == 0): ?>
                <span class="stock-dot out"></span>
                <span style="font-size:13px;color:#999">Out of Stock</span>
            <?php elseif($totalStock < 15): ?>
                <span class="stock-dot low"></span>
                <span style="font-size:13px;color:var(--red)">Low Stock — Only <?= $totalStock ?> left</span>
            <?php else: ?>
                <span class="stock-dot in"></span>
                <span style="font-size:13px;color:#1c8c3c">In Stock</span>
            <?php endif; ?>
        </div>

        <!-- Sizes -->
        <?php if(!empty($sizeStocks)): ?>
        <div>
            <div class="detail-section-label">
                Select Size
                <a href="#" style="font-size:11px;color:var(--grey3);font-family:var(--font-body);font-weight:400;border-bottom:1px solid">Size Guide</a>
            </div>
            <div class="sizes-grid">
                <?php foreach($sizeStocks as $size => $stock): ?>
                <button class="size-option <?= $stock == 0 ? 'out' : '' ?>"
                    onclick="selectSize(this, '<?= $size ?>')"
                    data-size="<?= $size ?>"
                    <?= $stock == 0 ? 'disabled title="Out of Stock"' : '' ?>>
                    <?= $size ?>
                </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Add to Cart -->
        <button class="add-to-cart-btn" onclick="addToCart(<?= $product['id'] ?>)">
            Add to Cart
        </button>

        <!-- Description -->
        <?php if($product['description']): ?>
        <div class="detail-desc"><?= nl2br(htmlspecialchars($product['description'])) ?></div>
        <?php endif; ?>

        <!-- Meta -->
        <div style="font-size:12px;color:var(--grey3);display:flex;flex-direction:column;gap:6px;border-top:1px solid var(--grey2);padding-top:20px">
            <?php if($product['category_name']): ?>
            <div>Category: <a href="<?= SITE_URL ?>/products.php?category=<?= $product['category_slug'] ?? '' ?>" style="text-decoration:underline"><?= htmlspecialchars($product['category_name']) ?></a></div>
            <?php endif; ?>
            <div>SKU: <?= htmlspecialchars($product['sku']) ?></div>
        </div>
    </div>
</div>

<!-- Related Products -->
<?php if(!empty($related)): ?>
<section class="section" style="border-top:2px solid var(--black)">
    <div class="section-header">
        <h2 class="section-title">You May Also Like</h2>
    </div>
    <div class="product-grid">
        <?php foreach($related as $p): ?>
        <a href="<?= SITE_URL ?>/product.php?slug=<?= $p['slug'] ?>" class="product-card">
            <div class="product-img">
                <img src="<?= getProductImage($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
            </div>
            <div class="product-info">
                <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                <div class="product-footer">
                    <span class="product-price">$<?= number_format($p['price'], 2) ?></span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<script>
function selectSize(btn, size) {
    document.querySelectorAll('.size-option').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
}
function addToCart(id) {
    const size = document.querySelector('.size-option.selected');
    if (!size) { alert('Please select a size first.'); return; }
    alert('Added to cart! (Size: ' + size.dataset.size + ')');
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
