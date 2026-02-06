<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Home';
$greeting = get_time_based_greeting();
$featured_books = get_featured_books();

// Check for registration success
if (isset($_GET['registered']) && $_GET['registered'] == 'true') {
    set_flash_message('success', 'Registration successful! Welcome to BookExchange.');
}

require_once 'includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-content">
        <h1>Share Books, Share Knowledge</h1>
        <p class="greeting"><?php echo $greeting; ?>! Join our community of book lovers to exchange books, discover new reads, and connect with fellow readers.</p>
        <div class="hero-actions">
            <a href="books.php" class="btn btn-primary btn-large">Browse Books</a>
            <a href="auth.php?register=true" class="btn btn-secondary btn-large">Join Now</a>
        </div>
    </div>
    <div class="hero-image">
        <img src="https://images.unsplash.com/photo-1521587760476-6c12a4b040da?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Books on shelf">
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
                <img src="<?php echo $book['image_url']; ?>" alt="<?php echo htmlspecialchars($book['title']); ?> Cover">
                <span class="book-condition"><?php echo $book['condition']; ?></span>
            </div>
            <div class="book-info">
                <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                <p class="book-author">By <?php echo htmlspecialchars($book['author']); ?></p>
                <div class="book-genre"><?php echo $book['genre']; ?></div>
                <p class="book-description"><?php echo htmlspecialchars($book['description']); ?></p>
                <div class="book-actions">
                    <button class="btn-details" data-book="<?php echo $book['id']; ?>">View Details</button>
                    <button class="btn-request">Request Exchange</button>
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