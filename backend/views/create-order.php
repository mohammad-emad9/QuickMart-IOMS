<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Create New Order">
    <title>QuickMart IOMS - Create Order</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/icons/icon-512.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Dashboard Styles (shared) -->
    <link rel="stylesheet" href="../../assets/dashboard/dashboard.css">
    <!-- Create Order Page Styles -->
    <link rel="stylesheet" href="../../assets/create-order/create-order.css">
    <!-- Common Shared Styles -->
    <link rel="stylesheet" href="../../assets/common.css">
</head>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid px-4 py-4">
            <!-- Order Form Card -->
            <div class="card shadow-lg border-0 order-card">
                <div class="card-body p-4 p-lg-5">

                    <!-- Page Title -->
                    <div class="text-center mb-4">
                        <h3 class="fw-bold text-dark">
                            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>CREATE NEW ORDER
                        </h3>
                        <p class="text-muted small">Fill in the details below to create a new order</p>
                    </div>

                    <!-- Order Type Section -->
                    <div class="section-header">
                        <i class="fas fa-exchange-alt me-2"></i>Order Type
                    </div>
                    <div class="order-type-container mb-4">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="orderType" id="sellOrder" value="sell" checked>
                            <label class="btn btn-outline-success order-type-btn" for="sellOrder">
                                <i class="fas fa-arrow-down me-2"></i>SELL ORDER
                                <small class="d-block text-muted">Sell to Customer</small>
                            </label>

                            <input type="radio" class="btn-check" name="orderType" id="purchaseOrder" value="purchase">
                            <label class="btn btn-outline-primary order-type-btn" for="purchaseOrder">
                                <i class="fas fa-arrow-up me-2"></i>PURCHASE ORDER
                                <small class="d-block text-muted">Buy from Supplier</small>
                            </label>
                        </div>
                    </div>

                    <!-- Customer/Supplier Information Section -->
                    <div class="section-header">
                        <i class="fas fa-user me-2"></i><span id="customerLabel">Customer</span> Information
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Name:</label>
                            <input type="text" class="form-control" id="customerName" placeholder="Enter name">
                        </div>
                    </div>

                    <!-- Add Products Section -->
                    <div class="section-header">
                        <i class="fas fa-box me-2"></i>Add Products To Order
                    </div>

                    <!-- Product Search -->
                    <div class="product-search-container mb-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="fas fa-search text-primary"></i>
                            </span>
                            <input type="text" class="form-control" id="productSearch"
                                placeholder="Search products... (Type 'RTX' or 'SSD')">
                            <button type="button" class="btn btn-dark" id="addItemBtn" data-bs-toggle="modal"
                                data-bs-target="#selectProductModal">
                                <i class="fas fa-plus me-1"></i> Add Item
                            </button>
                        </div>
                        <!-- Product Suggestions Dropdown -->
                        <div class="product-suggestions" id="productSuggestions"></div>
                    </div>

                    <!-- Order Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle order-table" id="orderTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 30%">Product</th>
                                    <th style="width: 15%">Available</th>
                                    <th style="width: 15%">Current Price</th>
                                    <th style="width: 15%">Qty</th>
                                    <th style="width: 15%">Total</th>
                                    <th style="width: 10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="orderTableBody">
                                <!-- Order items will be added dynamically -->
                            </tbody>
                        </table>
                        <div class="empty-order-message text-center py-4 text-muted" id="emptyMessage">
                            <i class="fas fa-shopping-basket fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0">No products added yet. Search and add products above.</p>
                        </div>
                    </div>

                    <!-- Add Another Product Link -->
                    <div class="text-center mb-4 py-2 bg-light border rounded add-product-link" id="addProductLink"
                        style="display: none;">
                        <a href="#" class="text-decoration-none text-primary fw-semibold">
                            <i class="fas fa-plus-circle me-1"></i> Add Another Product
                        </a>
                    </div>

                    <!-- Order Summary Section -->
                    <div class="section-header">
                        <i class="fas fa-calculator me-2"></i>Order Summary
                    </div>
                    <div class="summary-totals bg-light p-4 rounded mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">ESTIMATED TOTAL:</span>
                            <span class="grand-total-display">$<span id="grandTotalDisplay">0.00</span></span>
                        </div>
                        <small class="text-muted d-block mt-2">The final total and stored prices come from the backend when the order is created.</small>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex flex-wrap justify-content-end gap-2 mt-5 pt-4 border-top">
                        <button type="button" class="btn btn-outline-secondary px-4"
                            onclick="window.location.href='dashboard.php'">
                            <i class="fas fa-times me-1"></i> CANCEL
                        </button>
                        <button type="button" class="btn btn-dark px-4 fw-bold" id="createOrderBtn">
                            <i class="fas fa-check me-1"></i> CREATE ORDER
                        </button>
                    </div>

                </div>
            </div>

            <!-- Recent Orders Section -->
            <section class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-transparent border-0 pt-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-clock me-2 text-primary"></i>Recent Orders
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success" id="filterSellBtn">
                            <i class="fas fa-arrow-down me-1"></i>Sell
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-info" id="filterPurchaseBtn">
                            <i class="fas fa-arrow-up me-1"></i>Purchase
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary active" id="filterAllBtn">
                            All
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Staff</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="recentOrdersTable">
                                <!-- Orders will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-5">
                    <div class="success-icon mb-4">
                        <i class="fas fa-check-circle text-success"></i>
                    </div>
                    <h4 class="fw-bold mb-2">Order Created Successfully!</h4>
                    <p class="text-muted mb-2">Order <strong id="orderNumber">#0000</strong> has been created.</p>
                    <p class="fw-bold text-primary mb-4">Backend total: $<span id="orderTotal">0.00</span></p>
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-outline-primary"
                            onclick="window.location.href='create-order.php'">
                            <i class="fas fa-plus me-1"></i> New Order
                        </button>
                        <button type="button" class="btn btn-primary" onclick="window.location.href='dashboard.php'">
                            <i class="fas fa-home me-1"></i> Go to Dashboard
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <!-- Select Product Modal -->
    <div class="modal fade" id="selectProductModal" tabindex="-1" aria-labelledby="selectProductModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="selectProductModalLabel">
                        <i class="fas fa-boxes me-2"></i>Select Products
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Search in Modal -->
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" id="modalProductSearch"
                            placeholder="Search products...">
                    </div>
                    <!-- Products Table -->
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-hover table-sm">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 40px;"></th>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th class="text-center">Available</th>
                                    <th class="text-end">Price</th>
                                </tr>
                            </thead>
                            <tbody id="modalProductsBody">
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Loading products...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Selected Products Preview -->
                    <div class="mt-3 p-3 bg-light rounded border" id="selectedProductsPreview" style="display: none;">
                        <h6 class="fw-bold mb-2"><i class="fas fa-check-circle text-success me-2"></i>Selected Products:
                        </h6>
                        <div id="selectedProductsList"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <span class="me-auto text-muted" id="selectedCount">0 products selected</span>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmAddProductsBtn" disabled>
                        <i class="fas fa-plus me-1"></i>Add Selected Products
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Preview Modal (Review before confirming) -->
    <div class="modal fade" id="invoicePreviewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice me-2"></i>Review Order Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    <div class="p-4 bg-light">
                        <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                            <div>
                                <small class="text-muted d-block">BILL TO:</small>
                                <span class="fw-bold fs-5" id="prevCustomerName">-</span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">DATE:</small>
                                <span class="fw-bold" id="prevDate">-</span>
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm table-borderless mb-0">
                                <thead class="text-secondary small border-bottom">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="prevItemsBody">
                                </tbody>
                            </table>
                        </div>

                        <div class="bg-white p-3 rounded border border-dashed">
                            <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                <span class="fw-bold text-dark">ESTIMATED TOTAL:</span>
                                <span class="fw-bold text-primary fs-5">$<span id="prevGrandTotal">0.00</span></span>
                            </div>
                            <small class="text-muted d-block mt-2">Final total and stored prices are returned by the backend after confirmation.</small>
                        </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-between bg-white">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-edit me-1"></i> Edit / Modify
                    </button>

                    <button type="button" class="btn btn-success px-4 fw-bold" id="confirmOrderBtn">
                        <i class="fas fa-check-circle me-1"></i> Confirm & Process
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content"
                style="background: linear-gradient(145deg, #1a1a2e, #16213e); border: 1px solid rgba(255,255,255,0.1); border-radius: 16px;">
                <div class="modal-body text-center py-4">
                    <div class="mb-3">
                        <div
                            style="width: 70px; height: 70px; margin: 0 auto; border-radius: 50%; background: linear-gradient(135deg, #ef4444, #dc2626); display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sign-out-alt fa-2x text-white"></i>
                        </div>
                    </div>
                    <h5 class="text-white fw-bold mb-2">Logout</h5>
                    <p class="text-muted mb-4">Are you sure you want to logout?</p>
                    <div class="d-flex gap-2 justify-content-center">
                        <button type="button" class="btn btn-outline-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger px-4" id="confirmLogoutBtn">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Common Shared Scripts -->
    <script src="../../assets/common.js"></script>
    <!-- Create Order Logic -->
    <script src="../../assets/create-order/create-order.js"></script>
</body>

</html>
