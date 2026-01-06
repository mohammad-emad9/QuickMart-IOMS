<?php
/**
 * QuickMart IOMS - Get Staff API
 * GET: Retrieve single staff member by ID
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    errorResponse('Method not allowed', 405);
}

// Validate staff ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    errorResponse('Staff ID is required');
}

$staffId = sanitize($_GET['id']);

try {
    // Get staff member (exclude password)
    $stmt = $pdo->prepare("
        SELECT Staff_ID, Full_Name, Email, Role, Phone_Number
        FROM Staff
        WHERE Staff_ID = ?
    ");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if (!$staff) {
        errorResponse('Staff member not found', 404);
    }

    successResponse($staff);

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
