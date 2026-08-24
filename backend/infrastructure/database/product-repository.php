<?php
/**
 * QuickMart IOMS - Product Repository
 * Product persistence operations
 */

/**
 * Find one product by its identifier.
 *
 * @return array|false
 */
function findProductById(PDO $pdo, $productId)
{
    $statement = $pdo->prepare("SELECT * FROM Products WHERE Product_ID = ?");
    $statement->execute([$productId]);

    return $statement->fetch();
}

/**
 * Find products matching the supplied filters and return list metadata.
 *
 * @return array
 */
function findProductList(PDO $pdo, $category, $status, $search)
{
    $where = [];
    $params = [];

    if ($category !== null) {
        $where[] = "Category = ?";
        $params[] = $category;
    }

    if ($status !== null) {
        $where[] = "Status = ?";
        $params[] = $status;
    }

    if ($search !== null) {
        $where[] = "Name LIKE ?";
        $params[] = '%' . $search . '%';
    }

    $sql = "SELECT * FROM Products";
    if (!empty($where)) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $sql .= " ORDER BY Name ASC";

    $statement = $pdo->prepare($sql);
    $statement->execute($params);
    $products = $statement->fetchAll();

    $categoriesStatement = $pdo->query("SELECT DISTINCT Category FROM Products ORDER BY Category");
    $categories = $categoriesStatement->fetchAll(PDO::FETCH_COLUMN);

    return [
        'products' => $products,
        'categories' => $categories,
        'total' => count($products)
    ];
}

/**
 * Generate the next product identifier while the caller holds a transaction.
 */
function generateProductId(PDO $pdo)
{
    $statement = $pdo->prepare("SELECT Product_ID FROM Products ORDER BY Product_ID DESC LIMIT 1 FOR UPDATE");
    $statement->execute();
    $lastId = $statement->fetchColumn();

    if ($lastId) {
        $number = intval(substr($lastId, strlen('PRD'))) + 1;
    } else {
        $number = 1;
    }

    return 'PRD' . str_pad($number, 3, '0', STR_PAD_LEFT);
}

/**
 * Insert one product with its calculated status.
 */
function insertProduct(PDO $pdo, $productId, $name, $category, $quantity, $price, $status, $threshold)
{
    $statement = $pdo->prepare("
        INSERT INTO Products (Product_ID, Name, Category, Quantity, Price, Status, Threshold)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $statement->execute([$productId, $name, $category, $quantity, $price, $status, $threshold]);
}

/**
 * Update the supplied product fields in the existing fixed field order.
 */
function updateProductFields(PDO $pdo, $productId, array $updates)
{
    $columns = [
        'name' => 'Name',
        'category' => 'Category',
        'quantity' => 'Quantity',
        'price' => 'Price',
        'threshold' => 'Threshold'
    ];
    $fieldOrder = ['name', 'category', 'quantity', 'price', 'threshold'];
    $setClauses = [];
    $params = [];

    foreach ($fieldOrder as $field) {
        if (array_key_exists($field, $updates)) {
            $setClauses[] = $columns[$field] . ' = ?';
            $params[] = $updates[$field];
        }
    }

    $params[] = $productId;
    $statement = $pdo->prepare(
        "UPDATE Products SET " . implode(', ', $setClauses) . " WHERE Product_ID = ?"
    );
    $statement->execute($params);
}

/**
 * Persist a product's calculated status.
 */
function persistProductStatus(PDO $pdo, $productId, $status)
{
    $statement = $pdo->prepare("UPDATE Products SET Status = ? WHERE Product_ID = ?");
    $statement->execute([$status, $productId]);
}

/**
 * Find the product fields needed before deletion.
 *
 * @return array|false
 */
function findProductForDeletion(PDO $pdo, $productId)
{
    $statement = $pdo->prepare("SELECT Product_ID, Name FROM Products WHERE Product_ID = ?");
    $statement->execute([$productId]);

    return $statement->fetch();
}

/**
 * Count order details that reference a product.
 */
function countProductOrderReferences(PDO $pdo, $productId)
{
    $statement = $pdo->prepare("SELECT COUNT(*) FROM Order_Details WHERE Product_ID = ?");
    $statement->execute([$productId]);

    return $statement->fetchColumn();
}

/**
 * Delete a product record by its identifier.
 */
function deleteProductRecord(PDO $pdo, $productId)
{
    $statement = $pdo->prepare("DELETE FROM Products WHERE Product_ID = ?");
    $statement->execute([$productId]);
}
