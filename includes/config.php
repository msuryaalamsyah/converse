<?php
// ================================================
// Database Configuration - Laragon MySQL
// ================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'converse_store');
define('SITE_URL', 'http://localhost/converse');
define('UPLOADS_PATH', __DIR__ . '/../uploads/products/');
define('UPLOADS_URL', SITE_URL . '/uploads/products/');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die('<div style="font-family:monospace;padding:20px;background:#fff0f0;border:2px solid red;margin:20px">
                <strong>Database Connection Error:</strong><br>' . htmlspecialchars($e->getMessage()) . '
                <br><br>Make sure Laragon is running and you have imported database.sql
            </div>');
        }
    }
    return $pdo;
}
