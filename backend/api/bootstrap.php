<?php
/*
 * API bootstrap: common includes and response headers.
 */

if (basename($_SERVER['PHP_SELF']) === 'bootstrap.php') {
    http_response_code(403);
    exit;
}

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../core/db.php';

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: same-origin');
