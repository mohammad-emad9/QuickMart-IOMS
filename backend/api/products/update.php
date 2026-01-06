<?php
/**
 * QuickMart IOMS - Update Product API
 * POST/PUT: Update existing product
 */

require_once __DIR__ . '/../bootstrap.php';

// Accept POST or PUT
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
    errorResponse('Method not allowed', 405);
}

// Get input data
$input = getJsonInput();

// Validate required field
if (!isset($input['product_id']) || empty($input['product_id'])) {
    errorResponse('Product ID is required');
}

$productId = sanitize($input['product_id']);

try {
    // Check if product exists
    $stmt = $pdo->prepare("SELECT * FROM Products WHERE Product_ID = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        errorResponse('Product not found', 404);
    }

    // Build update query dynamically
    $updates = [];
    $params = [];

    if (isset($input['name']) && !empty($input['name'])) {
        $updates[] = "Name = ?";
        $params[] = sanitize($input['name']);
    }

    if (isset($input['category']) && !empty($input['category'])) {
        $updates[] = "Category = ?";
        $params[] = sanitize($input['category']);
    }

    if (isset($input['quantity'])) {
        $quantity = intval($input['quantity']);
        if ($quantity < 0) {
            errorResponse('Quantity cannot be negative');
        }
        $updates[] = "Quantity = ?";
        $params[] = $quantity;
    }

    if (isset($input['price'])) {
        $price = floatval($input['price']);
        if ($price <= 0) {
            errorResponse('Price must be greater than 0');
        }
        $updates[] = "Price = ?";
        $params[] = $price;
    }

    if (isset($input['threshold'])) {
        $updates[] = "Threshold = ?";
        $params[] = intval($input['threshold']);
    }

    if (empty($updates)) {
        errorResponse('No fields to update');
    }

    // Add product ID to params
    $params[] = $productId;

    // Execute update
    $sql = "UPDATE Products SET " . implode(', ', $updates) . " WHERE Product_ID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Update status based on new quantity
    updateProductStatus($pdo, $productId);

    // Get updated product
    $stmt = $pdo->prepare("SELECT * FROM Products WHERE Product_ID = ?");
    $stmt->execute([$productId]);
    $updatedProduct = $stmt->fetch();

    successResponse($updatedProduct, 'Product updated successfully');

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
