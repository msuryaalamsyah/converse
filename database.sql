-- ================================================
-- CONVERSE STORE - Database Setup
-- Import this file in phpMyAdmin or run via MySQL
-- ================================================

CREATE DATABASE IF NOT EXISTS converse_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE converse_store;

-- Categories Table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products Table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    sku VARCHAR(50) NOT NULL UNIQUE,
    category_id INT,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    is_new TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Product Variants (Size & Stock)
CREATE TABLE product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    size VARCHAR(10) NOT NULL,
    color VARCHAR(50) DEFAULT 'Default',
    stock INT NOT NULL DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Admin Users Table
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ================================================
-- SAMPLE DATA
-- ================================================

INSERT INTO categories (name, slug, description) VALUES
('High Top', 'high-top', 'Classic high-top sneakers for the streets'),
('Low Top', 'low-top', 'Versatile low-top everyday sneakers'),
('Platform', 'platform', 'Elevated platform sole sneakers'),
('Slip-On', 'slip-on', 'Easy slip-on canvas sneakers'),
('Skate', 'skate', 'Skate-inspired durable sneakers');

INSERT INTO products (name, slug, sku, category_id, description, price, image, is_featured, is_new) VALUES
('Chuck Taylor All Star Classic', 'chuck-taylor-all-star-classic', 'CV-100293-BLK', 1, 'The original canvas sneaker that started it all. Timeless style, relentless self-expression.', 85.00, 'chuck-black.jpg', 1, 0),
('Chuck 70 Vintage Canvas', 'chuck-70-vintage-canvas', 'CV-700112-MUS', 2, 'The Chuck 70 updates the classic with premium materials and retro details.', 90.00, 'chuck70-mustard.jpg', 1, 1),
('Run Star Hike Platform', 'run-star-hike-platform', 'CV-200481-WHT', 3, 'Dramatic platform sole meets the iconic Chuck Taylor upper.', 110.00, 'runstar-white.jpg', 0, 0),
('One Star Pro Skate', 'one-star-pro-skate', 'CV-300572-RED', 5, 'Skate-ready construction with the iconic one star branding.', 75.00, 'onestar-red.jpg', 0, 1),
('Chuck Taylor All Star Lugged', 'chuck-taylor-all-star-lugged', 'CV-400683-BLK', 3, 'A platform take on the All Star with chunky lugged sole.', 95.00, 'chuck-lugged.jpg', 0, 0),
('Chuck 70 Leather High Top', 'chuck-70-leather-high-top', 'CV-500794-BLK', 1, 'Premium leather upper on the beloved Chuck 70 silhouette.', 95.00, 'chuck70-leather.jpg', 0, 0);

INSERT INTO product_variants (product_id, size, color, stock) VALUES
(1, '7', 'Black', 45), (1, '8', 'Black', 62), (1, '9', 'Black', 120), (1, '10', 'Black', 89), (1, '11', 'Black', 34), (1, '12', 'Black', 22),
(1, '7', 'White', 38), (1, '8', 'White', 55), (1, '9', 'White', 98), (1, '10', 'White', 72), (1, '11', 'White', 15), (1, '12', 'White', 8),
(2, '7', 'Mustard', 20), (2, '8', 'Mustard', 44), (2, '9', 'Mustard', 60), (2, '10', 'Mustard', 38), (2, '11', 'Mustard', 12), (2, '12', 'Mustard', 10),
(3, '7', 'White', 5), (3, '8', 'White', 3), (3, '9', 'White', 2), (3, '10', 'White', 1), (3, '11', 'White', 1),
(4, '7', 'Red', 30), (4, '8', 'Red', 45), (4, '9', 'Red', 55), (4, '10', 'Red', 40), (4, '11', 'Red', 25),
(5, '7', 'Black', 15), (5, '8', 'Black', 28), (5, '9', 'Black', 35), (5, '10', 'Black', 22),
(6, '7', 'Black', 18), (6, '8', 'Black', 32), (6, '9', 'Black', 48), (6, '10', 'Black', 35), (6, '11', 'Black', 20);

-- Admin user: username=admin, password=admin123
INSERT INTO admin_users (username, password, name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Store Admin');
