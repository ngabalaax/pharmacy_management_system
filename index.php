<?php
// index.php

require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Redirect to login if not logged in, otherwise to appropriate dashboard
if (!isLoggedIn()) {
    header("Location: website/index.php");
    exit();
} else {
    redirectBasedOnRole();
}
?>