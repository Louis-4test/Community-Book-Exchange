<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_admin();

$page_title = 'Admin Dashboard';
$stats = get_dashboard_statistics();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-dashboard">
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="admin-header-container">
            <div class="admin-header-left">
                <a href="index.php" class="admin-logo">
                    <i class="fas fa-book-open"></i>
                    <span>BookExchange Admin</span>
                </a>
            </div>
            
            <div class="admin-header-right">
                <div class="admin-user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['user_name']; ?> (Admin)</span>
                </div>
                <a href="../index.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-external-link-alt"></i> View Site
                </a>
                <a href="../auth.php?action=logout" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>
    
    <!-- Admin Sidebar -->
    <div class="admin-container">
        <nav class="admin-sidebar">
            <ul class="admin-menu">
                <li class="active">
                    <a href="index.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="books.php">
                        <i class="fas fa-book"></i>
                        <span>Manage Books</span>
                    </a>
                </li>
                <li>
                    <a href="books.php?action=add">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add New Book</span>
                    </a>
                </li>
                <li>
                    <a href="users.php">
                        <i class="fas fa-users"></i>
                        <span>Manage Users</span>
                    </a>
                </li>
                <li>
                    <a href="messages.php">
                        <i class="fas fa-envelope"></i>
                        <span>Contact Messages</span>
                        <?php if ($stats['unread_messages'] > 0): ?>
                        <span class="badge badge-danger"><?php echo $stats['unread_messages']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li>
                    <a href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-content">
                <!-- Flash Messages -->
                <?php $flash = get_flash_message(); ?>
                <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
                <?php endif; ?>
                
                <!-- Page Header -->
                <div class="page-header">
                    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                    <p>Welcome to the BookExchange administration panel</p>
                </div>
                
                <!-- Statistics Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #3498db;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['total_books']; ?></h3>
                            <p>Total Books</p>
                        </div>
                        <a href="books.php" class="stat-link">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #2ecc71;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['available_books']; ?></h3>
                            <p>Available Books</p>
                        </div>
                        <a href="books.php?status=available" class="stat-link">View Available <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #9b59b6;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['total_users']; ?></h3>
                            <p>Registered Users</p>
                        </div>
                        <a href="users.php" class="stat-link">View Users <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon" style="background-color: #e74c3c;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo $stats['unread_messages']; ?></h3>
                            <p>Unread Messages</p>
                        </div>
                        <a href="messages.php" class="stat-link">View Messages <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2><i class="fas fa-history"></i> Recent Activity</h2>
                    </div>
                    
                    <div class="activity-list">
                        <?php
                        // Get recent books
                        $recent_books = BookModel::getAllBooks([], 5, 0);
                        ?>
                        <?php if (!empty($recent_books)): ?>
                            <?php foreach ($recent_books as $book): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-book"></i>
                                </div>
                                <div class="activity-content">
                                    <h4>New Book Added</h4>
                                    <p>"<?php echo htmlspecialchars($book['title']); ?>" by <?php echo htmlspecialchars($book['author']); ?></p>
                                    <small><?php echo format_datetime($book['created_at']); ?></small>
                                </div>
                                <div class="activity-actions">
                                    <a href="books.php?action=edit&id=<?php echo $book['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-book"></i>
                                <p>No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="admin-section">
                    <div class="section-header">
                        <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                    </div>
                    
                    <div class="quick-actions">
                        <a href="books.php?action=add" class="quick-action">
                            <i class="fas fa-plus-circle"></i>
                            <span>Add New Book</span>
                        </a>
                        <a href="users.php" class="quick-action">
                            <i class="fas fa-user-plus"></i>
                            <span>Add New User</span>
                        </a>
                        <a href="messages.php" class="quick-action">
                            <i class="fas fa-envelope"></i>
                            <span>Check Messages</span>
                        </a>
                        <a href="settings.php" class="quick-action">
                            <i class="fas fa-cog"></i>
                            <span>Site Settings</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script src="../js/admin.js"></script>
</body>
</html>