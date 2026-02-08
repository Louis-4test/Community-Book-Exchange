<?php
require_once 'config.php';
require_once 'functions.php';

// Get current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Merriweather:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Flash Message Display -->
    <?php $flash = get_flash_message(); ?>
    <?php if ($flash): ?>
    <div class="flash-message flash-<?php echo $flash['type']; ?>">
        <div class="flash-content">
            <span><?php echo htmlspecialchars($flash['message']); ?></span>
            <button class="flash-close">&times;</button>
        </div>
    </div>
    
    <script>
        document.querySelector('.flash-close')?.addEventListener('click', function() {
            this.closest('.flash-message').remove();
        });
        
        // Auto-remove flash messages after 5 seconds
        setTimeout(() => {
            document.querySelector('.flash-message')?.remove();
        }, 5000);
    </script>
    <?php endif; ?>

    <!-- Navigation -->
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <a href="index.php" class="logo">
                    <i class="fas fa-book-open"></i>
                    <span>BookExchange</span>
                </a>
                
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <i class="fas fa-bars"></i>
                </button>
                
                <ul class="nav-menu" id="navMenu">
                    <li><a href="index.php" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="books.php" class="<?php echo $current_page == 'books.php' ? 'active' : ''; ?>">Browse Books</a></li>
                    <li><a href="auth.php" class="<?php echo $current_page == 'auth.php' || $current_page == 'process-auth.php' ? 'active' : ''; ?>"><?php echo is_logged_in() ? 'My Account' : 'Login/Register'; ?></a></li>
                    <li><a href="about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">About</a></li>
                </ul>
                
                <div class="user-actions">
                    <?php if (is_logged_in()): ?>
                        <span class="user-greeting">Hi, <?php echo htmlspecialchars(get_app_user()['name'] ?? 'User'); ?></span>
                        <a href="auth.php?action=logout" class="btn btn-outline">Logout</a>
                        <a href="books.php?action=add" class="btn btn-primary">Add Book</a>
                    <?php else: ?>
                        <a href="auth.php" class="btn btn-outline">Sign In</a>
                        <a href="auth.php?register=true" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    
    <main>