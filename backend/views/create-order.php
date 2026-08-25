<?php
require_once __DIR__ . '/../core/auth_check.php';
$pageTitle = 'QuickMart IOMS - Create Order';
$pageDescription = 'QuickMart IOMS - Create a sell or purchase order';
$pageViewport = 'width=device-width, initial-scale=1.0';
$pageStylesheets = [
    '../../dist/css/dashboard.min.css?v=ui10',
    '../../dist/css/create-order.min.css?v=print01',
    '../../dist/css/common.min.css?v=ui12',
];
$pageScript = '../../dist/js/create-order.min.js';
$includePoppinsFont = true;
?>
<?php require __DIR__ . '/partials/authenticated-head.php'; ?>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content create-order-page">
        <div class="container-fluid create-order-container">
            <?php require __DIR__ . '/partials/create-order/workspace.php'; ?>

            <?php require __DIR__ . '/partials/create-order/recent-orders.php'; ?>
        </div>
    </main>

    <?php require __DIR__ . '/partials/create-order/dialogs.php'; ?>

<?php require __DIR__ . '/partials/authenticated-script-loader.php'; ?>
</body>

</html>
