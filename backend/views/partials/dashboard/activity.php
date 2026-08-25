            <div class="dashboard-content-grid">
                <section class="dashboard-section dashboard-inventory-section" aria-labelledby="inventoryHealthHeading">
                    <div class="dashboard-section-heading">
                        <div>
                            <p class="dashboard-section-kicker">Inventory health</p>
                            <h2 class="dashboard-section-title" id="inventoryHealthHeading">Stock at a glance</h2>
                        </div>
                        <a href="products.php" class="dashboard-text-link">
                            <span>Open products</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </a>
                    </div>

                    <div class="inventory-health-grid" aria-label="Inventory status totals">
                        <div class="inventory-health-item inventory-health-normal">
                            <span class="inventory-health-mark" aria-hidden="true"></span>
                            <span class="inventory-health-copy">
                                <span class="inventory-health-label">Normal</span>
                                <strong class="inventory-health-value qm-ltr" id="inventoryNormalCount">—</strong>
                            </span>
                        </div>
                        <div class="inventory-health-item inventory-health-low">
                            <span class="inventory-health-mark" aria-hidden="true"></span>
                            <span class="inventory-health-copy">
                                <span class="inventory-health-label">Low stock</span>
                                <strong class="inventory-health-value qm-ltr" id="inventoryLowCount">—</strong>
                            </span>
                        </div>
                        <div class="inventory-health-item inventory-health-out">
                            <span class="inventory-health-mark" aria-hidden="true"></span>
                            <span class="inventory-health-copy">
                                <span class="inventory-health-label">Out of stock</span>
                                <strong class="inventory-health-value qm-ltr" id="inventoryOutCount">—</strong>
                            </span>
                        </div>
                    </div>
                    <p class="inventory-health-note" id="inventoryHealthNote">Awaiting live inventory data.</p>

                    <div class="dashboard-list-panel">
                        <div class="dashboard-list-heading">
                            <span class="dashboard-list-title">Needs attention</span>
                            <span class="dashboard-list-meta">Low and out-of-stock items</span>
                        </div>
                        <div class="low-stock-list" id="lowStockList">
                            <div class="dashboard-inline-state dashboard-inline-state-loading" role="status"
                                aria-live="polite">
                                <span class="dashboard-skeleton-line dashboard-skeleton-line-wide"></span>
                                <span class="dashboard-skeleton-line dashboard-skeleton-line-short"></span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="dashboard-section dashboard-orders-section" aria-labelledby="recentOrdersHeading">
                    <div class="dashboard-section-heading">
                        <div>
                            <p class="dashboard-section-kicker">Order activity</p>
                            <h2 class="dashboard-section-title" id="recentOrdersHeading">Recent orders</h2>
                        </div>
                        <button type="button" class="dashboard-text-link dashboard-text-button" data-bs-toggle="modal"
                            data-bs-target="#allOrdersModal">
                            <span>View all</span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14m-6-6 6 6-6 6" />
                            </svg>
                        </button>
                    </div>
                    <div class="dashboard-table-wrap">
                        <table class="table dashboard-table">
                            <caption class="visually-hidden">Recent orders</caption>
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
                            <tbody id="recentOrdersTableBody">
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
                </section>
            </div>
