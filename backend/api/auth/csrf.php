<?php
/**
 * QuickMart IOMS - CSRF Token API
 * GET: Return the session-bound token for an authenticated browser session
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/helpers.php';

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireApiAuth();

successResponse([
    'csrf_token' => getCsrfToken()
]);
