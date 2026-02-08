<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_auth();

$page_title = 'My Books';

// Handle book actions via PRG
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Invalid form submission.');
        header('Location: my-books.php');
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $book_id = $_POST['book_id'] ?? null;
    
    switch ($action) {
        case 'delete':
            if ($book_id) {
                // Verify user owns the book
                $book = BookModel::getBookById($book_id);
                if ($book && $book['user_id'] == $_SESSION['user_id']) {
                    // Delete image if exists
                    if ($book['image_url']) {
                        delete_image($book['image_url']);
                    }
                    
                    BookModel::deleteBook($book_id);
                    set_flash_message('success', 'Book deleted successfully.');
                } else {
                    set_flash_message('error', 'You cannot delete this book.');
                }
            }
            break;
            
        case 'update_status':
            $status = $_POST['status'] ?? '';
            if ($book_id && in_array($status, ['available', 'pending', 'exchanged'])) {
                // Verify user owns the book
                $book = BookModel::getBookById($book_id);
                if ($book && $book['user_id'] == $_SESSION['user_id']) {
                    BookModel::updateBookStatus($book_id, $status);
                    set_flash_message('success', 'Book status updated.');
                }
            }
            break;
    }
    
    // PRG: Redirect to GET
    header('Location: my-books.php');
    exit;
}

// Get user's books
$current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 9;
$offset = ($current_page - 1) * $limit;

$user_books = UserModel::getUserBooks($_SESSION['user_id'], $limit, $offset);
$total_books = UserModel::countUserBooks($_SESSION['user_id']);
$total_pages = ceil($total_books / $limit);

// Get book statistics
$available_books = 0;
$pending_books = 0;
$exchanged_books = 0;

foreach ($user_books as $book) {
    switch ($book['status']) {
        case 'available': $available_books++; break;
        case 'pending': $pending_books++; break;
        case 'exchanged': $exchanged_books++; break;
    }
}

require_once 'includes/header.php';
?>

<div class="my-books-page">
    <div class="page-header">
        <h1><i class="fas fa-book"></i> My Books</h1>
        <p>Manage books you've listed for exchange</p>
        
        <div class="page-actions">
            <a href="add-book.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Book
            </a>
            <a href="profile.php" class="btn btn-outline">
                <i class="fas fa-user"></i> My Profile
            </a>
        </div>
    </div>
    
    <!-- Flash Messages -->
    <?php $flash = get_flash_message(); ?>
    <?php if ($flash): ?>
    <div class="alert alert-<?php echo $flash['type']; ?>">
        <?php echo htmlspecialchars($flash['message']); ?>
    </div>
    <?php endif; ?>
    
    <!-- Statistics -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $total_books; ?></h3>
                <p>Total Books</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #27ae60;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $available_books; ?></h3>
                <p>Available</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #f39c12;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $pending_books; ?></h3>
                <p>Pending</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background-color: #3498db;">
                <i class="fas fa-exchange-alt"></i>
            </div>
            <div class="stat-content">
                <h3><?php echo $exchanged_books; ?></h3>
                <p>Exchanged</p>
            </div>
        </div>
    </div>
    
    <!-- Books Grid -->
    <div class="books-section">
        <?php if ($total_books > 0): ?>
            <div class="books-grid">
                <?php foreach ($user_books as $book): ?>
                <div class="book-card">
                    <div class="book-header">
                        <div class="book-status">
                            <span class="status-badge status-<?php echo $book['status']; ?>">
                                <?php echo ucfirst($book['status']); ?>
                            </span>
                        </div>
                        <div class="book-actions-dropdown">
                            <button class="dropdown-toggle">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu">
                                <a href="books.php?id=<?php echo $book['id']; ?>" class="dropdown-item">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit-book.php?id=<?php echo $book['id']; ?>" class="dropdown-item">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                
                                <!-- Status Update Form -->
                                <form method="POST" action="my-books.php" class="dropdown-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <input type="hidden" name="status" value="available">
                                    <button type="submit" class="dropdown-item" 
                                            <?php echo $book['status'] == 'available' ? 'disabled' : ''; ?>>
                                        <i class="fas fa-check"></i> Mark Available
                                    </button>
                                </form>
                                
                                <form method="POST" action="my-books.php" class="dropdown-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <input type="hidden" name="status" value="pending">
                                    <button type="submit" class="dropdown-item"
                                            <?php echo $book['status'] == 'pending' ? 'disabled' : ''; ?>>
                                        <i class="fas fa-clock"></i> Mark Pending
                                    </button>
                                </form>
                                
                                <form method="POST" action="my-books.php" class="dropdown-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <input type="hidden" name="status" value="exchanged">
                                    <button type="submit" class="dropdown-item"
                                            <?php echo $book['status'] == 'exchanged' ? 'disabled' : ''; ?>>
                                        <i class="fas fa-exchange-alt"></i> Mark Exchanged
                                    </button>
                                </form>
                                
                                <div class="dropdown-divider"></div>
                                
                                <form method="POST" action="my-books.php" class="dropdown-form"
                                      onsubmit="return confirm('Delete this book? This action cannot be undone.')">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="book-cover">
                        <img src="<?php echo get_image_url($book['image_url']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?> Cover">
                        <span class="book-condition"><?php echo $book['condition']; ?></span>
                    </div>
                    
                    <div class="book-info">
                        <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                        <p class="book-author">By <?php echo htmlspecialchars($book['author']); ?></p>
                        <div class="book-meta">
                            <span class="book-genre"><?php echo $book['genre']; ?></span>
                            <?php if ($book['exchange_type'] == 'giveaway'): ?>
                            <span class="book-exchange-type giveaway">Giveaway</span>
                            <?php endif; ?>
                        </div>
                        <p class="book-description"><?php echo htmlspecialchars(substr($book['description'], 0, 80)); ?>...</p>
                        
                        <div class="book-footer">
                            <div class="book-date">
                                <small>Listed: <?php echo format_date($book['created_at']); ?></small>
                            </div>
                            <div class="book-actions">
                                <a href="books.php?id=<?php echo $book['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="edit-book.php?id=<?php echo $book['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
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
                $query_params = [];
                echo get_pagination_links($current_page, $total_pages, 'my-books.php', $query_params);
                ?>
            </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3>You haven't listed any books yet</h3>
                <p>Start sharing your books with the community for exchange.</p>
                <div class="empty-actions">
                    <a href="add-book.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> List Your First Book
                    </a>
                    <a href="books.php" class="btn btn-outline">
                        <i class="fas fa-search"></i> Browse Books
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Dropdown menu functionality
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.dropdown-toggle').forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            menu.classList.toggle('show');
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            menu.classList.remove('show');
        });
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>