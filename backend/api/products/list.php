<?php
/**
 * QuickMart IOMS - List Products API
 * GET: Retrieve all products with optional filters
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/products/product-service.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireApiAuth();

try {
    $category = null;
    $status = null;
    $search = null;

    // Filter by category
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category = $_GET['category'];
    }

    // Filter by status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $status = $_GET['status'];
    }

    // Search by name
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $_GET['search'];
    }

    $productList = getProductList($pdo, $category, $status, $search);

    successResponse($productList);

} catch (Throwable $e) {
    internalErrorResponse('product list database operation');
}
