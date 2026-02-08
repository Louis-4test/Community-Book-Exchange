-- Create database
CREATE DATABASE IF NOT EXISTS book_exchange;
USE book_exchange;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    profile_image VARCHAR(255),
    location VARCHAR(100),
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Books table
CREATE TABLE books (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(100) NOT NULL,
    isbn VARCHAR(20),
    genre VARCHAR(50) NOT NULL,
    state ENUM('New', 'Like New', 'Good', 'Fair', 'Poor') DEFAULT 'Good',
    description TEXT,
    image_url VARCHAR(255),
    status ENUM('available', 'pending', 'exchanged') DEFAULT 'available',
    year_published YEAR,
    exchange_type ENUM('giveaway', 'trade') DEFAULT 'trade',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Exchange requests table
CREATE TABLE exchange_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    book_id INT NOT NULL,
    requester_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'completed') DEFAULT 'pending',
    message TEXT,
    proposed_book_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (proposed_book_id) REFERENCES books(id) ON DELETE SET NULL
);

-- Messages table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exchange_id INT NOT NULL,
    sender_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (exchange_id) REFERENCES exchange_requests(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Contact messages table
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample admin user (password: Admin123!)
INSERT INTO users (name, email, password_hash, role) VALUES 
('Admin User', 'admin@bookexchange.com', '$2y$10$YourHashedPasswordHere', 'admin');

-- Insert sample regular user (password: User123!)
INSERT INTO users (name, email, password_hash) VALUES 
('Demo User', 'user@example.com', '$2y$10$YourHashedPasswordHere');

-- Insert sample books
INSERT INTO books (user_id, title, author, isbn, genre, state, description, status) VALUES
(1, 'The Silent Echo', 'Maria Rodriguez', '978-3-16-148410-0', 'Fiction', 'Like New', 'A gripping mystery novel about a detective solving a decades-old cold case.', 'available'),
(2, 'Cosmic Patterns', 'David Chen', '978-1-23-456789-7', 'Science', 'Good', 'Exploring the mathematical patterns that govern the universe.', 'available'),
(1, 'The Lost Kingdom', 'Elena Petrova', '978-0-12-345678-9', 'Fantasy', 'Excellent', 'An epic fantasy tale of a forgotten kingdom rise from the ashes.', 'available'),
(2, 'Urban Legends', 'James Peterson', '978-0-98-765432-1', 'Mystery', 'Good', 'A collection of modern urban legends with a supernatural twist.', 'available');

-- Create indexes for better performance
CREATE INDEX idx_books_genre ON books(genre);
CREATE INDEX idx_books_state ON books(state);
CREATE INDEX idx_books_status ON books(status);
CREATE INDEX idx_books_user_id ON books(user_id);