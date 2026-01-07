<?php
/**
 * QuickMart IOMS - Logout API
 * POST/GET: Destroy user session
 */

// Only need config for session (not full bootstrap)
require_once __DIR__ . '/../../core/config.php';

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

// Return success
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
