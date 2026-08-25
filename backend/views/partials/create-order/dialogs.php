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
