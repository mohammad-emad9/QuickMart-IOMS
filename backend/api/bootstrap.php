<?php
/**
 * API Bootstrap - Common includes for all endpoints
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF']) === 'bootstrap.php') {
    http_response_code(403);
    exit;
}

// Core includes
require_once __DIR__ . '/../core/config.php';
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/helpers.php';

// API Headers
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
