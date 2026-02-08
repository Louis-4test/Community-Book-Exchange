<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="admin-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h2 class="logo">
            <i class="fas fa-book-open"></i>
            <?php echo SITE_NAME; ?> Admin
        </h2>
    </div>

    <div class="header-right">
        <div class="admin-user">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
        </div>

        <a href="logout.php" class="btn btn-sm btn-danger">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</header>
