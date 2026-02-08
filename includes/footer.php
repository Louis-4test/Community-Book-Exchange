    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <div class="footer-section">
                <h3>BookExchange</h3>
                <p>Connecting readers through the joy of sharing books since 2023.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="books.php">Browse Books</a></li>
                    <li><a href="auth.php"><?php echo is_logged_in() ? 'My Account' : 'Login/Register'; ?></a></li>
                    <li><a href="about.php">About Us</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Contact Info</h4>
                <ul class="contact-info">
                    <li><i class="fas fa-map-marker-alt"></i> +237 Carrefour Etou-egbe, Yaounde Cameroon</li>
                    <li><i class="fas fa-phone"></i> +237 679455965</li>
                    <li><i class="fas fa-envelope"></i> <?php echo htmlspecialchars('fola.louis@yibs.org'); ?></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Community Book Exchange. All rights reserved.</p>
            <p>Server Time: <?php echo date('F j, Y, g:i a'); ?></p>
        </div>
    </footer>

    <!-- Book Details Modal -->
    <div class="modal" id="bookModal">
        <div class="modal-content">
            <button class="modal-close" id="modalClose">&times;</button>
            <div class="modal-body" id="modalBody">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>

    <script src="<?php echo SITE_URL; ?>/js/script.js"></script>
</body>
</html>