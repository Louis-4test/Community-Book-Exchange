<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Home';
$greeting = get_time_based_greeting();
$featured_books = get_featured_books(3);

// Check for registration success
if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
    set_flash_message('success', 'Registration successful! Welcome to BookExchange.');
}

// Get statistics for homepage
$stats = get_dashboard_statistics();

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Share Books, Share Knowledge</h1>
        <p class="greeting"><?php echo $greeting; ?>! Join our community of <?php echo $stats['total_users']; ?> book lovers to exchange books, discover new reads, and connect with fellow readers.</p>
        <div class="hero-actions">
            <a href="books.php" class="btn btn-primary btn-large">Browse <?php echo $stats['available_books']; ?> Books</a>
            <?php if (!is_logged_in()): ?>
            <a href="auth.php?register=true" class="btn btn-secondary btn-large">Join Now</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Books on shelf">
    </div>
</section>

<!-- Community Stats -->
<section class="stats-section">
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-number"><?php echo $stats['total_books']; ?></div>
            <div class="stat-label">Books Listed</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $stats['available_books']; ?></div>
            <div class="stat-label">Available Now</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
            <div class="stat-label">Community Members</div>
        </div>
        <div class="stat-item">
            <div class="stat-number"><?php echo $stats['unique_genres']; ?></div>
            <div class="stat-label">Genres</div>
        </div>
    </div>
</section>

<!-- Featured Books Section -->
<section class="featured-section">
    <div class="section-header">
        <h2>Featured Books</h2>
        <p>Recently added books available for exchange</p>
    </div>
    
    <div class="books-grid">
        <?php foreach ($featured_books as $book): ?>
        <div class="book-card">
            <div class="book-cover">
                <img src="<?php echo get_image_url($book['image_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?> Cover">
                <span class="book-condition"><?php echo $book['condition']; ?></span>
                <?php if ($book['exchange_type'] == 'giveaway'): ?>
                <span class="book-exchange-type giveaway">Giveaway</span>
                <?php endif; ?>
            </div>
            <div class="book-info">
                <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                <p class="book-author">By <?php echo htmlspecialchars($book['author']); ?></p>
                <div class="book-genre"><?php echo $book['genre']; ?></div>
                <p class="book-description"><?php echo htmlspecialchars(substr($book['description'], 0, 100)); ?>...</p>
                <div class="book-actions">
                    <button class="btn-details" data-book="<?php echo $book['id']; ?>">View Details</button>
                    <button class="btn-request" data-book="<?php echo $book['id']; ?>">Request Exchange</button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="section-footer">
        <a href="books.php" class="btn btn-outline">View All Books</a>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works">
    <div class="section-header">
        <h2>How It Works</h2>
        <p>Exchange books in three simple steps</p>
    </div>
    
    <div class="steps-container">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">
                <h3>Create an Account</h3>
                <p>Register for free and create your profile to join our book-loving community.</p>
            </div>
        </div>
        
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">
                <h3>List Your Books</h3>
                <p>Add books you're willing to exchange with details about condition and genre.</p>
            </div>
        </div>
        
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">
                <h3>Browse & Exchange</h3>
                <p>Search for books you'd like to read and request exchanges with other members.</p>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>