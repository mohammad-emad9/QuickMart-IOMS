<?php
/**
 * QuickMart IOMS - Change Password API
 * POST: Change user password
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Get input
$input = getJsonInput();

// Validate required fields
if (!isset($input['staff_id']) || !isset($input['current_password']) || !isset($input['new_password'])) {
    errorResponse('Missing required fields: staff_id, current_password, new_password');
}

$staffId = sanitize($input['staff_id']);
$currentPassword = $input['current_password'];
$newPassword = $input['new_password'];

// Validate new password
if (strlen($newPassword) < 6) {
    errorResponse('New password must be at least 6 characters');
}

try {
    // Get current staff record
    $stmt = $pdo->prepare("SELECT Password FROM Staff WHERE Staff_ID = ?");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if (!$staff) {
        errorResponse('Staff member not found', 404);
    }

    // Verify current password
    if (!password_verify($currentPassword, $staff['Password'])) {
        errorResponse('Current password is incorrect');
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $updateStmt = $pdo->prepare("UPDATE Staff SET Password = ? WHERE Staff_ID = ?");
    $updateStmt->execute([$hashedPassword, $staffId]);

    successResponse(['message' => 'Password changed successfully']);

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
