<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_auth();

$page_title = 'My Wishlist';

// Handle wishlist actions via PRG
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        set_flash_message('error', 'Invalid form submission.');
        header('Location: wishlist.php');
        exit;
    }
    
    $action = $_POST['action'] ?? '';
    $book_id = $_POST['book_id'] ?? null;
    
    if ($action === 'remove' && $book_id) {
        $result = remove_from_wishlist($book_id);
        if ($result['success']) {
            set_flash_message('success', 'Book removed from wishlist.');
        } else {
            set_flash_message('error', $result['error'] ?? 'Failed to remove from wishlist.');
        }
    } elseif ($action === 'clear_all') {
        // Clear all wishlist items
        $wishlist = get_user_wishlist();
        foreach ($wishlist as $item) {
            remove_from_wishlist($item['id']);
        }
        set_flash_message('success', 'Wishlist cleared.');
    }
    
    // PRG: Redirect to GET
    header('Location: wishlist.php');
    exit;
}

// Get user's wishlist
$wishlist = get_user_wishlist();
$wishlist_count = count($wishlist);

require_once 'includes/header.php';
?>

<div class="wishlist-page">
    <div class="page-header">
        <h1><i class="fas fa-heart"></i> My Wishlist</h1>
        <p>Books you've saved for later</p>
        
        <div class="page-actions">
            <?php if ($wishlist_count > 0): ?>
            <form method="POST" action="wishlist.php" class="inline-form" 
                  onsubmit="return confirm('Clear all items from your wishlist?')">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="action" value="clear_all">
                <button type="submit" class="btn btn-outline btn-sm">
                    <i class="fas fa-trash-alt"></i> Clear All
                </button>
            </form>
            <?php endif; ?>
            <a href="books.php" class="btn btn-primary">
                <i class="fas fa-search"></i> Browse More Books
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
    
    <!-- Wishlist Content -->
    <div class="wishlist-content">
        <?php if ($wishlist_count > 0): ?>
            <div class="wishlist-stats">
                <p>You have <strong><?php echo $wishlist_count; ?></strong> book<?php echo $wishlist_count !== 1 ? 's' : ''; ?> in your wishlist</p>
            </div>
            
            <div class="books-grid">
                <?php foreach ($wishlist as $book): ?>
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
                                <a href="books.php?id=<?php echo $book['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <form method="POST" action="wishlist.php" class="inline-form">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-heart-broken"></i> Remove
                                    </button>
                                </form>
                                <?php if ($_SESSION['user_id'] != $book['user_id']): ?>
                                <button class="btn btn-primary btn-sm btn-request" data-book="<?php echo $book['id']; ?>">
                                    <i class="fas fa-exchange-alt"></i> Request
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3>Your wishlist is empty</h3>
                <p>Save books you're interested in by clicking the heart icon on any book.</p>
                <div class="empty-actions">
                    <a href="books.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Books
                    </a>
                    <a href="my-books.php" class="btn btn-outline">
                        <i class="fas fa-book"></i> View My Books
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>