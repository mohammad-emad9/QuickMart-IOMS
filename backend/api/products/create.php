<?php
/*
 * POST: Add new product (Admin only).
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/products/product-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

requireApiRole('Admin');

$input = getJsonInput();

validateRequired($input, ['name', 'category', 'quantity', 'price']);

$name = validateBoundedText($input['name'], 'Product name', 100);
$category = validateBoundedText($input['category'], 'Category', 50);
$quantity = validateIntegerValue($input['quantity'], 'Quantity', 0, MAX_PRODUCT_QUANTITY);
$price = validateMoneyValue($input['price']);
$threshold = isset($input['threshold'])
    ? validateIntegerValue($input['threshold'], 'Threshold', 0, MAX_PRODUCT_QUANTITY)
    : 20;

try {
    $product = createProduct($pdo, $name, $category, $quantity, $price, $threshold);

    successResponse($product, 'Product created successfully');

} catch (Throwable $e) {
    internalErrorResponse('product creation database operation');
}
