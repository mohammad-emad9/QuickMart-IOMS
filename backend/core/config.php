<?php
/**
 * QuickMart IOMS - Application Configuration
 */

/**
 * Read an application value from the process environment.
 *
 * The project intentionally does not load a .env file. Configure these
 * values through Apache SetEnv, the Windows environment, or the PHP runtime.
 */
function quickmartEnv($key, $default = null, $trim = true)
{
    $value = getenv($key);

    if ($value === false && isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }

    if ($value === false && isset($_SERVER[$key])) {
        $value = $_SERVER[$key];
    }

    if ($value === false) {
        return $default;
    }

    return $trim ? trim((string) $value) : (string) $value;
}

/**
 * Stop safely when required application configuration is unavailable.
 */
function configurationError()
{
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Application configuration is unavailable.'
    ]);
    exit;
}

// Harden the PHP session while preserving local HTTP development.
ini_set('session.use_only_cookies', '1');
ini_set('session.use_strict_mode', '1');

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443');
    $currentCookieParams = session_get_cookie_params();

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $currentCookieParams['path'] ?: '/',
        'domain' => $currentCookieParams['domain'] ?? '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Riyadh');

// Same-origin is the default. A specific origin may be configured for an
// explicitly approved deployment; wildcard origins are never accepted.
$allowedOrigin = quickmartEnv('QUICKMART_ALLOWED_ORIGIN', '');
$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestScheme = (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
$serverHost = $_SERVER['SERVER_NAME'] ?? ($_SERVER['HTTP_HOST'] ?? '');
$serverPort = (string) ($_SERVER['SERVER_PORT'] ?? '');
$isDefaultPort = ($requestScheme === 'http' && $serverPort === '80')
    || ($requestScheme === 'https' && $serverPort === '443');
$serverOrigin = $requestScheme . '://' . $serverHost . ($serverPort !== '' && !$isDefaultPort ? ':' . $serverPort : '');
$isSameOrigin = $requestOrigin !== '' && $serverOrigin !== '' && hash_equals($serverOrigin, $requestOrigin);

if ($requestOrigin !== '') {
    $isConfiguredOrigin = $allowedOrigin !== '' && hash_equals(rtrim($allowedOrigin, '/'), $requestOrigin);

    if (!$isSameOrigin && !$isConfiguredOrigin) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Origin not allowed.'
        ]);
        exit;
    }

    if ($isConfiguredOrigin) {
        header('Access-Control-Allow-Origin: ' . rtrim($allowedOrigin, '/'));
        header('Access-Control-Allow-Credentials: true');
        header('Vary: Origin');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
    }
}

// Handle preflight OPTIONS request
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
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

define('MAX_PRODUCT_QUANTITY', 1000000);
define('MAX_ORDER_QUANTITY', 1000000);
define('MAX_PRICE', 99999999.99);
