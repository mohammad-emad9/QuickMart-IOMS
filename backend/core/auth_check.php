<?php
/**
 * Authentication guard for protected page views.
 * Redirects unauthenticated requests to the login page.
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
        renderForbiddenPage();
    }
}

/**
 * Render a shared, styled HTML authorization state for page requests. API
 * callers use helpers.php and keep their JSON error envelope instead.
 */
function renderAuthorizationPage($statusCode, $title, $message, $actionHref, $actionLabel)
{
    http_response_code((int) $statusCode);
    header('Content-Type: text/html; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: same-origin');

    require __DIR__ . '/../views/partials/access-state.php';
    exit;
}

function renderForbiddenPage()
{
    renderAuthorizationPage(
        403,
        'Access restricted',
        'Your account does not have permission to open this workspace.',
        'dashboard.php',
        'Return to Dashboard'
    );
}

// Automatically enforce authentication when included by a view.
requireAuth();
