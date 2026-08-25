<?php
/*
 * GET: Return session-bound CSRF token for authenticated requests.
 */

require_once __DIR__ . '/../../bootstrap.php';

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
