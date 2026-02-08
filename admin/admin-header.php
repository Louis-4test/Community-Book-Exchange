<?php
/**
 * Admin Header Template
 * 
 * @package CommunityBookEx
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Get current page for active navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' | ' : ''; ?>Admin Panel - BookExchange</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="../assets/images/favicon.ico">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <!-- Custom Admin CSS -->
    <link rel="stylesheet" href="../css/admin.css">
    
    <!-- Page-specific CSS -->
    <?php if (isset($page_css)): ?>
    <style><?php echo $page_css; ?></style>
    <?php endif; ?>
</head>
<body class="admin-body">
    <!-- Admin Wrapper -->
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav class="admin-sidebar">
            <div class="sidebar-header">
                <div class="admin-logo">
                    <i class="fas fa-book"></i>
                    <span class="logo-text">BookExchange</span>
                    <span class="logo-badge">Admin</span>
                </div>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="sidebar-user">
                <div class="user-avatar">
                    <img src="../assets/images/avatars/<?php echo $_SESSION['user_avatar'] ?? 'default.png'; ?>" alt="Admin Avatar">
                </div>
                <div class="user-info">
                    <h6 class="user-name"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></h6>
                    <span class="user-role badge bg-primary">Administrator</span>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-header">MAIN NAVIGATION</li>
                
                <li class="menu-item <?php echo $current_page == 'admin-dashboard.php' ? 'active' : ''; ?>">
                    <a href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo in_array($current_page, ['admin-books.php', 'admin-book-edit.php']) ? 'active' : ''; ?>">
                    <a href="admin-books.php">
                        <i class="fas fa-book"></i>
                        <span>Books Management</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo $current_page == 'admin-users.php' ? 'active' : ''; ?>">
                    <a href="admin-users.php">
                        <i class="fas fa-users"></i>
                        <span>Users Management</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo $current_page == 'admin-categories.php' ? 'active' : ''; ?>">
                    <a href="admin-categories.php">
                        <i class="fas fa-tags"></i>
                        <span>Categories & Genres</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo in_array($current_page, ['admin-transactions.php', 'admin-transaction-view.php']) ? 'active' : ''; ?>">
                    <a href="admin-transactions.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span>Transactions</span>
                        <span class="badge bg-danger pending-count">0</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo $current_page == 'admin-reports.php' ? 'active' : ''; ?>">
                    <a href="admin-reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports & Analytics</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo $current_page == 'admin-messages.php' ? 'active' : ''; ?>">
                    <a href="admin-messages.php">
                        <i class="fas fa-envelope"></i>
                        <span>Messages</span>
                        <span class="badge bg-warning unread-count">0</span>
                    </a>
                </li>
                
                <li class="menu-divider"></li>
                
                <li class="menu-header">SETTINGS</li>
                
                <li class="menu-item <?php echo $current_page == 'admin-settings.php' ? 'active' : ''; ?>">
                    <a href="admin-settings.php">
                        <i class="fas fa-cog"></i>
                        <span>System Settings</span>
                    </a>
                </li>
                
                <li class="menu-item <?php echo $current_page == 'admin-backup.php' ? 'active' : ''; ?>">
                    <a href="admin-backup.php">
                        <i class="fas fa-database"></i>
                        <span>Backup & Restore</span>
                    </a>
                </li>
                
                <li class="menu-divider"></li>
                
                <li class="menu-item">
                    <a href="../index.php" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        <span>View Site</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="admin-profile.php">
                        <i class="fas fa-user-cog"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                
                <li class="menu-item">
                    <a href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-footer">
                <div class="system-info">
                    <small>System Version: 2.1.0</small>
                    <small id="current-time"></small>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Top Navigation -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="admin-dashboard.php"><i class="fas fa-home"></i></a></li>
                            <?php if (isset($breadcrumbs)): ?>
                                <?php foreach ($breadcrumbs as $crumb): ?>
                                    <li class="breadcrumb-item <?php echo $crumb['active'] ? 'active' : ''; ?>">
                                        <?php if ($crumb['active']): ?>
                                            <?php echo $crumb['text']; ?>
                                        <?php else: ?>
                                            <a href="<?php echo $crumb['link']; ?>"><?php echo $crumb['text']; ?></a>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="breadcrumb-item active"><?php echo $page_title ?? 'Dashboard'; ?></li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </div>
                
                <div class="topbar-right">
                    <div class="topbar-actions">
                        <!-- Search -->
                        <div class="search-box">
                            <input type="text" class="form-control" placeholder="Search...">
                            <button class="search-btn">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        
                        <!-- Notifications -->
                        <div class="dropdown notifications-dropdown">
                            <button class="btn btn-notification" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge">3</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <div class="notification-header">
                                    <h6>Notifications</h6>
                                    <a href="#" class="mark-all-read">Mark all as read</a>
                                </div>
                                <div class="notification-list">
                                    <a href="#" class="notification-item unread">
                                        <div class="notification-icon bg-primary">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p>New book added for approval</p>
                                            <small>2 minutes ago</small>
                                        </div>
                                    </a>
                                    <a href="#" class="notification-item unread">
                                        <div class="notification-icon bg-success">
                                            <i class="fas fa-exchange-alt"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p>New exchange request</p>
                                            <small>15 minutes ago</small>
                                        </div>
                                    </a>
                                    <a href="#" class="notification-item">
                                        <div class="notification-icon bg-warning">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="notification-content">
                                            <p>New user registered</p>
                                            <small>1 hour ago</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="notification-footer">
                                    <a href="admin-notifications.php">View all notifications</a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="dropdown quick-actions-dropdown">
                            <button class="btn btn-quick-action" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bolt"></i>
                                Quick Actions
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="admin-books.php?action=add">
                                    <i class="fas fa-plus-circle"></i> Add New Book
                                </a>
                                <a class="dropdown-item" href="admin-users.php?action=add">
                                    <i class="fas fa-user-plus"></i> Add New User
                                </a>
                                <a class="dropdown-item" href="admin-transactions.php?filter=pending">
                                    <i class="fas fa-clock"></i> View Pending Transactions
                                </a>
                                <a class="dropdown-item" href="admin-reports.php">
                                    <i class="fas fa-chart-line"></i> Generate Report
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="admin-settings.php">
                                    <i class="fas fa-sliders-h"></i> System Settings
                                </a>
                            </div>
                        </div>
                        
                        <!-- User Menu -->
                        <div class="dropdown user-dropdown">
                            <button class="btn user-menu" type="button" data-bs-toggle="dropdown">
                                <div class="user-avatar-sm">
                                    <img src="../assets/images/avatars/<?php echo $_SESSION['user_avatar'] ?? 'default.png'; ?>" alt="User">
                                </div>
                                <span class="user-name-sm"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
                                <i class="fas fa-chevron-down"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="admin-profile.php">
                                    <i class="fas fa-user"></i> My Profile
                                </a>
                                <a class="dropdown-item" href="admin-settings.php">
                                    <i class="fas fa-cog"></i> Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../index.php" target="_blank">
                                    <i class="fas fa-external-link-alt"></i> View Site
                                </a>
                                <a class="dropdown-item" href="admin-help.php">
                                    <i class="fas fa-question-circle"></i> Help & Support
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../logout.php">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="content-wrapper">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="header-title">
                        <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
                        <?php if (isset($page_subtitle)): ?>
                            <p class="text-muted"><?php echo $page_subtitle; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="header-actions">
                        <?php if (isset($page_actions)): ?>
                            <?php echo $page_actions; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Alerts & Messages -->
                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo $_SESSION['success_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $_SESSION['error_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['error_message']); ?>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['warning_message'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo $_SESSION['warning_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['warning_message']); ?>
                <?php endif; ?>
                
                <!-- Main Content Area -->
                <div class="content-area">