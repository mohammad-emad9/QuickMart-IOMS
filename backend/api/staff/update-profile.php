<?php
/**
 * QuickMart IOMS - Update Own Profile API
 * POST: Update current user's own profile (for Profile page)
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Check authentication
if (!isset($_SESSION['user_id'])) {
    errorResponse('Authentication required', 401);
}

// Get input data
$input = getJsonInput();

// Validate staff_id matches current user
if (!isset($input['staff_id']) || $input['staff_id'] !== $_SESSION['user_id']) {
    errorResponse('You can only update your own profile');
}

$staffId = $_SESSION['user_id'];

try {
    // Check if staff exists
    $stmt = $pdo->prepare("SELECT * FROM Staff WHERE Staff_ID = ?");
    $stmt->execute([$staffId]);
    $staff = $stmt->fetch();

    if (!$staff) {
        errorResponse('User not found', 404);
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

    // Note: Users cannot change their own role through profile
    // Role changes must be done by Admin through staff management

    if (empty($updates)) {
        errorResponse('No fields to update');
    }

    // Add staff ID to params
    $params[] = $staffId;

    // Execute update
    $sql = "UPDATE Staff SET " . implode(', ', $updates) . " WHERE Staff_ID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    // Update session with new name if changed
    if (isset($input['full_name']) && !empty($input['full_name'])) {
        $_SESSION['user_name'] = sanitize($input['full_name']);
    }

    // Get updated staff
    $stmt = $pdo->prepare("SELECT Staff_ID, Full_Name, Email, Phone_Number, Role FROM Staff WHERE Staff_ID = ?");
    $stmt->execute([$staffId]);
    $updatedStaff = $stmt->fetch();

    successResponse($updatedStaff, 'Profile updated successfully');

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
