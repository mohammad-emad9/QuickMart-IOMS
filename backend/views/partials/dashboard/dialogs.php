    <div class="modal fade dashboard-modal" id="orderDetailsModal" tabindex="-1"
        aria-labelledby="orderDetailsModalLabel" aria-describedby="modalOrderState" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <p class="modal-kicker">Order record</p>
                        <h2 class="modal-title" id="orderDetailsModalLabel">
                            Order details
                            <span class="modal-order-id qm-ltr" id="modalOrderId">—</span>
                        </h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close order details"></button>
                </div>

                <div class="modal-body">
                    <div class="dashboard-order-meta-grid">
                        <div class="dashboard-order-meta-card">
                            <span class="dashboard-meta-label">Party</span>
                            <strong id="modalCustomerName">—</strong>
                            <span class="dashboard-meta-note" id="modalCustomerContact">Staff: —</span>
                        </div>
                        <div class="dashboard-order-meta-card">
                            <span class="dashboard-meta-label">Order context</span>
                            <strong class="qm-ltr" id="modalOrderDate">—</strong>
                            <span class="dashboard-meta-note">Type: <span id="modalOrderType"
                                    class="order-type-badge">—</span></span>
                        </div>
                    </div>

                    <div id="modalOrderState" class="dashboard-modal-state alert d-none" role="status"
                        aria-live="polite" hidden></div>

                    <div class="dashboard-modal-section-heading">
                        <div>
                            <p class="dashboard-section-kicker">Line items</p>
                            <h3>Stored order details</h3>
                        </div>
                        <span class="dashboard-modal-section-note">Prices are from the order record</span>
                    </div>
                    <div class="dashboard-table-wrap dashboard-modal-table-wrap">
                        <table class="table dashboard-table dashboard-modal-table">
                            <caption class="visually-hidden">Order line items</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Product</th>
                                    <th scope="col">Qty</th>
                                    <th scope="col">Sold price</th>
                                    <th scope="col">Line total</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsBody"></tbody>
                        </table>
                    </div>

                    <div class="dashboard-order-total">
                        <div>
                            <span class="dashboard-meta-label">Item count</span>
                            <strong class="qm-ltr" id="modalItemCount">—</strong>
                        </div>
                        <div class="dashboard-order-total-value">
                            <span class="dashboard-meta-label">Stored total</span>
                            <strong class="qm-ltr" id="modalTotal">—</strong>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printOrderBtn">
                        <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z" />
                        </svg>
                        Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade dashboard-modal dashboard-all-orders-modal" id="allOrdersModal" tabindex="-1"
        aria-labelledby="allOrdersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <p class="modal-kicker">Order activity</p>
                        <h2 class="modal-title" id="allOrdersModalLabel">All visible orders</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close all orders"></button>
                </div>
                <div class="modal-body">
                    <div class="dashboard-filter-bar" role="group" aria-label="Filter orders by type">
                        <button type="button" class="btn dashboard-filter-button active" id="filterAll"
                            aria-pressed="true">All orders</button>
                        <button type="button" class="btn dashboard-filter-button" id="filterSell" aria-pressed="false">
                            Sell orders
                        </button>
                        <button type="button" class="btn dashboard-filter-button" id="filterPurchase"
                            aria-pressed="false">Purchase orders</button>
                    </div>
                    <div class="dashboard-table-wrap">
                        <table class="table dashboard-table">
                            <caption class="visually-hidden">All visible orders</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Order ID</th>
                                    <th scope="col">Type</th>
                                    <th scope="col">Party</th>
                                    <th scope="col">Items</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Date</th>
                                    <th scope="col"><span class="visually-hidden">Action</span></th>
                                </tr>
                            </thead>
                            <tbody id="allOrdersTableBody">
                                <tr>
                                    <td colspan="7">
                                        <div class="dashboard-inline-state dashboard-inline-state-loading"
                                            role="status" aria-live="polite">
                                            <span class="dashboard-skeleton-line dashboard-skeleton-line-wide"></span>
                                            <span class="dashboard-skeleton-line dashboard-skeleton-line-short"></span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="create-order.php" class="btn btn-primary">
                        <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        Create new order
                    </a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade dashboard-modal dashboard-logout-modal" id="logoutModal" tabindex="-1"
        aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="dashboard-logout-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" />
                        </svg>
                    </div>
                    <h2 class="modal-title" id="logoutModalLabel">Sign out?</h2>
                    <p>End this QuickMart session on this device.</p>
                    <div class="dashboard-logout-actions">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmLogoutBtn">Sign out</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
