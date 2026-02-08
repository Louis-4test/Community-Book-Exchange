<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_login();

$page_title = 'List Your Book';

// Handle form submission
$errors = [];
$form_data = [];

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
        'condition' => sanitize_input($_POST['condition'] ?? ''),
        'description' => sanitize_input($_POST['description'] ?? ''),
        'year_published' => sanitize_input($_POST['year_published'] ?? ''),
        'exchange_type' => sanitize_input($_POST['exchange_type'] ?? 'trade')
    ];
    
    // Validate form data
    $errors = validate_book_data($form_data);
    
    // Handle image upload
    $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        try {
            $image_url = upload_image($_FILES['image'], 'book');
        } catch (Exception $e) {
            $errors['image'] = $e->getMessage();
        }
    }
    
    $form_data['image_url'] = $image_url;
    
    if (empty($errors)) {
        // Add book to database
        $form_data['user_id'] = $_SESSION['user_id'];
        BookModel::createBook($form_data);
        
        set_flash_message('success', 'Your book has been listed successfully!');
        header('Location: books.php');
        exit;
    }
}

// Get genres for dropdown
$genres = get_genre_options();

require_once 'includes/header.php';
?>

<div class="add-book-page">
    <div class="page-header">
        <h1><i class="fas fa-plus-circle"></i> List Your Book</h1>
        <p>Share a book with the community for exchange</p>
    </div>
    
    <div class="add-book-container">
        <div class="add-book-card">
            <form method="POST" action="" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                
                <div class="form-section">
                    <h3><i class="fas fa-info-circle"></i> Book Information</h3>
                    
                    <div class="form-group">
                        <label for="title">Book Title *</label>
                        <input type="text" id="title" name="title" required
                               value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>"
                               placeholder="Enter the book title">
                        <?php if (isset($errors['title'])): ?>
                        <span class="error"><?php echo $errors['title']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="author">Author *</label>
                        <input type="text" id="author" name="author" required
                               value="<?php echo htmlspecialchars($form_data['author'] ?? ''); ?>"
                               placeholder="Enter the author's name">
                        <?php if (isset($errors['author'])): ?>
                        <span class="error"><?php echo $errors['author']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="genre">Genre *</label>
                            <select id="genre" name="genre" required>
                                <option value="">Select a genre</option>
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
                            <label for="condition">Condition *</label>
                            <select id="condition" name="condition" required>
                                <option value="">Select condition</option>
                                <?php foreach (get_condition_options() as $condition): ?>
                                <option value="<?php echo $condition; ?>"
                                        <?php echo ($form_data['condition'] ?? '') == $condition ? 'selected' : ''; ?>>
                                    <?php echo $condition; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['condition'])): ?>
                            <span class="error"><?php echo $errors['condition']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="isbn">ISBN (Optional)</label>
                            <input type="text" id="isbn" name="isbn"
                                   value="<?php echo htmlspecialchars($form_data['isbn'] ?? ''); ?>"
                                   placeholder="Enter ISBN if known">
                        </div>
                        
                        <div class="form-group">
                            <label for="year_published">Year Published</label>
                            <input type="number" id="year_published" name="year_published" 
                                   min="1000" max="<?php echo date('Y'); ?>"
                                   value="<?php echo htmlspecialchars($form_data['year_published'] ?? ''); ?>"
                                   placeholder="e.g., 2020">
                            <?php if (isset($errors['year_published'])): ?>
                            <span class="error"><?php echo $errors['year_published']; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="exchange_type">Exchange Type</label>
                        <select id="exchange_type" name="exchange_type">
                            <option value="trade" <?php echo ($form_data['exchange_type'] ?? 'trade') == 'trade' ? 'selected' : ''; ?>>Trade for another book</option>
                            <option value="giveaway" <?php echo ($form_data['exchange_type'] ?? '') == 'giveaway' ? 'selected' : ''; ?>>Giveaway (free)</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fas fa-image"></i> Book Cover</h3>
                    <div class="form-group">
                        <label for="image">Upload Cover Image (Optional)</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <?php if (isset($errors['image'])): ?>
                        <span class="error"><?php echo $errors['image']; ?></span>
                        <?php endif; ?>
                        <small class="form-help">JPG, PNG, GIF, or WebP (Max 5MB). A good cover image increases exchange requests!</small>
                    </div>
                    
                    <div class="image-preview" id="imagePreview" style="display: none;">
                        <p>Preview:</p>
                        <img id="previewImage" src="" alt="Preview" style="max-width: 200px;">
                    </div>
                </div>
                
                <div class="form-section">
                    <h3><i class="fas fa-align-left"></i> Description</h3>
                    <div class="form-group">
                        <label for="description">Book Description *</label>
                        <textarea id="description" name="description" rows="6" required
                                  placeholder="Describe the book's condition, story, and why someone might want to exchange for it..."><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                        <span class="error"><?php echo $errors['description']; ?></span>
                        <?php endif; ?>
                        <small class="form-help">Be descriptive! Mention any wear and tear, special editions, or personal notes about the book.</small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-check"></i> List Book for Exchange
                    </button>
                    <a href="books.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
        
        <div class="add-book-help">
            <h3><i class="fas fa-lightbulb"></i> Listing Tips</h3>
            <ul>
                <li><strong>Be honest about condition</strong> - Mention any marks, tears, or wear</li>
                <li><strong>Take clear photos</strong> - Good images get more attention</li>
                <li><strong>Write detailed descriptions</strong> - Include what you liked about the book</li>
                <li><strong>Choose the right exchange type</strong> - Trade or giveaway</li>
                <li><strong>Set realistic expectations</strong> - Be clear about what you're looking for in exchange</li>
            </ul>
            
            <div class="help-note">
                <i class="fas fa-info-circle"></i>
                <p>Your book will be visible to all community members. You can edit or remove your listing at any time.</p>
            </div>
        </div>
    </div>
</div>

<script>
// Image preview functionality
document.getElementById('image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImage = document.getElementById('previewImage');
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            preview.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    } else {
        preview.style.display = 'none';
    }
});
</script>

<?php
require_once 'includes/footer.php';
?>