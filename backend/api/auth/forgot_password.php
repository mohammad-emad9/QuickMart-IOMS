<?php
/**
 * QuickMart IOMS - Forgot Password API
 * POST: Temporarily disabled until a secure token flow exists.
 */

require_once __DIR__ . '/../../core/config.php';
require_once __DIR__ . '/../../core/helpers.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Do not read, validate, enumerate, or persist reset input until a secure
// one-time, expiring token flow is approved and implemented.
errorResponse('Password reset is temporarily unavailable. Please contact an administrator.', 503);
