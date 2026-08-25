<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Products and inventory management">
    <title>QuickMart IOMS - Products</title>
    <link rel="icon" type="image/png" href="../../assets/icons/icon-512.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/dashboard/dashboard.css?v=ui10">
    <link rel="stylesheet" href="../../assets/products/products.css?v=ui10">
    <link rel="stylesheet" href="../../assets/common.css?v=ui12">
</head>

<body class="products-page">
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content">
        <div class="products-content">
            <section class="products-hero" aria-labelledby="productsPageTitle">
                <div class="products-hero-copy">
                    <p class="products-eyebrow">
                        <span class="products-eyebrow-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" />
                                <path d="m4.5 7.5 7.5 4 7.5-4M12 12v8.5" />
                            </svg>
                        </span>
                        Inventory workspace
                    </p>
                    <h1 class="page-title" id="productsPageTitle">Products &amp; Inventory</h1>
                    <p class="products-subtitle">
                        Keep the catalog accurate, spot stock pressure early, and maintain a reliable operating record.
                    </p>
                </div>
                <div class="products-hero-meta" aria-label="Catalog size">
                    <span class="products-meta-label">Catalog size</span>
                    <strong class="products-meta-value" id="productCount" aria-live="polite">Loading...</strong>
                    <span class="products-meta-note">Live inventory register</span>
                </div>
            </section>

            <section class="inventory-summary" aria-labelledby="inventorySummaryTitle">
                <div class="products-section-heading">
                    <div>
                        <p class="products-section-kicker">Inventory pulse</p>
                        <h2 class="products-section-title" id="inventorySummaryTitle">At-a-glance stock health</h2>
                    </div>
                    <span class="products-section-note">Backend-authoritative values</span>
                </div>

                <div class="inventory-summary-grid">
                    <article class="inventory-summary-card inventory-summary-card-total">
                        <div class="inventory-summary-topline">
                            <span class="inventory-summary-label">Total products</span>
                            <span class="inventory-summary-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" />
                                    <path d="m4.5 7.5 7.5 4 7.5-4M12 12v8.5" />
                                </svg>
                            </span>
                        </div>
                        <strong class="inventory-summary-value" id="summaryProductCount" aria-live="polite">—</strong>
                        <span class="inventory-summary-foot">Active catalog records</span>
                    </article>

                    <article class="inventory-summary-card inventory-summary-card-units">
                        <div class="inventory-summary-topline">
                            <span class="inventory-summary-label">Units in stock</span>
                            <span class="inventory-summary-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 8h16v12H4zM7 8V5h10v3M8 12h8M8 16h5" />
                                </svg>
                            </span>
                        </div>
                        <strong class="inventory-summary-value numeric-value" id="inventoryUnitCount"
                            aria-live="polite">—</strong>
                        <span class="inventory-summary-foot">Across all product records</span>
                    </article>

                    <article class="inventory-summary-card inventory-summary-card-low">
                        <div class="inventory-summary-topline">
                            <span class="inventory-summary-label">Low stock</span>
                            <span class="inventory-summary-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3 21 20H3L12 3Z" />
                                    <path d="M12 9v5M12 17h.01" />
                                </svg>
                            </span>
                        </div>
                        <strong class="inventory-summary-value numeric-value" id="lowStockCount" aria-live="polite">—</strong>
                        <span class="inventory-summary-foot">Needs replenishment review</span>
                    </article>

                    <article class="inventory-summary-card inventory-summary-card-out">
                        <div class="inventory-summary-topline">
                            <span class="inventory-summary-label">Out of stock</span>
                            <span class="inventory-summary-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" />
                                    <path d="m9 9 6 6M15 9l-6 6" />
                                </svg>
                            </span>
                        </div>
                        <strong class="inventory-summary-value numeric-value" id="outOfStockCount"
                            aria-live="polite">—</strong>
                        <span class="inventory-summary-foot">Requires immediate attention</span>
                    </article>
                </div>
            </section>

            <div class="products-notice products-notice-readonly" id="accessNotice" role="status" aria-live="polite" hidden>
                <span class="products-notice-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z" />
                        <path d="M12 10v5M12 7.5h.01" />
                    </svg>
                </span>
                <span class="products-notice-copy">
                    <strong>Read-only access</strong>
                    <span id="accessNoticeText">Your role can review inventory but cannot change product records.</span>
                </span>
            </div>

            <section class="products-toolbar" aria-labelledby="productsFiltersTitle">
                <div class="products-section-heading products-toolbar-heading">
                    <div>
                        <p class="products-section-kicker">Catalog controls</p>
                        <h2 class="products-section-title" id="productsFiltersTitle">Find the right record</h2>
                    </div>
                    <span class="products-section-note">Search, refine, then export the current register</span>
                </div>

                <div class="products-filter-grid">
                    <div class="filter-field filter-field-search">
                        <label for="searchInput">Search products</label>
                        <div class="products-search-control">
                            <svg class="products-control-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="10.8" cy="10.8" r="6.8" />
                                <path d="m16 16 5 5" />
                            </svg>
                            <input type="search" class="form-control" id="searchInput"
                                placeholder="Search by product name or ID" autocomplete="off">
                        </div>
                    </div>

                    <div class="filter-field">
                        <label for="categoryFilter">Category</label>
                        <select id="categoryFilter" class="form-select">
                            <option value="All">All categories</option>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="stockFilter">Stock status</label>
                        <select id="stockFilter" class="form-select">
                            <option value="All">All stock statuses</option>
                            <option value="normal">Normal only</option>
                            <option value="low">Low or out of stock</option>
                            <option value="out-of-stock">Out of stock only</option>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="sortFilter">Sort by</label>
                        <select id="sortFilter" class="form-select">
                            <option value="name">Name: A–Z</option>
                            <option value="name-desc">Name: Z–A</option>
                            <option value="price-asc">Price: low to high</option>
                            <option value="price-desc">Price: high to low</option>
                            <option value="quantity-asc">Stock: low to high</option>
                            <option value="quantity-desc">Stock: high to low</option>
                        </select>
                    </div>

                    <div class="filter-field filter-field-action" data-admin-only>
                        <span class="filter-field-label" aria-hidden="true">Record action</span>
                        <button class="btn btn-primary products-primary-action" id="addProductBtn" type="button" data-admin-only>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            <span>Add product</span>
                        </button>
                    </div>
                </div>
            </section>

            <section class="products-panel" aria-labelledby="productsListTitle">
                <div class="products-panel-heading">
                    <div>
                        <p class="products-section-kicker">Inventory register</p>
                        <h2 class="products-section-title" id="productsListTitle">Product catalog</h2>
                    </div>
                    <span class="products-panel-note" id="tableStateLabel">Ready to load</span>
                </div>

                <div class="products-table-wrap">
                    <table class="products-table" aria-describedby="showingInfo">
                        <thead>
                            <tr>
                                <th class="products-selection-column" scope="col">
                                    <label class="checkbox-hit-area" for="selectAllProducts" data-admin-only>
                                        <input type="checkbox" class="form-check-input" id="selectAllProducts"
                                            data-admin-only aria-label="Select all visible products" title="Select all visible products">
                                    </label>
                                </th>
                                <th scope="col">ID</th>
                                <th scope="col">Product name</th>
                                <th scope="col">Category</th>
                                <th scope="col" class="products-quantity-column">Quantity</th>
                                <th scope="col" class="products-price-column">Price</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="products-actions-column" data-admin-only>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody"></tbody>
                    </table>
                </div>

                <div class="products-panel-footer">
                    <p id="showingInfo" aria-live="polite">Showing 0 products</p>
                    <div class="products-panel-actions">
                        <button class="btn btn-outline-primary" id="exportBtn" type="button">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3v12M7 10l5 5 5-5M4 20h16" />
                            </svg>
                            <span>Export CSV</span>
                        </button>
                        <button class="btn btn-outline-danger" id="deleteSelectedBtn" type="button" data-admin-only disabled>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 7h14M10 11v6M14 11v6M7 7l1 13h8l1-13M9 7V4h6v3" />
                            </svg>
                            <span>Delete selected</span>
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <div class="modal fade" id="productModal" data-admin-only data-bs-keyboard="true" tabindex="-1"
        aria-labelledby="modalTitle" aria-describedby="productModalDescription" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header products-modal-header">
                    <div>
                        <p class="products-modal-kicker">Catalog record</p>
                        <h2 class="modal-title" id="modalTitle">
                            <svg class="products-modal-title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            <span>Add product</span>
                        </h2>
                    </div>
                    <button type="button" class="products-modal-close" data-bs-dismiss="modal" aria-label="Close product form">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <form id="productForm">
                    <div class="modal-body">
                        <p class="products-modal-description" id="productModalDescription">
                            Enter the fields supported by the inventory service. Prices, quantities, thresholds, and status remain server-authoritative.
                        </p>
                        <div class="products-form-alert" id="productFormError" role="alert" hidden></div>
                        <input type="hidden" id="productId">

                        <div class="products-form-grid">
                            <div class="filter-field products-form-field-wide">
                                <label for="productName">Product name</label>
                                <input type="text" class="form-control" id="productName" placeholder="Enter product name"
                                    autocomplete="off" maxlength="255" required>
                            </div>

                            <div class="filter-field">
                                <label for="productCategory">Category</label>
                                <select id="productCategory" class="form-select" required>
                                    <option value="">Select category</option>
                                </select>
                            </div>

                            <div class="filter-field">
                                <label for="productPrice">Price</label>
                                <input type="number" class="form-control" id="productPrice" placeholder="0.00"
                                    step="0.01" min="0" inputmode="decimal" required>
                            </div>

                            <div class="filter-field">
                                <label for="productQuantity">Quantity in stock</label>
                                <input type="number" class="form-control" id="productQuantity" placeholder="0" min="0"
                                    step="1" inputmode="numeric" required>
                            </div>

                            <div class="filter-field">
                                <label for="lowStockThreshold">Low-stock threshold</label>
                                <input type="number" class="form-control" id="lowStockThreshold" placeholder="20" min="0"
                                    step="1" inputmode="numeric" aria-describedby="thresholdHelp">
                                <span class="products-field-help" id="thresholdHelp">A low-stock status is set by the backend when quantity reaches this threshold.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer products-modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveProductBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 4h12l2 2v14H5zM8 4v6h8V4M8 20v-6h8v6" />
                            </svg>
                            <span>Save product</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" data-admin-only data-bs-keyboard="true" tabindex="-1"
        aria-labelledby="deleteModalTitle" aria-describedby="deleteModalDescription" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header products-modal-header products-modal-header-danger">
                    <div>
                        <p class="products-modal-kicker">Destructive action</p>
                        <h2 class="modal-title" id="deleteModalTitle">
                            <svg class="products-modal-title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3 21 20H3L12 3Z" />
                                <path d="M12 9v5M12 17h.01" />
                            </svg>
                            <span>Delete product?</span>
                        </h2>
                    </div>
                    <button type="button" class="products-modal-close" data-bs-dismiss="modal" aria-label="Close delete confirmation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body products-delete-body">
                    <div class="products-delete-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 7h14M10 11v6M14 11v6M7 7l1 13h8l1-13M9 7V4h6v3" />
                        </svg>
                    </div>
                    <p id="deleteModalDescription">This removes the selected record from the catalog. Confirm only if you are sure.</p>
                    <strong class="products-delete-name" id="deleteProductName"></strong>
                    <div class="products-form-alert" id="deleteModalError" role="alert" hidden></div>
                </div>
                <div class="modal-footer products-modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 7h14M10 11v6M14 11v6M7 7l1 13h8l1-13M9 7V4h6v3" />
                        </svg>
                        <span>Delete record</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalTitle" aria-describedby="logoutModalDescription"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content products-logout-modal">
                <div class="modal-body products-logout-body">
                    <span class="products-logout-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" />
                        </svg>
                    </span>
                    <h2 class="products-logout-title" id="logoutModalTitle">Log out?</h2>
                    <p class="products-logout-copy" id="logoutModalDescription">Your current session will be closed.</p>
                    <div class="products-logout-actions">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmLogoutBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
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
    <script src="../../assets/products/products.js"></script>
</body>

</html>
