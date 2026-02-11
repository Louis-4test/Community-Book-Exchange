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

// Get books from database with pagination using direct queries
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = BOOKS_PER_PAGE;
$offset = ($current_page - 1) * $limit;

// ===== DIRECT DATABASE QUERIES =====
// Build the base query
$base_query = "SELECT 
    b.id,
    b.user_id,
    b.title,
    b.author,
    b.isbn,
    b.genre,
    b.state,
    b.description,
    b.image_url,
    b.exchange_type,
    b.status,
    b.created_at,
    b.updated_at,
    u.name as owner_name,
    u.id as owner_id,
    COALESCE(u.profile_image, 'default-avatar.png') as owner_avatar,
    COALESCE(b.image_url, 'default-book.jpg') as image_url
FROM books b
LEFT JOIN users u ON b.user_id = u.id
WHERE b.status = 'available'";

$params = [];
$count_params = [];

// Add search filter
if (!empty($filters['search'])) {
    $base_query .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.genre LIKE ? OR b.description LIKE ?)";
    $search_term = "%{$filters['search']}%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $count_params = $params; // Copy for count query
}

// Add genre filter
if (!empty($filters['genre'])) {
    $base_query .= " AND b.genre = ?";
    $params[] = $filters['genre'];
    $count_params[] = $filters['genre'];
}

// Add state filter
if (!empty($filters['state'])) {
    $base_query .= " AND b.state = ?";
    $params[] = $filters['state'];
    $count_params[] = $filters['state'];
}

// Add exchange type filter
if (!empty($filters['exchange_type'])) {
    $base_query .= " AND b.exchange_type = ?";
    $params[] = $filters['exchange_type'];
    $count_params[] = $filters['exchange_type'];
}

// Add sorting
switch ($filters['sort']) {
    case 'oldest':
        $base_query .= " ORDER BY b.created_at ASC";
        break;
    case 'title':
        $base_query .= " ORDER BY b.title ASC";
        break;
    case 'state':
        $base_query .= " ORDER BY 
            CASE b.state 
                WHEN 'Excellent' THEN 1
                WHEN 'Like New' THEN 2
                WHEN 'Good' THEN 3
                WHEN 'Fair' THEN 4
                WHEN 'Poor' THEN 5
                ELSE 6
            END ASC";
        break;
    default: // newest
        $base_query .= " ORDER BY b.created_at DESC";
}

// Get total count for pagination
$count_query = "SELECT COUNT(*) as total FROM books b WHERE b.status = 'available'";
$count_params = [];

// Add filters to count query
if (!empty($filters['search'])) {
    $count_query .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.genre LIKE ? OR b.description LIKE ?)";
    $search_term = "%{$filters['search']}%";
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
    $count_params[] = $search_term;
}

if (!empty($filters['genre'])) {
    $count_query .= " AND b.genre = ?";
    $count_params[] = $filters['genre'];
}

if (!empty($filters['state'])) {
    $count_query .= " AND b.state = ?";
    $count_params[] = $filters['state'];
}

if (!empty($filters['exchange_type'])) {
    $count_query .= " AND b.exchange_type = ?";
    $count_params[] = $filters['exchange_type'];
}

// Execute count query
try {
    $count_stmt = $db->prepare($count_query);
    $count_stmt->execute($count_params);
    $total_books = $count_stmt->fetch()['total'] ?? 0;
} catch (PDOException $e) {
    error_log("Count query error: " . $e->getMessage());
    $total_books = 0;
}

// Add pagination to main query
$query = $base_query . " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

// Execute main query
try {
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Book query error: " . $e->getMessage());
    $books = [];
}

$total_pages = ceil($total_books / $limit);

// Get available genres for filter - DIRECT QUERY
$genres = [];
try {
    $genre_stmt = $db->query("SELECT DISTINCT genre FROM books WHERE status = 'available' AND genre IS NOT NULL AND genre != '' ORDER BY genre");
    $genres = $genre_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Genre query error: " . $e->getMessage());
    $genres = [];
}

// Debug: Check if we're getting results
if (empty($books) && !empty($filters['search'])) {
    // Log for debugging
    error_log("Search term: " . $filters['search'] . " returned 0 results");
}

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
                        <label for="stateFilter">Condition</label>
                        <select name="state" id="stateFilter">
                            <option value="">All Conditions</option>
                            <?php 
                            $state_options = ['Excellent', 'Like New', 'Good', 'Fair', 'Poor'];
                            foreach ($state_options as $state): 
                            ?>
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
                            <option value="state" <?php echo $filters['sort'] == 'state' ? 'selected' : ''; ?>>Best Condition</option>
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
                    $is_in_wishlist = is_logged_in() ? is_in_wishlist($book['id']) : false;
                    $is_owner = is_logged_in() && $_SESSION['user_id'] == $book['user_id'];
                ?>
                <div class="book-card" data-book-id="<?php echo $book['id']; ?>">
                    <div class="book-cover">
                        <img src="<?php echo get_image_url($book['image_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?> Cover">
                        <span class="book-state"><?php echo $book['state']; ?></span>
                        <?php if ($book['exchange_type'] == 'giveaway'): ?>
                        <span class="book-exchange-type giveaway">Free</span>
                        <?php else: ?>
                        <span class="book-exchange-type trade">Trade</span>
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
                            <span class="book-genre"><?php echo htmlspecialchars($book['genre']); ?></span>
                            <?php if (!empty($book['isbn'])): ?>
                            <span class="book-isbn">ISBN: <?php echo htmlspecialchars(substr($book['isbn'], 0, 13)); ?></span>
                            <?php endif; ?>
                        </div>
                        <p class="book-description"><?php echo htmlspecialchars(substr($book['description'] ?? 'No description available', 0, 100)); ?>...</p>
                        <div class="book-footer">
                            <div class="book-owner">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($book['owner_name'] ?? 'Unknown User'); ?></span>
                            </div>
                            <div class="book-actions">
                                <button class="btn-details" onclick="window.location.href='book-details.php?id=<?php echo $book['id']; ?>'">View Details</button>
                                <?php if (is_logged_in() && !$is_owner): ?>
                                <button class="btn-request" onclick="window.location.href='request-exchange.php?book_id=<?php echo $book['id']; ?>'">Request</button>
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
                // Build query parameters for pagination links
                $query_params = [];
                if (!empty($filters['search'])) $query_params['search'] = $filters['search'];
                if (!empty($filters['genre'])) $query_params['genre'] = $filters['genre'];
                if (!empty($filters['state'])) $query_params['state'] = $filters['state'];
                if (!empty($filters['exchange_type'])) $query_params['exchange_type'] = $filters['exchange_type'];
                if (!empty($filters['sort']) && $filters['sort'] != 'newest') $query_params['sort'] = $filters['sort'];
                
                // Previous page
                if ($current_page > 1) {
                    $query_params['page'] = $current_page - 1;
                    echo '<a href="books.php?' . http_build_query($query_params) . '" class="page-link"><i class="fas fa-chevron-left"></i></a>';
                }
                
                // Page numbers
                for ($i = 1; $i <= $total_pages; $i++) {
                    if ($i == $current_page) {
                        echo '<span class="page-link active">' . $i . '</span>';
                    } else {
                        $query_params['page'] = $i;
                        echo '<a href="books.php?' . http_build_query($query_params) . '" class="page-link">' . $i . '</a>';
                    }
                }
                
                // Next page
                if ($current_page < $total_pages) {
                    $query_params['page'] = $current_page + 1;
                    echo '<a href="books.php?' . http_build_query($query_params) . '" class="page-link"><i class="fas fa-chevron-right"></i></a>';
                }
                ?>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="no-results active">
                <i class="fas fa-book-open"></i>
                <h3>No books found</h3>
                <p>
                    <?php if (!empty($filters['search']) || !empty($filters['genre']) || !empty($filters['state']) || !empty($filters['exchange_type'])): ?>
                        No books match your search criteria. Try adjusting your filters.
                    <?php else: ?>
                        No books are currently available for exchange.
                    <?php endif; ?>
                </p>
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
                    csrf_token: '<?php echo $_SESSION['csrf_token'] ?? ''; ?>'
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
                    } else {
                        this.classList.add('active');
                        this.title = 'Remove from wishlist';
                        showToast('Book added to wishlist', 'success');
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
    
    // Toast notification function
    function showToast(message, type = 'info') {
        // Remove existing toasts
        document.querySelectorAll('.toast-notification').forEach(toast => toast.remove());
        
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
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