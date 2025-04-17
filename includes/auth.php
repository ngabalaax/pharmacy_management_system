<?php
// includes/auth.php
// This file contains functions to manage user authentication and authorization

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Add security headers
    header("X-Frame-Options: DENY");
    header("X-Content-Type-Options: nosniff");
}

// Database connection - Corrected path
require_once __DIR__ . '/../config/database.php';

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check user role
 */
function getUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Check if user has specific permission
 */
function hasPermission($permission) {
    if (!isLoggedIn()) {
        return false;
    }

    // Define role permissions
    $rolePermissions = [
        'manager' => [
            'view_inventory_reports',
            'view_expiry_reports',
            'manage_users',
            'manage_inventory',
            'view_all_sales'
        ],
        'pharmacist' => [
            'view_inventory_reports',
            'view_expiry_reports',
            'manage_inventory',
            'process_sales'
        ],
        'store_coordinator' => [
            'view_inventory_reports',
            'manage_inventory',
            'view_expiry_reports'
        ],
        'cashier' => [
            'process_sales',
            'view_sales_history'
        ]
    ];

    $role = getUserRole();
    return isset($rolePermissions[$role]) && in_array($permission, $rolePermissions[$role]);
}

/**
 * Redirect if not logged in
 */
function redirectIfNotLoggedIn($redirectTo = '/login.php') {
    if (!isLoggedIn()) {
        header("Location: $redirectTo");
        exit();
    }
}

/**
 * Redirect based on user role
 */
function redirectBasedOnRole() {
    if (isLoggedIn()) {
        $role = getUserRole();
        $dashboardPages = [
            'manager' => '/modules/dashboard/manager.php',
            'pharmacist' => '/modules/dashboard/pharmacist.php',
            'store_coordinator' => '/modules/dashboard/store_coordinator.php',
            'cashier' => '/modules/dashboard/cashier.php'
        ];

        if (isset($dashboardPages[$role])) {
            header("Location: " . $dashboardPages[$role]);
            exit();
        }
    }
    header("Location: /login.php");
    exit();
}

/**
 * Check access permission and redirect if not authorized
 */
function checkPermission($permission) {
    redirectIfNotLoggedIn();
    if (!hasPermission($permission)) {
        // Log the unauthorized access attempt
        error_log("Unauthorized access attempt by user ID: " . $_SESSION['user_id'] . " for permission: $permission");
        header("Location: /access_denied.php");
        exit();
    }
}

/**
 * Role-specific checkers (kept for backward compatibility)
 */
function isManager() {
    return isLoggedIn() && getUserRole() === 'manager';
}

function isPharmacist() {
    return isLoggedIn() && getUserRole() === 'pharmacist';
}

function isStoreCoordinator() {
    return isLoggedIn() && getUserRole() === 'store_coordinator';
}

function isCashier() {
    return isLoggedIn() && getUserRole() === 'cashier';
}