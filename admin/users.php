<?php
/**
 * Admin Users Management Page
 * 
 * @package CommunityBookEx
 */

// Include config first
require_once __DIR__ . '/../includes/config.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? 'user') !== 'admin') {
    header('Location: ../auth.php');
    exit;
}

// Set page title
$page_title = 'Manage Users';

// Check if database connection exists
if (!isset($db)) {
    die("Database connection not available. Please check your config.php file.");
}

// Simple test query to verify connection
try {
    $test = $db->query("SELECT 1");
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle actions
$action = $_GET['action'] ?? '';
$user_id = $_GET['id'] ?? 0;

// Delete user
if ($action == 'delete' && $user_id) {
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $_SESSION['success_message'] = 'User deleted successfully!';
    } catch (PDOException $e) {
        $_SESSION['error_message'] = 'Error deleting user: ' . $e->getMessage();
    }
    header('Location: users.php');
    exit;
}

// Get search parameter
$search = $_GET['search'] ?? '';

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY created_at DESC";

// Get users
try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $users = [];
    $_SESSION['error_message'] = "Error loading users: " . $e->getMessage();
}

// Get statistics
try {
    $stats_query = "
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
            SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users
        FROM users
    ";
    $stats_stmt = $db->query($stats_query);
    $stats = $stats_stmt->fetch();
} catch (PDOException $e) {
    $stats = ['total' => 0, 'admins' => 0, 'regular_users' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | BookExchange Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #1e3a8a;
            --secondary-color: #1e40af;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .admin-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary-color);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 5px;
        }
        
        .users-table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
        }
        
        .role-badge {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .role-admin { background: #ede9fe; color: #8b5cf6; }
        .role-user { background: #e0f2fe; color: #0ea5e9; }
        
        @media (max-width: 768px) {
            .admin-content {
                margin-left: 0;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <?php 
    // Include sidebar
    $sidebar_file = __DIR__ . '/admin-sidebar.php';
    if (file_exists($sidebar_file)) {
        include $sidebar_file;
    }
    ?>
    
    <div class="admin-content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">Manage Users</h1>
                    <p class="text-muted mb-0">Manage all registered users</p>
                </div>
                <div>
                    <a href="users-add.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i> Add New User
                    </a>
                </div>
            </div>
        </div>
        
        <!-- User Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['total'] ?? 0; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['admins'] ?? 0; ?></div>
                <div class="stat-label">Administrators</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-value"><?php echo $stats['regular_users'] ?? 0; ?></div>
                <div class="stat-label">Regular Users</div>
            </div>
        </div>
        
        <!-- Search -->
        <div class="mb-3">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search by name or email..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Users Table -->
        <div class="users-table-container">
            <?php if (!empty($users)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="../assets/images/avatars/<?php echo !empty($user['profile_image']) ? htmlspecialchars($user['profile_image']) : 'default.png'; ?>" 
                                             alt="<?php echo htmlspecialchars($user['name']); ?>" 
                                             class="user-avatar me-3"
                                             onerror="this.src='../assets/images/default-avatar.png'">
                                        <div>
                                            <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                            <?php if (!empty($user['location'])): ?>
                                            <small><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['location']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="../profile.php?id=<?php echo $user['id']; ?>" 
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Profile">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="users-edit.php?id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-warning" 
                                           title="Edit User">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger" 
                                           title="Delete User"
                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users-slash fa-3x text-muted mb-3"></i>
                    <h4>No Users Found</h4>
                    <p class="text-muted"><?php echo empty($search) ? 'No users in the system yet.' : 'No users match your search.'; ?></p>
                    <?php if (!empty($search)): ?>
                    <a href="users.php" class="btn btn-primary mt-2">
                        <i class="fas fa-redo me-2"></i> Clear Search
                    </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Show messages if any
    <?php if (isset($_SESSION['success_message'])): ?>
        alert('<?php echo $_SESSION['success_message']; ?>');
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        alert('<?php echo $_SESSION['error_message']; ?>');
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    </script>
</body>
</html>