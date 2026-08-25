<?php
/*
 * Application bootstrap.
 *
 * Loads Composer autoloader when present; falls back to manual requires
 * for local environments without vendor packages generated.
 */

if (basename($_SERVER['PHP_SELF'] ?? '') === 'bootstrap.php') {
    http_response_code(403);
    exit;
}

$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

require_once __DIR__ . '/core/config.php';
require_once __DIR__ . '/core/helpers.php';
