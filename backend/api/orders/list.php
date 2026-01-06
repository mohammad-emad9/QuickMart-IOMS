<?php
/**
 * QuickMart IOMS - List Orders API
 * GET: Retrieve all orders with optional filters
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

    // Filter by order type
    if (isset($_GET['type']) && !empty($_GET['type'])) {
        $where[] = "o.Order_Type = ?";
        $params[] = $_GET['type'];
    }

    // Filter by staff
    if (isset($_GET['staff_id']) && !empty($_GET['staff_id'])) {
        $where[] = "o.Staff_ID = ?";
        $params[] = $_GET['staff_id'];
    }

    // Filter by date range
    if (isset($_GET['date_from']) && !empty($_GET['date_from'])) {
        $where[] = "DATE(o.Order_Date) >= ?";
        $params[] = $_GET['date_from'];
    }
    if (isset($_GET['date_to']) && !empty($_GET['date_to'])) {
        $where[] = "DATE(o.Order_Date) <= ?";
        $params[] = $_GET['date_to'];
    }

    // Build SQL query
    $sql = "
        SELECT 
            o.Order_ID,
            o.Staff_ID,
            s.Full_Name as Staff_Name,
            o.Order_Date,
            o.Order_Type,
            o.Party_Name,
            COUNT(od.Detail_ID) as Item_Count,
            COALESCE(SUM(od.Ordered_Qty * od.Sold_Price), 0) as Total_Amount
        FROM Orders o
        LEFT JOIN Staff s ON o.Staff_ID = s.Staff_ID
        LEFT JOIN Order_Details od ON o.Order_ID = od.Order_ID
    ";

    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }

    $sql .= " GROUP BY o.Order_ID ORDER BY o.Order_Date DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $orders = $stmt->fetchAll();

    successResponse([
        'orders' => $orders,
        'total' => count($orders)
    ]);

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
