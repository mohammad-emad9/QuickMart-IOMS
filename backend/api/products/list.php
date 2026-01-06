<?php
/**
 * QuickMart IOMS - List Products API
 * GET: Retrieve all products with optional filters
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

try {
    // Build query with optional filters
    $where = [];
    $params = [];

    // Filter by category
    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $where[] = "Category = ?";
        $params[] = $_GET['category'];
    }

    // Filter by status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where[] = "Status = ?";
        $params[] = $_GET['status'];
    }

    // Search by name
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $where[] = "Name LIKE ?";
        $params[] = '%' . $_GET['search'] . '%';
    }

    // Build SQL query
    $sql = "SELECT * FROM Products";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $sql .= " ORDER BY Name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Get unique categories for filter dropdown
    $categoriesStmt = $pdo->query("SELECT DISTINCT Category FROM Products ORDER BY Category");
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_COLUMN);

    successResponse([
        'products' => $products,
        'categories' => $categories,
        'total' => count($products)
    ]);

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
