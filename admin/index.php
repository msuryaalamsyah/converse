<?php
require_once __DIR__ . '/auth.php';
requireAdminAuth();

$pageTitle = 'Dashboard';
$stats = getAdminStats();

// Low stock products
$db = getDB();
$lowStock = $db->query("SELECT p.name, p.sku, COALESCE(SUM(pv.stock),0) AS total
                        FROM products p
                        LEFT JOIN product_variants pv ON p.id = pv.product_id
                        GROUP BY p.id
                        HAVING total < 15
                        ORDER BY total ASC LIMIT 8")->fetchAll();

$recentProducts = $db->query("SELECT p.*, c.name AS cat,
                               COALESCE(SUM(pv.stock),0) AS total_stock
                               FROM products p
                               LEFT JOIN categories c ON p.category_id = c.id
                               LEFT JOIN product_variants pv ON p.id = pv.product_id
                               GROUP BY p.id
                               ORDER BY p.created_at DESC LIMIT 5")->fetchAll();

include __DIR__ . '/layout-header.php';
$flash = getFlash();
?>

<?php if($flash): ?>
<div class="flash <?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div>
<?php endif; ?>

<!-- STAT CARDS -->
<div class="stats-row">
    <div class="stat-card">
        <div class="stat-label">Total Products</div>
        <div class="stat-value"><?= $stats['total_products'] ?></div>
        <div class="stat-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Categories</div>
        <div class="stat-value"><?= $stats['total_categories'] ?></div>
        <div class="stat-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
    </div>
    <div class="stat-card <?= $stats['low_stock_count'] > 0 ? 'alert' : '' ?>">
        <div class="stat-label">Low Stock Alerts</div>
        <div class="stat-value"><?= $stats['low_stock_count'] ?> Items</div>
        <div class="stat-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Out of Stock</div>
        <div class="stat-value"><?= $stats['out_of_stock'] ?></div>
        <div class="stat-icon">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.5fr 1fr;gap:24px">

    <!-- Recent Products -->
    <div>
        <div class="section-hd">
            <h2>Recent Products</h2>
            <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin secondary sm">View All</a>
        </div>
        <div class="admin-table-wrap">
            <table>
                <thead><tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th></th>
                </tr></thead>
                <tbody>
                <?php foreach($recentProducts as $p): ?>
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <img src="<?= getProductImage($p['image']) ?>" class="product-img-sm" alt="">
                            <div>
                                <div style="font-weight:600;font-size:13px"><?= htmlspecialchars($p['name']) ?></div>
                                <div style="font-size:11px;color:#999"><?= htmlspecialchars($p['sku']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="tag grey"><?= htmlspecialchars($p['cat'] ?? '-') ?></span></td>
                    <td style="font-weight:600">$<?= number_format($p['price'],2) ?></td>
                    <td>
                        <?php $s = $p['total_stock'];
                            if($s == 0) echo '<span class="stock-status"><span class="stock-dot out"></span> Out</span>';
                            elseif($s < 15) echo '<span class="stock-status"><span class="stock-dot low"></span> ' . $s . ' Low</span>';
                            else echo '<span class="stock-status"><span class="stock-dot good"></span> ' . $s . '</span>';
                        ?>
                    </td>
                    <td>
                        <a href="<?= ADMIN_URL ?>/product-edit.php?id=<?= $p['id'] ?>" class="btn-icon" title="Edit">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    <div>
        <div class="section-hd">
            <h2>Low Stock Alert</h2>
            <a href="<?= ADMIN_URL ?>/stock.php" class="btn-admin danger sm">Manage Stock</a>
        </div>
        <div class="admin-table-wrap">
            <table>
                <thead><tr><th>Product</th><th>Total</th></tr></thead>
                <tbody>
                <?php foreach($lowStock as $p): ?>
                <tr>
                    <td>
                        <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($p['name']) ?></div>
                        <div style="font-size:11px;color:#999"><?= htmlspecialchars($p['sku']) ?></div>
                    </td>
                    <td>
                        <?php if($p['total'] == 0): ?>
                            <span class="tag red">OUT</span>
                        <?php else: ?>
                            <span style="color:var(--red);font-weight:700"><?= $p['total'] ?></span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($lowStock)): ?>
                <tr><td colspan="2" style="text-align:center;padding:24px;color:#999">All products are well stocked ✓</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick actions -->
<div style="display:flex;gap:12px;margin-top:28px">
    <a href="<?= ADMIN_URL ?>/product-add.php" class="btn-admin primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add New Product
    </a>
    <a href="<?= ADMIN_URL ?>/categories.php" class="btn-admin secondary">Manage Categories</a>
    <a href="<?= ADMIN_URL ?>/stock.php" class="btn-admin secondary">Update Stock</a>
</div>

<?php include __DIR__ . '/layout-footer.php'; ?>
