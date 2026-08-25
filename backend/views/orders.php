<?php
require_once __DIR__ . '/../core/auth_check.php';
$pageTitle = 'QuickMart IOMS - Orders';
$pageDescription = 'QuickMart IOMS - Orders Management';
$pageViewport = 'width=device-width, initial-scale=1.0';
$pageStylesheets = [
    '../../dist/css/dashboard.min.css?v=ui10',
    '../../dist/css/orders.min.css?v=ui12',
    '../../dist/css/common.min.css?v=ui12',
];
$pageScript = '../../dist/js/orders.min.js';
?>
<?php require __DIR__ . '/partials/authenticated-head.php'; ?>

<body class="orders-page">
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content">
        <div class="orders-page__container">
            <section class="orders-hero no-print" aria-labelledby="ordersPageTitle">
                <div class="orders-hero__copy">
                    <p class="eyebrow"><span class="eyebrow__mark" aria-hidden="true"></span> Operations ledger</p>
                    <h1 id="ordersPageTitle">Orders</h1>
                    <p class="orders-hero__lede">Review movement across the business with a clear, accountable order trail.</p>
                    <p class="orders-hero__meta"><span class="live-dot" aria-hidden="true"></span> Live records from the order service</p>
                </div>
                <div class="orders-hero__action">
                    <span class="orders-hero__hint">Create a sell or purchase order</span>
                    <button type="button" class="qm-button qm-button--primary qm-button--large" data-bs-toggle="modal"
                        data-bs-target="#createOrderModal" aria-controls="createOrderModal">
                        <svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        <span>New order</span>
                    </button>
                </div>
            </section>

            <section class="orders-summary" aria-label="Order summary">
                <article class="orders-metric orders-metric--ink stats-card">
                    <span class="orders-metric__icon stats-icon" aria-hidden="true">
                        <svg class="qm-icon" viewBox="0 0 24 24"><path d="M6 3h12v18H6zM9 7h6M9 11h6M9 15h4" /></svg>
                    </span>
                    <div class="stats-info"><span class="orders-metric__label">Orders in view</span><strong class="orders-metric__value numeric-value" id="totalOrdersCount" aria-live="polite">—</strong><span class="orders-metric__note">Current filters</span></div>
                </article>
                <article class="orders-metric orders-metric--teal stats-card">
                    <span class="orders-metric__icon stats-icon" aria-hidden="true">
                        <svg class="qm-icon" viewBox="0 0 24 24"><path d="M4 12h14M13 7l5 5-5 5M4 5v14" /></svg>
                    </span>
                    <div class="stats-info"><span class="orders-metric__label">Sell orders</span><strong class="orders-metric__value numeric-value" id="sellOrdersCount" aria-live="polite">—</strong><span class="orders-metric__note">Inventory leaving</span></div>
                </article>
                <article class="orders-metric orders-metric--blue stats-card">
                    <span class="orders-metric__icon stats-icon" aria-hidden="true">
                        <svg class="qm-icon" viewBox="0 0 24 24"><path d="M20 12H6M11 7l-5 5 5 5M20 5v14" /></svg>
                    </span>
                    <div class="stats-info"><span class="orders-metric__label">Purchase orders</span><strong class="orders-metric__value numeric-value" id="purchaseOrdersCount" aria-live="polite">—</strong><span class="orders-metric__note">Inventory arriving</span></div>
                </article>
                <article class="orders-metric orders-metric--amber stats-card">
                    <span class="orders-metric__icon stats-icon" aria-hidden="true">
                        <svg class="qm-icon" viewBox="0 0 24 24"><path d="M12 3v18M16.5 7.5c0-1.7-1.8-3-4.5-3S7.5 5.7 7.5 7.5 9 10 12 10s4.5 1.3 4.5 3-1.8 3.5-4.5 3.5-4.5-1.3-4.5-3" /></svg>
                    </span>
                    <div class="stats-info"><span class="orders-metric__label">Sell value</span><strong class="orders-metric__value money-value" id="totalRevenue" aria-live="polite">—</strong><span class="orders-metric__note">Backend totals</span></div>
                </article>
            </section>

            <section class="orders-panel" aria-labelledby="ordersListTitle">
                <header class="orders-panel__header">
                    <div><p class="section-kicker">Order register</p><h2 id="ordersListTitle">All orders</h2><p class="orders-panel__description">Search by order, party, staff member, or type.</p></div>
                    <div class="orders-panel__status" role="status" aria-live="polite"><span class="status-pip" aria-hidden="true"></span><span id="ordersResultSummary">Preparing order records</span></div>
                </header>

                <div class="orders-toolbar no-print">
                    <div class="order-type-filter" role="group" aria-label="Filter by order type">
                        <button type="button" class="filter-chip active" id="filterAllBtn" aria-pressed="true">All orders</button>
                        <button type="button" class="filter-chip filter-chip--sell" id="filterSellBtn" aria-pressed="false"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12h14M13 7l5 5-5 5M4 5v14" /></svg><span>Sell</span></button>
                        <button type="button" class="filter-chip filter-chip--purchase" id="filterPurchaseBtn" aria-pressed="false"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 12H6M11 7l-5 5 5 5M20 5v14" /></svg><span>Purchase</span></button>
                    </div>
                    <div class="orders-search">
                        <label class="visually-hidden" for="searchOrders">Search orders</label>
                        <svg class="qm-icon orders-search__icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4 4" /></svg>
                        <input type="search" id="searchOrders" placeholder="Search orders" autocomplete="off" aria-describedby="searchOrdersHint">
                        <span id="searchOrdersHint" class="visually-hidden">Search by order ID, party, staff, or order type.</span>
                    </div>
                </div>

                <div class="orders-filter-rail no-print" id="orderFilterControls">
                    <div class="filter-field"><label for="dateFromFilter">From date</label><div class="filter-input-wrap"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2" /><path d="M8 3v4M16 3v4M4 10h16" /></svg><input type="date" class="form-control form-control-sm" id="dateFromFilter"></div></div>
                    <div class="filter-field"><label for="dateToFilter">To date</label><div class="filter-input-wrap"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2" /><path d="M8 3v4M16 3v4M4 10h16" /></svg><input type="date" class="form-control form-control-sm" id="dateToFilter"></div></div>
                    <div class="filter-field filter-field--staff" data-admin-only><label for="staffFilter">Staff ID <span class="field-optional">Admin</span></label><input type="text" class="form-control form-control-sm" id="staffFilter" placeholder="Filter by staff ID" maxlength="20" autocomplete="off"></div>
                    <div class="orders-filter-actions"><button type="button" class="qm-button qm-button--primary" id="applyFiltersBtn"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16M7 12h10M10 19h4" /></svg><span>Apply filters</span></button><button type="button" class="qm-button qm-button--quiet" id="clearFiltersBtn">Clear</button></div>
                </div>

                <div class="orders-table-wrap">
                    <table class="orders-table"><caption class="visually-hidden">Orders visible for the current filters</caption>
                        <thead><tr><th scope="col">Order ID</th><th scope="col">Staff</th><th scope="col">Customer / supplier</th><th scope="col" class="numeric-column">Items</th><th scope="col" class="numeric-column">Amount</th><th scope="col">Type</th><th scope="col">Date</th><th scope="col" class="actions-column">Action</th></tr></thead>
                        <tbody id="ordersTableBody"><tr class="table-state-row"><td colspan="8"><div class="table-state table-state--loading" role="status"><span class="state-spinner" aria-hidden="true"></span><span>Loading orders…</span></div></td></tr></tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable"><div class="modal-content order-modal">
            <div class="modal-header order-modal__header"><div><p class="modal-kicker">Order entry</p><h2 class="modal-title" id="createOrderModalLabel">Create a new order</h2><p class="modal-subtitle">Use current inventory prices; the backend confirms the saved total.</p></div><button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close create order dialog"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg></button></div>
            <div class="modal-body order-modal__body"><form id="createOrderForm" novalidate>
                <section class="order-step"><div class="order-step__heading section-header"><span class="step-number">01</span><div><h3>Order direction</h3><p>Choose how this order affects inventory.</p></div></div><div class="order-type-options order-type-container" role="radiogroup" aria-label="Order type">
                    <input type="radio" class="btn-check" name="orderType" id="sellOrder" value="sell" checked><label class="order-type-option order-type-option--sell order-type-btn" for="sellOrder"><span class="order-type-option__icon" aria-hidden="true"><svg class="qm-icon" viewBox="0 0 24 24"><path d="M4 12h14M13 7l5 5-5 5M4 5v14" /></svg></span><span><strong>Sell order</strong><small>Release stock to a customer</small></span></label>
                    <input type="radio" class="btn-check" name="orderType" id="purchaseOrder" value="purchase"><label class="order-type-option order-type-option--purchase order-type-btn" for="purchaseOrder"><span class="order-type-option__icon" aria-hidden="true"><svg class="qm-icon" viewBox="0 0 24 24"><path d="M20 12H6M11 7l-5 5 5 5M20 5v14" /></svg></span><span><strong>Purchase order</strong><small>Receive stock from a supplier</small></span></label>
                </div></section>
                <section class="order-step"><div class="order-step__heading section-header"><span class="step-number">02</span><div><h3><span id="customerLabel">Customer</span> details</h3><p>Keep the party name attached to the order record.</p></div></div><div class="field-block field-block--wide"><label for="customerName">Name <span class="required-mark" aria-hidden="true">*</span></label><input type="text" class="form-control" id="customerName" placeholder="Enter customer or supplier name" required autocomplete="organization"></div></section>
                <section class="order-step"><div class="order-step__heading section-header"><span class="step-number">03</span><div><h3>Add products</h3><p>Search the live product catalog or open the full selector.</p></div></div>
                    <div class="product-search-container"><label class="visually-hidden" for="productSearch">Search products</label><div class="product-search-bar"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4 4" /></svg><input type="search" class="form-control" id="productSearch" placeholder="Search product name or category" autocomplete="off" aria-controls="productSuggestions" aria-expanded="false"><button type="button" class="qm-button qm-button--quiet btn" id="addItemBtn" aria-label="Open product selector"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg><span>Browse products</span></button></div><div class="product-suggestions" id="productSuggestions" role="listbox" aria-label="Product suggestions"></div></div>
                    <div class="order-items-heading"><h4>Selected items</h4><span class="order-items-heading__note">Quantity is checked before submit</span></div><div class="order-table-wrap order-table-wrap--modal"><table class="orders-table order-items-table" id="orderTable"><caption class="visually-hidden">Products selected for this order</caption><thead><tr><th scope="col">Product</th><th scope="col" class="numeric-column">Available</th><th scope="col" class="numeric-column">Current price</th><th scope="col" class="numeric-column">Quantity</th><th scope="col" class="numeric-column">Line total</th><th scope="col" class="actions-column">Action</th></tr></thead><tbody id="orderTableBody"></tbody></table></div><div class="empty-order-message" id="emptyMessage"><svg class="qm-icon empty-order-message__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 8h14l-1 11H6L5 8Z" /><path d="M8 8a4 4 0 0 1 8 0M9 12h.01M15 12h.01" /></svg><strong>No products selected</strong><span>Search above or browse the product catalog to start the order.</span></div>
                </section>
                <section class="order-total-card summary-totals" aria-label="Estimated order total"><div><span class="order-total-card__label">Estimated total</span><small>The backend remains authoritative for stored prices and the final total.</small></div><strong class="money-value grand-total-display" id="grandTotalDisplay">0.00</strong></section>
            </form></div>
            <div class="modal-footer order-modal__footer"><button type="button" class="qm-button qm-button--quiet" data-bs-dismiss="modal">Cancel</button><button type="button" class="qm-button qm-button--primary" id="createOrderBtn"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg><span>Review order</span></button></div>
        </div></div>
    </div>

    <div class="modal fade" id="selectProductModal" tabindex="-1" aria-labelledby="selectProductModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content order-modal">
        <div class="modal-header order-modal__header"><div><p class="modal-kicker">Catalog</p><h2 class="modal-title" id="selectProductModalLabel">Select products</h2><p class="modal-subtitle">Availability and price are read from the product service.</p></div><button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close product selector"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg></button></div>
        <div class="modal-body order-modal__body"><div class="product-search-bar product-search-bar--modal"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4 4" /></svg><label class="visually-hidden" for="modalProductSearch">Search products</label><input type="search" class="form-control" id="modalProductSearch" placeholder="Search by name, ID, or category" autocomplete="off"></div><div class="order-table-wrap order-table-wrap--selector"><table class="orders-table selector-table"><caption class="visually-hidden">Available products for order selection</caption><thead><tr><th scope="col"><span class="visually-hidden">Select</span></th><th scope="col">Product</th><th scope="col">Category</th><th scope="col" class="numeric-column">Available</th><th scope="col" class="numeric-column">Price</th></tr></thead><tbody id="modalProductsBody"><tr class="table-state-row"><td colspan="5"><div class="table-state table-state--loading"><span class="state-spinner" aria-hidden="true"></span><span>Loading products…</span></div></td></tr></tbody></table></div><div class="selected-products-preview" id="selectedProductsPreview" hidden><div class="selected-products-preview__heading"><strong>Selected products</strong><span id="selectedCount">0 products selected</span></div><div id="selectedProductsList"></div></div></div>
        <div class="modal-footer order-modal__footer"><span class="modal-footer__status" id="selectedCountFooter" aria-hidden="true"></span><button type="button" class="qm-button qm-button--quiet" data-bs-dismiss="modal">Cancel</button><button type="button" class="qm-button qm-button--primary" id="confirmAddProductsBtn" disabled><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg><span>Add selected</span></button></div>
    </div></div></div>

    <div class="modal fade" id="orderReviewModal" tabindex="-1" aria-labelledby="orderReviewModalLabel" aria-hidden="true" data-bs-backdrop="static"><div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content order-modal">
        <div class="modal-header order-modal__header order-modal__header--review"><div><p class="modal-kicker">Final review</p><h2 class="modal-title" id="orderReviewModalLabel">Review before confirming</h2><p class="modal-subtitle">Confirm the request before inventory is updated.</p></div><button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close order review"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg></button></div>
        <div class="modal-body order-modal__body"><div class="review-callout" role="note"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 22 20H2L12 3Z" /><path d="M12 9v5M12 17h.01" /></svg><span>Review the party, direction, products, and estimated total. The backend will re-check stock and prices.</span></div><div class="review-grid"><div class="review-card"><span class="review-card__label">Order direction</span><strong id="reviewOrderType" class="order-type-badge order-type-sell">Sell</strong><span class="review-card__meta">Staff: <span id="reviewStaffName">Authenticated staff</span></span></div><div class="review-card"><span class="review-card__label"><span id="reviewPartyTitle">Customer</span></span><strong id="reviewPartyName">-</strong><span class="review-card__meta">Party attached to this order</span></div></div><div class="review-items-heading"><h3>Order items <span class="review-items-heading__count" id="reviewItemCount">0</span></h3></div><div class="order-table-wrap order-table-wrap--modal"><table class="orders-table review-table"><caption class="visually-hidden">Items included in the order review</caption><thead><tr><th scope="col">Product</th><th scope="col" class="numeric-column">Qty</th><th scope="col" class="numeric-column">Unit price</th><th scope="col" class="numeric-column">Total</th></tr></thead><tbody id="reviewItemsBody"></tbody></table></div><div class="review-total"><span>Estimated total</span><strong class="money-value" id="reviewGrandTotal">0.00</strong></div></div>
        <div class="modal-footer order-modal__footer"><button type="button" class="qm-button qm-button--quiet" id="backToEditBtn"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5 5 12l7 7M5 12h14" /></svg><span>Back to edit</span></button><button type="button" class="qm-button qm-button--primary" id="confirmOrderBtn"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg><span>Confirm order</span></button></div>
    </div></div></div>

    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content order-modal invoice-modal">
        <div class="modal-header order-modal__header order-modal__header--success"><div><p class="modal-kicker">Saved record</p><h2 class="modal-title" id="successModalLabel">Order created</h2><p class="modal-subtitle">The backend confirmed this order and its stored prices.</p></div><button type="button" class="modal-close" data-bs-dismiss="modal" aria-label="Close confirmation"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg></button></div>
        <div class="modal-body order-modal__body"><div class="invoice-mark"><span class="invoice-mark__icon" aria-hidden="true"><svg class="qm-icon" viewBox="0 0 24 24"><path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z" /><path d="m8.5 11 2 2 5-5" /></svg></span><div><span class="invoice-mark__label">Order reference</span><strong class="identifier-value" id="orderNumber">-</strong></div></div><div class="review-grid"><div class="review-card"><span class="review-card__label">Order direction</span><strong id="confirmOrderType" class="order-type-badge order-type-sell">Sell</strong><span class="review-card__meta">Staff ID: <span id="confirmStaffName">-</span></span></div><div class="review-card"><span class="review-card__label"><span id="confirmPartyTitle">Customer</span></span><strong id="confirmPartyName">-</strong><span class="review-card__meta">Backend confirmed</span></div></div><div class="review-items-heading"><h3>Order items</h3></div><div class="order-table-wrap order-table-wrap--modal"><table class="orders-table review-table"><caption class="visually-hidden">Saved order items</caption><thead><tr><th scope="col">Product</th><th scope="col" class="numeric-column">Qty</th><th scope="col" class="numeric-column">Unit price</th><th scope="col" class="numeric-column">Total</th></tr></thead><tbody id="confirmItemsBody"></tbody></table></div><div class="review-total"><span>Total amount</span><strong class="money-value" id="confirmGrandTotal">0.00</strong></div></div>
        <div class="modal-footer order-modal__footer"><button type="button" class="qm-button qm-button--quiet" id="newOrderBtn"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg><span>New order</span></button><button type="button" class="qm-button qm-button--quiet" id="printInvoiceBtn"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 9V4h10v5M7 17H5a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2h-2M7 14h10v7H7z" /></svg><span>Print invoice</span></button><button type="button" class="qm-button qm-button--primary" data-bs-dismiss="modal"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16v14H4zM8 9h8M8 13h5" /></svg><span>View orders</span></button></div>
    </div></div></div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-sm"><div class="modal-content logout-modal"><div class="modal-body text-center"><span class="logout-modal__icon" aria-hidden="true"><svg class="qm-icon" viewBox="0 0 24 24"><path d="M10 4H5v16h5M14 8l4 4-4 4M18 12H8" /></svg></span><h2 id="logoutModalLabel">Sign out?</h2><p>Your current workspace session will end.</p><div class="logout-modal__actions"><button type="button" class="qm-button qm-button--quiet" data-bs-dismiss="modal">Cancel</button><button type="button" class="qm-button qm-button--danger" id="confirmLogoutBtn"><svg class="qm-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4H5v16h5M14 8l4 4-4 4M18 12H8" /></svg><span>Sign out</span></button></div></div></div></div></div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>
<?php require __DIR__ . '/partials/authenticated-script-loader.php'; ?>
</body>

</html>
