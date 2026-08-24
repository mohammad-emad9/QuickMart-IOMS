<?php
/**
 * QuickMart IOMS - Order Repository
 * Order persistence operations
 */

/**
 * Find orders matching the supplied filters.
 *
 * @return array
 */
function findOrderList(PDO $pdo, $orderType, $staffId, $dateFrom, $dateTo)
{
    $where = [];
    $params = [];

    if ($orderType !== null) {
        $where[] = "o.Order_Type = ?";
        $params[] = $orderType;
    }

    if ($staffId !== null) {
        $where[] = "o.Staff_ID = ?";
        $params[] = $staffId;
    }

    if ($dateFrom !== null) {
        $where[] = "o.Order_Date >= ?";
        $params[] = $dateFrom;
    }

    if ($dateTo !== null) {
        $where[] = "o.Order_Date < ?";
        $params[] = $dateTo;
    }

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

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetchAll();
}

/**
 * Find one order header with optional Staff ownership restriction.
 *
 * @return array|false
 */
function findOrderHeader(PDO $pdo, $orderId, $staffId)
{
    $sql = "
        SELECT 
            o.*,
            s.Full_Name as Staff_Name,
            s.Email as Staff_Email
        FROM Orders o
        LEFT JOIN Staff s ON o.Staff_ID = s.Staff_ID
        WHERE o.Order_ID = ?";
    $params = [$orderId];

    if ($staffId !== null) {
        $sql .= " AND o.Staff_ID = ?";
        $params[] = $staffId;
    }

    $statement = $pdo->prepare($sql);
    $statement->execute($params);

    return $statement->fetch();
}

/**
 * Find all detail rows for one order.
 *
 * @return array
 */
function findOrderDetails(PDO $pdo, $orderId)
{
    $statement = $pdo->prepare("
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
    $statement->execute([$orderId]);

    return $statement->fetchAll();
}

/**
 * Verify that a staff member exists for order creation.
 *
 * @return array|false
 */
function findStaffForOrderCreation(PDO $pdo, $staffId)
{
    $statement = $pdo->prepare("SELECT Staff_ID FROM Staff WHERE Staff_ID = ?");
    $statement->execute([$staffId]);

    return $statement->fetch();
}

/**
 * Generate the next order identifier while the caller holds a transaction.
 */
function generateOrderId(PDO $pdo)
{
    $statement = $pdo->prepare("SELECT Order_ID FROM Orders ORDER BY Order_ID DESC LIMIT 1 FOR UPDATE");
    $statement->execute();
    $lastId = $statement->fetchColumn();

    if ($lastId) {
        $number = intval(substr($lastId, strlen('ORD'))) + 1;
    } else {
        $number = 1;
    }

    return 'ORD' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

/**
 * Insert an order header.
 */
function insertOrderHeader(PDO $pdo, $orderId, $staffId, $orderType, $partyName)
{
    $statement = $pdo->prepare("
        INSERT INTO Orders (Order_ID, Staff_ID, Order_Type, Party_Name)
        VALUES (?, ?, ?, ?)
    ");
    $statement->execute([$orderId, $staffId, $orderType, $partyName]);
}

/**
 * Find and lock a product row for order stock processing.
 *
 * @return array|false
 */
function findLockedProductForOrder(PDO $pdo, $productId)
{
    $statement = $pdo->prepare("SELECT Product_ID, Name, Quantity, Threshold, Price FROM Products WHERE Product_ID = ? FOR UPDATE");
    $statement->execute([$productId]);

    return $statement->fetch();
}

/**
 * Insert one order detail row.
 */
function insertOrderDetail(PDO $pdo, $orderId, $productId, $quantity, $price)
{
    $statement = $pdo->prepare("
        INSERT INTO Order_Details (Order_ID, Product_ID, Ordered_Qty, Sold_Price)
        VALUES (?, ?, ?, ?)
    ");
    $statement->execute([$orderId, $productId, $quantity, $price]);
}

/**
 * Update product quantity using the existing safe condition.
 */
function updateProductQuantityForOrder(PDO $pdo, $newQuantity, $productId, $orderType)
{
    $statement = $pdo->prepare("UPDATE Products SET Quantity = ? WHERE Product_ID = ? AND (? >= 0 OR ? = 'Purchase')");
    $statement->execute([$newQuantity, $productId, $newQuantity, $orderType]);

    return $statement->rowCount();
}

/**
 * Persist the product status calculated for the resulting quantity.
 */
function persistProductStatusForOrder(PDO $pdo, $productId, $status)
{
    $statement = $pdo->prepare("UPDATE Products SET Status = ? WHERE Product_ID = ?");
    $statement->execute([$status, $productId]);
}
