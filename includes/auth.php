<?php
/**
 * Authentication and session management functions
 */
require_once 'database.php';

class Auth {
    /**
     * Login user with email and password
     */
    public static function login($email, $password, $remember = false) {
        $user = UserModel::getUserByEmail($email);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid email or password'];
        }
        
        // Check if account is locked or needs verification
        if (!$user['email_verified']) {
            return ['success' => false, 'error' => 'Please verify your email address before logging in.'];
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Update last login
        UserModel::updateLastLogin($user['id']);
        
        // Handle "remember me" functionality
        if ($remember) {
            self::setRememberMeCookie($user['id']);
        }
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        return ['success' => true, 'user' => $user];
    }
    
    /**
     * Register new user
     */
    public static function register($name, $email, $password) {
        // Check if email already exists
        $existingUser = UserModel::getUserByEmail($email);
        if ($existingUser) {
            return ['success' => false, 'error' => 'Email already registered'];
        }
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        
        // Create user
        $user_id = UserModel::createUserWithVerification($name, $email, $password_hash, $verification_token);
        
        if (!$user_id) {
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
        
        // Send verification email (in production)
        // self::sendVerificationEmail($email, $name, $verification_token);
        
        // For demo purposes, auto-verify
        UserModel::verifyEmail($email);
        
        return ['success' => true, 'user_id' => $user_id];
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            self::clearRememberMeCookie();
        }
        
        // Destroy session
        $_SESSION = [];
        
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     */
    public static function check() {
        // Check session
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            return true;
        }
        
        // Check remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            return self::loginWithRememberToken($_COOKIE['remember_token']);
        }
        
        return false;
    }
    
    /**
     * Set remember me cookie
     */
    private static function setRememberMeCookie($user_id) {
        $token = bin2hex(random_bytes(32));
        $expiry = time() + (30 * 24 * 60 * 60); // 30 days
        
        // Store token in database
        UserModel::createRememberToken($user_id, $token, date('Y-m-d H:i:s', $expiry));
        
        // Set cookie
        setcookie('remember_token', $token, $expiry, '/', '', true, true);
    }
    
    /**
     * Clear remember me cookie
     */
    private static function clearRememberMeCookie() {
        if (isset($_COOKIE['remember_token'])) {
            // Remove from database
            UserModel::deleteRememberToken($_COOKIE['remember_token']);
            
            // Clear cookie
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            unset($_COOKIE['remember_token']);
        }
    }
    
    /**
     * Login with remember token
     */
    private static function loginWithRememberToken($token) {
        $session = UserModel::getSessionByToken($token);
        
        if (!$session || strtotime($session['expires_at']) < time()) {
            // Token expired or invalid
            self::clearRememberMeCookie();
            return false;
        }
        
        // Get user
        $user = UserModel::getUserById($session['user_id']);
        
        if (!$user) {
            return false;
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        
        // Update last login
        UserModel::updateLastLogin($user['id']);
        
        // Extend token expiry
        $new_expiry = time() + (30 * 24 * 60 * 60);
        UserModel::updateTokenExpiry($token, date('Y-m-d H:i:s', $new_expiry));
        setcookie('remember_token', $token, $new_expiry, '/', '', true, true);
        
        return true;
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::check()) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            set_flash_message('error', 'Please log in to access this page.');
            header('Location: auth.php');
            exit;
        }
    }
    
    /**
     * Require admin role
     */
    public static function requireAdmin() {
        self::requireAuth();
        
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            set_flash_message('error', 'You do not have permission to access this page.');
            header('Location: index.php');
            exit;
        }
    }
    
    /**
     * Get current user ID
     */
    public static function userId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Get current user role
     */
    public static function userRole() {
        return $_SESSION['user_role'] ?? null;
    }
    
    /**
     * Get current user data
     */
    public static function user() {
        if (!self::check()) {
            return null;
        }
        
        return UserModel::getUserById($_SESSION['user_id']);
    }
    
    /**
     * Change user password
     */
    public static function changePassword($user_id, $current_password, $new_password) {
        $user = UserModel::getUserById($user_id);
        
        if (!$user || !password_verify($current_password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Current password is incorrect'];
        }
        
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        UserModel::updatePassword($user_id, $new_hash);
        
        return ['success' => true];
    }
    
    /**
     * Generate password reset token
     */
    public static function generateResetToken($email) {
        $user = UserModel::getUserByEmail($email);
        
        if (!$user) {
            return ['success' => false, 'error' => 'Email not found'];
        }
        
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + (60 * 60)); // 1 hour
        
        UserModel::setResetToken($user['id'], $token, $expiry);
        
        // In production: Send reset email
        // self::sendResetEmail($email, $token);
        
        return ['success' => true, 'token' => $token];
    }
    
    /**
     * Reset password with token
     */
    public static function resetPassword($token, $new_password) {
        $user = UserModel::getUserByResetToken($token);
        
        if (!$user || strtotime($user['reset_token_expiry']) < time()) {
            return ['success' => false, 'error' => 'Invalid or expired reset token'];
        }
        
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        UserModel::updatePassword($user['id'], $new_hash);
        UserModel::clearResetToken($user['id']);
        
        return ['success' => true];
    }
}

/**
 * Wishlist management
 */
class Wishlist {
    /**
     * Add book to user's wishlist
     */
    public static function add($user_id, $book_id) {
        // Check if book exists and is available
        $book = BookModel::getBookById($book_id);
        if (!$book || $book['status'] !== 'available') {
            return ['success' => false, 'error' => 'Book not available'];
        }
        
        // Check if already in wishlist
        if (self::has($user_id, $book_id)) {
            return ['success' => false, 'error' => 'Book already in wishlist'];
        }
        
        $sql = "INSERT INTO wishlist (user_id, book_id) VALUES (?, ?)";
        Database::query($sql, [$user_id, $book_id]);
        
        return ['success' => true];
    }
    
    /**
     * Remove book from wishlist
     */
    public static function remove($user_id, $book_id) {
        $sql = "DELETE FROM wishlist WHERE user_id = ? AND book_id = ?";
        Database::query($sql, [$user_id, $book_id]);
        
        return ['success' => true];
    }
    
    /**
     * Check if book is in user's wishlist
     */
    public static function has($user_id, $book_id) {
        $sql = "SELECT COUNT(*) as count FROM wishlist WHERE user_id = ? AND book_id = ?";
        $result = Database::fetch($sql, [$user_id, $book_id]);
        
        return $result['count'] > 0;
    }
    
    /**
     * Get user's wishlist
     */
    public static function getUserWishlist($user_id, $limit = null, $offset = 0) {
        $sql = "SELECT b.*, w.added_at, u.name as owner_name 
                FROM wishlist w 
                JOIN books b ON w.book_id = b.id 
                LEFT JOIN users u ON b.user_id = u.id 
                WHERE w.user_id = ? AND b.status = 'available' 
                ORDER BY w.added_at DESC";
        
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            return Database::fetchAll($sql, [$user_id, $limit, $offset]);
        }
        
        return Database::fetchAll($sql, [$user_id]);
    }
    
    /**
     * Count user's wishlist items
     */
    public static function count($user_id) {
        $sql = "SELECT COUNT(*) as count FROM wishlist w 
                JOIN books b ON w.book_id = b.id 
                WHERE w.user_id = ? AND b.status = 'available'";
        $result = Database::fetch($sql, [$user_id]);
        
        return $result['count'];
    }
    
    /**
     * Toggle wishlist status
     */
    public static function toggle($user_id, $book_id) {
        if (self::has($user_id, $book_id)) {
            self::remove($user_id, $book_id);
            return ['success' => true, 'action' => 'removed'];
        } else {
            return self::add($user_id, $book_id);
        }
    }
}
?>