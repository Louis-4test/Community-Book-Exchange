<?php
require_once 'config.php';
require_once 'functions.php';

// Get current page for active navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Get wishlist count for logged-in users
$wishlist_count = is_logged_in() ? get_wishlist_count() : 0;
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
                    <?php if (is_logged_in()): ?>
                    <li><a href="my-books.php" class="<?php echo $current_page == 'my-books.php' ? 'active' : ''; ?>">My Books</a></li>
                    <li>
                        <a href="wishlist.php" class="<?php echo $current_page == 'wishlist.php' ? 'active' : ''; ?>">
                            <i class="fas fa-heart"></i> Wishlist
                            <?php if ($wishlist_count > 0): ?>
                            <span class="wishlist-count"><?php echo $wishlist_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li><a href="about.php" class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">About</a></li>
                </ul>
                
                <div class="user-actions">
                    <?php if (is_logged_in()): ?>
                        <div class="user-dropdown">
                            <button class="user-dropdown-toggle">
                                <i class="fas fa-user-circle"></i>
                                <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="user-dropdown-menu">
                                <a href="profile.php" class="dropdown-item">
                                    <i class="fas fa-user"></i> My Profile
                                </a>
                                <a href="my-books.php" class="dropdown-item">
                                    <i class="fas fa-book"></i> My Books
                                </a>
                                <a href="wishlist.php" class="dropdown-item">
                                    <i class="fas fa-heart"></i> Wishlist
                                    <?php if ($wishlist_count > 0): ?>
                                    <span class="badge"><?php echo $wishlist_count; ?></span>
                                    <?php endif; ?>
                                </a>
                                <?php if (is_admin()): ?>
                                <div class="dropdown-divider"></div>
                                <a href="admin/index.php" class="dropdown-item">
                                    <i class="fas fa-cog"></i> Admin Panel
                                </a>
                                <?php endif; ?>
                                <div class="dropdown-divider"></div>
                                <a href="auth.php?action=logout" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="auth.php" class="btn btn-outline">Sign In</a>
                        <a href="auth.php?register=true" class="btn btn-primary">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    
    <main>