<?php
require_once __DIR__ . '/auth.php';
requireAdminAuth();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db = getDB();
    $prod = $db->prepare("SELECT image FROM products WHERE id = ?");
    $prod->execute([$id]);
    $row = $prod->fetch();
    if ($row && $row['image'] && file_exists(UPLOADS_PATH . $row['image'])) {
        unlink(UPLOADS_PATH . $row['image']);
    }
    $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    flash('success', 'Product deleted successfully.');
    header('Location: ' . ADMIN_URL . '/products.php');
    exit;
}

$db = getDB();
$q = trim($_GET['q'] ?? '');
$catFilter = (int)($_GET['cat'] ?? 0);
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$sql = "SELECT p.*, c.name AS cat_name,
               COALESCE(SUM(pv.stock),0) AS total_stock
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN product_variants pv ON p.id = pv.product_id";
$params = [];
$where = [];
if ($q)         { $where[] = "(p.name LIKE ? OR p.sku LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
if ($catFilter) { $where[] = "p.category_id = ?"; $params[] = $catFilter; }
if ($where)     $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " GROUP BY p.id ORDER BY p.created_at DESC";

// Count total
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id=c.id";
if ($where) $countSql .= " WHERE " . implode(" AND ", $where);
$countStmt = $db->prepare($countSql);
$countStmt->execute($catFilter ? ($q ? ["%$q%","%$q%",$catFilter] : [$catFilter]) : ($q ? ["%$q%","%$q%"] : []));
$total = $countStmt->fetchColumn();
$pages = max(1, ceil($total / $perPage));

$sql .= " LIMIT $perPage OFFSET " . (($page - 1) * $perPage);
$stmt = $db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = getAllCategories();
$pageTitle = 'Products';
include __DIR__ . '/layout-header.php';
$flash = getFlash();
?>

<?php if($flash): ?><div class="flash <?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>

<div class="section-hd">
    <h2>All Products (<?= $total ?>)</h2>
    <a href="<?= ADMIN_URL ?>/product-add.php" class="btn-admin primary sm">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add New Product
    </a>
</div>

<div class="admin-table-wrap">
    <div class="table-toolbar">
        <form method="GET" style="display:flex;gap:10px;flex:1;flex-wrap:wrap">
            <div class="search-input" style="max-width:320px">
                <input type="text" name="q" placeholder="Search by name or SKU..." value="<?= htmlspecialchars($q) ?>">
                <button type="submit">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </button>
            </div>
            <select name="cat" class="form-control" style="width:auto;padding:8px 12px" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php foreach($categories as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $catFilter==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <?php if($q || $catFilter): ?>
            <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin secondary sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <table>
        <thead><tr>
            <th style="width:60px">Image</th>
            <th>Product Name</th>
            <th>SKU</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Actions</th>
        </tr></thead>
        <tbody>
        <?php if(empty($products)): ?>
        <tr><td colspan="8" style="text-align:center;padding:40px;color:#999">No products found.</td></tr>
        <?php endif; ?>
        <?php foreach($products as $p): ?>
        <tr>
            <td><img src="<?= getProductImage($p['image']) ?>" class="product-img-sm" alt=""></td>
            <td>
                <div style="font-weight:600"><?= htmlspecialchars($p['name']) ?></div>
            </td>
            <td style="font-size:12px;color:#999"><?= htmlspecialchars($p['sku']) ?></td>
            <td><span class="tag grey"><?= htmlspecialchars($p['cat_name'] ?? '-') ?></span></td>
            <td style="font-weight:600">$<?= number_format($p['price'],2) ?></td>
            <td>
                <?php $s = $p['total_stock'];
                    if($s == 0) echo '<span class="stock-status"><span class="stock-dot out"></span> Out</span>';
                    elseif($s < 15) echo '<span class="stock-status"><span class="stock-dot low"></span> ' . $s . '</span>';
                    else echo '<span class="stock-status"><span class="stock-dot good"></span> ' . $s . '</span>';
                ?>
            </td>
            <td>
                <?php if($p['is_new']): ?>    <span class="tag red">New</span>
                <?php elseif($p['is_featured']): ?><span class="tag">Featured</span>
                <?php else: ?>                    <span class="tag grey">Normal</span>
                <?php endif; ?>
            </td>
            <td>
                <div class="action-btns">
                    <a href="<?= ADMIN_URL ?>/product-edit.php?id=<?= $p['id'] ?>" class="btn-icon" title="Edit">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </a>
                    <a href="<?= SITE_URL ?>/product.php?slug=<?= $p['slug'] ?>" class="btn-icon" target="_blank" title="View">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    </a>
                    <button class="btn-icon danger" onclick="confirmDelete('<?= ADMIN_URL ?>/products.php?delete=<?= $p['id'] ?>', '<?= addslashes($p['name']) ?>')" title="Delete">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                    </button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if($pages > 1): ?>
    <div class="pagination">
        <span class="pagination-info">Showing <?= (($page-1)*$perPage)+1 ?> to <?= min($page*$perPage, $total) ?> of <?= $total ?> entries</span>
        <div class="page-btns">
            <?php if($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&q=<?= urlencode($q) ?>&cat=<?= $catFilter ?>" class="page-btn">‹</a>
            <?php endif; ?>
            <?php for($i = max(1,$page-2); $i <= min($pages,$page+2); $i++): ?>
            <a href="?page=<?= $i ?>&q=<?= urlencode($q) ?>&cat=<?= $catFilter ?>" class="page-btn <?= $i==$page?'active':'' ?>"><?= $i ?></a>
            <?php endfor; ?>
            <?php if($page < $pages): ?>
            <a href="?page=<?= $page+1 ?>&q=<?= urlencode($q) ?>&cat=<?= $catFilter ?>" class="page-btn">›</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/layout-footer.php'; ?>
