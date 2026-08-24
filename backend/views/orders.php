<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Orders Management">
    <title>QuickMart IOMS - Orders</title>
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
    <!-- Orders Page Styles -->
    <link rel="stylesheet" href="../../assets/orders/orders.css">
    <!-- Common Shared Styles -->
    <link rel="stylesheet" href="../../assets/common.css">
</head>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid px-4 py-4">
            <!-- Page Header -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="page-title mb-1">
                        <i class="fas fa-file-invoice me-2 text-primary"></i>Orders Management
                    </h1>
                    <p class="text-muted mb-0">View and manage all orders</p>
                </div>
                <button type="button" class="btn btn-primary btn-lg px-4 shadow" data-bs-toggle="modal"
                    data-bs-target="#createOrderModal">
                    <i class="fas fa-plus me-2"></i>New Order
                </button>
            </div>

            <!-- Orders Stats Cards -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="stats-card bg-primary text-white">
                        <div class="stats-icon"><i class="fas fa-receipt"></i></div>
                        <div class="stats-info">
                            <h3 id="totalOrdersCount">0</h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card bg-success text-white">
                        <div class="stats-icon"><i class="fas fa-arrow-down"></i></div>
                        <div class="stats-info">
                            <h3 id="sellOrdersCount">0</h3>
                            <p>Sell Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card bg-info text-white">
                        <div class="stats-icon"><i class="fas fa-arrow-up"></i></div>
                        <div class="stats-info">
                            <h3 id="purchaseOrdersCount">0</h3>
                            <p>Purchase Orders</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card bg-warning text-dark">
                        <div class="stats-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stats-info">
                            <h3>$<span id="totalRevenue">0</span></h3>
                            <p>Total Revenue</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Orders Table Section -->
            <section class="card shadow-sm border-0">
                <div
                    class="card-header bg-transparent border-0 pt-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-list me-2 text-primary"></i>All Orders
                    </h5>
                    <div class="d-flex gap-2 flex-wrap">
                        <!-- Filter Buttons -->
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary active" id="filterAllBtn">
                                All
                            </button>
                            <button type="button" class="btn btn-outline-success" id="filterSellBtn">
                                <i class="fas fa-arrow-down me-1"></i>Sell
                            </button>
                            <button type="button" class="btn btn-outline-info" id="filterPurchaseBtn">
                                <i class="fas fa-arrow-up me-1"></i>Purchase
                            </button>
                        </div>
                        <!-- Search Box -->
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" class="form-control" id="searchOrders" placeholder="Search orders...">
                        </div>
                    </div>
                </div>
                <div class="card-body border-top py-3">
                    <div class="row g-2 align-items-end" id="orderFilterControls">
                        <div class="col-sm-6 col-lg-2">
                            <label class="form-label small text-muted mb-1" for="dateFromFilter">From</label>
                            <input type="date" class="form-control form-control-sm" id="dateFromFilter">
                        </div>
                        <div class="col-sm-6 col-lg-2">
                            <label class="form-label small text-muted mb-1" for="dateToFilter">To</label>
                            <input type="date" class="form-control form-control-sm" id="dateToFilter">
                        </div>
                        <div class="col-sm-6 col-lg-3" data-admin-only>
                            <label class="form-label small text-muted mb-1" for="staffFilter">Staff ID</label>
                            <input type="text" class="form-control form-control-sm" id="staffFilter"
                                placeholder="Admin filter only" maxlength="20" autocomplete="off">
                        </div>
                        <div class="col-sm-6 col-lg-auto d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-primary" id="applyFiltersBtn">
                                <i class="fas fa-filter me-1"></i>Apply
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="clearFiltersBtn">
                                Clear
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Staff</th>
                                    <th>Customer/Supplier</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="ordersTableBody">
                                <!-- Orders will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Create Order Modal -->
    <div class="modal fade" id="createOrderModal" tabindex="-1" aria-labelledby="createOrderModalLabel"
        aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="createOrderModalLabel">
                        <i class="fas fa-file-invoice-dollar me-2"></i>CREATE NEW ORDER
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Order Type Section -->
                    <div class="section-header mb-3">
                        <i class="fas fa-exchange-alt me-2"></i>Order Type
                    </div>
                    <div class="order-type-container mb-4">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="orderType" id="sellOrder" value="sell" checked>
                            <label class="btn btn-outline-success order-type-btn py-3" for="sellOrder">
                                <i class="fas fa-arrow-down me-2"></i>SELL ORDER
                                <small class="d-block text-muted">Sell to Customer</small>
                            </label>

                            <input type="radio" class="btn-check" name="orderType" id="purchaseOrder" value="purchase">
                            <label class="btn btn-outline-primary order-type-btn py-3" for="purchaseOrder">
                                <i class="fas fa-arrow-up me-2"></i>PURCHASE ORDER
                                <small class="d-block text-muted">Buy from Supplier</small>
                            </label>
                        </div>
                    </div>

                    <!-- Customer/Supplier Information Section -->
                    <div class="section-header mb-3">
                        <i class="fas fa-user me-2"></i><span id="customerLabel">Customer</span> Information
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Name: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerName" placeholder="Enter name">
                        </div>
                    </div>

                    <!-- Add Products Section -->
                    <div class="section-header mb-3">
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
                            <button type="button" class="btn btn-dark" id="addItemBtn">
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

                    <!-- Order Summary Section -->
                    <div class="section-header mb-3">
                        <i class="fas fa-calculator me-2"></i>Order Summary
                    </div>
                    <div class="summary-totals bg-light p-4 rounded mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold fs-5">ESTIMATED TOTAL:</span>
                            <span class="grand-total-display fs-4 fw-bold text-primary">$<span
                                    id="grandTotalDisplay">0.00</span></span>
                        </div>
                        <small class="text-muted d-block mt-2">The final total and stored prices come from the backend when the order is created.</small>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> CANCEL
                    </button>
                    <button type="button" class="btn btn-success px-4 fw-bold" id="createOrderBtn">
                        <i class="fas fa-check me-1"></i> CREATE ORDER
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Select Product Modal -->
    <div class="modal fade" id="selectProductModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
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

    <!-- Order Review Modal (Step 1 - Before Confirmation) -->
    <div class="modal fade" id="orderReviewModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title">
                        <i class="fas fa-clipboard-check me-2"></i>Review Order Before Confirming
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Alert Message -->
                    <div class="alert alert-warning d-flex align-items-center mb-4" role="alert">
                        <i class="fas fa-exclamation-triangle me-2 fs-4"></i>
                        <div>
                            <strong>Please review the order details carefully.</strong><br>
                            Once confirmed, the order will be created and stock will be updated.
                        </div>
                    </div>

                    <!-- Order Info Row -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="fw-bold text-muted mb-2">
                                    <i class="fas fa-info-circle me-1"></i>Order Information
                                </h6>
                                <p class="mb-1"><strong>Type:</strong> <span id="reviewOrderType"
                                        class="badge bg-success">Sell</span></p>
                                <p class="mb-0"><strong>Staff:</strong> <span id="reviewStaffName">Authenticated staff</span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="fw-bold text-muted mb-2">
                                    <i class="fas fa-user me-1"></i><span id="reviewPartyTitle">Customer</span> Details
                                </h6>
                                <p class="mb-0"><strong>Name:</strong> <span id="reviewPartyName">-</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Review Items -->
                    <h6 class="fw-bold mb-3"><i class="fas fa-box me-1"></i>Order Items (<span
                            id="reviewItemCount">0</span> items)</h6>
                    <div class="table-responsive mb-4" style="max-height: 200px;">
                        <table class="table table-bordered table-sm table-hover">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width: 40%">Product</th>
                                    <th class="text-center" style="width: 15%">Qty</th>
                                    <th class="text-end" style="width: 20%">Unit Price</th>
                                    <th class="text-end" style="width: 25%">Total</th>
                                </tr>
                            </thead>
                            <tbody id="reviewItemsBody">
                                <!-- Items will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Review Summary -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="bg-light p-3 rounded border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5">ESTIMATED TOTAL:</span>
                                    <span class="fw-bold fs-4 text-primary">$<span
                                            id="reviewGrandTotal">0.00</span></span>
                                </div>
                                <small class="text-muted d-block mt-2">The final total and stored prices come from the backend.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" id="backToEditBtn">
                        <i class="fas fa-edit me-1"></i> Back to Edit
                    </button>
                    <button type="button" class="btn btn-success btn-lg px-4 fw-bold" id="confirmOrderBtn">
                        <i class="fas fa-check-circle me-1"></i> CONFIRM ORDER
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Confirmation/Invoice Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Order Created Successfully!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Invoice Header -->
                    <div class="text-center mb-4 pb-3 border-bottom">
                        <h3 class="fw-bold mb-1">
                            <i class="fas fa-shopping-cart me-2 text-primary"></i>QUICKMART
                        </h3>
                        <p class="text-muted mb-2">Inventory & Order Management System</p>
                        <h4 class="text-primary fw-bold" id="orderNumber">#ORD-0000</h4>
                    </div>

                    <!-- Order Info Row -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded h-100">
                                <h6 class="fw-bold text-muted mb-2">
                                    <i class="fas fa-info-circle me-1"></i>Order Information
                                </h6>
                                <p class="mb-1"><strong>Type:</strong> <span id="confirmOrderType"
                                        class="badge bg-success">Sell</span></p>
                                <p class="mb-1"><strong>Staff ID:</strong> <span id="confirmStaffName">-</span></p>
                                <p class="mb-0"><strong>Saved:</strong> <span>Backend confirmed</span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded h-100">
                                <h6 class="fw-bold text-muted mb-2">
                                    <i class="fas fa-user me-1"></i><span id="confirmPartyTitle">Customer</span> Details
                                </h6>
                                <p class="mb-0"><strong>Name:</strong> <span id="confirmPartyName">-</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <h6 class="fw-bold mb-3"><i class="fas fa-box me-1"></i>Order Items</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 40%">Product</th>
                                    <th class="text-center" style="width: 15%">Qty</th>
                                    <th class="text-end" style="width: 20%">Unit Price</th>
                                    <th class="text-end" style="width: 25%">Total</th>
                                </tr>
                            </thead>
                            <tbody id="confirmItemsBody">
                                <!-- Items will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Invoice Summary -->
                    <div class="row">
                        <div class="col-md-6 offset-md-6">
                            <div class="bg-light p-3 rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="fw-bold fs-5">TOTAL AMOUNT:</span>
                                    <span class="fw-bold fs-4 text-primary">$<span
                                            id="confirmGrandTotal">0.00</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-primary" id="newOrderBtn">
                        <i class="fas fa-plus me-1"></i> New Order
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i> Print Invoice
                    </button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                        <i class="fas fa-list me-1"></i> View Orders
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

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Common Shared Scripts -->
    <script src="../../assets/common.js"></script>
    <!-- Orders Page Logic -->
    <script src="../../assets/orders/orders.js"></script>
</body>

</html>
