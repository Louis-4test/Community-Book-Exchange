<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (is_logged_in() && is_admin()) {
    header('Location: index.php');
    exit;
}

$page_title = 'Admin Login';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    if (empty($errors)) {
        $user = UserModel::getUserByEmail($email);
        
        if ($user && verify_password($password, $user['password_hash'])) {
            if ($user['role'] === 'admin') {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['name'];
                
                set_flash_message('success', 'Welcome back, ' . $user['name'] . '!');
                header('Location: index.php');
                exit;
            } else {
                $errors['general'] = 'You do not have administrator privileges';
            }
        } else {
            $errors['general'] = 'Invalid email or password';
        }
    }
}
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
<body class="admin-login">
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-book-open"></i> BookExchange Admin</h1>
            <p>Community Book Exchange Administration Panel</p>
        </div>
        
        <div class="login-card">
            <h2>Admin Login</h2>
            
            <?php if (isset($errors['general'])): ?>
            <div class="alert alert-error">
                <?php echo $errors['general']; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo $errors['password']; ?></span>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="login-footer">
                <a href="../index.php"><i class="fas fa-home"></i> Back to Main Site</a>
            </div>
        </div>
        
        <div class="login-info">
            <p><strong>Demo Credentials:</strong></p>
            <p>Email: admin@bookexchange.com</p>
            <p>Password: Admin123!</p>
        </div>
    </div>
</body>
</html>