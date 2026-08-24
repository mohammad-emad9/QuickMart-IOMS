<?php
/**
 * QuickMart IOMS - Update Product API
 * POST/PUT: Update existing product
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/products/product-service.php';

// Accept POST or PUT
if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT'])) {
    errorResponse('Method not allowed', 405);
}

requireApiRole('Admin');

// Get input data
$input = getJsonInput();

// Validate required field
if (!isset($input['product_id']) || empty($input['product_id'])) {
    errorResponse('Product ID is required');
}

$productId = sanitize($input['product_id']);

try {
    $updates = [];

    if (isset($input['name']) && !empty($input['name'])) {
        $updates['name'] = sanitize($input['name']);
    }

    if (isset($input['category']) && !empty($input['category'])) {
        $updates['category'] = sanitize($input['category']);
    }

    if (isset($input['quantity'])) {
        $quantity = validateIntegerValue($input['quantity'], 'Quantity', 0, MAX_PRODUCT_QUANTITY);
        $updates['quantity'] = $quantity;
    }

    if (isset($input['price'])) {
        $price = validateMoneyValue($input['price']);
        $updates['price'] = $price;
    }

    if (isset($input['threshold'])) {
        $updates['threshold'] = validateIntegerValue($input['threshold'], 'Threshold', 0, MAX_PRODUCT_QUANTITY);
    }

    $updatedProduct = updateProduct($pdo, $productId, $updates);

    if ($updatedProduct === null) {
        errorResponse('Product not found', 404);
    }

    if ($updatedProduct === false) {
        errorResponse('No fields to update');
    }

    successResponse($updatedProduct, 'Product updated successfully');

} catch (Throwable $e) {
    internalErrorResponse('product update database operation');
}
