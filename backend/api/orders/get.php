<?php
/*
 * GET: Retrieve a single order with line items.
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

if (!isset($_GET['id']) || empty($_GET['id'])) {
    errorResponse('Order ID is required');
}

$orderId = validateIdentifier($_GET['id'], 'Order ID');

try {
    $orderDetails = getOrderDetails($pdo, $orderId, $staffId, $role);

    if ($orderDetails === null) {
        errorResponse('Order not found', 404);
    }

    successResponse($orderDetails);

} catch (Throwable $e) {
    internalErrorResponse('order lookup database operation');
}
