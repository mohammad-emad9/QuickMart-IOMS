<?php
require_once __DIR__ . '/../core/auth_check.php';
$pageTitle = 'QuickMart IOMS - Dashboard';
$pageDescription = 'QuickMart IOMS - Operations dashboard';
$pageViewport = 'width=device-width, initial-scale=1.0, viewport-fit=cover';
$pageStylesheets = [
    '../../dist/css/dashboard.min.css?v=ui10',
    '../../dist/css/common.min.css?v=ui12',
];
$pageScript = '../../dist/js/dashboard.min.js';
$includeDashboardHeadMeta = true;
?>
<?php require __DIR__ . '/partials/authenticated-head.php'; ?>

<body class="dashboard-page">
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content">
        <div class="container-fluid dashboard-content">
            <?php require __DIR__ . '/partials/dashboard/overview.php'; ?>

            <?php require __DIR__ . '/partials/dashboard/activity.php'; ?>
        </div>
    </main>

    <?php require __DIR__ . '/partials/dashboard/dialogs.php'; ?>

<?php require __DIR__ . '/partials/authenticated-script-loader.php'; ?>
</body>

</html>
