<?php
require_once __DIR__ . '/auth.php';
requireAdminAuth();

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: ' . ADMIN_URL . '/products.php'); exit; }

$product = getProductById($id);
if (!$product) { flash('error', 'Product not found.'); header('Location: ' . ADMIN_URL . '/products.php'); exit; }

$variants = getProductVariants($id);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $sku         = strtoupper(trim($_POST['sku'] ?? ''));
    $category_id = (int)($_POST['category_id'] ?? 0);
    $price       = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_new      = isset($_POST['is_new']) ? 1 : 0;

    if (!$name)  $errors[] = 'Product name is required.';
    if (!$sku)   $errors[] = 'SKU is required.';
    if (!is_numeric($price)) $errors[] = 'Valid price is required.';

    // Handle image
    $imageFile = $product['image'];
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image'], $product['image']);
        if (isset($upload['error'])) $errors[] = $upload['error'];
        else $imageFile = $upload['filename'];
    }

    if (empty($errors)) {
        $db = getDB();

        // Regenerate slug if name changed
        $slug = $product['slug'];
        if ($name !== $product['name']) {
            $slug = slugify($name);
            $check = $db->prepare("SELECT id FROM products WHERE slug = ? AND id != ?");
            $check->execute([$slug, $id]);
            if ($check->fetch()) $slug .= '-' . $id;
        }

        $stmt = $db->prepare("UPDATE products SET name=?, slug=?, sku=?, category_id=?, price=?,
                               description=?, image=?, is_featured=?, is_new=?, updated_at=NOW()
                               WHERE id=?");
        $stmt->execute([$name, $slug, $sku, $category_id ?: null,
                        $price, $description, $imageFile, $is_featured, $is_new, $id]);

        // Update variants: delete old, insert new
        $db->prepare("DELETE FROM product_variants WHERE product_id = ?")->execute([$id]);
        $sizes  = $_POST['sizes']  ?? [];
        $colors = $_POST['colors'] ?? [];
        $stocks = $_POST['stocks'] ?? [];

        $insVariant = $db->prepare("INSERT INTO product_variants (product_id, size, color, stock) VALUES (?,?,?,?)");
        foreach ($sizes as $i => $sz) {
            if (trim($sz) === '') continue;
            $insVariant->execute([$id, trim($sz), trim($colors[$i] ?? 'Default'), max(0, (int)($stocks[$i] ?? 0))]);
        }

        flash('success', 'Product updated successfully.');
        header('Location: ' . ADMIN_URL . '/products.php');
        exit;
    }

    // On error, reload variants from POST
    $variants = [];
    foreach(($_POST['sizes'] ?? []) as $i => $sz) {
        if(trim($sz) !== '') $variants[] = ['size'=>$sz,'color'=>$_POST['colors'][$i]??'','stock'=>$_POST['stocks'][$i]??0];
    }
    $product = array_merge($product, compact('name','sku','category_id','price','description','is_featured','is_new'));
}

$categories = getAllCategories();
$pageTitle = 'Edit Product';
include __DIR__ . '/layout-header.php';
?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
    <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin secondary sm">← Back</a>
    <h2 style="font-family:var(--font-display);font-weight:800;font-size:20px;text-transform:uppercase">Edit: <?= htmlspecialchars($product['name']) ?></h2>
</div>

<?php if($errors): ?>
<div class="flash error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">

    <div>
        <div class="form-card" style="margin-bottom:24px">
            <h2>Basic Information</h2>
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku']) ?>" required style="text-transform:uppercase">
                </div>
                <div class="form-group">
                    <label>Price (USD) *</label>
                    <input type="number" name="price" step="0.01" class="form-control" value="<?= htmlspecialchars($product['price']) ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    <option value="">— No Category —</option>
                    <?php foreach($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $product['category_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
            <div style="display:flex;gap:24px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                    <input type="checkbox" name="is_featured" <?= $product['is_featured']?'checked':'' ?> style="accent-color:#000">
                    <span>Featured Product</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                    <input type="checkbox" name="is_new" <?= $product['is_new']?'checked':'' ?> style="accent-color:#000">
                    <span>Mark as New</span>
                </label>
            </div>
        </div>

        <!-- Variants -->
        <div class="form-card">
            <h2>Sizes &amp; Stock</h2>
            <table class="variants-table" id="variantsTable">
                <thead><tr>
                    <th>Size</th><th>Color / Style</th><th>Stock Qty</th><th style="width:40px"></th>
                </tr></thead>
                <tbody>
                <?php foreach($variants as $v): ?>
                <tr>
                    <td><input type="text" name="sizes[]"  value="<?= htmlspecialchars($v['size']) ?>"></td>
                    <td><input type="text" name="colors[]" value="<?= htmlspecialchars($v['color']) ?>"></td>
                    <td><input type="number" name="stocks[]" value="<?= (int)$v['stock'] ?>" min="0"></td>
                    <td><button type="button" onclick="this.closest('tr').remove()" style="background:none;border:none;color:#D80027;cursor:pointer;font-size:16px">✕</button></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" onclick="addVariantRow()" class="btn-admin secondary sm" style="margin-top:12px">+ Add Row</button>
        </div>
    </div>

    <!-- Right -->
    <div>
        <div class="form-card">
            <h2>Product Image</h2>
            <div class="img-upload-area" onclick="document.getElementById('imageInput').click()" id="uploadArea">
                <img id="imgPreview" class="img-preview"
                     src="<?= getProductImage($product['image']) ?>"
                     alt="" style="display:block">
                <p style="margin-top:8px;font-size:12px;color:#999">Click to change image</p>
            </div>
            <input type="file" name="image" id="imageInput" accept="image/*" style="display:none" onchange="previewImage(this)">
            <?php if($product['image']): ?>
            <p style="font-size:11px;color:#999;margin-top:6px">Current: <?= htmlspecialchars($product['image']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form-card" style="margin-top:20px">
            <button type="submit" class="btn-admin primary" style="width:100%;justify-content:center">Update Product</button>
            <a href="<?= SITE_URL ?>/product.php?slug=<?= $product['slug'] ?>" target="_blank"
               class="btn-admin secondary" style="width:100%;justify-content:center;margin-top:8px">
               View on Store ↗
            </a>
        </div>
    </div>

</div>
</form>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('imgPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
function addVariantRow() {
    const tbody = document.querySelector('#variantsTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="sizes[]" placeholder="e.g. 9"></td>
        <td><input type="text" name="colors[]" value="Default"></td>
        <td><input type="number" name="stocks[]" value="0" min="0"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" style="background:none;border:none;color:#D80027;cursor:pointer;font-size:16px">✕</button></td>
    `;
    tbody.appendChild(tr);
}
</script>

<?php include __DIR__ . '/layout-footer.php'; ?>
