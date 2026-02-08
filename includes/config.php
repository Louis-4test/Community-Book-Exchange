<?php
// Database configuration (for future modules)
define('DB_HOST', 'localhost');
define('DB_NAME', 'book_exchange');  // Note: your database name has underscore
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Community Book Exchange');
define('SITE_URL', 'http://localhost/CommunityBookEx');
define('SITE_EMAIL', 'fola.louis@yibs.org');
define('ADMIN_EMAIL', 'admin@bookexchange.com');

// File upload configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('UPLOAD_PATH', 'uploads/');

// Pagination settings
define('BOOKS_PER_PAGE', 9);
define('ADMIN_ITEMS_PER_PAGE', 10);

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// CSRF protection token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
}

// ===== ADD DATABASE CONNECTION HERE =====
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    
    $db = new PDO($dsn, DB_USER, DB_PASS, $options);
    
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
// ========================================
?>