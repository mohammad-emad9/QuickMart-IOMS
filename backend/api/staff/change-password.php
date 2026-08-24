<?php
/**
 * QuickMart IOMS - Change Password API
 * POST: Change user password
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/staff/staff-service.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// The authenticated session is the only source of ownership.
$staffId = requireApiAuth();

// Get input
$input = getJsonInput();
validateAllowedInputFields($input, ['current_password', 'new_password']);

// Validate required fields
if (!array_key_exists('current_password', $input)
    || !array_key_exists('new_password', $input)) {
    errorResponse('Missing required password fields.', 422);
}

$currentPassword = validateStaffPassword($input['current_password'], 'Current password', 1);
$newPassword = validateStaffPassword($input['new_password'], 'New password');


try {
    $result = changeOwnStaffPassword($pdo, $staffId, $currentPassword, $newPassword);

    if ($result['status'] === 'not_found') {
        errorResponse('Authentication required', 401);
    }

    if ($result['status'] === 'incorrect_current_password') {
        errorResponse('Current password is incorrect.', 401);
    }

    if ($result['status'] === 'invalid_password') {
        errorResponse('Password input is invalid.', 422);
    }

    if ($result['status'] === 'updated') {
        if (!isValidAuthRevision($result['auth_revision'] ?? null)) {
            internalErrorResponse('password change revision update');
        }

        // Keep the current session active after a successful own-password
        // change. Every other session becomes stale against the new revision.
        $_SESSION['auth_revision'] = $result['auth_revision'];
    }

    successResponse(['message' => 'Password changed successfully']);

} catch (Throwable $e) {
    internalErrorResponse('password change database operation');
}
