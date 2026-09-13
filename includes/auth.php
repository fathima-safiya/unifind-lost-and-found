<?php
// Authentication and Authorization Helper Functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the current user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Redirect to login if not logged in
function requireLogin() {
    global $base_url;
    if (!isLoggedIn()) {
        header("Location: $base_url/login.php?error=login_required");
        exit();
    }
}

// Redirect to dashboard if already logged in (for login/register pages)
function requireGuest() {
    global $base_url;
    if (isLoggedIn()) {
        header("Location: $base_url/dashboard.php");
        exit();
    }
}

// Check if logged-in user is an admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Redirect to dashboard if user is not an admin
function requireAdmin() {
    global $base_url;
    requireLogin();
    if (!isAdmin()) {
        header("Location: $base_url/dashboard.php");
        exit();
    }
}

// Return current user data from session
function getCurrentUser() {
    if (isLoggedIn()) {
        return [
            'id'        => $_SESSION['user_id'],
            'full_name' => $_SESSION['full_name'],
            'email'     => $_SESSION['email'],
            'role'      => $_SESSION['role']
        ];
    }
    return null;
}
?>
