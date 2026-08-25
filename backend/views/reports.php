<?php
// Reports workspace is restricted to administrators.
// auth_check.php verifies the session on include; enforce the Admin role gate.
require_once __DIR__ . '/../core/auth_check.php';
if (!isAdmin()) {
    renderForbiddenPage();
}
$pageTitle = 'QuickMart IOMS - Reports';
$pageDescription = 'QuickMart IOMS - Reports and analytics';
$pageViewport = 'width=device-width, initial-scale=1.0';
$pageStylesheets = [
    '../../dist/css/reports.min.css?v=ui12',
    '../../dist/css/common.min.css?v=ui12',
];
$pageScript = '../../dist/js/reports.min.js';
?>
<?php require __DIR__ . '/partials/authenticated-head.php'; ?>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content reports-page">
        <div class="reports-content">
            <?php require __DIR__ . '/partials/reports/header-and-state.php'; ?>

            <?php require __DIR__ . '/partials/reports/summary.php'; ?>

            <?php require __DIR__ . '/partials/reports/performance.php'; ?>

            <?php require __DIR__ . '/partials/reports/recent-orders.php'; ?>

            <?php require __DIR__ . '/partials/reports/monthly-trend.php'; ?>

            <p class="reports-footnote">
                Report values are calculated from stored orders, order details, and the current product catalog.
            </p>
        </div>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <?php require __DIR__ . '/partials/reports/dialogs.php'; ?>

<?php require __DIR__ . '/partials/authenticated-script-loader.php'; ?>
</body>

</html>
