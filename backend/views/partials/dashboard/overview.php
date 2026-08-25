            <section class="dashboard-hero" aria-labelledby="dashboardPageTitle">
                <div class="dashboard-hero-copy">
                    <p class="dashboard-eyebrow">
                        <span class="dashboard-eyebrow-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M5 12h14M12 5v14" />
                            </svg>
                        </span>
                        Live operations
                    </p>
                    <h1 class="page-title" id="dashboardPageTitle">Operations overview</h1>
                    <p class="dashboard-subtitle" id="welcomeMessage">
                        Welcome back. Here is the latest operational picture.
                    </p>
                </div>
                <div class="dashboard-clock" aria-label="Current local time">
                    <span class="dashboard-clock-label">Today</span>
                    <time class="dashboard-clock-date qm-ltr" id="currentDate"></time>
                    <time class="dashboard-clock-time qm-ltr" id="currentTime"></time>
                </div>
            </section>

            <div id="dashboardState" class="dashboard-state alert d-none" role="status" aria-live="polite" hidden>
                <span class="dashboard-state-copy">
                    <span class="dashboard-state-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 8v4m0 4h.01M10.3 3.4 2.9 19a2 2 0 0 0 1.7 3h14.8a2 2 0 0 0 1.7-3L13.7 3.4a2 2 0 0 0-3.4 0Z" />
                        </svg>
                    </span>
                    <span id="dashboardStateMessage"></span>
                </span>
                <button type="button" class="btn btn-sm dashboard-state-retry d-none" id="dashboardRetryBtn"
                    hidden>Retry</button>
            </div>

            <div id="reportingRestriction" class="dashboard-restriction alert d-none" role="status"
                aria-live="polite" hidden>
                <span class="dashboard-restriction-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a9 9 0 1 0 9 9 9 9 0 0 0-9-9Zm0 5v4m0 4h.01" />
                    </svg>
                </span>
                <span id="reportingRestrictionMessage">
                    Reporting KPIs are available to Administrators only. The dashboard is showing data available to
                    your role.
                </span>
            </div>

            <section class="dashboard-section dashboard-kpi-section" aria-labelledby="dashboardKpiHeading">
                <div class="visually-hidden" id="dashboardKpiHeading">Key operating metrics</div>
                <div class="dashboard-kpi-grid">
                    <article class="dashboard-kpi-card kpi-card card">
                        <div class="dashboard-kpi-topline">
                            <span class="dashboard-kpi-label">Total products</span>
                            <span class="dashboard-kpi-icon dashboard-kpi-icon-amber" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" />
                                    <path d="m4.4 7.7 7.6 4.4 7.6-4.4M12 12.1V21" />
                                </svg>
                            </span>
                        </div>
                        <div class="dashboard-kpi-value qm-ltr" id="totalProducts">—</div>
                        <div class="dashboard-kpi-foot" id="totalProductsTrend">Awaiting live data</div>
                    </article>

                    <article class="dashboard-kpi-card kpi-card card dashboard-kpi-card-alert">
                        <div class="dashboard-kpi-topline">
                            <span class="dashboard-kpi-label">Stock alerts</span>
                            <span class="dashboard-kpi-icon dashboard-kpi-icon-danger" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10.3 3.4 2.9 19a2 2 0 0 0 1.7 3h14.8a2 2 0 0 0 1.7-3L13.7 3.4a2 2 0 0 0-3.4 0Z" />
                                    <path d="M12 8v4m0 4h.01" />
                                </svg>
                            </span>
                        </div>
                        <div class="dashboard-kpi-value qm-ltr" id="lowStock">—</div>
                        <div class="dashboard-kpi-foot" id="lowStockTrend">Awaiting live data</div>
                    </article>

                    <article class="dashboard-kpi-card kpi-card card">
                        <div class="dashboard-kpi-topline">
                            <span class="dashboard-kpi-label">Orders visible</span>
                            <span class="dashboard-kpi-icon dashboard-kpi-icon-teal" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 3h9l3 3v15H6zM9 12h6M9 16h6M9 8h3" />
                                </svg>
                            </span>
                        </div>
                        <div class="dashboard-kpi-value qm-ltr" id="totalOrders">—</div>
                        <div class="dashboard-kpi-foot" id="totalOrdersTrend">Awaiting live data</div>
                    </article>

                    <article class="dashboard-kpi-card kpi-card card dashboard-kpi-card-revenue">
                        <div class="dashboard-kpi-topline">
                            <span class="dashboard-kpi-label">Net revenue</span>
                            <span class="dashboard-kpi-icon dashboard-kpi-icon-ink" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19V5h16v14H4Z" />
                                    <path d="m7 15 3-3 2 2 5-6M15 8h2v2" />
                                </svg>
                            </span>
                        </div>
                        <div class="dashboard-kpi-value dashboard-kpi-value-revenue qm-ltr" id="netProfit">
                            <span id="netProfitValue">—</span>
                        </div>
                        <div class="dashboard-kpi-breakdown">
                            <span class="dashboard-kpi-breakdown-item dashboard-kpi-breakdown-sales">
                                <span>Sales</span>
                                <span class="qm-ltr" id="totalSales">—</span>
                            </span>
                            <span class="dashboard-kpi-breakdown-item dashboard-kpi-breakdown-purchases">
                                <span>Purchases</span>
                                <span class="qm-ltr" id="totalPurchases">—</span>
                            </span>
                        </div>
                    </article>
                </div>
            </section>

            <section class="dashboard-section dashboard-actions-section" aria-labelledby="quickActionsHeading">
                <div class="dashboard-section-heading">
                    <div>
                        <p class="dashboard-section-kicker">Workflow shortcuts</p>
                        <h2 class="dashboard-section-title" id="quickActionsHeading">Quick actions</h2>
                    </div>
                    <span class="dashboard-section-note">Keep the ledger moving</span>
                </div>
                <div class="dashboard-actions-grid">
                    <div class="dashboard-action-slot" data-admin-only>
                        <a href="products.php" class="quick-action-btn">
                            <span class="action-icon dashboard-action-icon dashboard-action-icon-amber"
                                aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                            </span>
                            <span class="dashboard-action-copy">
                                <strong>Add product</strong>
                                <small>Update the catalogue</small>
                            </span>
                        </a>
                    </div>
                    <div class="dashboard-action-slot">
                        <a href="create-order.php" class="quick-action-btn">
                            <span class="action-icon dashboard-action-icon dashboard-action-icon-teal"
                                aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 3h9l3 3v15H6zM9 12h6M9 16h4M9 8h3" />
                                </svg>
                            </span>
                            <span class="dashboard-action-copy">
                                <strong>Create order</strong>
                                <small>Record a sell or purchase</small>
                            </span>
                        </a>
                    </div>
                    <div class="dashboard-action-slot" data-admin-only>
                        <a href="reports.php" class="quick-action-btn">
                            <span class="action-icon dashboard-action-icon dashboard-action-icon-info"
                                aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 19V5h16v14H4Z" />
                                    <path d="M8 16v-4m4 4V8m4 8v-6" />
                                </svg>
                            </span>
                            <span class="dashboard-action-copy">
                                <strong>View reports</strong>
                                <small>Review the verified ledger</small>
                            </span>
                        </a>
                    </div>
                    <div class="dashboard-action-slot admin-only-action" id="manageStaffAction">
                        <a href="staff.php" class="quick-action-btn">
                            <span class="action-icon dashboard-action-icon dashboard-action-icon-warning"
                                aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 8a3 3 0 1 1 0 6M20 21v-2a4 4 0 0 0-2.5-3.7" />
                                </svg>
                            </span>
                            <span class="dashboard-action-copy">
                                <strong>Manage staff</strong>
                                <small>Maintain access and roles</small>
                            </span>
                        </a>
                    </div>
                </div>
            </section>
