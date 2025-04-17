<?php
// includes/header.php

require_once 'auth.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Hanan Pharmacy'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Header Styles */
        header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 1000;
        }

        header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #fff;
        }

        .user-welcome {
            display: flex;
            align-items: center;
            gap: 1rem;
            position: relative;
        }

        .user-name {
            font-size: 1rem;
            font-weight: 500;
            color: #ecf0f1;
        }

        .user-role {
            font-size: 0.85rem;
            background: #3498db;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
        }

        .navbar {
            display: flex;
            gap: 1.5rem;
        }

        .navbar a {
            color: #ecf0f1;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .navbar a:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .navbar a i {
            font-size: 0.9rem;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding: 1rem;
            }

            .user-welcome {
                width: 100%;
                justify-content: space-between;
                padding-bottom: 1rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .navbar {
                width: 100%;
                justify-content: flex-end;
                gap: 1rem;
            }
        }

        @media (max-width: 480px) {
            header h1 {
                font-size: 1.2rem;
            }

            .navbar {
                gap: 0.5rem;
            }

            .navbar a {
                padding: 0.5rem;
                font-size: 0.8rem;
            }

            .navbar a span {
                display: none;
            }

            .navbar a i {
                font-size: 1rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <h1>Hanan Pharmacy Management System</h1>
    
        <nav class="navbar">
            <a href="../users/profile.php"><i class="fas fa-user"></i> <span>Profile</span></a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </nav>
    </header>