<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Create a sell or purchase order">
    <title>QuickMart IOMS - Create Order</title>
    <link rel="icon" type="image/png" href="../../assets/icons/icon-512.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/dashboard/dashboard.css?v=ui10">
    <link rel="stylesheet" href="../../assets/create-order/create-order.css?v=print01">
    <link rel="stylesheet" href="../../assets/common.css?v=ui12">
</head>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content create-order-page">
        <div class="container-fluid create-order-container">
            <section class="create-order-shell" id="createOrderModal" aria-labelledby="createOrderTitle">
                <header class="page-intro">
                    <div class="page-intro-copy">
                        <p class="qm-eyebrow">Order intake</p>
                        <h1 id="createOrderTitle">Create an order</h1>
                        <p class="page-intro-lead">Build a clear order draft, review the backend pricing, then submit it once.</p>
                    </div>
                    <div class="workflow-status-panel" aria-label="Order workspace status">
                        <span class="status-panel-label">Draft workspace</span>
                        <span class="status-panel-value" id="workflowStatus" role="status" aria-live="polite">Ready for products</span>
                    </div>
                </header>

                <div class="workflow-alert" id="workflowAlert" role="alert" aria-live="assertive" hidden></div>

                <div class="workflow-grid">
                    <form class="order-form" id="orderForm" novalidate>
                        <section class="flow-section" aria-labelledby="orderTypeHeading">
                            <div class="section-heading-row">
                                <div>
                                    <p class="section-kicker">01 / Direction</p>
                                    <h2 id="orderTypeHeading">Choose the order type</h2>
                                </div>
                                <span class="section-index" aria-hidden="true">01</span>
                            </div>

                            <fieldset class="order-type-fieldset">
                                <legend class="visually-hidden">Order type</legend>
                                <div class="order-type-grid">
                                    <div class="order-type-option">
                                        <input class="order-type-input" type="radio" name="orderType" id="sellOrder" value="sell" checked>
                                        <label class="order-type-card" for="sellOrder">
                                            <span class="order-type-icon order-type-icon-sell" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 4v16M7 9l5-5 5 5M5 20h14" />
                                                </svg>
                                            </span>
                                            <span class="order-type-copy">
                                                <span class="order-type-title">Sell order</span>
                                                <span class="order-type-description">Release stock to a customer</span>
                                            </span>
                                            <span class="selection-mark" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m5 12 4 4L19 6" />
                                                </svg>
                                            </span>
                                        </label>
                                    </div>

                                    <div class="order-type-option">
                                        <input class="order-type-input" type="radio" name="orderType" id="purchaseOrder" value="purchase">
                                        <label class="order-type-card" for="purchaseOrder">
                                            <span class="order-type-icon order-type-icon-purchase" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 20V4m5 11-5 5-5-5M5 4h14" />
                                                </svg>
                                            </span>
                                            <span class="order-type-copy">
                                                <span class="order-type-title">Purchase order</span>
                                                <span class="order-type-description">Receive stock from a supplier</span>
                                            </span>
                                            <span class="selection-mark" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m5 12 4 4L19 6" />
                                                </svg>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </section>

                        <section class="flow-section" aria-labelledby="partyHeading">
                            <div class="section-heading-row">
                                <div>
                                    <p class="section-kicker">02 / Party</p>
                                    <h2 id="partyHeading">Add the <span id="customerLabel">Customer</span> information</h2>
                                </div>
                                <span class="section-index" aria-hidden="true">02</span>
                            </div>
                            <div class="field-block">
                                <label class="field-label" for="customerName"><span id="customerLabelText">Customer or supplier</span> name <span class="required-mark" aria-hidden="true">*</span></label>
                                <input class="form-control field-control" type="text" id="customerName" name="party_name" autocomplete="organization" maxlength="120"
                                    placeholder="Enter a name for this order" aria-describedby="customerNameHelp customerNameError" required>
                                <div class="field-help" id="customerNameHelp">Use the customer or supplier name that should appear on the order.</div>
                                <div class="field-error" id="customerNameError" role="alert" hidden></div>
                            </div>
                        </section>

                        <section class="flow-section" aria-labelledby="productsHeading">
                            <div class="section-heading-row">
                                <div>
                                    <p class="section-kicker">03 / Products</p>
                                    <h2 id="productsHeading">Select products</h2>
                                </div>
                                <span class="section-index" aria-hidden="true">03</span>
                            </div>

                            <div class="product-search-block">
                                <label class="field-label" for="productSearch">Search the product catalog</label>
                                <div class="product-search-row">
                                    <div class="product-search-control">
                                        <span class="input-leading-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="11" cy="11" r="6.5" />
                                                <path d="m16 16 4.5 4.5" />
                                            </svg>
                                        </span>
                                        <input class="form-control field-control" type="search" id="productSearch" autocomplete="off"
                                            placeholder="Search by name or category" role="combobox" aria-autocomplete="list"
                                            aria-controls="productSuggestions" aria-expanded="false" aria-describedby="productAvailabilityState">
                                    </div>
                                    <button class="btn btn-secondary action-button" type="button" id="addItemBtn" data-bs-toggle="modal" data-bs-target="#selectProductModal">
                                        <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 5v14M5 12h14" />
                                        </svg>
                                        <span>Add from catalog</span>
                                    </button>
                                </div>
                                <div class="product-availability-state" id="productAvailabilityState" role="status" aria-live="polite">Loading product availability…</div>
                                <div class="product-suggestions" id="productSuggestions" role="listbox" aria-label="Product suggestions" hidden></div>
                            </div>

                            <div class="catalog-note">
                                <span class="note-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 10v6M12 7h.01" />
                                    </svg>
                                </span>
                                <span>Sell quantities are limited by the latest available stock. Purchase quantities are checked again by the backend when submitted.</span>
                            </div>
                        </section>

                        <section class="flow-section cart-section" aria-labelledby="cartHeading">
                            <div class="section-heading-row section-heading-row-tight">
                                <div>
                                    <p class="section-kicker">04 / Cart</p>
                                    <h2 id="cartHeading">Review selected products</h2>
                                </div>
                                <span class="cart-count" id="cartItemCount" aria-live="polite">0 items</span>
                            </div>

                            <div class="order-table-frame">
                                <table class="table order-table" id="orderTable" aria-describedby="orderItemsHelp">
                                    <caption class="visually-hidden">Products selected for this order</caption>
                                    <thead>
                                <tr>
                                            <th scope="col">Product</th>
                                            <th scope="col">Available</th>
                                            <th scope="col">Unit price</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Line total after confirmation</th>
                                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderTableBody"></tbody>
                                </table>
                                <div class="empty-order-message" id="emptyMessage" role="status">
                                    <span class="empty-state-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 6h16v13H4zM8 6V4h8v2M8 11h8M8 15h5" />
                                        </svg>
                                    </span>
                                    <strong>Your cart is ready for its first product</strong>
                                    <span>Search the catalog above or open the full product list to begin.</span>
                                </div>
                            </div>
                            <div class="field-error cart-error" id="orderItemsError" role="alert" hidden></div>
                            <button class="add-product-link" type="button" id="addProductLink">
                                <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                <span>Add another product</span>
                            </button>
                            <p class="field-help" id="orderItemsHelp">Prices shown here come from the product API. The backend confirms stored prices, stock, and the final total when you submit.</p>
                        </section>

                        <section class="order-summary-panel" aria-labelledby="summaryHeading">
                            <div>
                                <p class="section-kicker">05 / Ready to review</p>
                                <h2 id="summaryHeading">Order summary</h2>
                                <p class="summary-note">The total stays pending until the backend confirms stored prices, stock, and the final amount.</p>
                            </div>
                            <div class="summary-total-block">
                                <span class="summary-total-label">Backend total pending</span>
                                <span class="summary-total-value currency-value" id="grandTotalDisplay">—</span>
                            </div>
                        </section>

                        <div class="form-actions">
                            <button class="btn btn-quiet action-button" type="button" id="cancelOrderBtn">
                                <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6 6 18" />
                                </svg>
                                <span>Cancel</span>
                            </button>
                            <button class="btn btn-primary action-button action-button-primary" type="submit" id="createOrderBtn">
                                <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m5 12 4 4L19 6" />
                                </svg>
                                <span id="createOrderBtnLabel">Review order</span>
                            </button>
                        </div>
                    </form>

                    <aside class="workflow-aside" aria-label="Order workflow guide">
                        <div class="aside-card aside-card-primary">
                            <p class="aside-eyebrow">Quick guide</p>
                            <h2>Keep the handoff clear</h2>
                            <p>Complete each checkpoint before confirming. The final response from QuickMart remains the source of truth.</p>
                            <ol class="workflow-steps">
                                <li class="workflow-step is-active">
                                    <span class="step-number">01</span>
                                    <span><strong>Choose a direction</strong><small>Sell stock or receive stock.</small></span>
                                </li>
                                <li class="workflow-step">
                                    <span class="step-number">02</span>
                                    <span><strong>Add a party</strong><small>Record who the order is for.</small></span>
                                </li>
                                <li class="workflow-step">
                                    <span class="step-number">03</span>
                                    <span><strong>Review and confirm</strong><small>Submit only the quantities.</small></span>
                                </li>
                            </ol>
                        </div>
                        <div class="aside-card aside-card-note">
                            <span class="aside-note-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5" />
                                </svg>
                            </span>
                            <div><strong>Stored values stay authoritative</strong><span>Unit prices, stock, order ID, and final totals are confirmed by the API.</span></div>
                        </div>
                    </aside>
                </div>
            </section>

            <section class="recent-orders-section" aria-labelledby="recentOrdersHeading">
                <div class="recent-orders-header">
                    <div>
                        <p class="qm-eyebrow">Activity</p>
                        <h2 id="recentOrdersHeading">Recent orders</h2>
                        <p>Keep the latest order trail close while you work.</p>
                    </div>
                    <div class="recent-order-filters" role="group" aria-label="Filter recent orders">
                        <button class="filter-button is-active" type="button" id="filterAllBtn" aria-pressed="true">All</button>
                        <button class="filter-button" type="button" id="filterSellBtn" aria-pressed="false">Sell</button>
                        <button class="filter-button" type="button" id="filterPurchaseBtn" aria-pressed="false">Purchase</button>
                    </div>
                </div>
                <div class="recent-orders-table-frame">
                    <table class="table recent-orders-table" aria-describedby="recentOrdersHeading">
                        <caption class="visually-hidden">Recent orders</caption>
                        <thead>
                            <tr>
                                <th scope="col">Order ID</th>
                                <th scope="col">Staff</th>
                                <th scope="col">Customer / supplier</th>
                                <th scope="col">Items</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Type</th>
                                <th scope="col">Date</th>
                                <th scope="col"><span class="visually-hidden">Action</span></th>
                            </tr>
                        </thead>
                        <tbody id="recentOrdersTable">
                            <tr><td colspan="8" class="table-state-cell">Loading recent orders…</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content success-modal-content">
                <div class="modal-header modal-header-clean">
                    <div>
                        <p class="modal-eyebrow">Backend confirmation</p>
                        <h2 class="modal-title" id="successModalTitle">Order created</h2>
                    </div>
                    <button type="button" class="modal-close-button" data-bs-dismiss="modal" aria-label="Close confirmation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>
                <div class="modal-body success-modal-body">
                    <div class="success-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6" /></svg>
                    </div>
                    <p class="success-lead">The backend accepted this order.</p>
                    <div class="confirmed-order-number">
                        <span>Order number</span>
                        <strong class="numeric-value" id="orderNumber">—</strong>
                    </div>
                    <div class="confirmed-summary-grid">
                        <div><span>Order type</span><strong id="successOrderType">—</strong></div>
                        <div><span>Party</span><strong id="successPartyName">—</strong></div>
                        <div><span>Confirmed items</span><strong class="numeric-value" id="confirmItemsCount">—</strong></div>
                        <div><span>Backend total</span><strong class="currency-value" id="orderTotal">—</strong></div>
                    </div>
                    <div class="confirmed-items-frame">
                        <div class="confirmed-items-heading"><span>Confirmed lines</span><span class="muted-caption">Stored values</span></div>
                        <div class="table-responsive">
                            <table class="table confirmed-items-table">
                                <thead><tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Line total</th></tr></thead>
                                <tbody id="confirmItemsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-actions">
                    <button type="button" class="btn btn-quiet action-button" id="newOrderBtn">
                        <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                        <span>New order</span>
                    </button>
                    <div class="modal-action-cluster">
                        <button type="button" class="btn btn-secondary action-button" id="successInvoiceBtn">
                            <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h9l3 3v15H6zM9 12h6M9 16h6M9 8h3" /></svg>
                            <span>Invoice preview</span>
                        </button>
                        <a class="btn btn-primary action-button" id="successOrdersBtn" href="orders.php">
                            <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 3h9l3 3v15H6zM9 12h6M9 16h6M9 8h3" /></svg>
                            <span>View orders</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="selectProductModal" tabindex="-1" aria-labelledby="selectProductModalLabel" aria-describedby="selectProductModalHelp" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content product-modal-content">
                <div class="modal-header modal-header-dark">
                    <div>
                        <p class="modal-eyebrow">Catalog</p>
                        <h2 class="modal-title" id="selectProductModalLabel">Select products</h2>
                    </div>
                    <button type="button" class="modal-close-button modal-close-button-light" data-bs-dismiss="modal" aria-label="Close product catalog">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>
                <div class="modal-body product-modal-body">
                    <p class="modal-help" id="selectProductModalHelp">Select available products and set the quantity before adding them to this draft.</p>
                    <label class="field-label" for="modalProductSearch">Search catalog</label>
                    <div class="modal-search-control">
                        <span class="input-leading-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="6.5" /><path d="m16 16 4.5 4.5" /></svg></span>
                        <input class="form-control field-control" type="search" id="modalProductSearch" autocomplete="off" placeholder="Search by product, ID, or category">
                    </div>
                    <div class="modal-table-frame">
                        <table class="table modal-products-table">
                            <thead><tr><th scope="col">Select</th><th scope="col">Product</th><th scope="col">Category</th><th scope="col">Available</th><th scope="col">Unit price</th></tr></thead>
                            <tbody id="modalProductsBody">
                                <tr><td colspan="5" class="table-state-cell">Loading product availability…</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="selected-products-preview" id="selectedProductsPreview" hidden>
                        <div class="selected-preview-heading"><span>Selected products</span><span class="muted-caption" id="selectedCount">0 products selected</span></div>
                        <div id="selectedProductsList"></div>
                    </div>
                </div>
                <div class="modal-footer modal-footer-actions">
                    <button type="button" class="btn btn-quiet action-button" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary action-button" id="confirmAddProductsBtn" disabled>
                        <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14" /></svg>
                        <span>Add selected</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="invoicePreviewModal" tabindex="-1" aria-labelledby="orderReviewModalTitle" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content review-modal-content" id="orderReviewModal">
                <div class="print-invoice-header">
                    <div class="print-invoice-header__identity">
                        <p class="print-invoice-brand">QuickMart IOMS</p>
                        <p class="print-invoice-kicker">Operations ledger</p>
                        <h1>Invoice</h1>
                    </div>
                    <dl class="print-invoice-summary">
                        <div><dt>Order number</dt><dd dir="ltr">#<span id="prevOrderNumber">—</span></dd></div>
                        <div><dt>Staff</dt><dd dir="ltr" id="prevStaffId">—</dd></div>
                    </dl>
                </div>
                <div class="modal-header modal-header-clean">
                    <div>
                        <p class="modal-eyebrow" id="reviewModeLabel">Final review</p>
                        <h2 class="modal-title" id="orderReviewModalTitle">Review this order</h2>
                    </div>
                    <button type="button" class="modal-close-button" data-bs-dismiss="modal" aria-label="Close order review">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                    </button>
                </div>
                <div class="modal-body review-modal-body">
                    <div class="review-status" id="reviewStatus" role="status" aria-live="assertive" hidden></div>
                    <div class="review-meta-grid">
                        <div><span>Party</span><strong id="prevCustomerName">—</strong></div>
                        <div><span>Order type</span><strong id="prevOrderType">—</strong></div>
                        <div><span>Prepared at</span><strong class="numeric-value" id="prevDate">—</strong></div>
                    </div>
                    <div class="review-items-frame" id="reviewItemsBody">
                        <div class="review-items-heading"><span>Items</span><span class="muted-caption">Backend values on confirmation</span></div>
                        <div class="table-responsive">
                            <table class="table review-items-table">
                                <thead><tr><th>Product</th><th>Qty</th><th>Unit price</th><th>Line total after confirmation</th></tr></thead>
                                <tbody id="prevItemsBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="review-total-row"><span>Backend total</span><strong class="currency-value" id="prevGrandTotal">—</strong></div>
                    <p class="review-authority-note" id="reviewAuthorityNote">The backend will confirm stored prices, stock, line totals, and the final total when the order is submitted.</p>
                </div>
                <div class="modal-footer modal-footer-actions">
                    <button type="button" class="btn btn-quiet action-button" id="editOrderBtn" data-bs-dismiss="modal">
                        <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 5 4 4M5 19l3.5-.7L18 8.8a2.1 2.1 0 0 0-3-3L5.5 15.3z" /></svg>
                        <span>Edit draft</span>
                    </button>
                    <div class="modal-action-cluster">
                        <button type="button" class="btn btn-secondary action-button" id="printInvoiceBtn" hidden>
                            <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z" /></svg>
                            <span>Print</span>
                        </button>
                        <button type="button" class="btn btn-primary action-button" id="confirmOrderBtn">
                            <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12 4 4L19 6" /></svg>
                            <span id="confirmOrderLabel">Confirm order</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content logout-modal-content">
                <div class="modal-body logout-modal-body">
                    <div class="logout-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" /></svg></div>
                    <h2 id="logoutModalTitle">Sign out?</h2>
                    <p>Your current session will be closed.</p>
                    <div class="logout-actions">
                        <button type="button" class="btn btn-quiet action-button" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger action-button" id="confirmLogoutBtn">Sign out</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/common.js"></script>
    <script src="../../assets/create-order/create-order.js"></script>
</body>

</html>
