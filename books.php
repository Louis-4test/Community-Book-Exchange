<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Browse Books';

// Handle filters
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filters['search'] = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
    $filters['genre'] = isset($_GET['genre']) ? sanitize_input($_GET['genre']) : '';
    $filters['condition'] = isset($_GET['condition']) ? sanitize_input($_GET['condition']) : '';
    $filters['sort'] = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'newest';
}

// Get filtered books
$books = get_all_books($filters);

// Handle pagination
$books_per_page = 6;
$total_books = count($books);
$total_pages = ceil($total_books / $books_per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, min($current_page, $total_pages));
$start_index = ($current_page - 1) * $books_per_page;
$paginated_books = array_slice($books, $start_index, $books_per_page);

require_once 'includes/header.php';
?>

<!-- Books Page Main Content -->
<div class="books-page">
    <div class="books-header">
        <h1>Browse Available Books</h1>
        <p>Discover books shared by our community members</p>
    </div>
    
    <!-- Search and Filter Section -->
    <section class="search-section">
        <form method="GET" action="books.php" class="search-form">
            <div class="search-container">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" id="bookSearch" placeholder="Search by title, author, or genre..." 
                           value="<?php echo htmlspecialchars($filters['search']); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                
                <div class="filter-options">
                    <div class="filter-group">
                        <label for="genreFilter">Genre</label>
                        <select name="genre" id="genreFilter">
                            <option value="">All Genres</option>
                            <option value="fiction" <?php echo $filters['genre'] == 'fiction' ? 'selected' : ''; ?>>Fiction</option>
                            <option value="non-fiction" <?php echo $filters['genre'] == 'non-fiction' ? 'selected' : ''; ?>>Non-Fiction</option>
                            <option value="fantasy" <?php echo $filters['genre'] == 'fantasy' ? 'selected' : ''; ?>>Fantasy</option>
                            <option value="science" <?php echo $filters['genre'] == 'science' ? 'selected' : ''; ?>>Science</option>
                            <option value="mystery" <?php echo $filters['genre'] == 'mystery' ? 'selected' : ''; ?>>Mystery</option>
                            <option value="biography" <?php echo $filters['genre'] == 'biography' ? 'selected' : ''; ?>>Biography</option>
                            <option value="history" <?php echo $filters['genre'] == 'history' ? 'selected' : ''; ?>>History</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="conditionFilter">Condition</label>
                        <select name="condition" id="conditionFilter">
                            <option value="">All Conditions</option>
                            <option value="new" <?php echo $filters['condition'] == 'new' ? 'selected' : ''; ?>>New</option>
                            <option value="like-new" <?php echo $filters['condition'] == 'like-new' ? 'selected' : ''; ?>>Like New</option>
                            <option value="good" <?php echo $filters['condition'] == 'good' ? 'selected' : ''; ?>>Good</option>
                            <option value="fair" <?php echo $filters['condition'] == 'fair' ? 'selected' : ''; ?>>Fair</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sortBy">Sort By</label>
                        <select name="sort" id="sortBy">
                            <option value="newest" <?php echo $filters['sort'] == 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="title" <?php echo $filters['sort'] == 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                            <option value="condition" <?php echo $filters['sort'] == 'condition' ? 'selected' : ''; ?>>Best Condition</option>
                        </select>
                    </div>
                    
                    <button type="button" class="btn btn-outline" onclick="window.location.href='books.php'">Reset Filters</button>
                </div>
            </div>
        </form>
    </section>
    
    <!-- Books Grid Section -->
    <section class="books-section">
        <?php if ($total_books > 0): ?>
            <div class="books-grid" id="booksGrid">
                <?php foreach ($paginated_books as $book): ?>
                <div class="book-card">
                    <div class="book-cover">
                        <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" alt="Book Cover">
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
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                <a href="books.php?page=<?php echo $current_page - 1; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['genre']) ? '&genre=' . urlencode($filters['genre']) : ''; ?><?php echo !empty($filters['condition']) ? '&condition=' . urlencode($filters['condition']) : ''; ?><?php echo !empty($filters['sort']) ? '&sort=' . urlencode($filters['sort']) : ''; ?>" 
                   class="pagination-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
                <?php endif; ?>
                
                <div class="page-numbers">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == $current_page): ?>
                            <span class="page-number active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="books.php?page=<?php echo $i; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['genre']) ? '&genre=' . urlencode($filters['genre']) : ''; ?><?php echo !empty($filters['condition']) ? '&condition=' . urlencode($filters['condition']) : ''; ?><?php echo !empty($filters['sort']) ? '&sort=' . urlencode($filters['sort']) : ''; ?>" 
                               class="page-number">
                                <?php echo $i; ?>
                            </a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                
                <?php if ($current_page < $total_pages): ?>
                <a href="books.php?page=<?php echo $current_page + 1; ?><?php echo !empty($filters['search']) ? '&search=' . urlencode($filters['search']) : ''; ?><?php echo !empty($filters['genre']) ? '&genre=' . urlencode($filters['genre']) : ''; ?><?php echo !empty($filters['condition']) ? '&condition=' . urlencode($filters['condition']) : ''; ?><?php echo !empty($filters['sort']) ? '&sort=' . urlencode($filters['sort']) : ''; ?>" 
                   class="pagination-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <div class="results-info">
                <p>Showing <?php echo count($paginated_books); ?> of <?php echo $total_books; ?> books</p>
            </div>
            
        <?php else: ?>
            <div class="no-results active">
                <i class="fas fa-book-open"></i>
                <h3>No books found</h3>
                <p>Try adjusting your search or filters</p>
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
require_once 'includes/footer.php';
?>