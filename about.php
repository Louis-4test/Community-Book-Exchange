<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'About Us';

// Handle contact form submission
$form_data = [];
$form_errors = [];
$form_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Invalid CSRF token');
    }
    
    // Sanitize and validate inputs
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $subject = sanitize_input($_POST['subject'] ?? '');
    $message = sanitize_input($_POST['message'] ?? '');
    $subscribe = isset($_POST['subscribe']) ? true : false;
    
    // Validation
    if (empty($name)) {
        $form_errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $form_errors['email'] = 'Email is required';
    } elseif (!validate_email($email)) {
        $form_errors['email'] = 'Please enter a valid email address';
    }
    
    if (empty($subject)) {
        $form_errors['subject'] = 'Subject is required';
    }
    
    if (empty($message)) {
        $form_errors['message'] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $form_errors['message'] = 'Message must be at least 10 characters';
    }
    
    // If no errors, process the form
    if (empty($form_errors)) {
        // In a real application, you would:
        // 1. Save to database
        // 2. Send email notification
        // 3. Send confirmation email to user
        
        // For now, we'll just simulate success
        $form_success = true;
        
        // Clear form data
        $name = $email = $subject = $message = '';
        $subscribe = false;
        
        // Set success message
        set_flash_message('success', 'Thank you for your message! We\'ll get back to you within 2 business days.');
    } else {
        // Store form data for repopulation
        $form_data = compact('name', 'email', 'subject', 'message', 'subscribe');
    }
}

require_once 'includes/header.php';
?>

<!-- About Page Main Content -->
<div class="about-page">
    <!-- About Hero Section -->
    <section class="about-hero">
        <div class="about-hero-content">
            <h1>About BookExchange</h1>
            <p>We're building a community where book lovers can share their passion, discover new reads, and connect with fellow readers.</p>
        </div>
    </section>
    
    <!-- Our Story Section -->
    <section class="story-section">
        <div class="section-header">
            <h2>Our Story</h2>
        </div>
        
        <div class="story-content">
            <div class="story-image">
                <img src="https://images.unsplash.com/photo-1524578271613-d550eacf6090?ixlib=rb-4.0.3&auto=format&fit=crop&w=1170&q=80" alt="Community reading">
            </div>
            
            <div class="story-text">
                <p>BookExchange was founded in <?php echo date('Y'); ?> by a group of avid readers who wanted to create a sustainable way to share books. We noticed that many books sit on shelves after being read once, while others search for those same titles.</p>
                
                <p>Our platform connects readers who have books to share with those looking for their next read. By facilitating book exchanges, we're not just saving money and resources—we're building a community around the shared love of reading.</p>
                
                <p>Today, BookExchange has grown to thousands of members across the country, exchanging hundreds of books each month and forming lasting connections through shared literary interests.</p>
            </div>
        </div>
    </section>
    
    <!-- Mission & Values Section -->
    <section class="values-section">
        <div class="section-header">
            <h2>Our Mission & Values</h2>
        </div>
        
        <div class="values-container">
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-book"></i>
                </div>
                <h3>Promote Reading</h3>
                <p>We believe in making books accessible to everyone, regardless of budget or location.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-recycle"></i>
                </div>
                <h3>Sustainable Reading</h3>
                <p>By sharing books, we reduce waste and promote a circular economy for literature.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Build Community</h3>
                <p>We connect readers with shared interests, fostering discussion and friendship.</p>
            </div>
            
            <div class="value-card">
                <div class="value-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h3>Trust & Safety</h3>
                <p>We prioritize member safety with secure exchanges and a verified community.</p>
            </div>
        </div>
    </section>
    
    <!-- Team Section -->
    <section class="team-section">
        <div class="section-header">
            <h2>Meet Our Team</h2>
            <p>The passionate readers behind BookExchange</p>
        </div>
        
        <div class="team-container">
            <div class="team-member">
                <div class="member-image">
                    <img src="media/figo.jpeg" alt="Team Member">
                </div>
                <h3>Fola Ludwig</h3>
                <p class="member-role">Founder & CEO</p>
                <p class="member-bio">Avid reader with a passion for community building and sustainable living.</p>
            </div>
            
            <div class="team-member">
                <div class="member-image">
                    <img src="media/figo.jpeg" alt="Team Member">
                </div>
                <h3>Jordan Ayuk</h3>
                <p class="member-role">Community Manager</p>
                <p class="member-bio">Connects members and organizes local book exchange events.</p>
            </div>
            
            <div class="team-member">
                <div class="member-image">
                    <img src="media/figo.jpeg" alt="Team Member">
                </div>
                <h3>Onana Cedric</h3>
                <p class="member-role">Technical Lead</p>
                <p class="member-bio">Ensures our platform is secure, responsive, and user-friendly.</p>
            </div>
        </div>
    </section>
    
    <!-- Contact Form Section -->
    <section class="contact-section">
        <div class="section-header">
            <h2>Get In Touch</h2>
            <p>Have questions or suggestions? We'd love to hear from you!</p>
        </div>
        
        <div class="contact-container">
            <div class="contact-info">
                <h3>Contact Information</h3>
                <div class="contact-detail">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Address</h4>
                        <p>+237 Carrefour Etou-egbe, Yaounde Cameroon</p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h4>Phone</h4>
                        <p>+237 679455965</p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p><?php echo SITE_EMAIL; ?></p>
                    </div>
                </div>
                
                <div class="contact-detail">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Business Hours</h4>
                        <p>Monday - Friday: 9am - 6pm</p>
                        <p>Saturday: 10am - 4pm</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-form-container">
                <form method="POST" action="about.php" id="contactForm">
                    <?php echo csrf_field(); ?>
                    
                    <div class="form-group">
                        <label for="contactName">Your Name</label>
                        <input type="text" name="name" id="contactName" placeholder="Enter your name" required
                               value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>">
                        <div class="form-error" id="contactNameError">
                            <?php echo $form_errors['name'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contactEmail">Email Address</label>
                        <input type="email" name="email" id="contactEmail" placeholder="Enter your email" required
                               value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>">
                        <div class="form-error" id="contactEmailError">
                            <?php echo $form_errors['email'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contactSubject">Subject</label>
                        <input type="text" name="subject" id="contactSubject" placeholder="What is this regarding?" required
                               value="<?php echo htmlspecialchars($form_data['subject'] ?? ''); ?>">
                        <div class="form-error" id="contactSubjectError">
                            <?php echo $form_errors['subject'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="contactMessage">Message</label>
                        <textarea name="message" id="contactMessage" rows="5" placeholder="Your message here..." required><?php echo htmlspecialchars($form_data['message'] ?? ''); ?></textarea>
                        <div class="form-error" id="contactMessageError">
                            <?php echo $form_errors['message'] ?? ''; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" name="subscribe" id="contactSubscription" <?php echo ($form_data['subscribe'] ?? false) ? 'checked' : ''; ?>>
                            <span class="checkmark"></span>
                            Subscribe to our newsletter for updates
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                </form>
            </div>
        </div>
    </section>
    
    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="section-header">
            <h2>Frequently Asked Questions</h2>
        </div>
        
        <div class="faq-container">
            <div class="faq-item">
                <button class="faq-question">
                    How does the book exchange work?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Members list books they're willing to exchange on their profile. When you find a book you want, you can request an exchange with the owner. If they accept, you'll arrange the exchange details through our secure messaging system.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Is there a fee to join BookExchange?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>No, BookExchange is completely free to join! Our mission is to make reading accessible to everyone. We may introduce optional premium features in the future, but the core exchange functionality will always remain free.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    How do I ensure safe exchanges?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>We recommend meeting in public places for exchanges, like libraries or coffee shops. Our platform includes a rating system so you can see feedback on other members. Never share personal financial information through our platform.</p>
                </div>
            </div>
            
            <div class="faq-item">
                <button class="faq-question">
                    Can I exchange books by mail?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Yes! Many members exchange books by mail. When arranging an exchange, you can discuss shipping details with the other member. We recommend using tracked shipping and agreeing on who covers shipping costs beforehand.</p>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
require_once 'includes/footer.php';
?>