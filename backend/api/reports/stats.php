<?php
/**
 * Reports API Endpoint
 * Returns statistics for orders, products, and sales
 */

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../../application/reports/report-service.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    errorResponse('Method not allowed', 405);
}

requireApiRole('Admin');

try {
    $reportData = getReportStatistics($pdo);

    // Keep the existing {success, data} response shape while using the
    // shared JSON helper for consistent headers and response termination.
    jsonResponse([
        'success' => true,
        'data' => $reportData
    ]);

} catch (Throwable $e) {
    internalErrorResponse('reports database operation');
}
