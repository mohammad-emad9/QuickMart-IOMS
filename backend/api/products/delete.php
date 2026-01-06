<?php
/**
 * QuickMart IOMS - Delete Product API
 * DELETE/POST: Remove product
 */

require_once __DIR__ . '/../bootstrap.php';

// Accept DELETE or POST
if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    errorResponse('Method not allowed', 405);
}

// Get product ID from query string or body
$productId = null;

if (isset($_GET['id'])) {
    $productId = sanitize($_GET['id']);
} else {
    $input = getJsonInput();
    if (isset($input['product_id'])) {
        $productId = sanitize($input['product_id']);
    }
}

if (!$productId) {
    errorResponse('Product ID is required');
}

try {
    // Check if product exists
    $stmt = $pdo->prepare("SELECT Product_ID, Name FROM Products WHERE Product_ID = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        errorResponse('Product not found', 404);
    }

    // Check if product is in any orders
    $orderCheck = $pdo->prepare("SELECT COUNT(*) FROM Order_Details WHERE Product_ID = ?");
    $orderCheck->execute([$productId]);
    $orderCount = $orderCheck->fetchColumn();

    if ($orderCount > 0) {
        errorResponse('Cannot delete product. It is referenced in ' . $orderCount . ' order(s)');
    }

    // Delete product
    $stmt = $pdo->prepare("DELETE FROM Products WHERE Product_ID = ?");
    $stmt->execute([$productId]);

    successResponse([
        'product_id' => $productId,
        'name' => $product['Name']
    ], 'Product deleted successfully');

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
