<?php
// Profile is available to all authenticated roles.
require_once __DIR__ . '/../core/auth_check.php';
$pageTitle = 'QuickMart IOMS - Profile';
$pageDescription = 'QuickMart IOMS - Profile and account settings';
$pageViewport = 'width=device-width, initial-scale=1.0';
$pageStylesheets = [
    '../../dist/css/profile.min.css?v=ui10',
    '../../dist/css/common.min.css?v=ui12',
];
$pageScript = '../../dist/js/profile.min.js';
?>
<?php require __DIR__ . '/partials/authenticated-head.php'; ?>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content profile-page">
        <div class="profile-content">
            <?php require __DIR__ . '/partials/profile/status-and-hero.php'; ?>

            <div class="profile-layout">
                <?php require __DIR__ . '/partials/profile/identity-card.php'; ?>

                <div class="profile-workspace">
                    <?php require __DIR__ . '/partials/profile/activity-stats.php'; ?>

                    <?php require __DIR__ . '/partials/profile/settings.php'; ?>
                </div>
            </div>
        </div>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <?php require __DIR__ . '/partials/profile/dialogs.php'; ?>

<?php require __DIR__ . '/partials/authenticated-script-loader.php'; ?>
</body>

</html>
