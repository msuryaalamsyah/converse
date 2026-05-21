<?php
require_once __DIR__ . '/auth.php';
requireAdminAuth();

$db = getDB();
$errors = [];

// Handle bulk stock update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['variant_id'])) {
    $variantIds = $_POST['variant_id'];
    $stocks     = $_POST['stock'];
    $upd = $db->prepare("UPDATE product_variants SET stock=? WHERE id=?");
    foreach ($variantIds as $i => $vid) {
        $upd->execute([max(0, (int)($stocks[$i] ?? 0)), (int)$vid]);
    }
    flash('success', 'Stock updated successfully.');
    header('Location: ' . ADMIN_URL . '/stock.php' . ($_GET['product_id'] ? '?product_id='.(int)$_GET['product_id'] : ''));
    exit;
}

$productId = (int)($_GET['product_id'] ?? 0);
$filterLow = isset($_GET['low']);

// All products for dropdown
$allProducts = $db->query("SELECT p.id, p.name, p.sku FROM products p ORDER BY p.name ASC")->fetchAll();

// Load variants
$sql = "SELECT pv.*, p.name AS product_name, p.sku
        FROM product_variants pv
        JOIN products p ON pv.product_id = p.id";
$params = [];
$where = [];
if ($productId) { $where[] = "pv.product_id = ?"; $params[] = $productId; }
if ($filterLow) { $where[] = "pv.stock < 10"; }
if ($where) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY p.name ASC, pv.size+0 ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$variants = $stmt->fetchAll();

$pageTitle = 'Stock Management';
include __DIR__ . '/layout-header.php';
$flash = getFlash();
?>

<?php if($flash): ?><div class="flash <?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>

<!-- Toolbar -->
<div style="display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap">
    <form method="GET" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <select name="product_id" class="form-control" style="width:280px" onchange="this.form.submit()">
            <option value="">— All Products —</option>
            <?php foreach($allProducts as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $productId==$p['id']?'selected':'' ?>>
                <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['sku']) ?>)
            </option>
            <?php endforeach; ?>
        </select>
        <label style="display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer">
            <input type="checkbox" name="low" <?= $filterLow?'checked':'' ?> onchange="this.form.submit()" style="accent-color:#D80027">
            Show Low Stock Only (&lt;10)
        </label>
        <?php if($productId || $filterLow): ?>
        <a href="<?= ADMIN_URL ?>/stock.php" class="btn-admin secondary sm">Clear Filter</a>
        <?php endif; ?>
    </form>
    <div style="margin-left:auto;font-size:13px;color:#666"><?= count($variants) ?> variant(s)</div>
</div>

<form method="POST">
<div class="admin-table-wrap">
    <table>
        <thead><tr>
            <th>Product</th>
            <th>SKU</th>
            <th>Size</th>
            <th>Color</th>
            <th>Current Stock</th>
            <th>Update Stock</th>
            <th>Status</th>
        </tr></thead>
        <tbody>
        <?php if(empty($variants)): ?>
        <tr><td colspan="7" style="text-align:center;padding:40px;color:#999">No variants found.</td></tr>
        <?php endif; ?>
        <?php foreach($variants as $v): ?>
        <tr <?= $v['stock'] == 0 ? 'style="background:#fff9f9"' : ($v['stock'] < 10 ? 'style="background:#fffaf0"' : '') ?>>
            <td style="font-size:13px;font-weight:600"><?= htmlspecialchars($v['product_name']) ?></td>
            <td style="font-size:12px;color:#999"><?= htmlspecialchars($v['sku']) ?></td>
            <td><span class="tag grey"><?= htmlspecialchars($v['size']) ?></span></td>
            <td style="font-size:13px"><?= htmlspecialchars($v['color']) ?></td>
            <td style="font-weight:700;font-size:15px;<?= $v['stock']==0?'color:var(--red)':($v['stock']<10?'color:#d97706':'') ?>">
                <?= $v['stock'] ?>
            </td>
            <td>
                <input type="hidden" name="variant_id[]" value="<?= $v['id'] ?>">
                <input type="number" name="stock[]" value="<?= $v['stock'] ?>" min="0"
                       style="width:90px;border:2px solid #000;padding:6px 10px;font-size:14px;font-weight:700;outline:none"
                       onfocus="this.style.borderColor='#D80027'"
                       onblur="this.style.borderColor='#000'">
            </td>
            <td>
                <?php if($v['stock'] == 0): ?>
                    <span class="stock-status"><span class="stock-dot out"></span> Out of Stock</span>
                <?php elseif($v['stock'] < 10): ?>
                    <span class="stock-status"><span class="stock-dot low"></span> Low Stock</span>
                <?php else: ?>
                    <span class="stock-status"><span class="stock-dot good"></span> In Stock</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if(!empty($variants)): ?>
<div style="margin-top:16px;display:flex;gap:10px">
    <button type="submit" class="btn-admin primary">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Save All Stock Changes
    </button>
    <a href="<?= ADMIN_URL ?>/stock.php<?= $productId ? '?product_id='.$productId : '' ?>" class="btn-admin secondary">Reset</a>
</div>
<?php endif; ?>
</form>

<?php include __DIR__ . '/layout-footer.php'; ?>
