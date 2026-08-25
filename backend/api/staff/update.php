<?php
/*
 * POST: Update staff member details (Admin only).
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/staff/staff-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

requireApiRole('Admin');

$input = getJsonInput();
validateAllowedInputFields($input, ['staff_id', 'full_name', 'email', 'phone_number', 'role', 'password']);

if (!array_key_exists('staff_id', $input)) {
    errorResponse('Staff ID is required.', 422);
}

$staffId = validateStaffIdentifier($input['staff_id']);

// Prevent admin from modifying themselves through this endpoint
if ($staffId === (string) $_SESSION['user_id']) {
    errorResponse('Cannot modify your own account through this endpoint.', 403);
}

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

    if (array_key_exists('role', $input)) {
        $updates['role'] = validateStaffRoleValue($input['role']);
    }

    if (array_key_exists('password', $input)) {
        $updates['password'] = validateStaffPassword($input['password']);
    }

    $result = updateStaffByAdmin($pdo, $staffId, $updates);

    if ($result['status'] === 'not_found') {
        errorResponse('Staff not found', 404);
    }

    if ($result['status'] === 'duplicate_email') {
        errorResponse('Email already in use by another staff member.', 409);
    }

    if ($result['status'] === 'no_fields') {
        errorResponse('No fields to update.', 422);
    }

    successResponse($result['staff'], 'Staff updated successfully');

} catch (PDOException $e) {
    if (isStaffDuplicateKeyViolation($e)) {
        errorResponse('Email already in use by another staff member.', 409);
    }

    internalErrorResponse('staff update database operation');
} catch (Throwable $e) {
    internalErrorResponse('staff update database operation');
}
