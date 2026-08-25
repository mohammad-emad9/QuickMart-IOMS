<?php
/*
 * DELETE/POST: Remove a staff account (Admin only).
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/staff/staff-service.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    errorResponse('Method not allowed', 405);
}

requireApiRole('Admin');

$staffId = null;

if (isset($_GET['id'])) {
    $staffId = validateStaffIdentifier($_GET['id']);
} else {
    $input = getJsonInput();
    validateAllowedInputFields($input, ['staff_id']);
    if (array_key_exists('staff_id', $input)) {
        $staffId = validateStaffIdentifier($input['staff_id']);
    }
}

if ($staffId === null) {
    errorResponse('Staff ID is required.', 422);
}

// Prevent admin from deleting themselves
if ($staffId === (string) $_SESSION['user_id']) {
    errorResponse('Cannot delete your own account.', 403);
}

// Prevent deleting the original admin (STF001)
if ($staffId === 'STF001') {
    errorResponse('Cannot delete the primary admin account.', 403);
}

try {
    $result = deleteStaffByAdmin($pdo, $staffId);

    if ($result['status'] === 'not_found') {
        errorResponse('Staff not found', 404);
    }

    if ($result['status'] === 'referenced') {
        errorResponse(
            'Cannot delete staff. They have ' . $result['order_count'] . ' order(s) in the system.',
            409
        );
    }

    successResponse([
        'staff_id' => $result['staff_id'],
        'name' => $result['name']
    ], 'Staff deleted successfully');

} catch (PDOException $e) {
    if (isStaffForeignKeyViolation($e)) {
        errorResponse('Cannot delete staff because referenced orders exist.', 409);
    }

    internalErrorResponse('staff deletion database operation');
} catch (Throwable $e) {
    internalErrorResponse('staff deletion database operation');
}
