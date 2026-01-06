<?php
/**
 * QuickMart IOMS - List All Staff API
 * GET: Retrieve all staff members (Admin only)
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

// Check authentication and admin role
if (!isset($_SESSION['user_id'])) {
    errorResponse('Authentication required', 401);
}

if ($_SESSION['user_role'] !== 'Admin') {
    errorResponse('Access denied. Admin only.', 403);
}

try {
    // Get all staff (exclude password)
    $stmt = $pdo->query("
        SELECT Staff_ID, Full_Name, Email, Phone_Number, Role 
        FROM Staff 
        ORDER BY Staff_ID ASC
    ");
    $staff = $stmt->fetchAll();

    successResponse([
        'staff' => $staff,
        'total' => count($staff)
    ]);

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
