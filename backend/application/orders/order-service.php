<?php
/**
 * QuickMart IOMS - Order Service
 * Order application operations
 */

require_once __DIR__ . '/../../infrastructure/database/order-repository.php';
require_once __DIR__ . '/../shared/transaction-retry.php';

/**
 * Return whether a session role may use the Order Management backend.
 * Admin has global visibility; all other supported roles remain owner-scoped.
 */
function isOrderManagementRole($role)
{
    return is_string($role) && in_array($role, ['Admin', 'Manager', 'Staff'], true);
}

/**
 * Reject missing or unsupported order access context before querying data.
 */
function assertOrderAccessContext($staffId, $role)
{
    if (!is_string($staffId) || trim($staffId) === '' || !isOrderManagementRole($role)) {
        throw new RuntimeException('Order access context is invalid.');
    }
}

/**
 * Validate an order type at the service boundary as well as at the API.
 */
function validateOrderTypeValue($orderType)
{
    if (!is_string($orderType) || !in_array($orderType, ['Sell', 'Purchase'], true)) {
        throw new InvalidArgumentException('Order type must be "Sell" or "Purchase".');
    }

    return $orderType;
}

/**
 * Parse a strict calendar date supplied to an order list filter.
 *
 * @return DateTimeImmutable|null
 */
function parseOrderDateFilter($value, $fieldName)
{
    if ($value === null) {
        return null;
    }

    if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $value)) {
        throw new InvalidArgumentException($fieldName . ' must use YYYY-MM-DD format.');
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    $errors = DateTimeImmutable::getLastErrors();
    if ($date === false || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
        throw new InvalidArgumentException($fieldName . ' must be a valid calendar date.');
    }

    return $date;
}

/**
 * Convert inclusive date filters into sargable datetime bounds.
 *
 * @return array{0: string|null, 1: string|null}
 */
function normalizeOrderDateBounds($dateFrom, $dateTo)
{
    $from = parseOrderDateFilter($dateFrom, 'date_from');
    $to = parseOrderDateFilter($dateTo, 'date_to');

    if ($from !== null && $to !== null && $from > $to) {
        throw new InvalidArgumentException('date_from must not be after date_to.');
    }

    $fromBound = $from === null ? null : $from->format('Y-m-d 00:00:00');
    $toBound = $to === null ? null : $to->modify('+1 day')->format('Y-m-d 00:00:00');

    return [$fromBound, $toBound];
}

/**
 * Validate the service-level order creation invariant that the API also checks.
 */
function validateOrderCreationItems(array $items)
{
    if (empty($items)) {
        throw new InvalidArgumentException('At least one item is required.');
    }

    foreach ($items as $item) {
        if (!is_array($item) || !array_key_exists('product_id', $item) || !array_key_exists('quantity', $item)) {
            throw new InvalidArgumentException('Each item must have product_id and quantity.');
        }

        $productId = $item['product_id'];
        if (!is_string($productId)
            || $productId === ''
            || strlen($productId) > 20
            || !preg_match('/^[A-Za-z0-9_-]+$/', $productId)) {
            throw new InvalidArgumentException('Product ID is invalid.');
        }

        $quantity = $item['quantity'];
        if (!is_int($quantity) || $quantity < 1 || $quantity > MAX_ORDER_QUANTITY) {
            throw new InvalidArgumentException('Quantity is outside the allowed range.');
        }
    }
}

/**
 * Marker exception for a concurrent Order_ID insert race.
 */
class OrderIdConflictException extends RuntimeException
{
}

/**
 * Recognize the MariaDB duplicate-key error for the order header insert.
 */
function isOrderIdDuplicateKeyViolation(PDOException $exception)
{
    $errorInfo = $exception->errorInfo;

    return ($errorInfo[0] ?? $exception->getCode()) === '23000'
        && (int) ($errorInfo[1] ?? 0) === 1062;
}

/**
 * Retrieve the order list while enforcing Admin/Staff ownership rules.
 *
 * @return array
 */
function getOrderList(PDO $pdo, $staffId, $role, $orderType, $requestedStaffId, $dateFrom, $dateTo)
{
    assertOrderAccessContext($staffId, $role);

    if ($orderType !== null) {
        $orderType = validateOrderTypeValue($orderType);
    }

    [$dateFromBound, $dateToBound] = normalizeOrderDateBounds($dateFrom, $dateTo);
    $effectiveStaffId = $role === 'Admin' ? $requestedStaffId : $staffId;
    $orders = findOrderList($pdo, $orderType, $effectiveStaffId, $dateFromBound, $dateToBound);

    return [
        'orders' => $orders,
        'total' => count($orders)
    ];
}

/**
 * Retrieve one order and its details while enforcing ownership rules.
 *
 * @return array|null
 */
function getOrderDetails(PDO $pdo, $orderId, $staffId, $role)
{
    assertOrderAccessContext($staffId, $role);

    $effectiveStaffId = $role === 'Admin' ? null : $staffId;
    $order = findOrderHeader($pdo, $orderId, $effectiveStaffId);

    if (!$order) {
        return null;
    }

    $details = findOrderDetails($pdo, $orderId);
    $total = array_reduce($details, function ($sum, $item) {
        return $sum + $item['Line_Total'];
    }, 0);

    return [
        'order' => $order,
        'details' => $details,
        'total_amount' => $total,
        'item_count' => count($details)
    ];
}

/**
 * Create an order and update its product stock within one transaction.
 *
 * @return array
 */
function createOrder(PDO $pdo, $staffId, $orderType, $partyName, array $items)
{
    validateOrderTypeValue($orderType);
    validateOrderCreationItems($items);

    $maxAttempts = 3;

    for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
        try {
            return createOrderAttempt($pdo, $staffId, $orderType, $partyName, $items);
        } catch (Throwable $e) {
            $isOrderIdConflict = $e instanceof OrderIdConflictException;
            if ($attempt === $maxAttempts || (!$isOrderIdConflict && !isRetryableTransactionFailure($e))) {
                throw $e;
            }

            backoffBeforeTransactionRetry($attempt);
        }
    }
}

/**
 * Execute one complete order creation transaction.
 *
 * @return array
 */
function createOrderAttempt(PDO $pdo, $staffId, $orderType, $partyName, array $items)
{
    $pdo->beginTransaction();

    try {
        if (!findStaffForOrderCreation($pdo, $staffId)) {
            throw new Exception('Staff not found');
        }

        $orderId = generateOrderId($pdo);
        try {
            insertOrderHeader($pdo, $orderId, $staffId, $orderType, $partyName);
        } catch (PDOException $e) {
            if (isOrderIdDuplicateKeyViolation($e)) {
                throw new OrderIdConflictException('Order identifier was allocated concurrently.', 0, $e);
            }

            throw $e;
        }

        $totalAmount = 0;
        $processedItems = [];

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $quantity = $item['quantity'];
            $product = findLockedProductForOrder($pdo, $productId);

            if (!$product) {
                throw new Exception("Product $productId not found");
            }

            if ($orderType === 'Sell') {
                $newQty = $product['Quantity'] - $quantity;

                if ($newQty < 0) {
                    throw new Exception("Insufficient stock for {$product['Name']}. Available: {$product['Quantity']}, Requested: {$quantity}");
                }
            } else {
                $newQty = $product['Quantity'] + $quantity;
            }

            if ($newQty > MAX_PRODUCT_QUANTITY) {
                throw new Exception('Resulting stock quantity is outside the allowed range.');
            }

            $rawProductPrice = trim((string) $product['Price']);
            if (!preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/', $rawProductPrice)) {
                throw new Exception('Product price is invalid.');
            }

            $price = (float) $rawProductPrice;
            if (!is_finite($price) || $price <= 0 || $price > MAX_PRICE) {
                throw new Exception('Product price is invalid.');
            }
            $price = round($price, 2);

            insertOrderDetail($pdo, $orderId, $productId, $quantity, $price);

            $updatedRows = updateProductQuantityForOrder($pdo, $newQty, $productId, $orderType);
            if ($updatedRows === 0) {
                throw new Exception("Failed to update stock for {$product['Name']}. Please try again.");
            }

            $status = STATUS_NORMAL;
            if ($newQty <= 0) {
                $status = STATUS_OUT_OF_STOCK;
            } elseif ($newQty <= $product['Threshold']) {
                $status = STATUS_LOW_STOCK;
            }
            persistProductStatusForOrder($pdo, $productId, $status);

            $lineTotal = $quantity * $price;
            $totalAmount += $lineTotal;
            $processedItems[] = [
                'product_id' => $productId,
                'product_name' => $product['Name'],
                'quantity' => $quantity,
                'price' => $price,
                'line_total' => $lineTotal
            ];
        }

        $pdo->commit();

        return [
            'order_id' => $orderId,
            'staff_id' => $staffId,
            'order_type' => $orderType,
            'party_name' => $partyName,
            'items' => $processedItems,
            'total_amount' => $totalAmount
        ];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        throw $e;
    }
}
