<?php
/*
 * GET: Revalidate the server-side session and return safe UI identity data.
 */

require_once __DIR__ . '/../bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireApiAuth();

successResponse([
    'authenticated' => true,
    'user' => [
        'staff_id' => (string) ($_SESSION['user_id'] ?? ''),
        'full_name' => (string) ($_SESSION['user_name'] ?? ''),
        'email' => (string) ($_SESSION['user_email'] ?? ''),
        'role' => (string) ($_SESSION['user_role'] ?? '')
    ]
]);
