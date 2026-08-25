<?php
/**
 * QuickMart IOMS - Helper Functions
 */

require_once __DIR__ . '/../infrastructure/database/auth-repository.php';

/**
 * Send JSON response and exit
 */
function jsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
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
    $decoded = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
        errorResponse('Invalid JSON request.', 400);
    }

    return $decoded;
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
 * Return a generic internal error without exposing database or server details.
 */
function internalErrorResponse($context = 'request')
{
    error_log('QuickMart internal error: ' . $context);
    errorResponse('An internal server error occurred.', 500);
}

/**
 * Validate the strict integer representation used for session revisions.
 * PHP session values are intentionally not coerced so legacy, missing, zero,
 * or malformed revisions fail closed.
 */
function isValidAuthRevision($revision)
{
    return is_int($revision) && $revision >= 1;
}

/**
 * Recognize the MariaDB duplicate-key error without exposing its details.
 */
function isStaffDuplicateKeyViolation(PDOException $exception)
{
    $errorInfo = $exception->errorInfo;

    return ($errorInfo[0] ?? $exception->getCode()) === '23000'
        && (int) ($errorInfo[1] ?? 0) === 1062;
}

/**
 * Recognize a MariaDB parent-delete foreign-key violation.
 */
function isStaffForeignKeyViolation(PDOException $exception)
{
    $errorInfo = $exception->errorInfo;

    return ($errorInfo[0] ?? $exception->getCode()) === '23000'
        && (int) ($errorInfo[1] ?? 0) === 1451;
}

/**
 * Create or return the session-bound CSRF token.
 */
function getCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validate the CSRF token for a state-changing request.
 */
function requireCsrfToken()
{
    $requestToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if ($requestToken === '' || $sessionToken === '' || !hash_equals($sessionToken, $requestToken)) {
        errorResponse('CSRF validation failed.', 403);
    }
}

/**
 * Load the database connection used for authentication revalidation.
 * API bootstrap already creates $pdo; the CSRF endpoint includes only the
 * core configuration and helpers, so it needs the same lazy fallback.
 *
 * @return PDO|null
 */
function getAuthenticationDatabase()
{
    if (isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO) {
        return $GLOBALS['pdo'];
    }

    require_once __DIR__ . '/db.php';

    // A file required inside this function is evaluated in the function
    // scope. Promote the connection so subsequent repository calls and
    // shared API code see the same PDO instance.
    if (isset($pdo) && $pdo instanceof PDO) {
        $GLOBALS['pdo'] = $pdo;
    }

    return isset($GLOBALS['pdo']) && $GLOBALS['pdo'] instanceof PDO
        ? $GLOBALS['pdo']
        : null;
}

/**
 * Clear a session that no longer maps to a valid staff account.
 */
function clearAuthenticatedSession()
{
    $_SESSION = [];

    if (session_status() === PHP_SESSION_ACTIVE) {
        session_unset();
        session_destroy();
    }
}

/**
 * Refresh session identity and role data from the current Staff row.
 *
 * @return array|false
 */
function refreshAuthenticatedSessionFromDatabase($sessionStaffId)
{
    $pdo = getAuthenticationDatabase();
    if (!$pdo) {
        throw new RuntimeException('Authentication database is unavailable.');
    }

    $sessionRevision = $_SESSION['auth_revision'] ?? null;

    if (!isValidAuthRevision($sessionRevision)) {
        return false;
    }

    require_once __DIR__ . '/../infrastructure/database/staff-repository.php';

    $staff = findStaffAuthContextWithRevision($pdo, $sessionStaffId);
    $databaseRevision = $staff['Auth_Revision'] ?? null;

    if ($staff === false
        || !isSupportedStaffRole($staff['Role'] ?? null)
        || !isValidAuthRevision($databaseRevision)
        || $sessionRevision !== $databaseRevision) {
        return false;
    }

    $_SESSION['user_id'] = (string) $staff['Staff_ID'];
    $_SESSION['user_name'] = $staff['Full_Name'];
    $_SESSION['user_email'] = $staff['Email'];
    $_SESSION['user_role'] = $staff['Role'];
    $_SESSION['auth_revision'] = $databaseRevision;

    return $staff;
}

/**
 * Authenticate an API request and enforce CSRF for mutations.
 */
function requireApiAuth()
{
    $sessionStaffId = $_SESSION['user_id'] ?? null;

    if (!is_string($sessionStaffId)) {
        clearAuthenticatedSession();
        errorResponse('Authentication required.', 401);
    }

    $sessionStaffId = trim($sessionStaffId);
    if ($sessionStaffId === ''
        || strlen($sessionStaffId) > 20
        || !preg_match('/^[A-Za-z0-9_-]+$/', $sessionStaffId)) {
        clearAuthenticatedSession();
        errorResponse('Authentication required.', 401);
    }

    try {
        $staff = refreshAuthenticatedSessionFromDatabase($sessionStaffId);
    } catch (Throwable $e) {
        clearAuthenticatedSession();
        internalErrorResponse('authentication revalidation');
    }

    if ($staff === false) {
        clearAuthenticatedSession();
        errorResponse('Authentication required.', 401);
    }

    if (in_array($_SERVER['REQUEST_METHOD'] ?? 'GET', ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
        requireCsrfToken();
    }

    return (string) $staff['Staff_ID'];
}

/**
 * Authenticate an API request and require a specific existing role.
 */
function requireApiRole($role)
{
    $userId = requireApiAuth();

    if (($_SESSION['user_role'] ?? null) !== $role) {
        errorResponse('Access denied.', 403);
    }

    return $userId;
}

/**
 * Validate an identifier used at an application boundary.
 */
function validateIdentifier($value, $fieldName, $maxLength = 20)
{
    if (!is_scalar($value)) {
        errorResponse($fieldName . ' is invalid.', 422);
    }

    $identifier = trim((string) $value);
    if ($identifier === '' || strlen($identifier) > $maxLength || !preg_match('/^[A-Za-z0-9_-]+$/', $identifier)) {
        errorResponse($fieldName . ' is invalid.', 422);
    }

    return $identifier;
}

/**
 * Validate a Staff_ID without coercing arrays, booleans, or numeric values.
 */
function validateStaffIdentifier($value, $fieldName = 'Staff ID', $maxLength = 20)
{
    if (!is_string($value)) {
        errorResponse($fieldName . ' is invalid.', 422);
    }

    return validateIdentifier($value, $fieldName, $maxLength);
}

/**
 * Reject keys that are not part of an endpoint's input contract.
 */
function validateAllowedInputFields(array $input, array $allowedFields)
{
    $unexpectedFields = array_values(array_diff(array_keys($input), $allowedFields));

    if (!empty($unexpectedFields)) {
        errorResponse('Unexpected field(s): ' . implode(', ', $unexpectedFields), 422);
    }
}

/**
 * Return the UTF-8 character length used by VARCHAR-aligned validation.
 */
function quickmartStringLength($value)
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : strlen($value);
}

/**
 * Validate a bounded string at a staff API boundary.
 */
function validateStaffString($value, $fieldName, $maxLength, $allowBlank = false)
{
    if (!is_string($value)) {
        errorResponse($fieldName . ' must be a string.', 422);
    }

    $normalized = trim($value);
    if (!$allowBlank && ($normalized === '' || preg_match('/\S/u', $normalized) !== 1)) {
        errorResponse($fieldName . ' must not be blank.', 422);
    }

    if (quickmartStringLength($normalized) > $maxLength) {
        errorResponse($fieldName . ' must be ' . $maxLength . ' characters or fewer.', 422);
    }

    return $normalized;
}

/**
 * Validate bounded text fields whose values are stored and later rendered as
 * data. This intentionally does not HTML-encode the stored value.
 */
function validateBoundedText($value, $fieldName, $maxLength, $allowBlank = false)
{
    if (!is_string($value)) {
        errorResponse($fieldName . ' must be a string.', 422);
    }

    $normalized = trim($value);
    if (!$allowBlank && ($normalized === '' || preg_match('/\S/u', $normalized) !== 1)) {
        errorResponse($fieldName . ' must not be blank.', 422);
    }

    if (quickmartStringLength($normalized) > $maxLength) {
        errorResponse($fieldName . ' must be ' . $maxLength . ' characters or fewer.', 422);
    }

    return $normalized;
}

/**
 * Validate a Staff full name against Staff.Full_Name VARCHAR(100).
 */
function validateStaffName($value)
{
    return validateStaffString($value, 'Full name', 100);
}

/**
 * Validate a Staff email against Staff.Email VARCHAR(100).
 */
function validateStaffEmail($value)
{
    $email = validateStaffString($value, 'Email', 100);

    if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        errorResponse('Email is invalid.', 422);
    }

    return $email;
}

/**
 * Validate the optional Staff.Phone_Number VARCHAR(20) field.
 */
function validateStaffPhone($value)
{
    if ($value === null) {
        return null;
    }

    return validateStaffString($value, 'Phone number', 20, true);
}

/**
 * Validate an exact Staff role.
 */
function validateStaffRoleValue($value)
{
    if (!is_string($value)
        || !in_array($value, ['Admin', 'Manager', 'Staff'], true)) {
        errorResponse('Invalid role. Allowed: Admin, Manager, Staff', 422);
    }

    return $value;
}

/**
 * Validate a password before it reaches password_hash/password_verify.
 */
function validateStaffPassword($value, $fieldName = 'Password', $minimumLength = 6)
{
    if (!is_string($value)) {
        errorResponse($fieldName . ' must be a string.', 422);
    }

    if (strlen($value) < $minimumLength) {
        errorResponse($fieldName . ' must be at least ' . $minimumLength . ' characters.', 422);
    }

    if (strlen($value) > 255) {
        errorResponse($fieldName . ' must be 255 characters or fewer.', 422);
    }

    return $value;
}

/**
 * Validate the login identifier without assuming it is an email: the
 * existing contract also permits Staff_ID login.
 */
function validateLoginIdentifier($value)
{
    $identifier = validateStaffString($value, 'Email or Staff ID', 100);

    if (filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false) {
        return $identifier;
    }

    if (strlen($identifier) > 20
        || !preg_match('/^[A-Za-z0-9_-]+$/', $identifier)) {
        errorResponse('Email or Staff ID is invalid.', 422);
    }

    return $identifier;
}

/**
 * Validate a whole-number value within a safe application range.
 */
function validateIntegerValue($value, $fieldName, $minimum = 0, $maximum = MAX_PRODUCT_QUANTITY)
{
    if (!is_scalar($value) || is_bool($value)) {
        errorResponse($fieldName . ' must be a whole number.', 422);
    }

    $rawValue = trim((string) $value);
    if (!preg_match('/^-?[0-9]+$/', $rawValue)) {
        errorResponse($fieldName . ' must be a whole number.', 422);
    }

    $number = filter_var($rawValue, FILTER_VALIDATE_INT);
    if ($number === false || $number < $minimum || $number > $maximum) {
        errorResponse($fieldName . ' is outside the allowed range.', 422);
    }

    return $number;
}

/**
 * Validate a positive decimal value with at most two decimal places.
 */
function validateMoneyValue($value, $fieldName = 'Price', $allowZero = false)
{
    if (!is_scalar($value) || is_bool($value)) {
        errorResponse($fieldName . ' is invalid.', 422);
    }

    $rawValue = trim((string) $value);
    if (!preg_match('/^[0-9]+(?:\.[0-9]{1,2})?$/', $rawValue)) {
        errorResponse($fieldName . ' must be a finite amount with at most 2 decimal places.', 422);
    }

    $number = (float) $rawValue;
    if (!is_finite($number) || (!$allowZero && $number <= 0) || $number > MAX_PRICE) {
        errorResponse($fieldName . ' is outside the allowed range.', 422);
    }

    return round($number, 2);
}

/**
 * Check if user is logged in
 */
function requireAuth()
{
    return requireApiAuth();
}

/**
 * Normalize a scalar input string without changing the value's output
 * context. Validation belongs at the API boundary; encoding belongs at the
 * output boundary.
 */
function normalizeInputString($value)
{
    if (!is_scalar($value) || is_bool($value)) {
        return '';
    }

    return trim((string) $value);
}

/**
 * Escape text for an HTML text node or a quoted HTML attribute.
 */
function escapeHtml($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Escape a value that will be placed in a quoted HTML attribute.
 */
function escapeAttr($value)
{
    return escapeHtml($value);
}

/**
 * Legacy compatibility alias. This is an output encoder, not validation.
 * New request handling must use explicit validators or normalizeInputString.
 */
function sanitize($string)
{
    return escapeHtml(normalizeInputString($string));
}
