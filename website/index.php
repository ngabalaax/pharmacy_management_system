<?php
$pageTitle = "Home";
include '../includes/website_header.php';
?>

<!-- Main CSS -->
<link rel="stylesheet" href="styles/home.css">

<section class="pharmacy-hero">
    <!-- Animated floating pills -->
    <div class="floating-pills">
        <div class="pill-1"><i class="fas fa-pills"></i></div>
        <div class="pill-2"><i class="fas fa-capsules"></i></div>
        <div class="pill-3"><i class="fas fa-tablets"></i></div>
        <div class="pill-4"><i class="fas fa-syringe"></i></div>
    </div>

    <div class="container">
        <h1>Your Health, Our Priority</h1>
        <p class="hero-subtitle">24/7 pharmacy services with automated inventory management</p>

        <div class="search-card">
            <div class="glow-effect"></div>
            <form action="drug_search.php" method="get">
                <div class="search-input-wrapper">
                    <div class="search-field">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" placeholder="Search for medications...">
                    </div>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>

                <div class="category-filter">
                    <label>Filter by Category</label>
                    <div class="select-wrapper">
                        <i class="fas fa-filter"></i>
                        <select name="category">
                            <option value="">All Categories</option>
                            <option value="pain_relievers">Pain Relievers</option>
                            <option value="antibiotics">Antibiotics</option>
                            <option value="vitamins">Vitamins & Supplements</option>
                            <option value="chronic">Chronic Conditions</option>
                            <option value="respiratory">Respiratory</option>
                            <option value="dermatological">Skin Care</option>
                        </select>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </form>
        </div>

        <div class="popular-searches">
            <p>Frequently searched:</p>
            <div class="search-tags">
                <a href="drug_search.php?search=paracetamol" class="search-tag">
                    <i class="fas fa-tablets"></i> Paracetamol
                </a>
                <a href="drug_search.php?search=amoxicillin" class="search-tag">
                    <i class="fas fa-capsules"></i> Amoxicillin
                </a>
                <a href="drug_search.php?search=vitamin c" class="search-tag">
                    <i class="fas fa-virus"></i> Vitamin C
                </a>
                <a href="drug_search.php?search=ibuprofen" class="search-tag">
                    <i class="fas fa-pills"></i> Ibuprofen
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features">
    <div class="container">
        <h2>Why Choose Hanan Pharmacy?</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <i class="fas fa-bell"></i>
                <h3>Expiry Alerts</h3>
                <p>Real-time notifications for expiring medications</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-chart-line"></i>
                <h3>Sales Analytics</h3>
                <p>Daily/weekly/monthly sales reports</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-user-shield"></i>
                <h3>Secure Access</h3>
                <p>Role-based dashboard for staff</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-robot"></i>
                <h3>AI Assistant</h3>
                <p>24/7 automated customer support</p>
            </div>
        </div>
    </div>
</section>

<!-- New Services Section -->
<section class="services">
    <div class="container">
        <h2>Our Services</h2>
        <div class="services-grid">
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-prescription-bottle-alt"></i>
                </div>
                <h3>Medication Management</h3>
                <p>Comprehensive tracking of all medications with automated refill reminders</p>
                <a href="#" class="learn-more">Learn more <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <h3>Health Monitoring</h3>
                <p>Track vital signs and medication adherence with our digital tools</p>
                <a href="#" class="learn-more">Learn more <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="service-card">
                <div class="service-icon">
                    <i class="fas fa-truck"></i>
                </div>
                <h3>Fast Delivery</h3>
                <p>Same-day delivery for all your prescription needs</p>
                <a href="#" class="learn-more">Learn more <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="testimonials">
    <div class="container">
        <h2>What Our Customers Say</h2>
        <div class="testimonial-slider">
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <i class="fas fa-quote-left"></i>
                    <p>The automated refill reminders have been a lifesaver for my elderly mother's medications.</p>
                </div>
                <div class="testimonial-author">
                    <img src="../assets/images/client.png" alt="Sarah J.">
                    <div>
                        <h4>Sarah J.</h4>
                        <span>Regular Customer</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <i class="fas fa-quote-left"></i>
                    <p>As a small clinic, the inventory management system has saved us countless hours of manual work.</p>
                </div>
                <div class="testimonial-author">
                    <img src="../assets/images/client.png" alt="Dr. Michael T.">
                    <div>
                        <h4>Dr. Michael T.</h4>
                        <span>Local Physician</span>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="testimonial-content">
                    <i class="fas fa-quote-left"></i>
                    <p>The mobile app integration makes it so easy to manage our pharmacy's inventory on the go.</p>
                </div>
                <div class="testimonial-author">
                    <img src="../assets/images/client.png" alt="Amina K.">
                    <div>
                        <h4>Amina K.</h4>
                        <span>Pharmacy Owner</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mobile App Section -->
<section class="mobile-app">
    <div class="container">
        <div class="app-content">
            <h2>Download Our Mobile App</h2>
            <p>Manage your medications, receive alerts, and access pharmacy services anytime, anywhere.</p>
            <div class="app-badges">
                <a href="#" class="app-badge">
                    <img src="../assets/images/play-store.png" alt="Get on Google Play">
                </a>
                <a href="#" class="app-badge">
                    <img src="../assets/images/app-store.png" alt="Download on the App Store">
                </a>
            </div>
        </div>
        <div class="app-image">
            <img src="../assets/images/logo.png" alt="Hanan Pharmacy Mobile App">
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats">
    <div class="container">
        <div class="stat-card">
            <i class="fas fa-pills"></i>
            <div>
                <span class="stat-number" data-count="2500">0</span>
                <span class="stat-label">Medications in Stock</span>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-users"></i>
            <div>
                <span class="stat-number" data-count="12000">0</span>
                <span class="stat-label">Happy Customers</span>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-calendar-check"></i>
            <div>
                <span class="stat-number" data-count="365">0</span>
                <span class="stat-label">Days Open Per Year</span>
            </div>
        </div>
        <div class="stat-card">
            <i class="fas fa-map-marker-alt"></i>
            <div>
                <span class="stat-number" data-count="5">0</span>
                <span class="stat-label">Locations</span>
            </div>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section class="newsletter">
    <div class="container">
        <div class="newsletter-content">
            <h2>Stay Updated</h2>
            <p>Subscribe to our newsletter for health tips, promotions, and pharmacy updates.</p>
        </div>
        <form class="newsletter-form">
            <div class="form-group">
                <input type="email" placeholder="Enter your email" required>
                <button type="submit" class="subscribe-btn">Subscribe</button>
            </div>
            <div class="form-check">
                <input type="checkbox" id="consent" required>
                <label for="consent">I agree to receive emails from Hanan Pharmacy</label>
            </div>
        </form>
    </div>
</section>

<?php include '../includes/system_footer.php'; ?>

<!-- JavaScript for animations -->
<script>
    // Animated counter for statistics
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;

    function animateCounters() {
        counters.forEach(counter => {
            const target = +counter.getAttribute('data-count');
            const count = +counter.innerText;
            const increment = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + increment);
                setTimeout(animateCounters, 1);
            } else {
                counter.innerText = target;
            }
        });
    }

    // Start animation when section is in view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    document.querySelectorAll('.stats').forEach(section => {
        observer.observe(section);
    });
</script>