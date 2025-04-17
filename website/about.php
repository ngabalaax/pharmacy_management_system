<?php 
$pageTitle = "About Us";
include '../includes/website_header.php'; 
?>

    <!-- About Section -->
    <section class="about">
        <div class="container">
            <h2>Our Story</h2>
            <div class="about-content">
                <div class="about-text">
                    <p>Founded in 2013, Hanan Pharmacy has been serving Jigjiga with quality medications and healthcare advice. Our new management system ensures we can serve you better with:</p>
                    <ul>
                        <li><strong>Accurate inventory tracking:</strong> Never face stockouts of essential medications</li>
                        <li><strong>Faster prescription processing:</strong> Reduced waiting times for our customers</li>
                        <li><strong>Automated expiry date monitoring:</strong> Ensuring only fresh medications are dispensed</li>
                        <li><strong>Real-time reporting:</strong> Better decision making for pharmacy management</li>
                    </ul>
                    <p>Our mission is to provide accessible, reliable pharmaceutical care to the Jigjiga community while maintaining the highest standards of professionalism.</p>
                </div>
                <div class="about-image">
                    <img src="../assets/images/pharmacy-interior.jpg" alt="Hanan Pharmacy Interior" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team">
        <div class="container">
            <h2>Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-card">
                    <img src="../assets/images/team1.jpg" alt="Dr. Ahmed Mohamed" loading="lazy">
                    <h3>Dr. Ahmed Mohamed</h3>
                    <p>Head Pharmacist</p>
                    <p class="team-bio">With over 10 years of experience, Dr. Ahmed ensures all medications meet quality standards.</p>
                </div>
                <div class="team-card">
                    <img src="../assets/images/team2.jpg" alt="Nurse Fatima Hussein" loading="lazy">
                    <h3>Fatima Hussein</h3>
                    <p>Senior Pharmacy Technician</p>
                    <p class="team-bio">Specializes in patient counseling and medication management.</p>
                </div>
                <div class="team-card">
                    <img src="../assets/images/team3.jpg" alt="Abdullahi Ali" loading="lazy">
                    <h3>Abdullahi Ali</h3>
                    <p>Inventory Manager</p>
                    <p class="team-bio">Oversees our automated inventory system and supply chain.</p>
                </div>
            </div>
        </div>
    </section>

<?php include '../includes/system_footer.php'; ?>