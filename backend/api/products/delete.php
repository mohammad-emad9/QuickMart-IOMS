<?php
/*
 * DELETE/POST: Remove product (Admin only).
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/products/product-service.php';

if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    errorResponse('Method not allowed', 405);
}

requireApiRole('Admin');

$productId = null;

if (isset($_GET['id'])) {
    $productId = validateIdentifier($_GET['id'], 'Product ID');
} else {
    $input = getJsonInput();
    if (isset($input['product_id'])) {
        $productId = validateIdentifier($input['product_id'], 'Product ID');
    }
}

if (!$productId) {
    errorResponse('Product ID is required');
}

try {
    $result = deleteProduct($pdo, $productId);

    if ($result['status'] === 'not_found') {
        errorResponse('Product not found', 404);
    }

    if ($result['status'] === 'referenced') {
        errorResponse('Cannot delete product. It is referenced in ' . $result['order_count'] . ' order(s)');
    }

    successResponse([
        'product_id' => $result['product_id'],
        'name' => $result['name']
    ], 'Product deleted successfully');

} catch (Throwable $e) {
    internalErrorResponse('product deletion database operation');
}
