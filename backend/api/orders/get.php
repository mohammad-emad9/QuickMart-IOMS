<?php
/**
 * QuickMart IOMS - Get Order API
 * GET: Retrieve single order with its details
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

// Validate order ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    errorResponse('Order ID is required');
}

$orderId = sanitize($_GET['id']);

try {
    // Get order with staff info
    $stmt = $pdo->prepare("
        SELECT 
            o.*,
            s.Full_Name as Staff_Name,
            s.Email as Staff_Email
        FROM Orders o
        LEFT JOIN Staff s ON o.Staff_ID = s.Staff_ID
        WHERE o.Order_ID = ?
    ");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();

    if (!$order) {
        errorResponse('Order not found', 404);
    }

    // Get order details with product info
    $detailsStmt = $pdo->prepare("
        SELECT 
            od.Detail_ID,
            od.Product_ID,
            p.Name as Product_Name,
            p.Category as Product_Category,
            od.Ordered_Qty,
            od.Sold_Price,
            (od.Ordered_Qty * od.Sold_Price) as Line_Total
        FROM Order_Details od
        LEFT JOIN Products p ON od.Product_ID = p.Product_ID
        WHERE od.Order_ID = ?
    ");
    $detailsStmt->execute([$orderId]);
    $details = $detailsStmt->fetchAll();

    // Calculate total
    $total = array_reduce($details, function ($sum, $item) {
        return $sum + $item['Line_Total'];
    }, 0);

    successResponse([
        'order' => $order,
        'details' => $details,
        'total_amount' => $total,
        'item_count' => count($details)
    ]);

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
