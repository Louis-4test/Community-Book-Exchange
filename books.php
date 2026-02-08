<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Browse Books';

// Handle wishlist actions via AJAX/PRG
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
    
    $action = $_POST['action'];
    $book_id = $_POST['book_id'] ?? null;
    
    if ($action === 'toggle_wishlist' && $book_id) {
        if (!is_logged_in()) {
            echo json_encode(['success' => false, 'error' => 'Please log in to use wishlist']);
            exit;
        }
        
        $result = toggle_wishlist($book_id);
        echo json_encode($result);
        exit;
    }
}

// Handle filters from GET request
$filters = [];
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $filters['search'] = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
    $filters['genre'] = isset($_GET['genre']) ? sanitize_input($_GET['genre']) : '';
    $filters['state'] = isset($_GET['state']) ? sanitize_input($_GET['state']) : '';
    $filters['exchange_type'] = isset($_GET['exchange_type']) ? sanitize_input($_GET['exchange_type']) : '';
    $filters['sort'] = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'newest';
}

// Get books from database with pagination
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = BOOKS_PER_PAGE;
$offset = ($current_page - 1) * $limit;

// Get books and total count
$books = BookModel::getAllBooks($filters, $limit, $offset);
$total_books = BookModel::countBooks($filters);
$total_pages = ceil($total_books / $limit);

// Get available genres for filter
$genres = get_genre_options();

require_once 'includes/header.php';
?>

<!-- Books Page Main Content -->
<div class="books-page">
    <div class="books-header">
        <h1>Browse Available Books</h1>
        <p>Discover books shared by our community members</p>
        
        <?php if (is_logged_in()): ?>
        <div class="user-actions-top">
            <a href="add-book.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> List Your Book
            </a>
            <a href="my-books.php" class="btn btn-outline">
                <i class="fas fa-book"></i> My Books
            </a>
        </div>
        <?php endif; ?>
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
                            <?php foreach ($genres as $genre): ?>
                            <option value="<?php echo htmlspecialchars($genre); ?>" 
                                    <?php echo $filters['genre'] == $genre ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($genre); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="stateFilter">state</label>
                        <select name="state" id="stateFilter">
                            <option value="">All states</option>
                            <?php foreach (get_state_options() as $state): ?>
                            <option value="<?php echo $state; ?>" 
                                    <?php echo $filters['state'] == $state ? 'selected' : ''; ?>>
                                <?php echo $state; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="exchangeTypeFilter">Exchange Type</label>
                        <select name="exchange_type" id="exchangeTypeFilter">
                            <option value="">All Types</option>
                            <option value="trade" <?php echo $filters['exchange_type'] == 'trade' ? 'selected' : ''; ?>>Trade</option>
                            <option value="giveaway" <?php echo $filters['exchange_type'] == 'giveaway' ? 'selected' : ''; ?>>Giveaway</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="sortBy">Sort By</label>
                        <select name="sort" id="sortBy">
                            <option value="newest" <?php echo $filters['sort'] == 'newest' ? 'selected' : ''; ?>>Newest</option>
                            <option value="oldest" <?php echo $filters['sort'] == 'oldest' ? 'selected' : ''; ?>>Oldest</option>
                            <option value="title" <?php echo $filters['sort'] == 'title' ? 'selected' : ''; ?>>Title A-Z</option>
                            <option value="state" <?php echo $filters['sort'] == 'state' ? 'selected' : ''; ?>>Best state</option>
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
            <div class="results-info">
                <p>Found <?php echo $total_books; ?> book<?php echo $total_books != 1 ? 's' : ''; ?> matching your criteria</p>
            </div>
            
            <div class="books-grid" id="booksGrid">
                <?php foreach ($books as $book): 
                    $is_in_wishlist = is_in_wishlist($book['id']);
                    $is_owner = is_logged_in() && $_SESSION['user_id'] == $book['user_id'];
                ?>
                <div class="book-card" data-book-id="<?php echo $book['id']; ?>">
                    <div class="book-cover">
                        <img src="<?php echo get_image_url($book['image_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?> Cover">
                        <span class="book-state"><?php echo $book['state']; ?></span>
                        <?php if ($book['exchange_type'] == 'giveaway'): ?>
                        <span class="book-exchange-type giveaway">Giveaway</span>
                        <?php endif; ?>
                        
                        <!-- Wishlist Button -->
                        <?php if (is_logged_in() && !$is_owner): ?>
                        <button class="wishlist-btn <?php echo $is_in_wishlist ? 'active' : ''; ?>" 
                                data-book-id="<?php echo $book['id']; ?>"
                                title="<?php echo $is_in_wishlist ? 'Remove from wishlist' : 'Add to wishlist'; ?>">
                            <i class="fas fa-heart"></i>
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="book-info">
                        <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="book-author">By <?php echo htmlspecialchars($book['author']); ?></p>
                        <div class="book-meta">
                            <span class="book-genre"><?php echo $book['genre']; ?></span>
                            <?php if ($book['year_published']): ?>
                            <span class="book-year"><?php echo $book['year_published']; ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="book-description"><?php echo htmlspecialchars(substr($book['description'], 0, 100)); ?>...</p>
                        <div class="book-footer">
                            <div class="book-owner">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($book['owner_name']); ?></span>
                            </div>
                            <div class="book-actions">
                                <button class="btn-details" data-book="<?php echo $book['id']; ?>">View Details</button>
                                <?php if (is_logged_in() && !$is_owner): ?>
                                <button class="btn-request" data-book="<?php echo $book['id']; ?>">Request Exchange</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php
                $query_params = array_filter($filters);
                echo get_pagination_links($current_page, $total_pages, 'books.php', $query_params);
                ?>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-results active">
                <i class="fas fa-book-open"></i>
                <h3>No books found</h3>
                <p>Try adjusting your search or filters</p>
                <?php if (is_logged_in()): ?>
                <a href="add-book.php" class="btn btn-primary">List Your First Book</a>
                <?php else: ?>
                <a href="auth.php?register=true" class="btn btn-primary">Join Now to List Books</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>
</div>

<!-- Wishlist AJAX Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wishlist button functionality
    document.querySelectorAll('.wishlist-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const bookId = this.getAttribute('data-book-id');
            const isActive = this.classList.contains('active');
            
            // Visual feedback
            this.classList.add('loading');
            this.disabled = true;
            
            // Send AJAX request
            fetch('books.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'toggle_wishlist',
                    book_id: bookId,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Toggle active state
                    if (data.action === 'removed') {
                        this.classList.remove('active');
                        this.title = 'Add to wishlist';
                        showToast('Book removed from wishlist', 'success');
                        
                        // Update wishlist count in header
                        updateWishlistCount(-1);
                    } else {
                        this.classList.add('active');
                        this.title = 'Remove from wishlist';
                        showToast('Book added to wishlist', 'success');
                        
                        // Update wishlist count in header
                        updateWishlistCount(1);
                    }
                } else {
                    if (data.error === 'Please log in to use wishlist') {
                        showToast('Please log in to use wishlist', 'error');
                        setTimeout(() => {
                            window.location.href = 'auth.php';
                        }, 1500);
                    } else {
                        showToast(data.error || 'An error occurred', 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
            })
            .finally(() => {
                this.classList.remove('loading');
                this.disabled = false;
            });
        });
    });
    
    // Update wishlist count in header
    function updateWishlistCount(change) {
        const wishlistCountElement = document.querySelector('.wishlist-count');
        const wishlistLink = document.querySelector('a[href="wishlist.php"] .wishlist-count');
        
        let currentCount = 0;
        if (wishlistCountElement) {
            currentCount = parseInt(wishlistCountElement.textContent);
        }
        
        const newCount = Math.max(0, currentCount + change);
        
        // Update or create count badge
        if (newCount > 0) {
            if (wishlistCountElement) {
                wishlistCountElement.textContent = newCount;
            } else if (wishlistLink) {
                const badge = document.createElement('span');
                badge.className = 'wishlist-count';
                badge.textContent = newCount;
                wishlistLink.appendChild(badge);
            }
        } else {
            // Remove count badge if count is 0
            if (wishlistCountElement) {
                wishlistCountElement.remove();
            }
        }
    }
    
    // Toast notification function
    function showToast(message, type = 'info') {
        // Remove existing toasts
        document.querySelectorAll('.toast').forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <span>${message}</span>
                <button class="toast-close">&times;</button>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Show toast
        setTimeout(() => toast.classList.add('show'), 10);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
        
        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>