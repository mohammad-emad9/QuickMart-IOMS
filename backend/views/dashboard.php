<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="description" content="QuickMart IOMS - Operations dashboard">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <meta name="theme-color" content="#10232c">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="QuickMart">
    <meta name="application-name" content="QuickMart IOMS">
    <meta name="msapplication-TileColor" content="#10232c">
    <meta name="msapplication-config" content="none">

    <link rel="icon" type="image/png" href="../../assets/icons/icon-512.png">
    <title>QuickMart IOMS - Dashboard</title>

    <!-- Bootstrap remains the project's existing layout and modal dependency. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/dashboard/dashboard.css">
    <link rel="stylesheet" href="../../assets/common.css">
</head>

<body class="dashboard-page">
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content">
        <div class="container-fluid dashboard-content">
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
        </div>
    </main>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/common.js"></script>
    <script src="../../assets/dashboard/dashboard.js"></script>
</body>

</html>
