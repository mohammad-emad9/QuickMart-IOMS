<?php
/**
 * QuickMart IOMS - Get Product API
 * GET: Retrieve single product by ID
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

// Validate product ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    errorResponse('Product ID is required');
}

$productId = sanitize($_GET['id']);

try {
    $stmt = $pdo->prepare("SELECT * FROM Products WHERE Product_ID = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        errorResponse('Product not found', 404);
    }

    successResponse($product);

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
