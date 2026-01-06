<?php
/**
 * QuickMart IOMS - Create Product API
 * POST: Add new product
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Get input data
$input = getJsonInput();

// Validate required fields
validateRequired($input, ['name', 'category', 'quantity', 'price']);

$name = sanitize($input['name']);
$category = sanitize($input['category']);
$quantity = intval($input['quantity']);
$price = floatval($input['price']);
$threshold = isset($input['threshold']) ? intval($input['threshold']) : 20;

// Validate values
if ($quantity < 0) {
    errorResponse('Quantity cannot be negative');
}
if ($price <= 0) {
    errorResponse('Price must be greater than 0');
}

try {
    // Generate Product ID
    $productId = generateId('PRD', $pdo, 'Products', 'Product_ID');

    // Determine initial status
    $status = STATUS_NORMAL;
    if ($quantity <= 0) {
        $status = STATUS_OUT_OF_STOCK;
    } elseif ($quantity <= $threshold) {
        $status = STATUS_LOW_STOCK;
    }

    // Insert product
    $stmt = $pdo->prepare("
        INSERT INTO Products (Product_ID, Name, Category, Quantity, Price, Status, Threshold)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$productId, $name, $category, $quantity, $price, $status, $threshold]);

    successResponse([
        'product_id' => $productId,
        'name' => $name,
        'category' => $category,
        'quantity' => $quantity,
        'price' => $price,
        'status' => $status,
        'threshold' => $threshold
    ], 'Product created successfully');

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
