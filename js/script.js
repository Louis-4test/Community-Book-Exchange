// DOM Elements
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const navMenu = document.getElementById('navMenu');
const bookModal = document.getElementById('bookModal');
const modalClose = document.getElementById('modalClose');
const modalBody = document.getElementById('modalBody');

// Mobile Menu Toggle
if (mobileMenuBtn && navMenu) {
    mobileMenuBtn.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        mobileMenuBtn.innerHTML = navMenu.classList.contains('active') 
            ? '<i class="fas fa-times"></i>' 
            : '<i class="fas fa-bars"></i>';
    });
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!navMenu.contains(e.target) && !mobileMenuBtn.contains(e.target) && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
}

// Book Details Modal
document.addEventListener('DOMContentLoaded', function() {
    // Handle book details button clicks
    const detailButtons = document.querySelectorAll('.btn-details');
    
    if (detailButtons.length > 0) {
        detailButtons.forEach(button => {
            button.addEventListener('click', function() {
                const bookId = this.getAttribute('data-book');
                openBookModal(bookId);
            });
        });
    }
    
    // Close modal when clicking close button
    if (modalClose) {
        modalClose.addEventListener('click', () => {
            bookModal.classList.remove('active');
        });
    }
    
    // Close modal when clicking outside
    bookModal.addEventListener('click', (e) => {
        if (e.target === bookModal) {
            bookModal.classList.remove('active');
        }
    });
    
    // Close modal with Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && bookModal.classList.contains('active')) {
            bookModal.classList.remove('active');
        }
    });
});

// Open Book Modal with Details
function openBookModal(bookId) {
    // Sample book data - in a real app, this would come from an API
    const books = {
        1: {
            title: "The Silent Echo",
            author: "Maria Rodriguez",
            genre: "Fiction",
            condition: "Like New",
            description: "A gripping mystery novel about a detective solving a decades-old cold case in a small coastal town. When Detective Anna Reed returns to her hometown after 15 years, she's drawn into the unsolved disappearance of a local girl that haunted her childhood. As she digs deeper, she uncovers secrets that powerful people want to stay buried.",
            isbn: "978-3-16-148410-0",
            year: 2022,
            owner: "Alex Johnson",
            location: "New York, NY",
            dateListed: "2023-10-15"
        },
        2: {
            title: "Cosmic Patterns",
            author: "David Chen",
            genre: "Science",
            condition: "Good",
            description: "Exploring the mathematical patterns that govern the universe, from galaxies to subatomic particles. This accessible science book takes readers on a journey through fractal geometry, Fibonacci sequences in nature, and the hidden mathematical order in what appears to be chaos. Perfect for science enthusiasts and curious minds alike.",
            isbn: "978-1-23-456789-7",
            year: 2021,
            owner: "Sam Wilson",
            location: "San Francisco, CA",
            dateListed: "2023-11-02"
        },
        3: {
            title: "The Lost Kingdom",
            author: "Elena Petrova",
            genre: "Fantasy",
            condition: "Excellent",
            description: "An epic fantasy tale of a forgotten kingdom's rise from the ashes and the hero destined to restore it. In a world where magic has faded, a young blacksmith discovers she is the last heir to a throne no one remembers. With the help of unlikely allies, she must reclaim her birthright before ancient darkness consumes the land.",
            isbn: "978-0-12-345678-9",
            year: 2023,
            owner: "Jordan Lee",
            location: "Chicago, IL",
            dateListed: "2023-11-10"
        }
    };
    
    const book = books[bookId];
    
    if (!book) return;
    
    const modalContent = `
        <div class="book-modal-content">
            <div class="book-modal-header">
                <h2>${book.title}</h2>
                <p class="book-modal-author">By ${book.author}</p>
            </div>
            
            <div class="book-modal-details">
                <div class="detail-row">
                    <div class="detail-item">
                        <span class="detail-label">Genre:</span>
                        <span class="detail-value">${book.genre}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Condition:</span>
                        <span class="detail-value">${book.condition}</span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-item">
                        <span class="detail-label">Published:</span>
                        <span class="detail-value">${book.year}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">ISBN:</span>
                        <span class="detail-value">${book.isbn}</span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-item">
                        <span class="detail-label">Listed by:</span>
                        <span class="detail-value">${book.owner}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value">${book.location}</span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-item">
                        <span class="detail-label">Date Listed:</span>
                        <span class="detail-value">${book.dateListed}</span>
                    </div>
                </div>
            </div>
            
            <div class="book-modal-description">
                <h3>Description</h3>
                <p>${book.description}</p>
            </div>
            
            <div class="book-modal-actions">
                <button class="btn btn-primary btn-large" id="requestExchangeBtn">Request Exchange</button>
                <button class="btn btn-outline btn-large" id="closeModalBtn">Close</button>
            </div>
        </div>
    `;
    
    if (modalBody) {
        modalBody.innerHTML = modalContent;
        bookModal.classList.add('active');
        
        // Add event listeners to modal buttons
        document.getElementById('requestExchangeBtn')?.addEventListener('click', () => {
            alert('Exchange request sent! The book owner will contact you soon.');
            bookModal.classList.remove('active');
        });
        
        document.getElementById('closeModalBtn')?.addEventListener('click', () => {
            bookModal.classList.remove('active');
        });
    }
}

// Authentication Form Handling
document.addEventListener('DOMContentLoaded', function() {
    // Toggle between login and register forms
    const loginToggle = document.getElementById('loginToggle');
    const registerToggle = document.getElementById('registerToggle');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    const switchToRegister = document.getElementById('switchToRegister');
    const switchToLogin = document.getElementById('switchToLogin');
    
    if (loginToggle && registerToggle) {
        loginToggle.addEventListener('click', () => {
            loginToggle.classList.add('active');
            registerToggle.classList.remove('active');
            loginForm.classList.add('active');
            registerForm.classList.remove('active');
        });
        
        registerToggle.addEventListener('click', () => {
            registerToggle.classList.add('active');
            loginToggle.classList.remove('active');
            registerForm.classList.add('active');
            loginForm.classList.remove('active');
        });
    }
    
    if (switchToRegister) {
        switchToRegister.addEventListener('click', (e) => {
            e.preventDefault();
            if (registerToggle) registerToggle.click();
        });
    }
    
    if (switchToLogin) {
        switchToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            if (loginToggle) loginToggle.click();
        });
    }
    
    // Check URL parameter for registration
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('register') === 'true' && registerToggle) {
        registerToggle.click();
    }
    
    // Form validation for registration
    const registerFormElement = document.getElementById('registerForm');
    if (registerFormElement) {
        // Password validation
        const passwordInput = document.getElementById('registerPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        
        const passwordRequirements = {
            length: document.getElementById('req-length'),
            uppercase: document.getElementById('req-uppercase'),
            number: document.getElementById('req-number'),
            special: document.getElementById('req-special')
        };
        
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                validatePassword(this.value, passwordRequirements);
            });
        }
        
        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                validateConfirmPassword();
            });
        }
        
        // Form submission
        registerFormElement.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateRegistrationForm()) {
                // Show success modal
                const successModal = document.getElementById('successModal');
                const successModalClose = document.getElementById('successModalClose');
                const successBtn = document.getElementById('successBtn');
                
                if (successModal) {
                    successModal.classList.add('active');
                    
                    if (successModalClose) {
                        successModalClose.addEventListener('click', () => {
                            successModal.classList.remove('active');
                        });
                    }
                    
                    if (successBtn) {
                        successBtn.addEventListener('click', () => {
                            successModal.classList.remove('active');
                            // Switch to login form
                            if (loginToggle) loginToggle.click();
                        });
                    }
                }
            }
        });
    }
    
    // Form validation for login
    const loginFormElement = document.getElementById('loginForm');
    if (loginFormElement) {
        loginFormElement.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('loginEmail')?.value;
            const password = document.getElementById('loginPassword')?.value;
            
            // Simple validation
            if (!email || !password) {
                alert('Please fill in all fields');
                return;
            }
            
            // In a real app, this would be an API call
            alert('Login successful! Redirecting to dashboard...');
            
            // Reset form
            loginFormElement.reset();
        });
    }
    
    // Contact form validation
    const contactForm = document.getElementById('contactForm');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateContactForm()) {
                // Show success modal
                const contactSuccessModal = document.getElementById('contactSuccessModal');
                const contactModalClose = document.getElementById('contactModalClose');
                const contactSuccessBtn = document.getElementById('contactSuccessBtn');
                
                if (contactSuccessModal) {
                    contactSuccessModal.classList.add('active');
                    
                    if (contactModalClose) {
                        contactModalClose.addEventListener('click', () => {
                            contactSuccessModal.classList.remove('active');
                        });
                    }
                    
                    if (contactSuccessBtn) {
                        contactSuccessBtn.addEventListener('click', () => {
                            contactSuccessModal.classList.remove('active');
                            contactForm.reset();
                        });
                    }
                }
            }
        });
    }
    
    // FAQ Accordion
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const isActive = question.classList.contains('active');
            
            // Close all other FAQ items
            faqQuestions.forEach(q => {
                q.classList.remove('active');
                q.nextElementSibling.classList.remove('active');
            });
            
            // Open clicked item if it wasn't already active
            if (!isActive) {
                question.classList.add('active');
                answer.classList.add('active');
            }
        });
    });
    
    // Book search and filter functionality
    initializeBookSearch();
});

// Password validation
function validatePassword(password, requirements) {
    const hasLength = password.length >= 8;
    const hasUppercase = /[A-Z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    // Update requirement indicators
    if (requirements.length) {
        requirements.length.classList.toggle('valid', hasLength);
    }
    
    if (requirements.uppercase) {
        requirements.uppercase.classList.toggle('valid', hasUppercase);
    }
    
    if (requirements.number) {
        requirements.number.classList.toggle('valid', hasNumber);
    }
    
    if (requirements.special) {
        requirements.special.classList.toggle('valid', hasSpecial);
    }
    
    return hasLength && hasUppercase && hasNumber && hasSpecial;
}

// Confirm password validation
function validateConfirmPassword() {
    const password = document.getElementById('registerPassword')?.value;
    const confirmPassword = document.getElementById('confirmPassword')?.value;
    const errorElement = document.getElementById('confirmPasswordError');
    
    if (!errorElement) return false;
    
    if (password !== confirmPassword) {
        errorElement.textContent = 'Passwords do not match';
        return false;
    } else {
        errorElement.textContent = '';
        return true;
    }
}

// Registration form validation
function validateRegistrationForm() {
    let isValid = true;
    
    // Name validation
    const name = document.getElementById('registerName');
    const nameError = document.getElementById('registerNameError');
    if (name && nameError) {
        if (!name.value.trim()) {
            nameError.textContent = 'Name is required';
            isValid = false;
        } else if (name.value.trim().length < 2) {
            nameError.textContent = 'Name must be at least 2 characters';
            isValid = false;
        } else {
            nameError.textContent = '';
        }
    }
    
    // Email validation
    const email = document.getElementById('registerEmail');
    const emailError = document.getElementById('registerEmailError');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && emailError) {
        if (!email.value.trim()) {
            emailError.textContent = 'Email is required';
            isValid = false;
        } else if (!emailRegex.test(email.value)) {
            emailError.textContent = 'Please enter a valid email address';
            isValid = false;
        } else {
            emailError.textContent = '';
        }
    }
    
    // Password validation
    const password = document.getElementById('registerPassword');
    const passwordError = document.getElementById('registerPasswordError');
    const passwordRequirements = {
        length: document.getElementById('req-length'),
        uppercase: document.getElementById('req-uppercase'),
        number: document.getElementById('req-number'),
        special: document.getElementById('req-special')
    };
    
    if (password && passwordError) {
        if (!password.value) {
            passwordError.textContent = 'Password is required';
            isValid = false;
        } else if (!validatePassword(password.value, passwordRequirements)) {
            passwordError.textContent = 'Password does not meet requirements';
            isValid = false;
        } else {
            passwordError.textContent = '';
        }
    }
    
    // Confirm password validation
    if (!validateConfirmPassword()) {
        isValid = false;
    }
    
    // Terms agreement validation
    const termsCheckbox = document.getElementById('termsAgreement');
    const termsError = document.getElementById('termsError');
    
    if (termsCheckbox && termsError) {
        if (!termsCheckbox.checked) {
            termsError.textContent = 'You must agree to the terms';
            isValid = false;
        } else {
            termsError.textContent = '';
        }
    }
    
    return isValid;
}

// Contact form validation
function validateContactForm() {
    let isValid = true;
    
    // Name validation
    const name = document.getElementById('contactName');
    const nameError = document.getElementById('contactNameError');
    if (name && nameError) {
        if (!name.value.trim()) {
            nameError.textContent = 'Name is required';
            isValid = false;
        } else {
            nameError.textContent = '';
        }
    }
    
    // Email validation
    const email = document.getElementById('contactEmail');
    const emailError = document.getElementById('contactEmailError');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email && emailError) {
        if (!email.value.trim()) {
            emailError.textContent = 'Email is required';
            isValid = false;
        } else if (!emailRegex.test(email.value)) {
            emailError.textContent = 'Please enter a valid email address';
            isValid = false;
        } else {
            emailError.textContent = '';
        }
    }
    
    // Subject validation
    const subject = document.getElementById('contactSubject');
    const subjectError = document.getElementById('contactSubjectError');
    if (subject && subjectError) {
        if (!subject.value.trim()) {
            subjectError.textContent = 'Subject is required';
            isValid = false;
        } else {
            subjectError.textContent = '';
        }
    }
    
    // Message validation
    const message = document.getElementById('contactMessage');
    const messageError = document.getElementById('contactMessageError');
    if (message && messageError) {
        if (!message.value.trim()) {
            messageError.textContent = 'Message is required';
            isValid = false;
        } else if (message.value.trim().length < 10) {
            messageError.textContent = 'Message must be at least 10 characters';
            isValid = false;
        } else {
            messageError.textContent = '';
        }
    }
    
    return isValid;
}

// Book Search and Filter Functionality
function initializeBookSearch() {
    const searchInput = document.getElementById('bookSearch');
    const searchBtn = document.getElementById('searchBtn');
    const genreFilter = document.getElementById('genreFilter');
    const conditionFilter = document.getElementById('conditionFilter');
    const sortBy = document.getElementById('sortBy');
    const resetFilters = document.getElementById('resetFilters');
    const booksGrid = document.getElementById('booksGrid');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const noResults = document.getElementById('noResults');
    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');
    
    // Sample book data for the books page
    const sampleBooks = [
        {
            id: 1,
            title: "The Silent Echo",
            author: "Maria Rodriguez",
            genre: "Fiction",
            condition: "Like New",
            description: "A gripping mystery novel about a detective solving a decades-old cold case.",
            year: 2022,
            owner: "Alex Johnson",
            dateListed: "2023-10-15"
        },
        {
            id: 2,
            title: "Cosmic Patterns",
            author: "David Chen",
            genre: "Science",
            condition: "Good",
            description: "Exploring the mathematical patterns that govern the universe.",
            year: 2021,
            owner: "Sam Wilson",
            dateListed: "2023-11-02"
        },
        {
            id: 3,
            title: "The Lost Kingdom",
            author: "Elena Petrova",
            genre: "Fantasy",
            condition: "Excellent",
            description: "An epic fantasy tale of a forgotten kingdom's rise from the ashes.",
            year: 2023,
            owner: "Jordan Lee",
            dateListed: "2023-11-10"
        },
        {
            id: 4,
            title: "Urban Legends",
            author: "James Peterson",
            genre: "Mystery",
            condition: "Good",
            description: "A collection of modern urban legends with a supernatural twist.",
            year: 2020,
            owner: "Taylor Kim",
            dateListed: "2023-10-28"
        },
        {
            id: 5,
            title: "The Art of Baking",
            author: "Claire Bennett",
            genre: "Non-Fiction",
            condition: "Like New",
            description: "Master the art of baking with this comprehensive guide.",
            year: 2021,
            owner: "Morgan Rhodes",
            dateListed: "2023-11-05"
        },
        {
            id: 6,
            title: "Echoes of War",
            author: "Robert Jackson",
            genre: "History",
            condition: "Fair",
            description: "A historical account of WWII from the perspective of soldiers.",
            year: 2019,
            owner: "Casey Smith",
            dateListed: "2023-10-20"
        },
        {
            id: 7,
            title: "Quantum Dreams",
            author: "Lisa Wong",
            genre: "Science Fiction",
            condition: "Excellent",
            description: "A scientist discovers how to enter dreams in this sci-fi thriller.",
            year: 2023,
            owner: "Derek Miller",
            dateListed: "2023-11-12"
        },
        {
            id: 8,
            title: "Mountain High",
            author: "Carlos Ruiz",
            genre: "Biography",
            condition: "Good",
            description: "The autobiography of a renowned mountain climber.",
            year: 2022,
            owner: "Sophia Chen",
            dateListed: "2023-10-18"
        }
    ];
    
    let currentPage = 1;
    const booksPerPage = 6;
    let filteredBooks = [...sampleBooks];
    
    // Initial render
    renderBooks();
    
    // Search functionality
    if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') performSearch();
        });
    }
    
    // Filter functionality
    if (genreFilter) {
        genreFilter.addEventListener('change', applyFilters);
    }
    
    if (conditionFilter) {
        conditionFilter.addEventListener('change', applyFilters);
    }
    
    if (sortBy) {
        sortBy.addEventListener('change', applyFilters);
    }
    
    // Reset filters
    if (resetFilters) {
        resetFilters.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            if (genreFilter) genreFilter.value = '';
            if (conditionFilter) conditionFilter.value = '';
            if (sortBy) sortBy.value = 'newest';
            
            filteredBooks = [...sampleBooks];
            currentPage = 1;
            renderBooks();
        });
    }
    
    // Pagination
    if (prevPage) {
        prevPage.addEventListener('click', function() {
            if (currentPage > 1) {
                currentPage--;
                renderBooks();
            }
        });
    }
    
    if (nextPage) {
        nextPage.addEventListener('click', function() {
            const totalPages = Math.ceil(filteredBooks.length / booksPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderBooks();
            }
        });
    }
    
    function performSearch() {
        applyFilters();
    }
    
    function applyFilters() {
        const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
        const genre = genreFilter ? genreFilter.value : '';
        const condition = conditionFilter ? conditionFilter.value : '';
        const sort = sortBy ? sortBy.value : 'newest';
        
        // Show loading indicator
        if (loadingIndicator) loadingIndicator.classList.add('active');
        
        // Simulate API delay
        setTimeout(() => {
            // Filter books
            filteredBooks = sampleBooks.filter(book => {
                const matchesSearch = !searchTerm || 
                    book.title.toLowerCase().includes(searchTerm) ||
                    book.author.toLowerCase().includes(searchTerm) ||
                    book.genre.toLowerCase().includes(searchTerm);
                
                const matchesGenre = !genre || book.genre.toLowerCase() === genre.toLowerCase();
                const matchesCondition = !condition || 
                    book.condition.toLowerCase().replace(' ', '-') === condition.toLowerCase();
                
                return matchesSearch && matchesGenre && matchesCondition;
            });
            
            // Sort books
            filteredBooks.sort((a, b) => {
                switch(sort) {
                    case 'title':
                        return a.title.localeCompare(b.title);
                    case 'condition':
                        const conditionOrder = { 'Excellent': 1, 'Like New': 2, 'Good': 3, 'Fair': 4 };
                        return conditionOrder[a.condition] - conditionOrder[b.condition];
                    case 'newest':
                    default:
                        return new Date(b.dateListed) - new Date(a.dateListed);
                }
            });
            
            currentPage = 1;
            renderBooks();
            
            // Hide loading indicator
            if (loadingIndicator) loadingIndicator.classList.remove('active');
        }, 500);
    }
    
    function renderBooks() {
        if (!booksGrid) return;
        
        const startIndex = (currentPage - 1) * booksPerPage;
        const endIndex = startIndex + booksPerPage;
        const booksToShow = filteredBooks.slice(startIndex, endIndex);
        
        // Clear grid
        booksGrid.innerHTML = '';
        
        // Show no results message if no books
        if (filteredBooks.length === 0) {
            if (noResults) noResults.classList.add('active');
            if (booksGrid) booksGrid.innerHTML = '';
            updatePagination();
            return;
        } else {
            if (noResults) noResults.classList.remove('active');
        }
        
        // Render books
        booksToShow.forEach(book => {
            const bookCard = document.createElement('div');
            bookCard.className = 'book-card';
            bookCard.innerHTML = `
                <div class="book-cover">
                    <img src="https://images.unsplash.com/photo-1544947950-fa07a98d237f?ixlib=rb-4.0.3&auto=format&fit=crop&w=687&q=80" alt="Book Cover">
                    <span class="book-condition">${book.condition}</span>
                </div>
                <div class="book-info">
                    <h3 class="book-title">${book.title}</h3>
                    <p class="book-author">By ${book.author}</p>
                    <div class="book-genre">${book.genre}</div>
                    <p class="book-description">${book.description}</p>
                    <div class="book-actions">
                        <button class="btn-details" data-book="${book.id}">View Details</button>
                        <button class="btn-request">Request Exchange</button>
                    </div>
                </div>
            `;
            
            booksGrid.appendChild(bookCard);
            
            // Add event listener to the details button
            const detailsBtn = bookCard.querySelector('.btn-details');
            if (detailsBtn) {
                detailsBtn.addEventListener('click', function() {
                    const bookId = this.getAttribute('data-book');
                    openBookModal(bookId);
                });
            }
            
            // Add event listener to the request button
            const requestBtn = bookCard.querySelector('.btn-request');
            if (requestBtn) {
                requestBtn.addEventListener('click', function() {
                    alert(`Exchange request sent for "${book.title}"! The owner will contact you soon.`);
                });
            }
        });
        
        updatePagination();
    }
    
    function updatePagination() {
        if (!prevPage || !nextPage) return;
        
        const totalPages = Math.ceil(filteredBooks.length / booksPerPage);
        
        // Update previous button
        prevPage.disabled = currentPage === 1;
        
        // Update next button
        nextPage.disabled = currentPage === totalPages || totalPages === 0;
        
        // Update page numbers (simplified for this example)
        const pageNumbers = document.querySelector('.page-numbers');
        if (pageNumbers) {
            pageNumbers.innerHTML = '';
            
            for (let i = 1; i <= Math.min(totalPages, 3); i++) {
                const pageNumber = document.createElement('span');
                pageNumber.className = 'page-number';
                if (i === currentPage) pageNumber.classList.add('active');
                pageNumber.textContent = i;
                pageNumber.addEventListener('click', () => {
                    currentPage = i;
                    renderBooks();
                });
                pageNumbers.appendChild(pageNumber);
            }
        }
    }


    // Keep the existing JavaScript functionality from Module 1
// Remove duplicate functions that are now handled by PHP

// Mobile Menu Toggle (unchanged)
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const navMenu = document.getElementById('navMenu');

if (mobileMenuBtn && navMenu) {
    mobileMenuBtn.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        mobileMenuBtn.innerHTML = navMenu.classList.contains('active') 
            ? '<i class="fas fa-times"></i>' 
            : '<i class="fas fa-bars"></i>';
    });
    
    document.addEventListener('click', (e) => {
        if (!navMenu.contains(e.target) && !mobileMenuBtn.contains(e.target) && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
            mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
        }
    });
}

// Book Details Modal (modified to work with PHP)
document.addEventListener('DOMContentLoaded', function() {
    const detailButtons = document.querySelectorAll('.btn-details');
    const modal = document.getElementById('bookModal');
    const modalClose = document.getElementById('modalClose');
    
    if (detailButtons.length > 0) {
        detailButtons.forEach(button => {
            button.addEventListener('click', function() {
                const bookId = this.getAttribute('data-book');
                // In a real app, this would fetch from an API
                // For now, we'll use the JavaScript function from Module 1
                openBookModal(bookId);
            });
        });
    }
    
    if (modalClose) {
        modalClose.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
    
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            modal.classList.remove('active');
        }
    });
    
    // FAQ Accordion (unchanged)
    const faqQuestions = document.querySelectorAll('.faq-question');
    faqQuestions.forEach(question => {
        question.addEventListener('click', () => {
            const answer = question.nextElementSibling;
            const isActive = question.classList.contains('active');
            
            faqQuestions.forEach(q => {
                q.classList.remove('active');
                q.nextElementSibling.classList.remove('active');
            });
            
            if (!isActive) {
                question.classList.add('active');
                answer.classList.add('active');
            }
        });
    });
    
    // Auth form toggle (unchanged)
    const loginToggle = document.getElementById('loginToggle');
    const registerToggle = document.getElementById('registerToggle');
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');
    
    if (loginToggle && registerToggle) {
        loginToggle.addEventListener('click', () => {
            loginToggle.classList.add('active');
            registerToggle.classList.remove('active');
            loginForm.classList.add('active');
            registerForm.classList.remove('active');
        });
        
        registerToggle.addEventListener('click', () => {
            registerToggle.classList.add('active');
            loginToggle.classList.remove('active');
            registerForm.classList.add('active');
            loginForm.classList.remove('active');
        });
    }
    
    // Password validation (unchanged)
    const passwordInput = document.getElementById('registerPassword');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePassword(this.value, {
                length: document.getElementById('req-length'),
                uppercase: document.getElementById('req-uppercase'),
                number: document.getElementById('req-number'),
                special: document.getElementById('req-special')
            });
        });
    }
    
    // Confirm password validation (unchanged)
    const confirmPasswordInput = document.getElementById('confirmPassword');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);
    }
});

// Password validation functions (unchanged from Module 1)
function validatePassword(password, requirements) {
    const hasLength = password.length >= 8;
    const hasUppercase = /[A-Z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
    
    if (requirements.length) {
        requirements.length.classList.toggle('valid', hasLength);
    }
    
    if (requirements.uppercase) {
        requirements.uppercase.classList.toggle('valid', hasUppercase);
    }
    
    if (requirements.number) {
        requirements.number.classList.toggle('valid', hasNumber);
    }
    
    if (requirements.special) {
        requirements.special.classList.toggle('valid', hasSpecial);
    }
    
    return hasLength && hasUppercase && hasNumber && hasSpecial;
}

function validateConfirmPassword() {
    const password = document.getElementById('registerPassword')?.value;
    const confirmPassword = document.getElementById('confirmPassword')?.value;
    const errorElement = document.getElementById('confirmPasswordError');
    
    if (!errorElement) return false;
    
    if (password !== confirmPassword) {
        errorElement.textContent = 'Passwords do not match';
        return false;
    } else {
        errorElement.textContent = '';
        return true;
    }
}

// Book modal function (from Module 1, kept for compatibility)
function openBookModal(bookId) {
    const books = {
        1: {
            title: "The Silent Echo",
            author: "Maria Rodriguez",
            genre: "Fiction",
            condition: "Like New",
            description: "A gripping mystery novel about a detective solving a decades-old cold case in a small coastal town. When Detective Anna Reed returns to her hometown after 15 years, she's drawn into the unsolved disappearance of a local girl that haunted her childhood. As she digs deeper, she uncovers secrets that powerful people want to stay buried.",
            isbn: "978-3-16-148410-0",
            year: 2022,
            owner: "Alex Johnson",
            location: "New York, NY",
            dateListed: "2023-10-15"
        },
        2: {
            title: "Cosmic Patterns",
            author: "David Chen",
            genre: "Science",
            condition: "Good",
            description: "Exploring the mathematical patterns that govern the universe, from galaxies to subatomic particles. This accessible science book takes readers on a journey through fractal geometry, Fibonacci sequences in nature, and the hidden mathematical order in what appears to be chaos. Perfect for science enthusiasts and curious minds alike.",
            isbn: "978-1-23-456789-7",
            year: 2021,
            owner: "Sam Wilson",
            location: "San Francisco, CA",
            dateListed: "2023-11-02"
        },
        3: {
            title: "The Lost Kingdom",
            author: "Elena Petrova",
            genre: "Fantasy",
            condition: "Excellent",
            description: "An epic fantasy tale of a forgotten kingdom's rise from the ashes and the hero destined to restore it. In a world where magic has faded, a young blacksmith discovers she is the last heir to a throne no one remembers. With the help of unlikely allies, she must reclaim her birthright before ancient darkness consumes the land.",
            isbn: "978-0-12-345678-9",
            year: 2023,
            owner: "Jordan Lee",
            location: "Chicago, IL",
            dateListed: "2023-11-10"
        }
    };
    
    const book = books[bookId];
    
    if (!book) return;
    
    const modal = document.getElementById('bookModal');
    const modalBody = document.getElementById('modalBody');
    
    if (!modal || !modalBody) return;
    
    const modalContent = `
        <div class="book-modal-content">
            <div class="book-modal-header">
                <h2>${book.title}</h2>
                <p class="book-modal-author">By ${book.author}</p>
            </div>
            
            <div class="book-modal-details">
                <div class="detail-row">
                    <div class="detail-item">
                        <span class="detail-label">Genre:</span>
                        <span class="detail-value">${book.genre}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Condition:</span>
                        <span class="detail-value">${book.condition}</span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-item">
                        <span class="detail-label">Published:</span>
                        <span class="detail-value">${book.year}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">ISBN:</span>
                        <span class="detail-value">${book.isbn}</span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-item">
                        <span class="detail-label">Listed by:</span>
                        <span class="detail-value">${book.owner}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Location:</span>
                        <span class="detail-value">${book.location}</span>
                    </div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-item">
                        <span class="detail-label">Date Listed:</span>
                        <span class="detail-value">${book.dateListed}</span>
                    </div>
                </div>
            </div>
            
            <div class="book-modal-description">
                <h3>Description</h3>
                <p>${book.description}</p>
            </div>
            
            <div class="book-modal-actions">
                <button class="btn btn-primary btn-large" id="requestExchangeBtn">Request Exchange</button>
                <button class="btn btn-outline btn-large" id="closeModalBtn">Close</button>
            </div>
        </div>
    `;
    
    modalBody.innerHTML = modalContent;
    modal.classList.add('active');
    
    document.getElementById('requestExchangeBtn')?.addEventListener('click', () => {
        alert('Exchange request sent! The book owner will contact you soon.');
        modal.classList.remove('active');
    });
    
    document.getElementById('closeModalBtn')?.addEventListener('click', () => {
        modal.classList.remove('active');
    });
}   

}