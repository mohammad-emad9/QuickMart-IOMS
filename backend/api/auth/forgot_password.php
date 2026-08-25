<?php
/*
 * POST: Password reset endpoint (disabled pending approved delivery provider).
 */

require_once __DIR__ . '/../../bootstrap.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    errorResponse('Method not allowed', 405);
}

// Do not read, validate, enumerate, or persist reset input while the public
// delivery gate is disabled. The internal token lifecycle remains available
// only to controlled callers that do not expose raw tokens over HTTP.
if (!PUBLIC_PASSWORD_RESET_ENABLED) {
    errorResponse('Password reset is unavailable because secure delivery is not configured. Please contact an administrator.', 503);
}

// A future delivery adapter must be wired here only after the documented
// provider, secret, HTTPS, throttling, audit, and session-invalidation gates
// are approved. Fail closed until then.
errorResponse('Password reset is unavailable because secure delivery is not configured. Please contact an administrator.', 503);
