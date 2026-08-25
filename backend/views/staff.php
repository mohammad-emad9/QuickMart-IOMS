<?php
// Staff management is restricted to administrators.
// auth_check.php verifies the session on include; enforce the Admin role gate.
require_once __DIR__ . '/../core/auth_check.php';
if (!isAdmin()) {
    renderForbiddenPage();
}
$pageTitle = 'QuickMart IOMS - Staff Management';
$pageDescription = 'QuickMart IOMS - Staff management';
$pageViewport = 'width=device-width, initial-scale=1.0';
$pageStylesheets = [
    '../../dist/css/staff.min.css?v=ui10',
    '../../dist/css/common.min.css?v=ui12',
];
$pageScript = '../../dist/js/staff.min.js';
?>
<?php require __DIR__ . '/partials/authenticated-head.php'; ?>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content staff-page">
        <div class="staff-content">
            <?php require __DIR__ . '/partials/staff/states.php'; ?>

            <?php require __DIR__ . '/partials/staff/directory.php'; ?>
        </div>
    </main>

    <?php require __DIR__ . '/partials/staff/dialogs.php'; ?>

<?php require __DIR__ . '/partials/authenticated-script-loader.php'; ?>
</body>

</html>
