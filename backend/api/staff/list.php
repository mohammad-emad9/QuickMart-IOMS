<?php
/*
 * GET: Retrieve all staff members (Admin only).
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/staff/staff-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireApiRole('Admin');

try {
    $staffList = getStaffList($pdo);

    successResponse($staffList);

} catch (Throwable $e) {
    internalErrorResponse('staff list database operation');
}
