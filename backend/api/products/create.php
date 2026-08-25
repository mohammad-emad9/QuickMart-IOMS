<?php
/**
 * QuickMart IOMS - Create Product API
 * POST: Add new product
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/products/product-service.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

requireApiRole('Admin');

// Get input data
$input = getJsonInput();

// Validate required fields
validateRequired($input, ['name', 'category', 'quantity', 'price']);

$name = validateBoundedText($input['name'], 'Product name', 100);
$category = validateBoundedText($input['category'], 'Category', 50);
$quantity = validateIntegerValue($input['quantity'], 'Quantity', 0, MAX_PRODUCT_QUANTITY);
$price = validateMoneyValue($input['price']);
$threshold = isset($input['threshold'])
    ? validateIntegerValue($input['threshold'], 'Threshold', 0, MAX_PRODUCT_QUANTITY)
    : 20;

// Validate values
try {
    $product = createProduct($pdo, $name, $category, $quantity, $price, $threshold);

    successResponse($product, 'Product created successfully');

} catch (Throwable $e) {
    internalErrorResponse('product creation database operation');
}
