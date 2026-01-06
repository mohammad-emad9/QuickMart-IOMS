<?php
/**
 * QuickMart IOMS - Forgot Password API
 * POST: Reset password for existing user
 * 
 * Note: In production, this should send email with reset token.
 * Resets user password directly.
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Get input data
$input = getJsonInput();

// Validate required fields
validateRequired($input, ['email', 'new_password']);

$email = sanitize($input['email']);
$newPassword = $input['new_password'];

// Validate password length
if (strlen($newPassword) < 6) {
    errorResponse('Password must be at least 6 characters');
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT Staff_ID, Full_Name, Email FROM Staff WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        errorResponse('No account found with this email', 404);
    }

    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update password
    $updateStmt = $pdo->prepare("UPDATE Staff SET Password = ? WHERE Email = ?");
    $updateStmt->execute([$hashedPassword, $email]);

    successResponse([
        'email' => $user['Email'],
        'name' => $user['Full_Name']
    ], 'Password reset successfully. You can now login with your new password.');

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
