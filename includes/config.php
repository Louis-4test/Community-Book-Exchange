<?php
// Database configuration (for future modules)
define('DB_HOST', 'localhost');
define('DB_NAME', 'book_exchange');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_NAME', 'Community Book Exchange');
define('SITE_URL', 'http://localhost/book-exchange');
define('SITE_EMAIL', 'fola.louis@yibs.org');

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
?>