<?php
/**
 * QuickMart IOMS - Delete Staff API
 * DELETE/POST: Remove staff member (Admin only)
 */

require_once __DIR__ . '/../bootstrap.php';

// Accept DELETE or POST
if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    errorResponse('Method not allowed', 405);
}

// Check authentication and admin role
if (!isset($_SESSION['user_id'])) {
    errorResponse('Authentication required', 401);
}

if ($_SESSION['user_role'] !== 'Admin') {
    errorResponse('Access denied. Admin only.', 403);
}

// Get staff ID from query string or body
$staffId = null;

if (isset($_GET['id'])) {
    $staffId = sanitize($_GET['id']);
} else {
    $input = getJsonInput();
    if (isset($input['staff_id'])) {
        $staffId = sanitize($input['staff_id']);
    }
}

if (!$staffId) {
    errorResponse('Staff ID is required');
}

// Prevent admin from deleting themselves
if ($staffId === $_SESSION['user_id']) {
    errorResponse('Cannot delete your own account');
}

// Prevent deleting the original admin (STF001)
if ($staffId === 'STF001') {
    errorResponse('Cannot delete the primary admin account');
}

try {
    // Check if staff exists
    $stmt = $pdo->prepare("SELECT Staff_ID, Full_Name, Role FROM Staff WHERE Staff_ID = ?");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if (!$staff) {
        errorResponse('Staff not found', 404);
    }

    // Check if staff has orders
    $orderCheck = $pdo->prepare("SELECT COUNT(*) FROM Orders WHERE Staff_ID = ?");
    $orderCheck->execute([$staffId]);
    $orderCount = $orderCheck->fetchColumn();

    if ($orderCount > 0) {
        errorResponse("Cannot delete staff. They have $orderCount order(s) in the system. Consider deactivating instead.");
    }

    // Delete staff
    $stmt = $pdo->prepare("DELETE FROM Staff WHERE Staff_ID = ?");
    $stmt->execute([$staffId]);

    successResponse([
        'staff_id' => $staffId,
        'name' => $staff['Full_Name']
    ], 'Staff deleted successfully');

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
