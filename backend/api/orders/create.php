<?php
/*
 * POST: Create a new order with line items.
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/orders/order-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

$staffId = requireApiAuth();
$role = $_SESSION['user_role'] ?? null;
if (!isOrderManagementRole($role)) {
    errorResponse('Access denied.', 403);
}

$input = getJsonInput();

validateRequired($input, ['order_type', 'items']);

$orderType = $input['order_type'];
if (!is_string($orderType)) {
    errorResponse('Order type must be "Sell" or "Purchase"', 422);
}
$orderType = trim($orderType);
$partyName = null;
if (array_key_exists('party_name', $input) && $input['party_name'] !== null) {
    if (!is_scalar($input['party_name']) || is_bool($input['party_name'])) {
        errorResponse('Party name is invalid.', 422);
    }

    $partyName = validateBoundedText((string) $input['party_name'], 'Party name', 100, true);
}
$items = $input['items'];

if (!in_array($orderType, ['Sell', 'Purchase'], true)) {
    errorResponse('Order type must be "Sell" or "Purchase"', 422);
}

if (!is_array($items) || empty($items)) {
    errorResponse('At least one item is required');
}

// Validate the complete request before opening a transaction. The browser's
// price field is intentionally ignored; the database price is authoritative.
$validatedItems = [];
foreach ($items as $item) {
    if (!is_array($item) || !isset($item['product_id']) || !isset($item['quantity'])) {
        errorResponse('Each item must have product_id and quantity.', 422);
    }

    $validatedItems[] = [
        'product_id' => validateIdentifier($item['product_id'], 'Product ID'),
        'quantity' => validateIntegerValue($item['quantity'], 'Quantity', 1, MAX_ORDER_QUANTITY)
    ];
}

try {
    $order = createOrder($pdo, $staffId, $orderType, $partyName, $validatedItems);

    successResponse($order, 'Order created successfully');

} catch (PDOException $e) {
    internalErrorResponse('order creation database operation');
} catch (Exception $e) {
    error_log('QuickMart order creation validation failed.');
    errorResponse('Order could not be created. Please review the order and try again.', 422);
}
