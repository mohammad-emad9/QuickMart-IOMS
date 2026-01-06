<?php
/**
 * QuickMart IOMS - Update Staff API
 * POST: Update staff member details (Admin only)
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Check authentication and admin role
if (!isset($_SESSION['user_id'])) {
    errorResponse('Authentication required', 401);
}

if ($_SESSION['user_role'] !== 'Admin') {
    errorResponse('Access denied. Admin only.', 403);
}

// Get input data
$input = getJsonInput();

// Validate required field
if (!isset($input['staff_id']) || empty($input['staff_id'])) {
    errorResponse('Staff ID is required');
}

$staffId = sanitize($input['staff_id']);

// Prevent admin from modifying themselves through this endpoint
if ($staffId === $_SESSION['user_id']) {
    errorResponse('Cannot modify your own account through this endpoint');
}

try {
    // Check if staff exists
    $stmt = $pdo->prepare("SELECT * FROM Staff WHERE Staff_ID = ?");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if (!$staff) {
        errorResponse('Staff not found', 404);
    }

    // Build update query dynamically
    $updates = [];
    $params = [];

    if (isset($input['full_name']) && !empty($input['full_name'])) {
        $updates[] = "Full_Name = ?";
        $params[] = sanitize($input['full_name']);
    }

    if (isset($input['email']) && !empty($input['email'])) {
        $email = sanitize($input['email']);

        // Check if email already exists for another user
        $emailCheck = $pdo->prepare("SELECT Staff_ID FROM Staff WHERE Email = ? AND Staff_ID != ?");
        $emailCheck->execute([$email, $staffId]);
        if ($emailCheck->fetch()) {
            errorResponse('Email already in use by another staff member');
        }

        $updates[] = "Email = ?";
        $params[] = $email;
    }

    if (isset($input['phone_number'])) {
        $updates[] = "Phone_Number = ?";
        $params[] = sanitize($input['phone_number']);
    }

    if (isset($input['role']) && !empty($input['role'])) {
        $allowedRoles = ['Admin', 'Manager', 'Staff'];
        $role = sanitize($input['role']);

        if (!in_array($role, $allowedRoles)) {
            errorResponse('Invalid role. Allowed: Admin, Manager, Staff');
        }

        $updates[] = "Role = ?";
        $params[] = $role;
    }

    // If new password provided, update it
    if (isset($input['password']) && !empty($input['password'])) {
        if (strlen($input['password']) < 6) {
            errorResponse('Password must be at least 6 characters');
        }
        $updates[] = "Password = ?";
        $params[] = password_hash($input['password'], PASSWORD_DEFAULT);
    }

    if (empty($updates)) {
        errorResponse('No fields to update');
    }

    // Add staff ID to params
    $params[] = $staffId;

    // Execute update
    $sql = "UPDATE Staff SET " . implode(', ', $updates) . " WHERE Staff_ID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Get updated staff
    $stmt = $pdo->prepare("SELECT Staff_ID, Full_Name, Email, Phone_Number, Role FROM Staff WHERE Staff_ID = ?");
    $stmt->execute([$staffId]);
    $updatedStaff = $stmt->fetch();

    successResponse($updatedStaff, 'Staff updated successfully');

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
