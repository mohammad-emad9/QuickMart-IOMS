<?php
/**
 * QuickMart IOMS - Authentication Guard
 * Include this file at the top of every protected page
 * Redirects to login if user is not authenticated
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../infrastructure/database/auth-repository.php';
require_once __DIR__ . '/../infrastructure/database/staff-repository.php';

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
 * Refresh the protected page session from the current Staff row.
 * Missing or invalid accounts fail closed and are handled by requireAuth().
 */
function refreshProtectedPageSession()
{
    $sessionStaffId = $_SESSION['user_id'] ?? null;
    $sessionRevision = $_SESSION['auth_revision'] ?? null;

    if (!is_string($sessionStaffId)
        || !is_int($sessionRevision)
        || $sessionRevision < 1) {
        return false;
    }

    $sessionStaffId = trim($sessionStaffId);
    if ($sessionStaffId === ''
        || strlen($sessionStaffId) > 20
        || !preg_match('/^[A-Za-z0-9_-]+$/', $sessionStaffId)) {
        return false;
    }

    try {
        require_once __DIR__ . '/db.php';
        if (!isset($pdo) || !$pdo instanceof PDO) {
            return false;
        }

        $staff = findStaffAuthContextWithRevision($pdo, $sessionStaffId);
        if ($staff === false
            || !isSupportedStaffRole($staff['Role'] ?? null)
            || !is_int($staff['Auth_Revision'] ?? null)
            || $staff['Auth_Revision'] < 1
            || $staff['Auth_Revision'] !== $sessionRevision) {
            return false;
        }

        $_SESSION['user_id'] = (string) $staff['Staff_ID'];
        $_SESSION['user_name'] = $staff['Full_Name'];
        $_SESSION['user_email'] = $staff['Email'];
        $_SESSION['user_role'] = $staff['Role'];
        $_SESSION['auth_revision'] = $staff['Auth_Revision'];
        return true;
    } catch (Throwable $e) {
        error_log('QuickMart internal error: page authentication revalidation');
        return false;
    }
}

/**
 * Clear stale page-session data before redirecting to login.
 */
function clearProtectedPageSession()
{
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
}

/**
 * Require authentication - redirect to login if not authenticated
 * Call this at the top of every protected page
 */
function requireAuth()
{
    if (!isAuthenticated() || !refreshProtectedPageSession()) {
        clearProtectedPageSession();

        // Redirect to login page
        header('Location: ../../assets/login-signup/login.html');
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
