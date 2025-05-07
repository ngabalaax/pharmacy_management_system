<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hanan Pharmacy | <?php echo $pageTitle; ?></title>
    <!-- Correct CSS Path -->
    <link rel="stylesheet" href="../website/styles/website_header.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .logo img {
            height: 80px;
            width: fit-content;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">
                <img src="../assets/images/logo.png" alt="Hanan Pharmacy">
            </a>
            <div class="nav-links" id="navLinks">
                <a href="index.php"><i class="fas fa-home"></i> Home</a>
                <a href="about.php"><i class="fas fa-info-circle"></i> About</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
            
                <a href="../modules/login.php" class="btn-login"><i class="fas fa-lock"></i> Staff Login</a>
            </div>
        </div>
    </nav>
