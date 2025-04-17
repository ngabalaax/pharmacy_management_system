<?php 
$pageTitle = "Home";
include '../includes/website_header.php'; 
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Your Health, Our Priority</h1>
            <p>24/7 pharmacy services with automated inventory management</p>
            <div class="cta-buttons">
                <a href="../modules/login.php" class="btn-primary">Manage Inventory</a>
                <a href="contact.php" class="btn-secondary">Contact Us</a>
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
            </div>
        </div>
    </section>

<?php include '../includes/system_footer.php'; ?>