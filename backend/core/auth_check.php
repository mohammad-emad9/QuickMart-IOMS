<?php
/**
 * QuickMart IOMS - Authentication Guard
 * Include this file at the top of every protected page
 * Redirects to login if user is not authenticated
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is authenticated
 * @return bool True if authenticated, false otherwise
 */
function isAuthenticated()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user's ID
 * @return string|null User ID or null if not logged in
 */
function getCurrentUserId()
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user's name
 * @return string User name or 'Guest'
 */
function getCurrentUserName()
{
    return $_SESSION['user_name'] ?? 'Guest';
}

/**
 * Get current user's role
 * @return string User role or 'Staff'
 */
function getCurrentUserRole()
{
    return $_SESSION['user_role'] ?? 'Staff';
}

/**
 * Check if current user is an Admin
 * @return bool True if admin
 */
function isAdmin()
{
    return getCurrentUserRole() === 'Admin';
}

/**
 * Require authentication - redirect to login if not authenticated
 * Call this at the top of every protected page
 */
function requireAuth()
{
    if (!isAuthenticated()) {
        // Clear any stale session data
        session_unset();
        session_destroy();

        // Redirect to login page
        header('Location: /QuickMart code/assets/login-signup/login.html');
        exit;
    }
}

/**
 * Require Admin role - show 403 error if not admin
 */
function requireAdmin()
{
    requireAuth();
    if (!isAdmin()) {
        http_response_code(403);
        die('<h1>403 Forbidden</h1><p>You do not have permission to access this page.</p><a href="dashboard.php">Back to Dashboard</a>');
    }
}

// Auto-check authentication when this file is included
// Comment out the line below if you want to manually call requireAuth()
requireAuth();
