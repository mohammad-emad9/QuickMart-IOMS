<?php
/**
 * QuickMart IOMS - Logout API
 * POST: Destroy user session
 */

// Only need config for session (not full bootstrap)
require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/helpers.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Authenticated logout is a state-changing request and therefore requires CSRF.
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    requireCsrfToken();
}

// Destroy session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

successResponse(null, 'Logged out successfully');
