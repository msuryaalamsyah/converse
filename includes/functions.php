<?php
require_once __DIR__ . '/config.php';

// ── Product Helpers ──────────────────────────────────────────

function getAllProducts($limit = null, $featured = false, $isNew = false) {
    $db = getDB();
    $sql = "SELECT p.*, c.name AS category_name,
                   COALESCE(SUM(pv.stock), 0) AS total_stock
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id";
    $params = [];
    $where = [];
    if ($featured) $where[] = "p.is_featured = 1";
    if ($isNew)    $where[] = "p.is_new = 1";
    if ($where)    $sql .= " WHERE " . implode(" AND ", $where);
    $sql .= " GROUP BY p.id ORDER BY p.created_at DESC";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    return $db->query($sql)->fetchAll();
}

function getProductBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          WHERE p.slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getProductById($id) {
    $db = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name
                          FROM products p
                          LEFT JOIN categories c ON p.category_id = c.id
                          WHERE p.id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProductVariants($productId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY size+0 ASC");
    $stmt->execute([$productId]);
    return $stmt->fetchAll();
}

function getProductTotalStock($productId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT COALESCE(SUM(stock), 0) as total FROM product_variants WHERE product_id = ?");
    $stmt->execute([$productId]);
    return $stmt->fetch()['total'];
}

function getProductsByCategory($categorySlug, $limit = null) {
    $db = getDB();
    $sql = "SELECT p.*, c.name AS category_name,
                   COALESCE(SUM(pv.stock), 0) AS total_stock
            FROM products p
            JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_variants pv ON p.id = pv.product_id
            WHERE c.slug = ?
            GROUP BY p.id ORDER BY p.created_at DESC";
    if ($limit) $sql .= " LIMIT " . (int)$limit;
    $stmt = $db->prepare($sql);
    $stmt->execute([$categorySlug]);
    return $stmt->fetchAll();
}

// ── Category Helpers ──────────────────────────────────────────

function getAllCategories() {
    $db = getDB();
    return $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
}

function getCategoryBySlug($slug) {
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

// ── Stats Helpers ──────────────────────────────────────────

function getAdminStats() {
    $db = getDB();
    $stats = [];
    $stats['total_products'] = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $stats['total_categories'] = $db->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    $stats['low_stock_count'] = $db->query(
        "SELECT COUNT(DISTINCT product_id) FROM product_variants WHERE stock < 10"
    )->fetchColumn();
    $stats['out_of_stock'] = $db->query(
        "SELECT COUNT(*) FROM products p WHERE (SELECT COALESCE(SUM(stock),0) FROM product_variants WHERE product_id = p.id) = 0"
    )->fetchColumn();
    return $stats;
}

// ── Utility ──────────────────────────────────────────

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9-]/', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    return trim($text, '-');
}

function uploadImage($file, $oldImage = null) {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($file['type'], $allowed)) return ['error' => 'Only JPG, PNG, WEBP allowed'];
    if ($file['size'] > 5 * 1024 * 1024) return ['error' => 'Max file size is 5MB'];

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('product_') . '.' . $ext;
    $dest = UPLOADS_PATH . $filename;

    if (!is_dir(UPLOADS_PATH)) mkdir(UPLOADS_PATH, 0755, true);

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        if ($oldImage && file_exists(UPLOADS_PATH . $oldImage)) {
            unlink(UPLOADS_PATH . $oldImage);
        }
        return ['success' => true, 'filename' => $filename];
    }
    return ['error' => 'Upload failed'];
}

function getProductImage($image) {
    if ($image && file_exists(UPLOADS_PATH . $image)) {
        return UPLOADS_URL . $image;
    }
    return SITE_URL . '/assets/img/placeholder.svg';
}

function flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
