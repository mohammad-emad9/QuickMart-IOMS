<?php
/*
 * GET: List orders with optional filters.
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/orders/order-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

$staffId = requireApiAuth();
$role = $_SESSION['user_role'] ?? null;
if (!isOrderManagementRole($role)) {
    errorResponse('Access denied.', 403);
}
$isAdmin = $role === 'Admin';

try {
    $orderType = null;
    $requestedStaffId = null;
    $dateFrom = null;
    $dateTo = null;

    if (isset($_GET['type']) && $_GET['type'] !== '') {
        $orderType = $_GET['type'];
    }

    // Admins may filter by staff. Staff filters are ignored; their result set
    // is always constrained to the authenticated session user below.
    if ($isAdmin && isset($_GET['staff_id']) && !empty($_GET['staff_id'])) {
        $requestedStaffId = validateIdentifier($_GET['staff_id'], 'staff_id');
    }

    if (isset($_GET['date_from']) && $_GET['date_from'] !== '') {
        $dateFrom = $_GET['date_from'];
    }
    if (isset($_GET['date_to']) && $_GET['date_to'] !== '') {
        $dateTo = $_GET['date_to'];
    }

    $orderList = getOrderList($pdo, $staffId, $role, $orderType, $requestedStaffId, $dateFrom, $dateTo);

    successResponse($orderList);

} catch (InvalidArgumentException $e) {
    errorResponse($e->getMessage(), 422);
} catch (Throwable $e) {
    internalErrorResponse('order list database operation');
}
