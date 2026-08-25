<?php
require_once __DIR__ . '/../core/auth_check.php';
$pageTitle = 'QuickMart IOMS - Products';
$pageDescription = 'QuickMart IOMS - Products and inventory management';
$pageViewport = 'width=device-width, initial-scale=1.0';
$pageStylesheets = [
    '../../dist/css/dashboard.min.css?v=ui10',
    '../../dist/css/products.min.css?v=ui10',
    '../../dist/css/common.min.css?v=ui12',
];
$pageScript = '../../dist/js/products.min.js';
?>
<?php require __DIR__ . '/partials/authenticated-head.php'; ?>

<body class="products-page">
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content">
        <div class="products-content">
            <?php require __DIR__ . '/partials/products/catalog-overview.php'; ?>

            <?php require __DIR__ . '/partials/products/catalog-controls.php'; ?>

            <?php require __DIR__ . '/partials/products/catalog-table.php'; ?>
        </div>
    </main>

    <?php require __DIR__ . '/partials/products/dialogs.php'; ?>

<?php require __DIR__ . '/partials/authenticated-script-loader.php'; ?>
</body>

</html>
