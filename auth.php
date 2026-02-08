<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Authentication';

// Handle logout
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Check if user is already logged in
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

// Determine if showing login or register form
$show_register = isset($_GET['register']) && $_GET['register'] == 'true';

require_once 'includes/header.php';
?>

<!-- Authentication Section -->
<div class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <h1>Welcome to BookExchange</h1>
            <p>Join our community to start exchanging books today</p>
        </div>
        
        <!-- Toggle between Login and Register forms -->
        <div class="auth-toggle">
            <button class="toggle-btn <?php echo !$show_register ? 'active' : ''; ?>" id="loginToggle">Login</button>
            <button class="toggle-btn <?php echo $show_register ? 'active' : ''; ?>" id="registerToggle">Register</button>
        </div>
        
        <!-- Login Form -->
        <form class="auth-form <?php echo !$show_register ? 'active' : ''; ?>" id="loginForm" method="POST" action="process-auth.php">
            <input type="hidden" name="action" value="login">
            <?php echo csrf_field(); ?>
            
            <div class="form-group">
                <label for="loginEmail">Email Address</label>
                <input type="email" name="email" id="loginEmail" placeholder="Enter your email" required
                       value="<?php echo isset($_SESSION['form_data']['login_email']) ? htmlspecialchars($_SESSION['form_data']['login_email']) : ''; ?>">
                <div class="form-error" id="loginEmailError">
                    <?php echo isset($_SESSION['errors']['login_email']) ? $_SESSION['errors']['login_email'] : ''; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="loginPassword">Password</label>
                <input type="password" name="password" id="loginPassword" placeholder="Enter your password" required>
                <div class="form-error" id="loginPasswordError">
                    <?php echo isset($_SESSION['errors']['login_password']) ? $_SESSION['errors']['login_password'] : ''; ?>
                </div>
            </div>
            
            <div class="form-options">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember" id="rememberMe">
                    <span class="checkmark"></span>
                    Remember me
                </label>
                <a href="#" class="forgot-password">Forgot password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
            
            <div class="form-footer">
                <p>Don't have an account? <a href="auth.php?register=true" id="switchToRegister">Sign up here</a></p>
            </div>
        </form>
        
        <!-- Registration Form -->
        <form class="auth-form <?php echo $show_register ? 'active' : ''; ?>" id="registerForm" method="POST" action="process-auth.php">
            <input type="hidden" name="action" value="register">
            <?php echo csrf_field(); ?>
            
            <div class="form-group">
                <label for="registerName">Full Name</label>
                <input type="text" name="name" id="registerName" placeholder="Enter your full name" required
                       value="<?php echo isset($_SESSION['form_data']['register_name']) ? htmlspecialchars($_SESSION['form_data']['register_name']) : ''; ?>">
                <div class="form-error" id="registerNameError">
                    <?php echo isset($_SESSION['errors']['register_name']) ? $_SESSION['errors']['register_name'] : ''; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="registerEmail">Email Address</label>
                <input type="email" name="email" id="registerEmail" placeholder="Enter your email" required
                       value="<?php echo isset($_SESSION['form_data']['register_email']) ? htmlspecialchars($_SESSION['form_data']['register_email']) : ''; ?>">
                <div class="form-error" id="registerEmailError">
                    <?php echo isset($_SESSION['errors']['register_email']) ? $_SESSION['errors']['register_email'] : ''; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="registerPassword">Password</label>
                <input type="password" name="password" id="registerPassword" placeholder="Create a password" required>
                <div class="password-requirements">
                    <p>Password must contain:</p>
                    <ul>
                        <li id="req-length">At least 8 characters</li>
                        <li id="req-uppercase">One uppercase letter</li>
                        <li id="req-number">One number</li>
                        <li id="req-special">One special character</li>
                    </ul>
                </div>
                <div class="form-error" id="registerPasswordError">
                    <?php echo isset($_SESSION['errors']['register_password']) ? $_SESSION['errors']['register_password'] : ''; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm your password" required>
                <div class="form-error" id="confirmPasswordError">
                    <?php echo isset($_SESSION['errors']['confirm_password']) ? $_SESSION['errors']['confirm_password'] : ''; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label class="checkbox-container">
                    <input type="checkbox" name="terms" id="termsAgreement" required <?php echo isset($_SESSION['form_data']['terms']) ? 'checked' : ''; ?>>
                    <span class="checkmark"></span>
                    I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                </label>
                <div class="form-error" id="termsError">
                    <?php echo isset($_SESSION['errors']['terms']) ? $_SESSION['errors']['terms'] : ''; ?>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            
            <div class="form-footer">
                <p>Already have an account? <a href="auth.php" id="switchToLogin">Login here</a></p>
            </div>
        </form>
        
        <!-- Social Login Options -->
        <div class="social-auth">
            <div class="divider">
                <span>Or continue with</span>
            </div>
            
            <div class="social-buttons">
                <button class="social-btn google-btn">
                    <i class="fab fa-google"></i> Google
                </button>
                <button class="social-btn facebook-btn">
                    <i class="fab fa-facebook-f"></i> Facebook
                </button>
            </div>
        </div>
    </div>
</div>

<?php
// Clear form data and errors from session after displaying
unset($_SESSION['form_data']);
unset($_SESSION['errors']);

require_once 'includes/footer.php';
?>