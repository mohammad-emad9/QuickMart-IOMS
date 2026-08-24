<?php
/**
 * QuickMart IOMS - Report Repository
 * Read-only report persistence operations
 */

/**
 * Count all orders.
 */
function getTotalOrderCount(PDO $pdo)
{
    $statement = $pdo->prepare("SELECT COUNT(*) as total FROM Orders");
    $statement->execute();

    return $statement->fetch()['total'];
}

/**
 * Get order counts and calculated totals grouped by order type.
 *
 * @return array
 */
function findOrdersByType(PDO $pdo)
{
    $statement = $pdo->prepare("
        SELECT 
            o.Order_Type, 
            COUNT(DISTINCT o.Order_ID) as count, 
            COALESCE(SUM(od.Ordered_Qty * od.Sold_Price), 0) as total_amount
        FROM Orders o
        LEFT JOIN Order_Details od ON o.Order_ID = od.Order_ID
        GROUP BY o.Order_Type
    ");
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Count all products.
 */
function getTotalProductCount(PDO $pdo)
{
    $statement = $pdo->prepare("SELECT COUNT(*) as total FROM Products");
    $statement->execute();

    return $statement->fetch()['total'];
}

/**
 * Count products that are low stock or out of stock.
 */
function getLowStockProductCount(PDO $pdo)
{
    $statement = $pdo->prepare("SELECT COUNT(*) as total FROM Products WHERE Status IN ('Low Stock', 'Out of Stock')");
    $statement->execute();

    return $statement->fetch()['total'];
}

/**
 * Get product stock totals grouped by category.
 *
 * @return array
 */
function findProductsByCategory(PDO $pdo)
{
    $statement = $pdo->prepare("
        SELECT Category, COUNT(*) as count, SUM(Quantity) as total_stock
        FROM Products
        GROUP BY Category
        ORDER BY count DESC
    ");
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Get the ten most recent orders with staff names and calculated totals.
 *
 * @return array
 */
function findRecentOrders(PDO $pdo)
{
    $statement = $pdo->prepare("
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
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Get the five highest-volume selling products.
 *
 * @return array
 */
function findTopProducts(PDO $pdo)
{
    $statement = $pdo->prepare("
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
    $statement->execute();

    return $statement->fetchAll();
}

/**
 * Get the monthly sales and purchase trend for the last six months.
 *
 * @return array
 */
function findMonthlySalesTrend(PDO $pdo)
{
    $statement = $pdo->prepare("
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
    $statement->execute();

    return $statement->fetchAll();
}
