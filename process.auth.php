<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Initialize error and form data arrays
$_SESSION['errors'] = [];
$_SESSION['form_data'] = [];

// Validate CSRF token
if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
    die('Invalid CSRF token');
}

// Handle login
if ($_POST['action'] === 'login') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // Validation
    if (empty($email)) {
        $_SESSION['errors']['login_email'] = 'Email is required';
        $_SESSION['form_data']['login_email'] = $email;
    } elseif (!validate_email($email)) {
        $_SESSION['errors']['login_email'] = 'Please enter a valid email address';
        $_SESSION['form_data']['login_email'] = $email;
    }
    
    if (empty($password)) {
        $_SESSION['errors']['login_password'] = 'Password is required';
    }
    
    // If no errors, process login
    if (empty($_SESSION['errors'])) {
        // In a real application, you would:
        // 1. Verify credentials against database
        // 2. Set session variables
        // 3. Handle "remember me" functionality
        
        // For now, we'll simulate a successful login
        $_SESSION['user_id'] = 1;
        $_SESSION['user'] = [
            'id' => 1,
            'name' => 'Demo User',
            'email' => $email
        ];
        
        // Set flash message
        set_flash_message('success', 'Welcome back to BookExchange!');
        
        // Redirect to homepage
        header('Location: index.php');
        exit;
    } else {
        // Redirect back to login page with errors
        header('Location: auth.php');
        exit;
    }
}

// Handle registration
if ($_POST['action'] === 'register') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $terms = isset($_POST['terms']);
    
    // Store form data for repopulation
    $_SESSION['form_data']['register_name'] = $name;
    $_SESSION['form_data']['register_email'] = $email;
    $_SESSION['form_data']['terms'] = $terms;
    
    // Validation
    if (empty($name)) {
        $_SESSION['errors']['register_name'] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $_SESSION['errors']['register_name'] = 'Name must be at least 2 characters';
    }
    
    if (empty($email)) {
        $_SESSION['errors']['register_email'] = 'Email is required';
    } elseif (!validate_email($email)) {
        $_SESSION['errors']['register_email'] = 'Please enter a valid email address';
    }
    
    if (empty($password)) {
        $_SESSION['errors']['register_password'] = 'Password is required';
    } elseif (!validate_password($password)) {
        $_SESSION['errors']['register_password'] = 'Password must contain at least 8 characters, one uppercase letter, one number, and one special character';
    }
    
    if (empty($confirm_password)) {
        $_SESSION['errors']['confirm_password'] = 'Please confirm your password';
    } elseif ($password !== $confirm_password) {
        $_SESSION['errors']['confirm_password'] = 'Passwords do not match';
    }
    
    if (!$terms) {
        $_SESSION['errors']['terms'] = 'You must agree to the terms and conditions';
    }
    
    // If no errors, process registration
    if (empty($_SESSION['errors'])) {
        // In a real application, you would:
        // 1. Check if email already exists
        // 2. Hash the password
        // 3. Save user to database
        // 4. Send welcome email
        // 5. Log the user in automatically
        
        // For now, we'll simulate a successful registration
        $_SESSION['user_id'] = 1;
        $_SESSION['user'] = [
            'id' => 1,
            'name' => $name,
            'email' => $email
        ];
        
        // Set flash message
        set_flash_message('success', 'Registration successful! Welcome to BookExchange.');
        
        // Clear form data
        unset($_SESSION['form_data']);
        
        // Redirect to homepage
        header('Location: index.php?registered=true');
        exit;
    } else {
        // Redirect back to registration page with errors
        header('Location: auth.php?register=true');
        exit;
    }
}

// If no action matched, redirect to auth page
header('Location: auth.php');
exit;
?>