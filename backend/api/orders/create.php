<?php
/**
 * QuickMart IOMS - Create Order API
 * POST: Create new order with details
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Get input data
$input = getJsonInput();

// Validate required fields
validateRequired($input, ['staff_id', 'order_type', 'items']);

$staffId = sanitize($input['staff_id']);
$orderType = sanitize($input['order_type']);
$partyName = isset($input['party_name']) ? sanitize($input['party_name']) : null;
$items = $input['items'];

// Validate order type
if (!in_array($orderType, ['Sell', 'Purchase'])) {
    errorResponse('Order type must be "Sell" or "Purchase"');
}

// Validate items
if (!is_array($items) || empty($items)) {
    errorResponse('At least one item is required');
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Verify staff exists
    $staffCheck = $pdo->prepare("SELECT Staff_ID FROM Staff WHERE Staff_ID = ?");
    $staffCheck->execute([$staffId]);
    if (!$staffCheck->fetch()) {
        throw new Exception('Staff not found');
    }

    // Generate Order ID
    $orderId = generateId('ORD', $pdo, 'Orders', 'Order_ID');

    // Insert order
    $stmt = $pdo->prepare("
        INSERT INTO Orders (Order_ID, Staff_ID, Order_Type, Party_Name)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$orderId, $staffId, $orderType, $partyName]);

    // Process each item
    $totalAmount = 0;
    $processedItems = [];

    foreach ($items as $item) {
        if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
            throw new Exception('Each item must have product_id, quantity, and price');
        }

        $productId = sanitize($item['product_id']);
        $quantity = intval($item['quantity']);
        $price = floatval($item['price']);

        if ($quantity <= 0) {
            throw new Exception('Quantity must be greater than 0');
        }

        // Verify product exists and LOCK the row for update (prevents race condition)
        $productCheck = $pdo->prepare("SELECT Product_ID, Name, Quantity, Threshold FROM Products WHERE Product_ID = ? FOR UPDATE");
        $productCheck->execute([$productId]);
        $product = $productCheck->fetch();

        if (!$product) {
            throw new Exception("Product $productId not found");
        }

        // Calculate new quantity
        if ($orderType === 'Sell') {
            $newQty = $product['Quantity'] - $quantity;

            // CRITICAL: Prevent negative stock
            if ($newQty < 0) {
                throw new Exception("Insufficient stock for {$product['Name']}. Available: {$product['Quantity']}, Requested: {$quantity}");
            }
        } else {
            // Purchase order - add to stock
            $newQty = $product['Quantity'] + $quantity;
        }

        // Insert order detail
        $detailStmt = $pdo->prepare("
            INSERT INTO Order_Details (Order_ID, Product_ID, Ordered_Qty, Sold_Price)
            VALUES (?, ?, ?, ?)
        ");
        $detailStmt->execute([$orderId, $productId, $quantity, $price]);

        // Update product quantity with safe check
        $updateQty = $pdo->prepare("UPDATE Products SET Quantity = ? WHERE Product_ID = ? AND (? >= 0 OR ? = 'Purchase')");
        $updateQty->execute([$newQty, $productId, $newQty, $orderType]);

        // Verify the update was successful
        if ($updateQty->rowCount() === 0) {
            throw new Exception("Failed to update stock for {$product['Name']}. Please try again.");
        }

        // Update product status
        updateProductStatus($pdo, $productId);

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

    // Commit transaction
    $pdo->commit();

    successResponse([
        'order_id' => $orderId,
        'staff_id' => $staffId,
        'order_type' => $orderType,
        'party_name' => $partyName,
        'items' => $processedItems,
        'total_amount' => $totalAmount
    ], 'Order created successfully');

} catch (Exception $e) {
    // Rollback on error
    $pdo->rollBack();
    errorResponse($e->getMessage(), 400);
}
