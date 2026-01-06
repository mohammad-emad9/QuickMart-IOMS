<?php
/**
 * QuickMart IOMS - Login API
 * POST: Authenticate user with email and password
 */

require_once __DIR__ . '/../bootstrap.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Get input data
$input = getJsonInput();

// Validate required fields
validateRequired($input, ['email', 'password']);

$email = sanitize($input['email']);
$password = $input['password'];

try {
    // Find user by email or Staff_ID
    $stmt = $pdo->prepare("
        SELECT Staff_ID, Full_Name, Email, Password, Role 
        FROM Staff 
        WHERE Email = ? OR Staff_ID = ?
    ");
    $stmt->execute([$email, $email]);
    $user = $stmt->fetch();

    if (!$user) {
        errorResponse('Invalid email/staff ID or password', 401);
    }

    // Verify password
    if (!password_verify($password, $user['Password'])) {
        errorResponse('Invalid email/staff ID or password', 401);
    }

    // Create session
    $_SESSION['user_id'] = $user['Staff_ID'];
    $_SESSION['user_name'] = $user['Full_Name'];
    $_SESSION['user_email'] = $user['Email'];
    $_SESSION['user_role'] = $user['Role'];

    // Return success with user data (exclude password)
    successResponse([
        'staff_id' => $user['Staff_ID'],
        'full_name' => $user['Full_Name'],
        'email' => $user['Email'],
        'role' => $user['Role']
    ], 'Login successful');

} catch (PDOException $e) {
    errorResponse('Database error: ' . $e->getMessage(), 500);
}
