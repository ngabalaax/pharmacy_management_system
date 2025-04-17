<?php
// access_denied.php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

// Set page title
$pageTitle = "Access Denied";
?>

<div class="content">
    <div class="alert alert-danger" style="max-width: 600px; margin: 50px auto; text-align: center;">
        <div style="font-size: 5rem; margin-bottom: 20px;">
            <i class="fas fa-ban"></i>
        </div>
        <h1>Access Denied</h1>
        <p>You don't have permission to access this page.</p>
        <p>Please contact your administrator if you believe this is an error.</p>
        
        <div style="margin-top: 30px;">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/modules/dashboard/<?php echo strtolower($_SESSION['role']); ?>.php" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt"></i> Return to Dashboard
                </a>
            <?php else: ?>
                <a href="/login.php" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Login Page
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php 
// Include footer
require_once __DIR__ . '/includes/footer.php';
?>