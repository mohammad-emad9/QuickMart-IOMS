<?php
/*
 * GET: Retrieve single product by ID.
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/products/product-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireApiAuth();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    errorResponse('Product ID is required');
}

$productId = validateIdentifier($_GET['id'], 'Product ID');

try {
    $product = getProductById($pdo, $productId);

    if (!$product) {
        errorResponse('Product not found', 404);
    }

    successResponse($product);

} catch (Throwable $e) {
    internalErrorResponse('product lookup database operation');
}
