<?php
/**
 * QuickMart IOMS - Database Connection
 * PDO-based connection to MySQL
 */

// Database configuration must be supplied by the process environment.
$host = quickmartEnv('QUICKMART_DB_HOST');
$dbname = quickmartEnv('QUICKMART_DB_NAME');
$username = quickmartEnv('QUICKMART_DB_USER');
$password = quickmartEnv('QUICKMART_DB_PASSWORD', null, false);

if ($host === null || $dbname === null || $username === null || $password === null) {
    configurationError();
}

// PDO options for better error handling and security
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        $options
    );
} catch (PDOException $e) {
    error_log('QuickMart database connection failed.');
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection is unavailable.'
    ]);
    exit;
}
