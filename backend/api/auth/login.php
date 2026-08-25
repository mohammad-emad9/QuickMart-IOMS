<?php
/**
 * QuickMart IOMS - Login API
 * POST: Authenticate user with email and password
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/auth/auth-service.php';
require_once __DIR__ . '/../../application/auth/login-rate-limit-service.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Get input data
$input = getJsonInput();
validateAllowedInputFields($input, ['email', 'password']);

// Validate required fields
if (!array_key_exists('email', $input) || !array_key_exists('password', $input)) {
    errorResponse('Missing required fields: email, password', 422);
}

$email = validateLoginIdentifier($input['email']);
$password = validateStaffPassword($input['password'], 'Password', 1);

try {
    $rateLimit = reserveLoginAttempt($pdo, $email);
    if (!$rateLimit['allowed']) {
        header('Retry-After: ' . (string) $rateLimit['retry_after']);
        errorResponse('Too many sign-in attempts. Please wait and try again.', 429);
    }

    $user = authenticateStaff($pdo, $email, $password);

    if ($user === null) {
        errorResponse('Invalid email/staff ID or password', 401);
    }

    clearLoginRateLimit($pdo, $rateLimit['rate_key']);

    // Prevent session fixation after successful authentication.
    session_regenerate_id(true);

    // Create session
    $_SESSION['user_id'] = $user['staff_id'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['auth_revision'] = $user['auth_revision'];

    // Return success with user data (exclude password)
    successResponse([
        'staff_id' => $user['staff_id'],
        'full_name' => $user['full_name'],
        'email' => $user['email'],
        'role' => $user['role']
    ], 'Login successful');

} catch (Throwable $e) {
    internalErrorResponse('login database operation');
}
