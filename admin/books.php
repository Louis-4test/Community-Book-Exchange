<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_admin();

$page_title = 'Manage Books';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle different actions
switch ($action) {
    case 'add':
    case 'edit':
        handle_book_form($action, $id);
        break;
    case 'delete':
        handle_book_delete($id);
        break;
    default:
        list_books();
        break;
}

function handle_book_form($action, $id = null) {
    global $page_title;
    
    $book = null;
    $errors = [];
    $form_data = [];
    
    if ($action === 'edit' && $id) {
        $book = BookModel::getBookById($id);
        if (!$book) {
            redirect_with_message('books.php', 'error', 'Book not found.');
        }
        $page_title = 'Edit Book';
    } else {
        $page_title = 'Add New Book';
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token
        if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF token');
        }
        
        // Collect form data
        $form_data = [
            'title' => sanitize_input($_POST['title'] ?? ''),
            'author' => sanitize_input($_POST['author'] ?? ''),
            'isbn' => sanitize_input($_POST['isbn'] ?? ''),
            'genre' => sanitize_input($_POST['genre'] ?? ''),
            'state' => sanitize_input($_POST['state'] ?? ''),
            'description' => sanitize_input($_POST['description'] ?? ''),
            'year_published' => sanitize_input($_POST['year_published'] ?? ''),
            'exchange_type' => sanitize_input($_POST['exchange_type'] ?? 'trade'),
            'status' => sanitize_input($_POST['status'] ?? 'available')
        ];
        
        // Validate form data
        $errors = validate_book_data($form_data);
        
        // Handle image upload
        $image_url = $book['image_url'] ?? null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            try {
                $filename = upload_image($_FILES['image'], 'book');
                $image_url = $filename;
                
                // Delete old image if exists
                if ($book && $book['image_url']) {
                    delete_image($book['image_url']);
                }
            } catch (Exception $e) {
                $errors['image'] = $e->getMessage();
            }
        }
        
        $form_data['image_url'] = $image_url;
        
        if (empty($errors)) {
            if ($action === 'edit' && $id) {
                // Update existing book
                BookModel::updateBook($id, $form_data);
                $message = 'Book updated successfully!';
            } else {
                // Add new book (as admin user)
                $form_data['user_id'] = $_SESSION['user_id'];
                BookModel::createBook($form_data);
                $message = 'Book added successfully!';
            }
            
            redirect_with_message('books.php', 'success', $message);
        }
    } elseif ($book) {
        // Pre-fill form with existing data
        $form_data = $book;
    }
    
    // Display form
    display_book_form($action, $form_data, $errors, $id);
}

function handle_book_delete($id) {
    if (!$id) {
        redirect_with_message('books.php', 'error', 'Invalid book ID.');
    }
    
    // Validate CSRF token
    if (!validate_csrf_token($_GET['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    
    $book = BookModel::getBookById($id);
    if (!$book) {
        redirect_with_message('books.php', 'error', 'Book not found.');
    }
    
    // Delete image if exists
    if ($book['image_url']) {
        delete_image($book['image_url']);
    }
    
    // Delete book
    BookModel::deleteBook($id);
    
    redirect_with_message('books.php', 'success', 'Book deleted successfully!');
}

function list_books() {
    global $page_title;
    
    // Get filters
    $filters = [
        'search' => $_GET['search'] ?? '',
        'genre' => $_GET['genre'] ?? '',
        'state' => $_GET['state'] ?? '',
        'status' => $_GET['status'] ?? '',
        'sort' => $_GET['sort'] ?? 'newest'
    ];
    
    // Pagination
    $current_page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = ADMIN_ITEMS_PER_PAGE;
    $offset = ($current_page - 1) * $limit;
    
    // Get books
    $books = BookModel::getAllBooks($filters, $limit, $offset);
    $total_books = BookModel::countBooks($filters);
    $total_pages = ceil($total_books / $limit);
    
    // Get genres for filter
    $genres = get_genre_options();
    
    // Display book list
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $page_title; ?> | <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="../styles/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    </head>
    <body class="admin-dashboard">
        <?php include 'admin-header.php'; ?>
        
        <div class="admin-container">
            <?php include 'admin-sidebar.php'; ?>
            
            <main class="admin-main">
                <div class="admin-content">
                    <!-- Flash Messages -->
                    <?php $flash = get_flash_message(); ?>
                    <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Page Header -->
                    <div class="page-header">
                        <h1><i class="fas fa-book"></i> Manage Books</h1>
                        <p>View and manage all books in the system</p>
                        <div class="page-actions">
                            <a href="books.php?action=add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Book
                            </a>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="filters-card">
                        <form method="GET" class="filters-form">
                            <div class="filter-row">
                                <div class="filter-group">
                                    <input type="text" name="search" placeholder="Search books..." 
                                           value="<?php echo htmlspecialchars($filters['search']); ?>">
                                </div>
                                
                                <div class="filter-group">
                                    <select name="genre">
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
                                    <select name="state">
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
                                    <select name="status">
                                        <option value="">All Statuses</option>
                                        <?php foreach (get_status_options() as $status): ?>
                                        <option value="<?php echo $status; ?>" 
                                                <?php echo $filters['status'] == $status ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($status); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                
                                <a href="books.php" class="btn btn-outline">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Books Table -->
                    <div class="table-card">
                        <?php if (!empty($books)): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Genre</th>
                                            <th>state</th>
                                            <th>Status</th>
                                            <th>Owner</th>
                                            <th>Date Added</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($books as $book): ?>
                                        <tr>
                                            <td><?php echo $book['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                                                <?php if ($book['image_url']): ?>
                                                <br><small><i class="fas fa-image"></i> Has image</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                                            <td>
                                                <span class="badge badge-info"><?php echo $book['genre']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $book['state'])); ?>">
                                                    <?php echo $book['state']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-<?php echo $book['status']; ?>">
                                                    <?php echo ucfirst($book['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($book['owner_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo format_date($book['created_at']); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="../books.php?id=<?php echo $book['id']; ?>" 
                                                       class="btn btn-sm btn-outline" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="books.php?action=edit&id=<?php echo $book['id']; ?>" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="books.php?action=delete&id=<?php echo $book['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Are you sure you want to delete this book?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
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
                            
                            <div class="table-footer">
                                <p>Showing <?php echo count($books); ?> of <?php echo $total_books; ?> books</p>
                            </div>
                            
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-book"></i>
                                <h3>No Books Found</h3>
                                <p>No books match your search criteria.</p>
                                <a href="books.php?action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Your First Book
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
        
        <script src="../js/admin.js"></script>
    </body>
    </html>
    <?php
}

function display_book_form($action, $form_data, $errors, $id = null) {
    global $page_title;
    
    $states = get_state_options();
    $statuses = get_status_options();
    $exchange_types = get_exchange_type_options();
    $genres = get_genre_options();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo $page_title; ?> | <?php echo SITE_NAME; ?></title>
        <link rel="stylesheet" href="../css/admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    </head>
    <body class="admin-dashboard">
        <?php include 'admin-header.php'; ?>
        
        <div class="admin-container">
            <?php include 'admin-sidebar.php'; ?>
            
            <main class="admin-main">
                <div class="admin-content">
                    <!-- Flash Messages -->
                    <?php $flash = get_flash_message(); ?>
                    <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Page Header -->
                    <div class="page-header">
                        <h1>
                            <i class="fas fa-book"></i> 
                            <?php echo $action === 'edit' ? 'Edit Book' : 'Add New Book'; ?>
                        </h1>
                        <p><?php echo $action === 'edit' ? 'Update book information' : 'Add a new book to the exchange'; ?></p>
                        <div class="page-actions">
                            <a href="books.php" class="btn btn-outline">
                                <i class="fas fa-arrow-left"></i> Back to Books
                            </a>
                        </div>
                    </div>
                    
                    <!-- Book Form -->
                    <div class="form-card">
                        <form method="POST" action="" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            
                            <div class="form-grid">
                                <!-- Basic Information -->
                                <div class="form-section">
                                    <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                                    
                                    <div class="form-group">
                                        <label for="title">Book Title *</label>
                                        <input type="text" id="title" name="title" required
                                               value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>">
                                        <?php if (isset($errors['title'])): ?>
                                        <span class="error"><?php echo $errors['title']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="author">Author *</label>
                                        <input type="text" id="author" name="author" required
                                               value="<?php echo htmlspecialchars($form_data['author'] ?? ''); ?>">
                                        <?php if (isset($errors['author'])): ?>
                                        <span class="error"><?php echo $errors['author']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="genre">Genre *</label>
                                            <select id="genre" name="genre" required>
                                                <option value="">Select Genre</option>
                                                <?php foreach ($genres as $genre): ?>
                                                <option value="<?php echo htmlspecialchars($genre); ?>" 
                                                        <?php echo ($form_data['genre'] ?? '') == $genre ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($genre); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (isset($errors['genre'])): ?>
                                            <span class="error"><?php echo $errors['genre']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="isbn">ISBN</label>
                                            <input type="text" id="isbn" name="isbn"
                                                   value="<?php echo htmlspecialchars($form_data['isbn'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="year_published">Year Published</label>
                                            <input type="number" id="year_published" name="year_published" 
                                                   min="1000" max="<?php echo date('Y'); ?>"
                                                   value="<?php echo htmlspecialchars($form_data['year_published'] ?? ''); ?>">
                                            <?php if (isset($errors['year_published'])): ?>
                                            <span class="error"><?php echo $errors['year_published']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="exchange_type">Exchange Type</label>
                                            <select id="exchange_type" name="exchange_type">
                                                <?php foreach ($exchange_types as $type): ?>
                                                <option value="<?php echo $type; ?>" 
                                                        <?php echo ($form_data['exchange_type'] ?? 'trade') == $type ? 'selected' : ''; ?>>
                                                    <?php echo ucfirst($type); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- state & Status -->
                                <div class="form-section">
                                    <h3><i class="fas fa-tags"></i> state & Status</h3>
                                    
                                    <div class="form-group">
                                        <label for="state">state *</label>
                                        <select id="state" name="state" required>
                                            <option value="">Select state</option>
                                            <?php foreach ($states as $state): ?>
                                            <option value="<?php echo $state; ?>" 
                                                    <?php echo ($form_data['state'] ?? '') == $state ? 'selected' : ''; ?>>
                                                <?php echo $state; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <?php if (isset($errors['state'])): ?>
                                        <span class="error"><?php echo $errors['state']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="status">
                                            <?php foreach ($statuses as $status): ?>
                                            <option value="<?php echo $status; ?>" 
                                                    <?php echo ($form_data['status'] ?? 'available') == $status ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($status); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <!-- Image Upload -->
                                    <div class="form-group">
                                        <label for="image">Book Cover Image</label>
                                        <input type="file" id="image" name="image" accept="image/*">
                                        <?php if (isset($errors['image'])): ?>
                                        <span class="error"><?php echo $errors['image']; ?></span>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($form_data['image_url'])): ?>
                                        <div class="image-preview">
                                            <p>Current Image:</p>
                                            <img src="<?php echo get_image_url($form_data['image_url']); ?>" 
                                                 alt="Book Cover" style="max-width: 200px;">
                                        </div>
                                        <?php endif; ?>
                                        
                                        <small class="form-help">Allowed: JPG, PNG, GIF, WebP (Max 5MB)</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-section">
                                <h3><i class="fas fa-align-left"></i> Description</h3>
                                <div class="form-group">
                                    <label for="description">Description *</label>
                                    <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                                    <?php if (isset($errors['description'])): ?>
                                    <span class="error"><?php echo $errors['description']; ?></span>
                                    <?php endif; ?>
                                    <small class="form-help">Describe the book, its state, and why someone might want to exchange for it.</small>
                                </div>
                            </div>
                            
                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> 
                                    <?php echo $action === 'edit' ? 'Update Book' : 'Add Book'; ?>
                                </button>
                                <a href="books.php" class="btn btn-outline">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
        
        <script src="../js/admin.js"></script>
    </body>
    </html>
    <?php
}
?>