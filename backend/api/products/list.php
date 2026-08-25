<?php
/*
 * GET: List products with optional filters.
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/products/product-service.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireApiAuth();

try {
    $category = null;
    $status = null;
    $search = null;

    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category = $_GET['category'];
    }

    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $status = $_GET['status'];
    }

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $_GET['search'];
    }

    $productList = getProductList($pdo, $category, $status, $search);

    successResponse($productList);

} catch (Throwable $e) {
    internalErrorResponse('product list database operation');
}
