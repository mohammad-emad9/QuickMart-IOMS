<?php
require_once __DIR__ . '/../core/auth_check.php';
$pageTitle = 'QuickMart IOMS - Order Details';
$pageDescription = 'QuickMart IOMS - Order Details';
$pageViewport = 'width=device-width, initial-scale=1.0';
$pageStylesheets = [
    '../../dist/css/dashboard.min.css?v=ui10',
    '../../dist/css/order-details.min.css?v=print01',
    '../../dist/css/common.min.css?v=ui12',
];
$pageScript = '../../dist/js/order-details.min.js';
?>
<?php require __DIR__ . '/partials/authenticated-head.php'; ?>

<body class="order-details-page">
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content">
        <div class="order-details-page__container">
            <header class="details-toolbar no-print">
                <a href="orders.php" class="details-back-link back-link" id="backToOrdersLink">
                    <svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5 5 12l7 7M5 12h14" /></svg>
                    <span>Back to orders</span>
                </a>
                <button type="button" class="qm-button qm-button--quiet" id="printDetailsBtn">
                    <svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 14h10v7H7z" /></svg>
                    <span>Print details</span>
                </button>
            </header>

            <div id="orderDetailsState" class="order-details-state order-details-state--loading" role="status" aria-live="polite">
                <span class="state-spinner" aria-hidden="true"></span>
                <div><strong>Loading order details</strong><span>Connecting to the order service…</span></div>
            </div>

            <article class="invoice-box" id="invoiceBox" aria-busy="true" hidden>
                <header class="invoice-header">
                    <div class="invoice-header__identity">
                        <p class="invoice-print-brand">QuickMart IOMS</p>
                        <p class="eyebrow"><span class="eyebrow__mark" aria-hidden="true"></span> Operations ledger</p>
                        <h1 class="invoice-title"><span class="invoice-title__screen">Order details</span><span class="invoice-title__print">Invoice</span> <span class="invoice-header__reference" dir="ltr">#<span class="identifier-value" id="orderID">—</span></span></h1>
                        <p class="invoice-header__note">Read-only record sourced from the backend order service.</p>
                    </div>
                    <div class="invoice-header__type"><span class="invoice-header__type-label">Order direction</span><span class="order-type-badge" id="orderType">—</span></div>
                </header>

                <section class="invoice-meta" aria-label="Order information">
                    <div class="invoice-meta__item"><span>Order date</span><strong id="orderDate">—</strong></div>
                    <div class="invoice-meta__item"><span>Staff</span><strong id="staffName">—</strong></div>
                    <div class="invoice-meta__item invoice-meta__item--party"><span>Customer / supplier</span><strong id="customerName">—</strong></div>
                </section>

                <section class="invoice-items" aria-labelledby="orderItemsTitle">
                    <div class="invoice-section-heading"><div><p class="section-kicker section-label">Line items</p><h2 id="orderItemsTitle">Products in this order</h2></div><span class="items-count"><span class="numeric-value" id="itemCount">—</span> items</span></div>
                    <div class="details-table-wrap">
                        <table class="invoice-table"><caption class="visually-hidden">Products and stored prices in this order</caption><thead><tr><th scope="col">Product</th><th scope="col" class="numeric-column">Quantity</th><th scope="col" class="numeric-column">Stored unit price</th><th scope="col" class="numeric-column">Line total</th></tr></thead><tbody id="orderItemsBody"><tr class="details-table-state"><td colspan="4">Loading order items…</td></tr></tbody></table>
                    </div>
                </section>

                <section class="invoice-bottom-grid">
                    <div class="details-note-card notes-box"><span class="details-note-card__icon" aria-hidden="true"><svg class="qm-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" /><path d="M12 10v6M12 7h.01" /></svg></span><div><h2>Read-only record</h2><p>The backend does not provide edit, duplicate, delete, payment, tax, discount, or delivery fields for this order.</p></div></div>
                    <div class="details-total-card summary-box"><div><span>Backend total</span><small>Calculated from stored line prices</small></div><strong class="money-value grand-total" id="grandTotalAmount">0.00</strong></div>
                </section>

                <footer class="print-footer"><hr><p>QuickMart Operations Ledger · Order record <span id="printOrderReference">—</span></p></footer>
            </article>
        </div>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content logout-modal"><div class="modal-body text-center"><span class="logout-modal__icon" aria-hidden="true"><svg class="qm-icon" viewBox="0 0 24 24"><path d="M10 4H5v16h5M14 8l4 4-4 4M18 12H8" /></svg></span><h2 id="logoutModalLabel">Sign out?</h2><p>Your current workspace session will end.</p><div class="logout-modal__actions"><button type="button" class="qm-button qm-button--quiet" data-bs-dismiss="modal">Cancel</button><button type="button" class="qm-button qm-button--danger" id="confirmLogoutBtn"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4H5v16h5M14 8l4 4-4 4M18 12H8" /></svg><span>Sign out</span></button></div></div></div></div></div>

<?php require __DIR__ . '/partials/authenticated-script-loader.php'; ?>
</body>

</html>
