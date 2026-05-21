<?php
require_once __DIR__ . '/auth.php';
requireAdminAuth();

$pageTitle = 'Add Product';
$errors = [];
$data = ['name'=>'','sku'=>'','category_id'=>'','price'=>'','description'=>'','is_featured'=>0,'is_new'=>0];

// Default sizes
$defaultSizes = ['6','7','8','9','10','11','12','13'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name']        = trim($_POST['name'] ?? '');
    $data['sku']         = strtoupper(trim($_POST['sku'] ?? ''));
    $data['category_id'] = (int)($_POST['category_id'] ?? 0);
    $data['price']       = trim($_POST['price'] ?? '');
    $data['description'] = trim($_POST['description'] ?? '');
    $data['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    $data['is_new']      = isset($_POST['is_new']) ? 1 : 0;

    if (!$data['name'])  $errors[] = 'Product name is required.';
    if (!$data['sku'])   $errors[] = 'SKU is required.';
    if (!$data['price'] || !is_numeric($data['price'])) $errors[] = 'Valid price is required.';

    $imageFile = null;
    if (!empty($_FILES['image']['name'])) {
        $upload = uploadImage($_FILES['image']);
        if (isset($upload['error'])) $errors[] = $upload['error'];
        else $imageFile = $upload['filename'];
    }

    if (empty($errors)) {
        $db = getDB();
        $slug = slugify($data['name']);
        // Ensure unique slug
        $existing = $db->prepare("SELECT id FROM products WHERE slug = ?");
        $existing->execute([$slug]);
        if ($existing->fetch()) $slug .= '-' . time();

        $stmt = $db->prepare("INSERT INTO products (name, slug, sku, category_id, price, description, image, is_featured, is_new)
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'], $slug, $data['sku'],
            $data['category_id'] ?: null,
            $data['price'], $data['description'],
            $imageFile, $data['is_featured'], $data['is_new']
        ]);
        $productId = $db->lastInsertId();

        // Save variants
        $sizes  = $_POST['sizes']  ?? [];
        $colors = $_POST['colors'] ?? [];
        $stocks = $_POST['stocks'] ?? [];

        $insVariant = $db->prepare("INSERT INTO product_variants (product_id, size, color, stock) VALUES (?,?,?,?)");
        foreach ($sizes as $i => $sz) {
            if (trim($sz) === '') continue;
            $insVariant->execute([
                $productId,
                trim($sz),
                trim($colors[$i] ?? 'Default'),
                max(0, (int)($stocks[$i] ?? 0))
            ]);
        }

        flash('success', 'Product "' . $data['name'] . '" added successfully.');
        header('Location: ' . ADMIN_URL . '/products.php');
        exit;
    }
}

$categories = getAllCategories();
include __DIR__ . '/layout-header.php';
?>

<div style="display:flex;align-items:center;gap:12px;margin-bottom:24px">
    <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin secondary sm">← Back</a>
    <h2 style="font-family:var(--font-display);font-weight:800;font-size:20px;text-transform:uppercase">Add New Product</h2>
</div>

<?php if($errors): ?>
<div class="flash error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start">

    <!-- Left Column -->
    <div>
        <div class="form-card" style="margin-bottom:24px">
            <h2>Basic Information</h2>
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" required placeholder="e.g. Chuck Taylor All Star">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>SKU *</label>
                    <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($data['sku']) ?>" required placeholder="CV-100293-BLK" style="text-transform:uppercase">
                </div>
                <div class="form-group">
                    <label>Price (USD) *</label>
                    <input type="number" name="price" step="0.01" class="form-control" value="<?= htmlspecialchars($data['price']) ?>" required placeholder="85.00">
                </div>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" class="form-control">
                    <option value="">— No Category —</option>
                    <?php foreach($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $data['category_id']==$c['id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="4" placeholder="Product description..."><?= htmlspecialchars($data['description']) ?></textarea>
            </div>
            <div style="display:flex;gap:24px">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                    <input type="checkbox" name="is_featured" <?= $data['is_featured']?'checked':'' ?> style="accent-color:#000">
                    <span>Featured Product</span>
                </label>
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                    <input type="checkbox" name="is_new" <?= $data['is_new']?'checked':'' ?> style="accent-color:#000">
                    <span>Mark as New</span>
                </label>
            </div>
        </div>

        <!-- Variants / Stock -->
        <div class="form-card">
            <h2>Sizes &amp; Stock</h2>
            <p style="font-size:13px;color:#999;margin-bottom:16px">Add size variants and their initial stock quantities.</p>
            <table class="variants-table" id="variantsTable">
                <thead><tr>
                    <th>Size</th>
                    <th>Color / Style</th>
                    <th>Stock Qty</th>
                    <th style="width:40px"></th>
                </tr></thead>
                <tbody>
                <?php foreach($defaultSizes as $sz): ?>
                <tr>
                    <td><input type="text" name="sizes[]"  value="<?= $sz ?>" placeholder="e.g. 9"></td>
                    <td><input type="text" name="colors[]" value="Default"  placeholder="Black"></td>
                    <td><input type="number" name="stocks[]" value="0" min="0"></td>
                    <td><button type="button" onclick="this.closest('tr').remove()" style="background:none;border:none;color:#D80027;cursor:pointer;font-size:16px">✕</button></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" onclick="addVariantRow()" class="btn-admin secondary sm" style="margin-top:12px">+ Add Row</button>
        </div>
    </div>

    <!-- Right Column -->
    <div>
        <div class="form-card">
            <h2>Product Image</h2>
            <div class="img-upload-area" onclick="document.getElementById('imageInput').click()" id="uploadArea">
                <img id="imgPreview" class="img-preview" src="<?= SITE_URL ?>/assets/img/placeholder.svg" alt="" style="display:none">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ccc" stroke-width="1.5" id="uploadIcon"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <p>Click to upload image<br><small>JPG, PNG, WEBP — max 5MB</small></p>
            </div>
            <input type="file" name="image" id="imageInput" accept="image/*" style="display:none" onchange="previewImage(this)">
        </div>

        <div class="form-card" style="margin-top:20px">
            <button type="submit" class="btn-admin primary" style="width:100%;justify-content:center">
                Save Product
            </button>
            <a href="<?= ADMIN_URL ?>/products.php" class="btn-admin secondary" style="width:100%;justify-content:center;margin-top:8px">
                Cancel
            </a>
        </div>
    </div>

</div>
</form>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const img = document.getElementById('imgPreview');
            img.src = e.target.result;
            img.style.display = 'block';
            document.getElementById('uploadIcon').style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
function addVariantRow() {
    const tbody = document.querySelector('#variantsTable tbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td><input type="text" name="sizes[]" placeholder="e.g. 9"></td>
        <td><input type="text" name="colors[]" value="Default" placeholder="Color"></td>
        <td><input type="number" name="stocks[]" value="0" min="0"></td>
        <td><button type="button" onclick="this.closest('tr').remove()" style="background:none;border:none;color:#D80027;cursor:pointer;font-size:16px">✕</button></td>
    `;
    tbody.appendChild(tr);
}
</script>

<?php include __DIR__ . '/layout-footer.php'; ?>
