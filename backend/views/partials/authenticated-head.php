<?php
/**
 * Shared document head for authenticated QuickMart views.
 *
 * @var string $pageTitle
 * @var string $pageDescription
 * @var string $pageViewport
 * @var array<int, string> $pageStylesheets
 * @var bool $includeDashboardHeadMeta
 * @var bool $includePoppinsFont
 */
$includeDashboardHeadMeta = $includeDashboardHeadMeta ?? false;
$includePoppinsFont = $includePoppinsFont ?? false;
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="<?= htmlspecialchars($pageViewport, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
    <meta name="description" content="<?= htmlspecialchars($pageDescription, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<?php if ($includeDashboardHeadMeta): ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="theme-color" content="#10232c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="QuickMart">
    <meta name="application-name" content="QuickMart IOMS">
    <meta name="msapplication-TileColor" content="#10232c">
    <meta name="msapplication-config" content="none">
<?php endif; ?>

    <link rel="icon" type="image/png" href="../../assets/icons/icon-512.png">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>

    <!-- Local Bootstrap 5.3.0 keeps authenticated pages available offline. -->
    <link rel="stylesheet" href="../../dist/css/bootstrap.min.css?v=bootstrap-5.3.0">
<?php if ($includePoppinsFont): ?>
    <link rel="stylesheet" href="../../dist/css/poppins.min.css?v=frontend-opt-01">
<?php endif; ?>
<?php foreach ($pageStylesheets as $stylesheet): ?>
    <link rel="stylesheet" href="<?= htmlspecialchars($stylesheet, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
<?php endforeach; ?>
</head>
