<?php
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
 * Get featured books (simulated data - will connect to DB in later modules)
 */
function get_featured_books($limit = 3) {
    return [
        [
            'id' => 1,
            'title' => 'The Silent Echo',
            'author' => 'Maria Rodriguez',
            'genre' => 'Fiction',
            'condition' => 'Like New',
            'description' => 'A gripping mystery novel about a detective solving a decades-old cold case.',
            'image_url' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80',
            'date_listed' => '2023-10-15'
        ],
        [
            'id' => 2,
            'title' => 'Cosmic Patterns',
            'author' => 'David Chen',
            'genre' => 'Science',
            'condition' => 'Good',
            'description' => 'Exploring the mathematical patterns that govern the universe.',
            'image_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?ixlib=rb-4.0.3&auto=format&fit=crop&w=698&q=80',
            'date_listed' => '2023-11-02'
        ],
        [
            'id' => 3,
            'title' => 'The Lost Kingdom',
            'author' => 'Elena Petrova',
            'genre' => 'Fantasy',
            'condition' => 'Excellent',
            'description' => 'An epic fantasy tale of a forgotten kingdom\'s rise from the ashes.',
            'image_url' => 'https://images.unsplash.com/photo-1541963463532-d68292c34b19?ixlib=rb-4.0.3&auto=format&fit=crop&w=688&q=80',
            'date_listed' => '2023-11-10'
        ]
    ];
}

/**
 * Get all books with filtering (simulated data)
 */
function get_all_books($filters = []) {
    $books = [
        [
            'id' => 1,
            'title' => 'The Silent Echo',
            'author' => 'Maria Rodriguez',
            'genre' => 'Fiction',
            'condition' => 'Like New',
            'description' => 'A gripping mystery novel about a detective solving a decades-old cold case.',
            'year' => 2022,
            'date_listed' => '2023-10-15'
        ],
        [
            'id' => 2,
            'title' => 'Cosmic Patterns',
            'author' => 'David Chen',
            'genre' => 'Science',
            'condition' => 'Good',
            'description' => 'Exploring the mathematical patterns that govern the universe.',
            'year' => 2021,
            'date_listed' => '2023-11-02'
        ],
        [
            'id' => 3,
            'title' => 'The Lost Kingdom',
            'author' => 'Elena Petrova',
            'genre' => 'Fantasy',
            'condition' => 'Excellent',
            'description' => 'An epic fantasy tale of a forgotten kingdom\'s rise from the ashes.',
            'year' => 2023,
            'date_listed' => '2023-11-10'
        ],
        [
            'id' => 4,
            'title' => 'Urban Legends',
            'author' => 'James Peterson',
            'genre' => 'Mystery',
            'condition' => 'Good',
            'description' => 'A collection of modern urban legends with a supernatural twist.',
            'year' => 2020,
            'date_listed' => '2023-10-28'
        ],
        [
            'id' => 5,
            'title' => 'The Art of Baking',
            'author' => 'Claire Bennett',
            'genre' => 'Non-Fiction',
            'condition' => 'Like New',
            'description' => 'Master the art of baking with this comprehensive guide.',
            'year' => 2021,
            'date_listed' => '2023-11-05'
        ],
        [
            'id' => 6,
            'title' => 'Echoes of War',
            'author' => 'Robert Jackson',
            'genre' => 'History',
            'condition' => 'Fair',
            'description' => 'A historical account of WWII from the perspective of soldiers.',
            'year' => 2019,
            'date_listed' => '2023-10-20'
        ],
        [
            'id' => 7,
            'title' => 'Quantum Dreams',
            'author' => 'Lisa Wong',
            'genre' => 'Science Fiction',
            'condition' => 'Excellent',
            'description' => 'A scientist discovers how to enter dreams in this sci-fi thriller.',
            'year' => 2023,
            'date_listed' => '2023-11-12'
        ],
        [
            'id' => 8,
            'title' => 'Mountain High',
            'author' => 'Carlos Ruiz',
            'genre' => 'Biography',
            'condition' => 'Good',
            'description' => 'The autobiography of a renowned mountain climber.',
            'year' => 2022,
            'date_listed' => '2023-10-18'
        ]
    ];
    
    // Apply filters if provided
    if (!empty($filters)) {
        $filtered_books = [];
        
        foreach ($books as $book) {
            $include = true;
            
            if (isset($filters['genre']) && !empty($filters['genre']) && 
                strtolower($book['genre']) !== strtolower($filters['genre'])) {
                $include = false;
            }
            
            if (isset($filters['condition']) && !empty($filters['condition']) && 
                strtolower(str_replace(' ', '-', $book['condition'])) !== strtolower($filters['condition'])) {
                $include = false;
            }
            
            if (isset($filters['search']) && !empty($filters['search'])) {
                $search = strtolower($filters['search']);
                $book_text = strtolower($book['title'] . ' ' . $book['author'] . ' ' . $book['genre']);
                
                if (strpos($book_text, $search) === false) {
                    $include = false;
                }
            }
            
            if ($include) {
                $filtered_books[] = $book;
            }
        }
        
        return $filtered_books;
    }
    
    return $books;
}

/**
 * Format date for display
 */
function format_date($date_string) {
    return date('F j, Y', strtotime($date_string));
}

/**
 * Check if user is logged in (for future modules)
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user info (for future modules)
 */
function get_current_user() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
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
?>