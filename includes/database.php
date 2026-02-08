<?php
/**
 * Database connection and helper functions
 */
require_once 'config.php';

class Database {
    private static $connection = null;
    
    /**
     * Get database connection
     */
    public static function getConnection() {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO(
                    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                die("Database connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }
    
    /**
     * Execute a query with parameters
     */
    public static function query($sql, $params = []) {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    /**
     * Fetch all rows
     */
    public static function fetchAll($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch single row
     */
    public static function fetch($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Get row count
     */
    public static function count($sql, $params = []) {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Get last insert ID
     */
    public static function lastInsertId() {
        return self::getConnection()->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public static function beginTransaction() {
        return self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public static function commit() {
        return self::getConnection()->commit();
    }
    
    /**
     * Rollback transaction
     */
    public static function rollback() {
        return self::getConnection()->rollback();
    }
}

/**
 * User-related database operations
 */
class UserModel {
    /**
     * Get user by ID
     */
    public static function getUserById($id) {
        $sql = "SELECT id, name, email, role, profile_image, location, bio, created_at 
                FROM users WHERE id = ?";
        return Database::fetch($sql, [$id]);
    }
    
    /**
     * Get user by email
     */
    public static function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        return Database::fetch($sql, [$email]);
    }
    
    /**
     * Create new user
     */
    public static function createUser($name, $email, $password_hash) {
        $sql = "INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)";
        Database::query($sql, [$name, $email, $password_hash]);
        return Database::lastInsertId();
    }
    
    /**
     * Update user profile
     */
    public static function updateUser($id, $name, $location, $bio) {
        $sql = "UPDATE users SET name = ?, location = ?, bio = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        return Database::query($sql, [$name, $location, $bio, $id]);
    }
    
    /**
     * Update user password
     */
    public static function updatePassword($id, $password_hash) {
        $sql = "UPDATE users SET password_hash = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        return Database::query($sql, [$password_hash, $id]);
    }
    
    /**
     * Get all users (for admin)
     */
    public static function getAllUsers($limit = null, $offset = 0) {
        $sql = "SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            return Database::fetchAll($sql, [$limit, $offset]);
        }
        return Database::fetchAll($sql);
    }
    
    /**
     * Count total users
     */
    public static function countUsers() {
        $sql = "SELECT COUNT(*) as count FROM users";
        $result = Database::fetch($sql);
        return $result['count'];
    }
    
    /**
     * Delete user (admin only)
     */
    public static function deleteUser($id) {
        $sql = "DELETE FROM users WHERE id = ?";
        return Database::query($sql, [$id]);
    }
}

/**
 * Book-related database operations
 */
class BookModel {
    /**
     * Get all books with optional filters
     */
    public static function getAllBooks($filters = [], $limit = null, $offset = 0) {
        $sql = "SELECT b.*, u.name as owner_name, u.location as owner_location 
                FROM books b 
                LEFT JOIN users u ON b.user_id = u.id 
                WHERE b.status = 'available'";
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['genre'])) {
            $sql .= " AND b.genre = ?";
            $params[] = $filters['genre'];
        }
        
        if (!empty($filters['state'])) {
            $sql .= " AND b.state = ?";
            $params[] = $filters['state'];
        }
        
        if (!empty($filters['exchange_type'])) {
            $sql .= " AND b.exchange_type = ?";
            $params[] = $filters['exchange_type'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        // Apply sorting
        $sort = $filters['sort'] ?? 'newest';
        switch ($sort) {
            case 'title':
                $sql .= " ORDER BY b.title ASC";
                break;
            case 'state':
                $sql .= " ORDER BY FIELD(b.state, 'New', 'Like New', 'Good', 'Fair', 'Poor')";
                break;
            case 'oldest':
                $sql .= " ORDER BY b.created_at ASC";
                break;
            case 'newest':
            default:
                $sql .= " ORDER BY b.created_at DESC";
                break;
        }
        
        // Apply pagination
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }
        
        return Database::fetchAll($sql, $params);
    }
    
    /**
     * Count books with filters
     */
    public static function countBooks($filters = []) {
        $sql = "SELECT COUNT(*) as count FROM books b WHERE b.status = 'available'";
        $params = [];
        
        if (!empty($filters['genre'])) {
            $sql .= " AND b.genre = ?";
            $params[] = $filters['genre'];
        }
        
        if (!empty($filters['state'])) {
            $sql .= " AND b.state = ?";
            $params[] = $filters['state'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= " AND (b.title LIKE ? OR b.author LIKE ?)";
            $searchTerm = "%{$filters['search']}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $result = Database::fetch($sql, $params);
        return $result['count'];
    }
    
    /**
     * Get featured books (newest available books)
     */
    public static function getFeaturedBooks($limit = 3) {
        $sql = "SELECT b.*, u.name as owner_name 
                FROM books b 
                LEFT JOIN users u ON b.user_id = u.id 
                WHERE b.status = 'available' 
                ORDER BY b.created_at DESC 
                LIMIT ?";
        return Database::fetchAll($sql, [$limit]);
    }
    
    /**
     * Get book by ID
     */
    public static function getBookById($id) {
        $sql = "SELECT b.*, u.name as owner_name, u.email as owner_email, 
                       u.location as owner_location, u.bio as owner_bio 
                FROM books b 
                LEFT JOIN users u ON b.user_id = u.id 
                WHERE b.id = ?";
        return Database::fetch($sql, [$id]);
    }
    
    /**
     * Get books by user ID
     */
    public static function getBooksByUserId($user_id) {
        $sql = "SELECT * FROM books WHERE user_id = ? ORDER BY created_at DESC";
        return Database::fetchAll($sql, [$user_id]);
    }
    
    /**
     * Create new book
     */
    public static function createBook($book_data) {
        $sql = "INSERT INTO books (
                    user_id, title, author, isbn, genre, state, 
                    description, image_url, year_published, exchange_type
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $book_data['user_id'],
            $book_data['title'],
            $book_data['author'],
            $book_data['isbn'] ?? null,
            $book_data['genre'],
            $book_data['state'],
            $book_data['description'],
            $book_data['image_url'] ?? null,
            $book_data['year_published'] ?? null,
            $book_data['exchange_type'] ?? 'trade'
        ];
        
        Database::query($sql, $params);
        return Database::lastInsertId();
    }
    
    /**
     * Update book
     */
    public static function updateBook($id, $book_data) {
        $sql = "UPDATE books SET 
                    title = ?, author = ?, isbn = ?, genre = ?, state = ?, 
                    description = ?, image_url = ?, year_published = ?, 
                    exchange_type = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?";
        
        $params = [
            $book_data['title'],
            $book_data['author'],
            $book_data['isbn'] ?? null,
            $book_data['genre'],
            $book_data['state'],
            $book_data['description'],
            $book_data['image_url'] ?? null,
            $book_data['year_published'] ?? null,
            $book_data['exchange_type'] ?? 'trade',
            $id
        ];
        
        return Database::query($sql, $params);
    }
    
    /**
     * Delete book
     */
    public static function deleteBook($id) {
        $sql = "DELETE FROM books WHERE id = ?";
        return Database::query($sql, [$id]);
    }
    
    /**
     * Update book status
     */
    public static function updateBookStatus($id, $status) {
        $sql = "UPDATE books SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        return Database::query($sql, [$status, $id]);
    }
    
    /**
     * Get all genres
     */
    public static function getGenres() {
        $sql = "SELECT DISTINCT genre FROM books WHERE status = 'available' ORDER BY genre";
        $results = Database::fetchAll($sql);
        return array_column($results, 'genre');
    }
    
    /**
     * Get book statistics
     */
    public static function getStatistics() {
        $sql = "SELECT 
                    COUNT(*) as total_books,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_books,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_books,
                    SUM(CASE WHEN status = 'exchanged' THEN 1 ELSE 0 END) as exchanged_books,
                    COUNT(DISTINCT genre) as unique_genres,
                    COUNT(DISTINCT user_id) as unique_owners
                FROM books";
        return Database::fetch($sql);
    }
}

/**
 * Contact message database operations
 */
class ContactModel {
    /**
     * Create contact message
     */
    public static function createMessage($name, $email, $subject, $message) {
        $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
        return Database::query($sql, [$name, $email, $subject, $message]);
    }
    
    /**
     * Get all contact messages
     */
    public static function getAllMessages($limit = null, $offset = 0) {
        $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC";
        if ($limit !== null) {
            $sql .= " LIMIT ? OFFSET ?";
            return Database::fetchAll($sql, [$limit, $offset]);
        }
        return Database::fetchAll($sql);
    }
    
    /**
     * Get message by ID
     */
    public static function getMessageById($id) {
        $sql = "SELECT * FROM contact_messages WHERE id = ?";
        return Database::fetch($sql, [$id]);
    }
    
    /**
     * Update message status
     */
    public static function updateStatus($id, $status) {
        $sql = "UPDATE contact_messages SET status = ? WHERE id = ?";
        return Database::query($sql, [$status, $id]);
    }
    
    /**
     * Delete message
     */
    public static function deleteMessage($id) {
        $sql = "DELETE FROM contact_messages WHERE id = ?";
        return Database::query($sql, [$id]);
    }
    
    /**
     * Count messages by status
     */
    public static function countByStatus($status = null) {
        if ($status) {
            $sql = "SELECT COUNT(*) as count FROM contact_messages WHERE status = ?";
            $result = Database::fetch($sql, [$status]);
        } else {
            $sql = "SELECT COUNT(*) as count FROM contact_messages";
            $result = Database::fetch($sql);
        }
        return $result['count'];
    }
}
?>