<?php
require_once __DIR__ . '/auth.php';
requireAdminAuth();

$db = getDB();

// Handle actions
$action = $_GET['action'] ?? '';
$editId = (int)($_GET['edit'] ?? 0);

// Delete
if ($action === 'delete' && isset($_GET['id'])) {
    $db->prepare("DELETE FROM categories WHERE id=?")->execute([(int)$_GET['id']]);
    flash('success', 'Category deleted.');
    header('Location: ' . ADMIN_URL . '/categories.php');
    exit;
}

$editCat = null;
if ($editId) {
    $stmt = $db->prepare("SELECT * FROM categories WHERE id=?");
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $catId       = (int)($_POST['cat_id'] ?? 0);

    if (!$name) $errors[] = 'Category name is required.';

    $slug = slugify($name);

    if (empty($errors)) {
        if ($catId) {
            // Update
            $check = $db->prepare("SELECT id FROM categories WHERE slug=? AND id!=?");
            $check->execute([$slug, $catId]);
            if ($check->fetch()) $slug .= '-' . $catId;
            $db->prepare("UPDATE categories SET name=?, slug=?, description=? WHERE id=?")
               ->execute([$name, $slug, $description, $catId]);
            flash('success', 'Category updated.');
        } else {
            // Insert
            $check = $db->prepare("SELECT id FROM categories WHERE slug=?");
            $check->execute([$slug]);
            if ($check->fetch()) $slug .= '-' . time();
            $db->prepare("INSERT INTO categories (name, slug, description) VALUES (?,?,?)")
               ->execute([$name, $slug, $description]);
            flash('success', 'Category created.');
        }
        header('Location: ' . ADMIN_URL . '/categories.php');
        exit;
    }
}

// Load all categories with product count
$categories = $db->query("SELECT c.*, COUNT(p.id) AS product_count
                          FROM categories c
                          LEFT JOIN products p ON p.category_id = c.id
                          GROUP BY c.id ORDER BY c.name ASC")->fetchAll();

$pageTitle = 'Categories';
include __DIR__ . '/layout-header.php';
$flash = getFlash();
?>

<?php if($flash): ?><div class="flash <?= $flash['type'] ?>"><?= htmlspecialchars($flash['msg']) ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">

    <!-- Categories Table -->
    <div>
        <div class="section-hd">
            <h2>All Categories (<?= count($categories) ?>)</h2>
        </div>
        <div class="admin-table-wrap">
            <table>
                <thead><tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Description</th>
                    <th>Products</th>
                    <th>Actions</th>
                </tr></thead>
                <tbody>
                <?php if(empty($categories)): ?>
                <tr><td colspan="5" style="text-align:center;padding:32px;color:#999">No categories yet.</td></tr>
                <?php endif; ?>
                <?php foreach($categories as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                    <td><code style="font-size:12px;background:#f5f5f5;padding:2px 6px"><?= htmlspecialchars($c['slug']) ?></code></td>
                    <td style="font-size:13px;color:#666;max-width:200px"><?= htmlspecialchars(mb_strimwidth($c['description'] ?? '', 0, 60, '...')) ?></td>
                    <td><span class="tag grey"><?= $c['product_count'] ?> items</span></td>
                    <td>
                        <div class="action-btns">
                            <a href="<?= ADMIN_URL ?>/categories.php?edit=<?= $c['id'] ?>" class="btn-icon" title="Edit">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </a>
                            <?php if($c['product_count'] == 0): ?>
                            <button class="btn-icon danger" onclick="confirmDelete('<?= ADMIN_URL ?>/categories.php?action=delete&id=<?= $c['id'] ?>', '<?= addslashes($c['name']) ?>')" title="Delete">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                            </button>
                            <?php else: ?>
                            <button class="btn-icon" disabled title="Has products" style="opacity:0.3;cursor:not-allowed">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/></svg>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add / Edit Form -->
    <div class="form-card">
        <h2><?= $editCat ? 'Edit Category' : 'Add Category' ?></h2>
        <?php if($errors): ?>
        <div class="flash error"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="cat_id" value="<?= $editCat ? $editCat['id'] : 0 ?>">
            <div class="form-group">
                <label>Name *</label>
                <input type="text" name="name" class="form-control"
                       value="<?= htmlspecialchars($editCat ? $editCat['name'] : '') ?>" required
                       placeholder="e.g. High Top">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="3"
                          placeholder="Brief description..."><?= htmlspecialchars($editCat ? ($editCat['description'] ?? '') : '') ?></textarea>
            </div>
            <button type="submit" class="btn-admin primary" style="width:100%;justify-content:center">
                <?= $editCat ? 'Update Category' : 'Create Category' ?>
            </button>
            <?php if($editCat): ?>
            <a href="<?= ADMIN_URL ?>/categories.php" class="btn-admin secondary" style="width:100%;justify-content:center;margin-top:8px">
                Cancel Edit
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php include __DIR__ . '/layout-footer.php'; ?>
