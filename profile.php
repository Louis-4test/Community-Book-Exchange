<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_auth();

$page_title = 'My Profile';

// Get current user data
$user = get_current_user();
$user_books_count = UserModel::countUserBooks($_SESSION['user_id']);
$wishlist_count = get_wishlist_count();

// Handle profile update via PRG
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Invalid form submission.');
        header('Location: profile.php');
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_profile':
            $name = sanitize_input($_POST['name'] ?? '');
            $location = sanitize_input($_POST['location'] ?? '');
            $bio = sanitize_input($_POST['bio'] ?? '');
            
            if (empty($name)) {
                set_flash_message('error', 'Name is required.');
            } else {
                UserModel::updateProfile($_SESSION['user_id'], [
                    'name' => $name,
                    'location' => $location,
                    'bio' => $bio
                ]);
                
                // Update session
                $_SESSION['user_name'] = $name;
                
                set_flash_message('success', 'Profile updated successfully.');
            }
            break;
            
        case 'change_password':
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                set_flash_message('error', 'All password fields are required.');
            } elseif ($new_password !== $confirm_password) {
                set_flash_message('error', 'New passwords do not match.');
            } elseif (!validate_password($new_password)) {
                set_flash_message('error', 'New password must be at least 8 characters with uppercase, number, and special character.');
            } else {
                $result = Auth::changePassword($_SESSION['user_id'], $current_password, $new_password);
                if ($result['success']) {
                    set_flash_message('success', 'Password changed successfully.');
                } else {
                    set_flash_message('error', $result['error']);
                }
            }
            break;
    }
    
    // PRG: Redirect to GET
    header('Location: profile.php');
    exit;
}

require_once 'includes/header.php';
?>

<div class="profile-page">
    <div class="page-header">
        <h1><i class="fas fa-user-circle"></i> My Profile</h1>
        <p>Manage your account and preferences</p>
    </div>
    
    <!-- Flash Messages -->
    <?php $flash = get_flash_message(); ?>
    <?php if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <?php endif; ?>
    
    <div class="profile-container">
        <!-- Profile Sidebar -->
        <div class="profile-sidebar">
            <div class="profile-card">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <?php if ($user['location']): ?>
                    <p class="profile-location">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['location']); ?>
                    </p>
                    <?php endif; ?>
                    <p class="profile-role">
                        <span class="role-badge role-<?php echo $user['role']; ?>">
                            <?php echo ucfirst($user['role']); ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <div class="profile-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $user_books_count; ?></div>
                    <div class="stat-label">Books Listed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $wishlist_count; ?></div>
                    <div class="stat-label">Wishlist Items</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo format_date($user['created_at']); ?></div>
                    <div class="stat-label">Member Since</div>
                </div>
            </div>
            
            <div class="profile-navigation">
                <a href="my-books.php" class="nav-item">
                    <i class="fas fa-book"></i> My Books
                </a>
                <a href="wishlist.php" class="nav-item">
                    <i class="fas fa-heart"></i> My Wishlist
                </a>
                <a href="profile.php#settings" class="nav-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <a href="auth.php?action=logout" class="nav-item text-danger">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="profile-content">
            <!-- Edit Profile Form -->
            <div class="profile-section" id="edit-profile">
                <div class="section-header">
                    <h2><i class="fas fa-user-edit"></i> Edit Profile</h2>
                </div>
                
                <form method="POST" action="profile.php" class="profile-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required
                               value="<?php echo htmlspecialchars($user['name']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                        <small class="form-help">Email cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Location (Optional)</label>
                        <input type="text" id="location" name="location"
                               value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>"
                               placeholder="City, Country">
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Bio (Optional)</label>
                        <textarea id="bio" name="bio" rows="4" 
                                  placeholder="Tell us about yourself and your reading interests..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Change Password Form -->
            <div class="profile-section" id="change-password">
                <div class="section-header">
                    <h2><i class="fas fa-key"></i> Change Password</h2>
                </div>
                
                <form method="POST" action="profile.php" class="profile-form">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                        <small class="form-help">Must be at least 8 characters with uppercase, number, and special character</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Account Information -->
            <div class="profile-section" id="account-info">
                <div class="section-header">
                    <h2><i class="fas fa-info-circle"></i> Account Information</h2>
                </div>
                
                <div class="info-grid">
                    <div class="info-item">
                        <div class="info-label">Account Type</div>
                        <div class="info-value">
                            <span class="role-badge role-<?php echo $user['role']; ?>">
                                <?php echo ucfirst($user['role']); ?> Account
                            </span>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Member Since</div>
                        <div class="info-value"><?php echo format_datetime($user['created_at']); ?></div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Last Login</div>
                        <div class="info-value">
                            <?php echo $user['last_login'] ? format_datetime($user['last_login']) : 'Never'; ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-label">Email Status</div>
                        <div class="info-value">
                            <?php if ($user['email_verified']): ?>
                            <span class="badge badge-success">Verified</span>
                            <?php else: ?>
                            <span class="badge badge-warning">Unverified</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>