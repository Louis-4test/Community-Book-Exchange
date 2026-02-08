<?php
/**
 * Admin Sidebar Component
 * 
 * @package CommunityBookEx
 */

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth.php');
    exit;
}

// Determine current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<nav class="admin-sidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <h2 class="sidebar-title">Admin Panel</h2>
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
    </div>
    
    <!-- Menu Items -->
    <ul class="sidebar-menu">
        <!-- Dashboard -->
        <li class="menu-item <?php echo $current_page == 'admin-dashboard.php' ? 'active' : ''; ?>">
            <a href="index.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <!-- Manage Books -->
        <li class="menu-item has-submenu <?php echo in_array($current_page, ['admin-books.php', 'admin-books-add.php', 'admin-books-edit.php', 'admin-categories.php']) ? 'active open' : ''; ?>">
            <a href="#" class="menu-link">
                <i class="fas fa-book"></i>
                <span>Manage Books</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </a>
            <ul class="submenu">
                <li class="<?php echo $current_page == 'admin-books.php' ? 'active' : ''; ?>">
                    <a href="books.php">All Books</a>
                </li>
                <li class="<?php echo $current_page == 'admin-books-add.php' ? 'active' : ''; ?>">
                    <a href="books.php?action=add">Add New Book</a>
                </li>
                <li class="<?php echo $current_page == 'admin-categories.php' ? 'active' : ''; ?>">
                    <a href="categories.php">Categories</a>
                </li>
            </ul>
        </li>
        
        <!-- Manage Users -->
        <li class="menu-item <?php echo strpos($current_page, 'admin-users') !== false ? 'active' : ''; ?>">
            <a href="users.php">
                <i class="fas fa-users"></i>
                <span>Manage Users</span>
            </a>
        </li>
        
        <!-- Contact Messages -->
        <li class="menu-item <?php echo $current_page == 'admin-messages.php' ? 'active' : ''; ?>">
            <a href="admin-messages.php">
                <i class="fas fa-envelope"></i>
                <span>Contact Messages</span>
                <?php
                // Count unread messages
                $unread_count = 0; // You'll need to query your database
                // $stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE is_read = 0");
                // $unread_count = $stmt->fetchColumn();
                ?>
                <?php if ($unread_count > 0): ?>
                <span class="badge bg-danger"><?php echo $unread_count; ?></span>
                <?php endif; ?>
            </a>
        </li>
        
        <!-- Settings -->
        <li class="menu-item <?php echo $current_page == 'admin-settings.php' ? 'active' : ''; ?>">
            <a href="../profile.php">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
    </ul>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar">
                <?php
                $avatar = isset($_SESSION['user_avatar']) && !empty($_SESSION['user_avatar']) 
                    ? '../assets/images/avatars/' . $_SESSION['user_avatar']
                    : '../assets/images/default-avatar.png';
                ?>
                <img src="<?php echo $avatar; ?>" alt="User Avatar" 
                     onerror="this.src='../assets/images/default-avatar.png'">
            </div>
            <div class="user-details">
                <h6><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></h6>
                <small>Administrator</small>
            </div>
        </div>
        <a href="../logout.php" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>
</nav>

<style>
/* Admin Sidebar Styles - Dark Blue Theme */
.admin-sidebar {
    width: 250px;
    background: linear-gradient(180deg, #1e3a8a 0%, #1e40af 100%);
    color: white;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    text-align: center;
}

.sidebar-title {
    color: white;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    letter-spacing: 0.5px;
}

.sidebar-toggle {
    display: none;
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
}

/* Menu Items */
.sidebar-menu {
    list-style: none;
    padding: 20px 0;
    margin: 0;
}

.menu-item {
    margin: 5px 15px;
    border-radius: 8px;
    overflow: hidden;
}

.menu-item.active {
    background: rgba(255, 255, 255, 0.15);
}

.menu-item > a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    transition: all 0.3s;
    position: relative;
}

.menu-item > a:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    padding-left: 20px;
}

.menu-item.active > a {
    color: white;
    background: rgba(255, 255, 255, 0.2);
    font-weight: 500;
}

.menu-item i {
    width: 20px;
    text-align: center;
    margin-right: 10px;
    font-size: 1.1rem;
}

.menu-item span {
    flex-grow: 1;
}

/* Submenu */
.has-submenu .menu-link {
    cursor: pointer;
}

.dropdown-icon {
    transition: transform 0.3s;
    font-size: 0.9rem;
}

.has-submenu.open .dropdown-icon {
    transform: rotate(180deg);
}

.submenu {
    list-style: none;
    padding: 0;
    margin: 0;
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: rgba(0, 0, 0, 0.2);
    border-radius: 0 0 8px 8px;
}

.has-submenu.open .submenu {
    max-height: 200px;
}

.submenu li {
    margin: 0;
}

.submenu a {
    display: block;
    padding: 10px 15px 10px 45px;
    color: rgba(255, 255, 255, 0.8);
    text-decoration: none;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.submenu a:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
    padding-left: 50px;
}

.submenu li.active a {
    color: white;
    background: rgba(255, 255, 255, 0.15);
    font-weight: 500;
}

/* Badge */
.badge {
    padding: 4px 8px;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 10px;
    min-width: 20px;
    text-align: center;
}

/* Sidebar Footer */
.sidebar-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(0, 0, 0, 0.2);
}

.user-info {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.3);
    margin-right: 10px;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.user-details h6 {
    margin: 0;
    font-size: 0.9rem;
    color: white;
}

.user-details small {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.8rem;
}

.logout-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    padding: 10px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    transition: all 0.3s;
    font-size: 0.9rem;
}

.logout-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    text-decoration: none;
}

.logout-btn i {
    margin-right: 8px;
}

/* Responsive */
@media (max-width: 992px) {
    .admin-sidebar {
        transform: translateX(-100%);
    }
    
    .admin-sidebar.active {
        transform: translateX(0);
    }
    
    .sidebar-toggle {
        display: block;
    }
    
    .sidebar-header {
        text-align: left;
        padding-right: 50px;
    }
}

/* Animation for active menu */
.menu-item > a::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 0;
    background: white;
    border-radius: 0 3px 3px 0;
    transition: height 0.3s;
}

.menu-item.active > a::before {
    height: 70%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar on mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.admin-sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Handle submenu clicks
    const submenuLinks = document.querySelectorAll('.has-submenu > .menu-link');
    
    submenuLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const parent = this.parentElement;
            
            // Close other submenus
            submenuLinks.forEach(otherLink => {
                if (otherLink !== this) {
                    otherLink.parentElement.classList.remove('open');
                }
            });
            
            // Toggle current submenu
            parent.classList.toggle('open');
        });
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992) {
            if (!sidebar.contains(e.target) && !e.target.classList.contains('sidebar-toggle')) {
                sidebar.classList.remove('active');
            }
        }
    });
    
    // Highlight current page in submenu
    const currentPage = '<?php echo $current_page; ?>';
    const menuItems = document.querySelectorAll('.menu-item a');
    
    menuItems.forEach(item => {
        if (item.getAttribute('href') === currentPage) {
            item.parentElement.classList.add('active');
            
            // Open parent submenu if exists
            const parentSubmenu = item.closest('.submenu');
            if (parentSubmenu) {
                const parentItem = parentSubmenu.parentElement;
                parentItem.classList.add('open', 'active');
            }
        }
    });
});
</script>