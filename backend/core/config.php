<?php
/**
 * QuickMart IOMS - Application Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Riyadh');

// CORS Headers - Allow frontend requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Application constants
define('APP_NAME', 'QuickMart IOMS');
define('APP_VERSION', '1.0.0');

// Status constants for products
define('STATUS_NORMAL', 'Normal');
define('STATUS_LOW_STOCK', 'Low Stock');
define('STATUS_OUT_OF_STOCK', 'Out of Stock');
