<?php
/**
 * Reports API Endpoint
 * Returns statistics for orders, products, and sales
 */

require_once __DIR__ . '/../bootstrap.php';

try {

    // Total orders count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM Orders");
    $stmt->execute();
    $totalOrders = $stmt->fetch()['total'];

    // Orders by type with calculated totals from Order_Details
    $stmt = $pdo->prepare("
        SELECT 
            o.Order_Type, 
            COUNT(DISTINCT o.Order_ID) as count, 
            COALESCE(SUM(od.Ordered_Qty * od.Sold_Price), 0) as total_amount
        FROM Orders o
        LEFT JOIN Order_Details od ON o.Order_ID = od.Order_ID
        GROUP BY o.Order_Type
    ");
    $stmt->execute();
    $ordersByType = $stmt->fetchAll();

    $sellOrders = 0;
    $purchaseOrders = 0;
    $totalSales = 0;
    $totalPurchases = 0;

    foreach ($ordersByType as $row) {
        if ($row['Order_Type'] === 'Sell') {
            $sellOrders = $row['count'];
            $totalSales = floatval($row['total_amount'] ?? 0);
        } else {
            $purchaseOrders = $row['count'];
            $totalPurchases = floatval($row['total_amount'] ?? 0);
        }
    }

    // Total products count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM Products");
    $stmt->execute();
    $totalProducts = $stmt->fetch()['total'];

    // Low stock count
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM Products WHERE Status IN ('Low Stock', 'Out of Stock')");
    $stmt->execute();
    $lowStockCount = $stmt->fetch()['total'];

    // Products by category
    $stmt = $pdo->prepare("
        SELECT Category, COUNT(*) as count, SUM(Quantity) as total_stock
        FROM Products
        GROUP BY Category
        ORDER BY count DESC
    ");
    $stmt->execute();
    $productsByCategory = $stmt->fetchAll();

    // Recent orders (last 10) with calculated total + STAFF NAME
    $stmt = $pdo->prepare("
        SELECT 
            o.Order_ID, 
            o.Order_Type, 
            o.Party_Name, 
            s.Full_Name as Staff_Name,
            COALESCE(SUM(od.Ordered_Qty * od.Sold_Price), 0) as Total_Amount, 
            o.Order_Date
        FROM Orders o
        LEFT JOIN Order_Details od ON o.Order_ID = od.Order_ID
        LEFT JOIN Staff s ON o.Staff_ID = s.Staff_ID
        GROUP BY o.Order_ID, o.Order_Type, o.Party_Name, s.Full_Name, o.Order_Date
        ORDER BY o.Order_Date DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recentOrders = $stmt->fetchAll();

    // Top selling products
    $stmt = $pdo->prepare("
        SELECT 
            p.Name, 
            SUM(od.Ordered_Qty) as total_sold, 
            SUM(od.Ordered_Qty * od.Sold_Price) as revenue
        FROM Order_Details od
        JOIN Products p ON od.Product_ID = p.Product_ID
        JOIN Orders o ON od.Order_ID = o.Order_ID
        WHERE o.Order_Type = 'Sell'
        GROUP BY od.Product_ID, p.Name
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $stmt->execute();
    $topProducts = $stmt->fetchAll();

    // Monthly sales trend (last 6 months)
    $stmt = $pdo->prepare("
        SELECT 
            DATE_FORMAT(o.Order_Date, '%Y-%m') as month,
            COUNT(DISTINCT o.Order_ID) as order_count,
            COALESCE(SUM(CASE WHEN o.Order_Type = 'Sell' THEN od.Ordered_Qty * od.Sold_Price ELSE 0 END), 0) as sales,
            COALESCE(SUM(CASE WHEN o.Order_Type = 'Purchase' THEN od.Ordered_Qty * od.Sold_Price ELSE 0 END), 0) as purchases
        FROM Orders o
        LEFT JOIN Order_Details od ON o.Order_ID = od.Order_ID
        WHERE o.Order_Date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(o.Order_Date, '%Y-%m')
        ORDER BY month ASC
    ");
    $stmt->execute();
    $monthlyTrend = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'data' => [
            'summary' => [
                'total_orders' => $totalOrders,
                'sell_orders' => $sellOrders,
                'purchase_orders' => $purchaseOrders,
                'total_sales' => $totalSales,
                'total_purchases' => $totalPurchases,
                'net_revenue' => $totalSales - $totalPurchases,
                'total_products' => $totalProducts,
                'low_stock_count' => $lowStockCount
            ],
            'products_by_category' => $productsByCategory,
            'recent_orders' => $recentOrders,
            'top_products' => $topProducts,
            'monthly_trend' => $monthlyTrend
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
