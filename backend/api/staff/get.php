<?php
/*
 * GET: Retrieve single staff member by ID.
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/staff/staff-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$staffId = requireApiAuth();
$role = $_SESSION['user_role'] ?? null;

if (!array_key_exists('id', $_GET)) {
    errorResponse('Staff ID is required.', 422);
}

$requestedStaffId = validateStaffIdentifier($_GET['id']);

try {
    $staff = getStaffById($pdo, $staffId, $role, $requestedStaffId);

    if ($staff === null) {
        errorResponse('Staff member not found', 404);
    }

    successResponse($staff);

} catch (Throwable $e) {
    internalErrorResponse('staff lookup database operation');
}
