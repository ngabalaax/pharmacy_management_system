<?php 
$pageTitle = "Contact Us";
include '../includes/website_header.php'; 

// Form handling
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));
    
    // Validate inputs
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Please enter your name";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($message)) {
        $errors[] = "Please enter your message";
    }
    
    if (empty($errors)) {
        // Process form (in a real application, you would save to database or send email)
        $success = true;
        
        // For demonstration, we'll just show the success message
        // In production, you would:
        // 1. Save to database, or
        // 2. Send email using mail() or PHPMailer
    }
}
?>

    <!-- Contact Form -->
    <section class="contact">
        <div class="container">
            <h2>Get In Touch</h2>
            <div class="contact-container">
                <form method="POST" class="contact-form">
                    <?php if (isset($success)): ?>
                        <div class="alert success">
                            <i class="fas fa-check-circle"></i> Thank you for your message! We'll contact you within 24 hours.
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo implode('<br>', $errors); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" placeholder="Enter your name" value="<?php echo isset($name) ? $name : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" placeholder="How can we help you?" rows="5" required><?php echo isset($message) ? $message : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>

                <div class="contact-info">
                    <h3>Visit Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong>  Jigjiga City, Somali Region, Ethiopia</p>
                    
                    <p><i class="fas fa-phone"></i> <strong>Phone:</strong> +251 123 456 789</p>
                    
                    <p><i class="fas fa-envelope"></i> <strong>Email:</strong> info@hananpharmacy.com</p>
                    
                    <p><i class="fas fa-clock"></i> <strong>Opening Hours:</strong><br>
                    
                    Monday-Friday: 8:00 AM - 8:00 PM<br>
                    Saturday: 9:00 AM - 6:00 PM<br>
                    Sunday: 10:00 AM - 4:00 PM</p>
                    
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.123456789!2d42.123456!3d9.123456!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zOcKwMDcnMjQuNSJOIDQywrAwNyczNi4xIkU!5e0!3m2!1sen!2set!4v1234567890" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include '../includes/system_footer.php'; ?>