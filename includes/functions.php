<?php
require_once 'database.php';

/**
 * Helper functions for the Book Exchange application
 */

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 */
function validate_password($password) {
    // At least 8 characters, 1 uppercase, 1 number, 1 special char
    $pattern = '/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
    return preg_match($pattern, $password);
}

/**
 * Hash password
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Get greeting based on server time
 */
function get_time_based_greeting() {
    $hour = date('H');
    
    if ($hour < 12) {
        return "Good Morning";
    } elseif ($hour < 17) {
        return "Good Afternoon";
    } elseif ($hour < 21) {
        return "Good Evening";
    } else {
        return "Good Night";
    }
}

/**
 * Get featured books from database
 */
function get_featured_books($limit = 3) {
    return BookModel::getFeaturedBooks($limit);
}

/**
 * Get all books with filtering
 */
function get_all_books($filters = []) {
    return BookModel::getAllBooks($filters);
}

/**
 * Get book by ID
 */
function get_book_by_id($id) {
    return BookModel::getBookById($id);
}

/**
 * Format date for display
 */
function format_date($date_string) {
    return date('F j, Y', strtotime($date_string));
}

/**
 * Format date with time
 */
function format_datetime($date_string) {
    return date('M j, Y g:i A', strtotime($date_string));
}

/**
 * Check if user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user info
 */
function get_app_user() {
    if (isset($_SESSION['user_id'])) {
        return UserModel::getUserById($_SESSION['user_id']);
    }
    return null;
}

/**
 * Generate CSRF token field
 */
function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

/**
 * Validate CSRF token
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get and clear flash message
 */
function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $message = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $message;
    }
    return null;
}

/**
 * Upload image file
 */
function upload_image($file, $prefix = 'book') {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $file['error']);
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File is too large. Maximum size is 5MB.');
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, ALLOWED_IMAGE_TYPES)) {
        throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $prefix . '_' . uniqid() . '_' . time() . '.' . $extension;
    $filepath = UPLOAD_PATH . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file.');
    }
    
    return $filename;
}

/**
 * Delete uploaded image
 */
function delete_image($filename) {
    if ($filename && file_exists(UPLOAD_PATH . $filename)) {
        return unlink(UPLOAD_PATH . $filename);
    }
    return false;
}

/**
 * Get image URL
 */
function get_image_url($filename) {
    if ($filename && file_exists(UPLOAD_PATH . $filename)) {
        return SITE_URL . '/' . UPLOAD_PATH . $filename;
    }
    // Return default image if file doesn't exist
    return 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80';
}

/**
 * Get book condition options
 */
function get_condition_options() {
    return ['New', 'Like New', 'Good', 'Fair', 'Poor'];
}

/**
 * Get book status options
 */
function get_status_options() {
    return ['available', 'pending', 'exchanged'];
}

/**
 * Get exchange type options
 */
function get_exchange_type_options() {
    return ['trade', 'giveaway'];
}

/**
 * Get genre options from database
 */
function get_genre_options() {
    return BookModel::getGenres();
}

/**
 * Redirect with flash message
 */
function redirect_with_message($url, $type, $message) {
    set_flash_message($type, $message);
    header("Location: $url");
    exit;
}

/**
 * Validate book data
 */
function validate_book_data($data) {
    $errors = [];
    
    if (empty($data['title'])) {
        $errors['title'] = 'Title is required';
    }
    
    if (empty($data['author'])) {
        $errors['author'] = 'Author is required';
    }
    
    if (empty($data['genre'])) {
        $errors['genre'] = 'Genre is required';
    }
    
    if (empty($data['condition'])) {
        $errors['condition'] = 'Condition is required';
    }
    
    if (empty($data['description'])) {
        $errors['description'] = 'Description is required';
    } elseif (strlen($data['description']) < 10) {
        $errors['description'] = 'Description must be at least 10 characters';
    }
    
    if (!empty($data['year_published']) && 
        ($data['year_published'] < 1000 || $data['year_published'] > date('Y'))) {
        $errors['year_published'] = 'Invalid year';
    }
    
    return $errors;
}

/**
 * Get pagination links
 */
function get_pagination_links($current_page, $total_pages, $base_url, $query_params = []) {
    $links = '';
    
    // Previous link
    if ($current_page > 1) {
        $query_params['page'] = $current_page - 1;
        $prev_url = $base_url . '?' . http_build_query($query_params);
        $links .= '<a href="' . $prev_url . '" class="pagination-btn"><i class="fas fa-chevron-left"></i> Previous</a>';
    }
    
    // Page numbers
    $links .= '<div class="page-numbers">';
    for ($i = 1; $i <= $total_pages; $i++) {
        $query_params['page'] = $i;
        $page_url = $base_url . '?' . http_build_query($query_params);
        
        if ($i == $current_page) {
            $links .= '<span class="page-number active">' . $i . '</span>';
        } elseif ($i == 1 || $i == $total_pages || ($i >= $current_page - 2 && $i <= $current_page + 2)) {
            $links .= '<a href="' . $page_url . '" class="page-number">' . $i . '</a>';
        } elseif ($i == $current_page - 3 || $i == $current_page + 3) {
            $links .= '<span class="page-number">...</span>';
        }
    }
    $links .= '</div>';
    
    // Next link
    if ($current_page < $total_pages) {
        $query_params['page'] = $current_page + 1;
        $next_url = $base_url . '?' . http_build_query($query_params);
        $links .= '<a href="' . $next_url . '" class="pagination-btn">Next <i class="fas fa-chevron-right"></i></a>';
    }
    
    return $links;
}

/**
 * Get book statistics for dashboard
 */
function get_dashboard_statistics() {
    $book_stats = BookModel::getStatistics();
    $user_count = UserModel::countUsers();
    $unread_messages = ContactModel::countByStatus('unread');
    
    return [
        'total_books' => $book_stats['total_books'] ?? 0,
        'available_books' => $book_stats['available_books'] ?? 0,
        'total_users' => $user_count,
        'unread_messages' => $unread_messages,
        'unique_genres' => $book_stats['unique_genres'] ?? 0,
        'unique_owners' => $book_stats['unique_owners'] ?? 0
    ];
}

/**
 * Require admin authentication
 */
function require_admin() {
    if (!is_logged_in() || !is_admin()) {
        set_flash_message('error', 'You must be logged in as an administrator to access this page.');
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }
}

/**
 * Require user authentication
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash_message('error', 'You must be logged in to access this page.');
        header('Location: auth.php');
        exit;
    }
}

function get_state_options() {
    return [
        'new' => 'New',
        'used' => 'Used',
        'fair' => 'Fair',
        'old' => 'Old'
    ];
}
?>