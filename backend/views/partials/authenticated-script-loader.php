<?php
/**
 * Shared deterministic script order for authenticated QuickMart views.
 *
 * @var string $pageScript
 */
?>
    <script src="../../assets/vendor/bootstrap/bootstrap.bundle.min.js?v=bootstrap-5.3.0"></script>
    <script src="../../dist/js/common.min.js"></script>
    <script src="<?= htmlspecialchars($pageScript, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
