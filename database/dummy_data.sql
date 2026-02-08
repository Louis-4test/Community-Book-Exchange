USE book_exchange;

-- Insert more sample users
INSERT INTO users (name, email, password_hash, location, bio) VALUES
('Alex Johnson', 'alex@example.com', '$2y$10$YourHashedPasswordHere', 'New York, NY', 'Avid reader and book collector'),
('Sarah Wilson', 'sarah@example.com', '$2y$10$YourHashedPasswordHere', 'Los Angeles, CA', 'Love mystery and thriller novels'),
('Mike Chen', 'mike@example.com', '$2y$10$YourHashedPasswordHere', 'Chicago, IL', 'Science fiction enthusiast'),
('Emma Davis', 'emma@example.com', '$2y$10$YourHashedPasswordHere', 'Miami, FL', 'Romance novel lover'),
('James Miller', 'james@example.com', '$2y$10$YourHashedPasswordHere', 'Seattle, WA', 'Non-fiction and biographies');

-- Insert more sample books
INSERT INTO books (user_id, title, author, isbn, genre, condition, description, year_published, exchange_type) VALUES
(3, 'The Art of Baking', 'Claire Bennett', '978-1-234-56789-0', 'Non-Fiction', 'Like New', 'Master the art of baking with this comprehensive guide.', 2021, 'trade'),
(4, 'Echoes of War', 'Robert Jackson', '978-1-234-56789-1', 'History', 'Fair', 'A historical account of WWII from the perspective of soldiers.', 2019, 'giveaway'),
(5, 'Quantum Dreams', 'Lisa Wong', '978-1-234-56789-2', 'Science Fiction', 'Excellent', 'A scientist discovers how to enter dreams in this sci-fi thriller.', 2023, 'trade'),
(6, 'Mountain High', 'Carlos Ruiz', '978-1-234-56789-3', 'Biography', 'Good', 'The autobiography of a renowned mountain climber.', 2022, 'trade'),
(7, 'Digital Frontier', 'Kevin Smith', '978-1-234-56789-4', 'Technology', 'New', 'Exploring the future of digital technology and AI.', 2023, 'trade'),
(3, 'Ocean Depths', 'Maria Garcia', '978-1-234-56789-5', 'Science', 'Good', 'Discover the mysteries of the deep ocean.', 2020, 'giveaway'),
(4, 'Culinary Journey', 'Thomas Lee', '978-1-234-56789-6', 'Cookbook', 'Like New', 'A culinary journey through world cuisines.', 2021, 'trade'),
(5, 'Mindful Living', 'Rachel Green', '978-1-234-56789-7', 'Self-Help', 'Good', 'Practical guide to mindfulness and meditation.', 2022, 'trade');

-- Insert some exchange requests
INSERT INTO exchange_requests (book_id, requester_id, status, message) VALUES
(1, 3, 'pending', 'I have a great mystery book to trade!'),
(3, 4, 'accepted', 'I love fantasy novels!'),
(5, 5, 'rejected', 'Looking for this book for my collection');

-- Insert sample contact messages
INSERT INTO contact_messages (name, email, subject, message, status) VALUES
('John Doe', 'john@example.com', 'Question about exchange', 'How does the exchange process work?', 'read'),
('Jane Smith', 'jane@example.com', 'Feature request', 'Can you add a rating system?', 'unread');