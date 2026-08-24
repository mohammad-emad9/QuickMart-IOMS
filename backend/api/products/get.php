<?php
/**
 * QuickMart IOMS - Get Product API
 * GET: Retrieve single product by ID
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/products/product-service.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireApiAuth();

// Validate product ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    errorResponse('Product ID is required');
}

$productId = sanitize($_GET['id']);

try {
    $product = getProductById($pdo, $productId);

    if (!$product) {
        errorResponse('Product not found', 404);
    }

    successResponse($product);

} catch (Throwable $e) {
    internalErrorResponse('product lookup database operation');
}
