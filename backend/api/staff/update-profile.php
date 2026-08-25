<?php
/*
 * POST: Update profile for the authenticated staff member.
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/staff/staff-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// The authenticated session is the only source of ownership.
$staffId = requireApiAuth();

$input = getJsonInput();
validateAllowedInputFields($input, ['full_name', 'email', 'phone_number']);

try {
    $updates = [];

    if (array_key_exists('full_name', $input)) {
        $updates['full_name'] = validateStaffName($input['full_name']);
    }

    if (array_key_exists('email', $input)) {
        $updates['email'] = validateStaffEmail($input['email']);
    }

    if (array_key_exists('phone_number', $input)) {
        $updates['phone_number'] = validateStaffPhone($input['phone_number']);
    }

    $result = updateOwnStaffProfile($pdo, $staffId, $updates);

    if ($result['status'] === 'not_found') {
        errorResponse('User not found', 404);
    }

    if ($result['status'] === 'duplicate_email') {
        errorResponse('Email already in use by another staff member.', 409);
    }

    if ($result['status'] === 'no_fields') {
        errorResponse('No fields to update.', 422);
    }

    // Update session with the same normalized name persisted by the service.
    if (array_key_exists('full_name', $updates)) {
        $_SESSION['user_name'] = $updates['full_name'];
    }
    if (array_key_exists('email', $updates)) {
        $_SESSION['user_email'] = $updates['email'];
    }

    successResponse($result['profile'], 'Profile updated successfully');

} catch (PDOException $e) {
    if (isStaffDuplicateKeyViolation($e)) {
        errorResponse('Email already in use by another staff member.', 409);
    }

    internalErrorResponse('profile update database operation');
} catch (Throwable $e) {
    internalErrorResponse('profile update database operation');
}
