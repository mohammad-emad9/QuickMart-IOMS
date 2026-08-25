<?php
// Authentication Guard - Reports are available to administrators only.
require_once __DIR__ . '/../core/auth_check.php';
// auth_check.php performs the shared authenticated-session check on include.
// Keep the Reports page role gate here without running that check twice.
if (!isAdmin()) {
    renderForbiddenPage();
}
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Reports and analytics">
    <title>QuickMart IOMS - Reports</title>
    <link rel="icon" type="image/png" href="../../assets/icons/icon-512.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/reports/reports.css?v=ui12">
    <link rel="stylesheet" href="../../assets/common.css?v=ui12">
</head>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content reports-page">
        <div class="reports-content">
            <header class="reports-hero">
                <div class="reports-hero-copy">
                    <div class="reports-eyebrow">
                        <span class="reports-eyebrow-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19V5h16v14H4Zm4-3v-4m4 4V8m4 8V6" />
                            </svg>
                        </span>
                        <span>Administrator workspace</span>
                    </div>
                    <h1 class="reports-title">Reports &amp; Analytics</h1>
                    <p class="reports-subtitle">A calm, evidence-led view of commercial activity and inventory health.</p>
                </div>

                <div class="reports-actions" aria-label="Report actions">
                    <button type="button" class="reports-button reports-button--secondary" id="refreshReportsBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 11a8 8 0 0 0-14.9-4M4 5v4h4M4 13a8 8 0 0 0 14.9 4M20 19v-4h-4" />
                        </svg>
                        <span>Refresh report</span>
                    </button>
                    <button type="button" class="reports-button reports-button--primary" id="exportReportBtn" disabled aria-disabled="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 19h14" />
                        </svg>
                        <span>Export CSV</span>
                    </button>
                </div>
            </header>

            <section id="reportsState" class="reports-state" role="status" aria-live="polite" hidden>
                <div class="reports-state__content">
                    <span class="reports-state__icon" data-report-state-icon aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 8v5m0 3h.01M10.3 3.8 2.8 17a2 2 0 0 0 1.75 3h14.9a2 2 0 0 0 1.75-3l-7.5-13.2a2 2 0 0 0-3.4 0Z" />
                        </svg>
                    </span>
                    <span id="reportsStateMessage"></span>
                </div>
                <button type="button" class="reports-button reports-button--compact reports-button--state d-none" id="reportsRetryBtn" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 11a8 8 0 0 0-14.9-4M4 5v4h4" />
                    </svg>
                    <span>Retry</span>
                </button>
            </section>

            <section class="reports-summary" id="summaryCards" aria-labelledby="summaryHeading">
                <div class="section-heading reports-summary-heading">
                    <div>
                        <p class="section-kicker">At a glance</p>
                        <h2 id="summaryHeading">Operating picture</h2>
                    </div>
                    <p class="section-note">Backend-confirmed figures</p>
                </div>

                <div class="summary-grid">
                    <article class="metric-card metric-card--sales">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Total sales</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3v18m4-14.5C15.2 5.7 13.8 5 12 5c-2.2 0-4 1.1-4 2.7 0 4.2 8 2.1 8 6.3 0 1.7-1.7 3-4 3-1.8 0-3.3-.7-4-1.8" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value money-value" id="totalSales" dir="ltr">—</p>
                        <p class="metric-card__detail"><span class="number-value" id="sellOrderCount" dir="ltr">—</span> sell orders</p>
                    </article>

                    <article class="metric-card metric-card--purchases">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Total purchases</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 5h2l2 11h10l2-8H7m3 13h.01M17 21h.01" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value money-value" id="totalPurchases" dir="ltr">—</p>
                        <p class="metric-card__detail"><span class="number-value" id="purchaseOrderCount" dir="ltr">—</span> purchase orders</p>
                    </article>

                    <article class="metric-card metric-card--revenue">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Net revenue</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 17 9 12l3 3 7-8M15 7h4v4" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value money-value" id="netRevenue" dir="ltr">—</p>
                        <p class="metric-card__detail">Sales less purchases</p>
                    </article>

                    <article class="metric-card metric-card--products">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Total products</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5M12 12v9" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value number-value" id="totalProducts" dir="ltr">—</p>
                        <p class="metric-card__detail">Products in the catalog</p>
                    </article>

                    <article class="metric-card metric-card--attention">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Low-stock count</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 4v8m0 4h.01M5 20h14a2 2 0 0 0 1.73-3L13.73 5a2 2 0 0 0-3.46 0L3.27 17A2 2 0 0 0 5 20Z" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value number-value" id="lowStockCount" dir="ltr">—</p>
                        <p class="metric-card__detail">Low or out of stock</p>
                    </article>
                </div>
            </section>

            <section class="reports-grid reports-grid--primary" aria-label="Product performance">
                <article class="report-panel">
                    <header class="panel-heading">
                        <div>
                            <p class="section-kicker">Product performance</p>
                            <h2>Top products</h2>
                        </div>
                        <span class="panel-meta">Top five by sold quantity</span>
                    </header>
                    <div class="table-scroll">
                        <table class="reports-table reports-table--cards">
                            <caption class="visually-hidden">Top products by sold quantity and revenue</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Rank</th>
                                    <th scope="col">Product</th>
                                    <th scope="col">Sold</th>
                                    <th scope="col">Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="topProductsBody">
                                <tr class="table-state-row">
                                    <td colspan="4">Loading report data…</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="report-panel">
                    <header class="panel-heading">
                        <div>
                            <p class="section-kicker">Inventory mix</p>
                            <h2 id="categoryHeading">Products by category</h2>
                        </div>
                        <span class="panel-meta">Catalog count and stock units</span>
                    </header>
                    <div id="categoryChart" class="category-chart" aria-labelledby="categoryHeading">
                        <div class="chart-state">Loading report data…</div>
                    </div>
                </article>
            </section>

            <section class="report-panel report-panel--wide" aria-labelledby="recentOrdersHeading">
                <header class="panel-heading panel-heading--action">
                    <div>
                        <p class="section-kicker">Activity log</p>
                        <h2 id="recentOrdersHeading">Recent orders</h2>
                    </div>
                    <a class="reports-link" href="orders.php">
                        <span>View all orders</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>
                    </a>
                </header>
                <div class="table-scroll">
                    <table class="reports-table reports-table--cards reports-table--orders">
                        <caption class="visually-hidden">Ten most recent orders</caption>
                        <thead>
                            <tr>
                                <th scope="col">Order ID</th>
                                <th scope="col">Type</th>
                                <th scope="col">Staff</th>
                                <th scope="col">Customer or supplier</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody id="recentOrdersBody">
                            <tr class="table-state-row">
                                <td colspan="6">Loading report data…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="report-panel report-panel--wide" aria-labelledby="monthlyTrendHeading">
                <header class="panel-heading">
                    <div>
                        <p class="section-kicker">Activity over time</p>
                        <h2 id="monthlyTrendHeading">Monthly sales and purchases</h2>
                    </div>
                    <span class="panel-meta">Rolling six-month activity only</span>
                </header>
                <div id="monthlyTrendChart" class="monthly-trend-chart" aria-labelledby="monthlyTrendHeading">
                    <div class="chart-state">Loading report data…</div>
                </div>
            </section>

            <p class="reports-footnote">
                Report values are calculated from stored orders, order details, and the current product catalog.
            </p>
        </div>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content reports-logout-modal">
                <div class="modal-body">
                    <span class="reports-logout-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" />
                        </svg>
                    </span>
                    <h2 id="logoutModalTitle">Log out?</h2>
                    <p>Your workspace session will end on this device.</p>
                    <div class="reports-logout-actions">
                        <button type="button" class="reports-button reports-button--secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="reports-button reports-button--danger" id="confirmLogoutBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" />
                            </svg>
                            <span>Log out</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/common.js"></script>
    <script src="../../assets/reports/reports.js"></script>
</body>

</html>
