-- Add cache for Open Library API data
CREATE TABLE IF NOT EXISTS book_api_cache (
    id INT PRIMARY KEY AUTO_INCREMENT,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    cover_url VARCHAR(500),
    api_data JSON,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_isbn (isbn)
);

-- Add search index for better performance
CREATE FULLTEXT INDEX idx_books_search ON books(title, author, description, genre);

-- Add view counts for popularity tracking
ALTER TABLE books 
ADD COLUMN IF NOT EXISTS view_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS search_rank FLOAT DEFAULT 0;

-- Create search log table for analytics
CREATE TABLE IF NOT EXISTS search_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    search_query VARCHAR(255),
    result_count INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);