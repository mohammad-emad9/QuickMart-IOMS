<?php
/**
 * QuickMart IOMS - Helper Functions
 */

/**
 * Send JSON response and exit
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send success response
 */
function successResponse($data = null, $message = 'Success')
{
    jsonResponse([
        'success' => true,
        'message' => $message,
        'data' => $data
    ]);
}

/**
 * Send error response
 */
function errorResponse($message, $statusCode = 400)
{
    jsonResponse([
        'success' => false,
        'message' => $message
    ], $statusCode);
}

/**
 * Get JSON input from request body
 */
function getJsonInput()
{
    $input = file_get_contents('php://input');
    return json_decode($input, true) ?? [];
}

/**
 * Validate required fields
 */
function validateRequired($data, $requiredFields)
{
    $missing = [];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            $missing[] = $field;
        } elseif (is_string($data[$field]) && trim($data[$field]) === '') {
            $missing[] = $field;
        } elseif (is_array($data[$field]) && empty($data[$field])) {
            $missing[] = $field;
        }
    }

    if (!empty($missing)) {
        errorResponse('Missing required fields: ' . implode(', ', $missing));
    }

    return true;
}

/**
 * Generate unique ID with prefix
 */
function generateId($prefix, $pdo, $table, $column)
{
    // Get the last ID
    $stmt = $pdo->query("SELECT $column FROM $table ORDER BY $column DESC LIMIT 1");
    $lastId = $stmt->fetchColumn();

    if ($lastId) {
        // Extract number and increment
        $number = intval(substr($lastId, strlen($prefix))) + 1;
    } else {
        $number = 1;
    }

    return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
}

/**
 * Update product status based on quantity and threshold
 */
function updateProductStatus($pdo, $productId)
{
    $stmt = $pdo->prepare("SELECT Quantity, Threshold FROM Products WHERE Product_ID = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product) {
        $status = STATUS_NORMAL;

        if ($product['Quantity'] <= 0) {
            $status = STATUS_OUT_OF_STOCK;
        } elseif ($product['Quantity'] <= $product['Threshold']) {
            $status = STATUS_LOW_STOCK;
        }

        $updateStmt = $pdo->prepare("UPDATE Products SET Status = ? WHERE Product_ID = ?");
        $updateStmt->execute([$status, $productId]);
    }
}

/**
 * Check if user is logged in
 */
function requireAuth()
{
    if (!isset($_SESSION['user_id'])) {
        errorResponse('Authentication required', 401);
    }
    return $_SESSION['user_id'];
}

/**
 * Sanitize string input
 */
function sanitize($string)
{
    return htmlspecialchars(trim($string), ENT_QUOTES, 'UTF-8');
}
