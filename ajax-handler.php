<?php
/**
 * AJAX request handler for live search and dynamic content
 */
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Set JSON header
header('Content-Type: application/json');

// Enable CORS if needed
header('Access-Control-Allow-Origin: ' . SITE_URL);
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Validate request
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['error' => 'Direct access not allowed']);
    exit;
}

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'live_search':
            handleLiveSearch();
            break;
            
        case 'load_more':
            handleLoadMore();
            break;
            
        case 'get_book_covers':
            handleGetBookCovers();
            break;
            
        case 'get_popular_searches':
            handleGetPopularSearches();
            break;
            
        case 'get_recently_viewed':
            handleGetRecentlyViewed();
            break;
            
        case 'toggle_wishlist':
            handleToggleWishlist();
            break;
            
        case 'quick_view':
            handleQuickView();
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit;
    }
} catch (Exception $e) {
    // Log error
    error_log('AJAX Error: ' . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode([
        'error' => 'An internal error occurred',
        'debug' => DEBUG_MODE ? $e->getMessage() : null
    ]);
}

/**
 * Handle live search AJAX request
 */
function handleLiveSearch() {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    // Get search parameters
    $search = trim($_GET['q'] ?? '');
    $filters = [
        'genre' => $_GET['genre'] ?? '',
        'condition' => $_GET['condition'] ?? '',
        'exchange_type' => $_GET['exchange_type'] ?? '',
        'sort' => $_GET['sort'] ?? 'relevance'
    ];
    
    // Validate search query
    if (empty($search) || strlen($search) < 2) {
        echo json_encode([
            'results' => [],
            'total' => 0,
            'suggestions' => BookModel::getPopularSearches(5)
        ]);
        return;
    }
    
    // Sanitize search query
    $search = sanitize_input($search);
    
    // Get results (limit to 6 for live search)
    $results = BookModel::searchBooksAjax($search, $filters, 6, 0);
    $total = BookModel::countBooks(array_merge(['search' => $search], $filters));
    
    // Format results for JSON
    $formatted_results = array_map(function($book) {
        return [
            'id' => $book['id'],
            'title' => htmlspecialchars($book['title']),
            'author' => htmlspecialchars($book['author']),
            'genre' => $book['genre'],
            'condition' => $book['condition'],
            'description' => truncate_text($book['description'], 80),
            'cover_url' => BookModel::getBookCover($book['isbn'], $book['title'], $book['author']),
            'owner_name' => htmlspecialchars($book['owner_name'] ?? 'Unknown'),
            'exchange_type' => $book['exchange_type'],
            'url' => 'books.php?id=' . $book['id']
        ];
    }, $results);
    
    // Get search suggestions
    $suggestions = [];
    if (strlen($search) >= 3) {
        // Get similar searches from log
        $sql = "SELECT DISTINCT search_query 
                FROM search_logs 
                WHERE search_query LIKE ? 
                AND created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
                LIMIT 5";
        $similar = Database::fetchAll($sql, [$search . '%']);
        $suggestions = array_column($similar, 'search_query');
    }
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'results' => $formatted_results,
        'total' => $total,
        'query' => $search,
        'suggestions' => $suggestions,
        'filters' => $filters
    ]);
}

/**
 * Handle load more AJAX request
 */
function handleLoadMore() {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    // Get parameters
    $page = max(1, (int)($_GET['page'] ?? 2)); // Start from page 2
    $limit = (int)($_GET['limit'] ?? BOOKS_PER_PAGE);
    $offset = ($page - 1) * $limit;
    
    // Get filters
    $filters = [
        'search' => $_GET['search'] ?? '',
        'genre' => $_GET['genre'] ?? '',
        'condition' => $_GET['condition'] ?? '',
        'exchange_type' => $_GET['exchange_type'] ?? '',
        'sort' => $_GET['sort'] ?? 'newest'
    ];
    
    // Get books
    $books = BookModel::getAllBooks($filters, $limit, $offset);
    $total = BookModel::countBooks($filters);
    $has_more = ($page * $limit) < $total;
    
    // Format books for display
    ob_start();
    foreach ($books as $book) {
        displayBookCard($book);
    }
    $html = ob_get_clean();
    
    // Return JSON response
    echo json_encode([
        'success' => true,
        'html' => $html,
        'has_more' => $has_more,
        'next_page' => $has_more ? $page + 1 : null,
        'total_loaded' => $page * $limit,
        'total_books' => $total
    ]);
}

/**
 * Handle book covers batch request
 */
function handleGetBookCovers() {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    // Get ISBNs from request
    $data = json_decode(file_get_contents('php://input'), true);
    $isbns = $data['isbns'] ?? [];
    
    if (empty($isbns)) {
        echo json_encode(['error' => 'No ISBNs provided']);
        return;
    }
    
    // Get covers for each ISBN
    $covers = [];
    foreach ($isbns as $isbn_data) {
        $isbn = $isbn_data['isbn'] ?? '';
        $title = $isbn_data['title'] ?? '';
        $author = $isbn_data['author'] ?? '';
        
        if ($isbn) {
            $covers[$isbn] = BookModel::getBookCover($isbn, $title, $author);
        }
    }
    
    echo json_encode([
        'success' => true,
        'covers' => $covers
    ]);
}

/**
 * Handle popular searches request
 */
function handleGetPopularSearches() {
    $searches = BookModel::getPopularSearches(10);
    
    echo json_encode([
        'success' => true,
        'searches' => $searches
    ]);
}

/**
 * Handle recently viewed request
 */
function handleGetRecentlyViewed() {
    $books = BookModel::getRecentlyViewed(6);
    
    $formatted_books = array_map(function($book) {
        return [
            'id' => $book['id'],
            'title' => htmlspecialchars($book['title']),
            'author' => htmlspecialchars($book['author']),
            'cover_url' => BookModel::getBookCover($book['isbn'], $book['title'], $book['author']),
            'url' => 'books.php?id=' . $book['id']
        ];
    }, $books);
    
    echo json_encode([
        'success' => true,
        'books' => $formatted_books
    ]);
}

/**
 * Handle wishlist toggle (AJAX version)
 */
function handleToggleWishlist() {
    // Validate request method and authentication
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['error' => 'Please log in to use wishlist']);
        return;
    }
    
    // Validate CSRF token
    $data = json_decode(file_get_contents('php://input'), true);
    if (!validate_csrf_token($data['csrf_token'] ?? '')) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        return;
    }
    
    $book_id = (int)($data['book_id'] ?? 0);
    
    if ($book_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid book ID']);
        return;
    }
    
    // Toggle wishlist
    $result = toggle_wishlist($book_id);
    
    echo json_encode($result);
}

/**
 * Handle quick view modal
 */
function handleQuickView() {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $book_id = (int)($_GET['id'] ?? 0);
    
    if ($book_id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid book ID']);
        return;
    }
    
    // Get book details
    $book = BookModel::getBookById($book_id);
    
    if (!$book) {
        http_response_code(404);
        echo json_encode(['error' => 'Book not found']);
        return;
    }
    
    // Increment view count
    BookModel::incrementViewCount($book_id);
    
    // Check if in wishlist
    $in_wishlist = is_logged_in() ? Wishlist::has(Auth::userId(), $book_id) : false;
    
    // Format response
    echo json_encode([
        'success' => true,
        'book' => [
            'id' => $book['id'],
            'title' => htmlspecialchars($book['title']),
            'author' => htmlspecialchars($book['author']),
            'isbn' => $book['isbn'],
            'genre' => $book['genre'],
            'condition' => $book['condition'],
            'description' => nl2br(htmlspecialchars($book['description'])),
            'year_published' => $book['year_published'],
            'exchange_type' => $book['exchange_type'],
            'owner_name' => htmlspecialchars($book['owner_name']),
            'owner_location' => htmlspecialchars($book['owner_location'] ?? ''),
            'owner_bio' => htmlspecialchars($book['owner_bio'] ?? ''),
            'created_at' => format_date($book['created_at']),
            'cover_url' => BookModel::getBookCover($book['isbn'], $book['title'], $book['author']),
            'in_wishlist' => $in_wishlist,
            'is_owner' => is_logged_in() && $_SESSION['user_id'] == $book['user_id']
        ]
    ]);
}

/**
 * Helper function to display book card HTML
 */
function displayBookCard($book) {
    $is_in_wishlist = is_in_wishlist($book['id']);
    $is_owner = is_logged_in() && $_SESSION['user_id'] == $book['user_id'];
    ?>
    <div class="book-card" data-book-id="<?php echo $book['id']; ?>">
        <div class="book-cover">
            <img src="<?php echo BookModel::getBookCover($book['isbn'], $book['title'], $book['author']); ?>" 
                 alt="<?php echo htmlspecialchars($book['title']); ?> Cover"
                 loading="lazy">
            <span class="book-condition"><?php echo $book['condition']; ?></span>
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
            <p class="book-description"><?php echo truncate_text($book['description'], 100); ?></p>
            <div class="book-footer">
                <div class="book-owner">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($book['owner_name']); ?></span>
                </div>
                <div class="book-actions">
                    <button class="btn-quick-view" data-book-id="<?php echo $book['id']; ?>">
                        <i class="fas fa-eye"></i> Quick View
                    </button>
                    <?php if (is_logged_in() && !$is_owner): ?>
                    <button class="btn-request" data-book-id="<?php echo $book['id']; ?>">
                        <i class="fas fa-exchange-alt"></i> Request
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Helper function to truncate text
 */
function truncate_text($text, $length = 100) {
    if (strlen($text) <= $length) {
        return htmlspecialchars($text);
    }
    
    $truncated = substr($text, 0, $length);
    $truncated = substr($truncated, 0, strrpos($truncated, ' '));
    return htmlspecialchars($truncated . '...');
}
?>