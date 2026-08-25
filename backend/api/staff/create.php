<?php
/**
 * QuickMart IOMS - Create Staff API
 * POST: Create a staff member (Admin only)
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/staff/staff-service.php';

// Only accept POST requests.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

requireApiRole('Admin');

$input = getJsonInput();
validateAllowedInputFields($input, [
    'full_name',
    'email',
    'phone_number',
    'role',
    'password'
]);

foreach (['full_name', 'email', 'phone_number', 'role', 'password'] as $field) {
    if (!array_key_exists($field, $input)) {
        errorResponse('Missing required field: ' . $field . '.', 422);
    }
}

$fullName = validateStaffName($input['full_name']);
$email = validateStaffEmail($input['email']);
$phoneNumber = validateStaffPhone($input['phone_number']);
$role = validateStaffRoleValue($input['role']);
$password = validateStaffPassword($input['password']);

try {
    $result = createStaffByAdmin(
        $pdo,
        $fullName,
        $email,
        $phoneNumber,
        $role,
        $password
    );

    if ($result['status'] === 'duplicate_email') {
        errorResponse('Email already in use by another staff member.', 409);
    }

    if ($result['status'] !== 'created' || !isset($result['staff'])) {
        internalErrorResponse('staff creation result');
    }

    // The project establishes a 200 success envelope through successResponse.
    // The response contains no password or authentication internals.
    successResponse($result['staff'], 'Staff member created successfully');
} catch (PDOException $exception) {
    if (isStaffDuplicateKeyViolation($exception)) {
        errorResponse('Email already in use by another staff member.', 409);
    }

    internalErrorResponse('staff creation database operation');
} catch (Throwable $exception) {
    internalErrorResponse('staff creation database operation');
}
