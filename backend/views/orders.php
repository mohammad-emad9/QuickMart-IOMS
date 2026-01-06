<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Orders Management">
    <title>QuickMart IOMS - Orders</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Dashboard Styles (shared) -->
    <link rel="stylesheet" href="../../Frontend/dashboard/dashboard.css">
    <!-- Orders Page Styles -->
    <link rel="stylesheet" href="../../Frontend/orders/orders.css">
    <!-- Common Shared Styles -->
    <link rel="stylesheet" href="../../Frontend/common.css">
</head>

<body>
    <!-- Top Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow">
        <div class="container-fluid px-4">
            <!-- Brand Logo -->
            <a class="navbar-brand fw-bold d-flex align-items-center" href="dashboard.php">
                <div class="brand-icon me-2">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <span class="text-gradient">QUICK</span><span class="text-white">MART</span>
                <span class="badge bg-primary ms-2 small">IOMS</span>
            </a>

            <!-- Mobile Toggle -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-boxes me-1"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="orders.php">
                            <i class="fas fa-file-invoice me-1"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown">
                            <div class="user-avatar me-2">
                                <i class="fas fa-user"></i>
                            </div>
                            <span id="userName">Admin</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li><a class="dropdown-item" href="profile.php"><i
                                        class="fas fa-user-cog me-2"></i>Profile</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" id="logoutBtn">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

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
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Staff</th>
                                    <th>Customer/Supplier</th>
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
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Name: <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerName" placeholder="Enter name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Contact (Email):</label>
                            <input type="email" class="form-control" id="customerEmail" placeholder="Enter email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Address:</label>
                            <input type="text" class="form-control" id="customerAddress" placeholder="Enter address">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone:</label>
                            <input type="tel" class="form-control" id="customerPhone" placeholder="Enter phone">
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
                                    <th style="width: 15%">Price</th>
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
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            <div class="summary-inputs">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Tax Rate:</label>
                                    <input type="text" class="form-control bg-light" value="5% (Auto)" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Discount ($):</label>
                                    <input type="number" class="form-control" id="discountInput" value="0" min="0"
                                        step="0.01">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Shipping ($):</label>
                                    <input type="number" class="form-control" id="shippingInput" value="0" min="0"
                                        step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="summary-totals bg-light p-4 rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span class="fw-semibold">$<span id="subtotalDisplay">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-muted">
                                    <span>Tax (5%):</span>
                                    <span>$<span id="taxDisplay">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <span>-$<span id="discountDisplay">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-3 text-muted">
                                    <span>Shipping:</span>
                                    <span>$<span id="shippingDisplay">0.00</span></span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5">GRAND TOTAL:</span>
                                    <span class="grand-total-display fs-4 fw-bold text-primary">$<span
                                            id="grandTotalDisplay">0.00</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information Section -->
                    <div class="section-header mb-3">
                        <i class="fas fa-info-circle me-2"></i>Additional Information
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes:</label>
                            <textarea class="form-control" id="orderNotes" rows="2"
                                placeholder="Add any special instructions..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold d-block">Priority:</label>
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="priority" id="normalPriority" value="normal"
                                    checked>
                                <label class="btn btn-outline-secondary" for="normalPriority">Normal</label>

                                <input type="radio" class="btn-check" name="priority" id="urgentPriority"
                                    value="urgent">
                                <label class="btn btn-outline-danger" for="urgentPriority">
                                    <i class="fas fa-bolt me-1"></i>Urgent
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Payment Terms:</label>
                            <select class="form-select" id="paymentTerms">
                                <option value="cod">Cash on Delivery</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="credit">Credit (Net 30)</option>
                                <option value="card">Credit Card</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Delivery Date:</label>
                            <input type="date" class="form-control" id="deliveryDate">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i> CANCEL
                    </button>
                    <button type="button" class="btn btn-outline-primary px-4" id="saveDraftBtn">
                        <i class="fas fa-save me-1"></i> SAVE DRAFT
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

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice me-2"></i>Order Details - <span id="modalOrderId">ORD-001</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Order Header -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-muted mb-2"><i class="fas fa-user me-1"></i>Customer/Supplier</h6>
                                <p class="fw-bold mb-1" id="modalCustomerName">-</p>
                                <small class="text-muted" id="modalStaffName">-</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-muted mb-2"><i class="fas fa-info-circle me-1"></i>Order Info</h6>
                                <p class="mb-1"><strong>Type:</strong> <span id="modalOrderType" class="badge">-</span>
                                </p>
                                <p class="mb-0"><strong>Date:</strong> <span id="modalOrderDate">-</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h6 class="fw-bold mb-3"><i class="fas fa-box me-1"></i>Order Items</h6>
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody id="modalItemsBody">
                                <!-- Items loaded dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Order Summary -->
                    <div class="bg-light p-3 rounded">
                        <div class="row">
                            <div class="col-md-6 offset-md-6">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Subtotal:</span>
                                    <span>$<span id="modalSubtotal">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-1 text-muted">
                                    <span>Tax (5%):</span>
                                    <span>$<span id="modalTax">0.00</span></span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold fs-5">Total:</span>
                                    <span class="fw-bold fs-5 text-primary">$<span id="modalTotal">0.00</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Print
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
                                <p class="mb-0"><strong>Staff:</strong> <span id="reviewStaffName">-</span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="fw-bold text-muted mb-2">
                                    <i class="fas fa-user me-1"></i><span id="reviewPartyTitle">Customer</span> Details
                                </h6>
                                <p class="mb-1"><strong>Name:</strong> <span id="reviewPartyName">-</span></p>
                                <p class="mb-1"><strong>Email:</strong> <span id="reviewPartyEmail">-</span></p>
                                <p class="mb-0"><strong>Phone:</strong> <span id="reviewPartyPhone">-</span></p>
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
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span class="fw-semibold">$<span id="reviewSubtotal">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-muted">
                                    <span>Tax (5%):</span>
                                    <span>$<span id="reviewTax">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <span>-$<span id="reviewDiscount">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-muted">
                                    <span>Shipping:</span>
                                    <span>$<span id="reviewShipping">0.00</span></span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5">GRAND TOTAL:</span>
                                    <span class="fw-bold fs-4 text-primary">$<span
                                            id="reviewGrandTotal">0.00</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment & Delivery Info -->
                    <div class="mt-4 p-3 bg-secondary bg-opacity-10 rounded border">
                        <div class="row">
                            <div class="col-md-6">
                                <strong><i class="fas fa-credit-card me-1"></i>Payment:</strong>
                                <span id="reviewPaymentMethod">Cash on Delivery</span>
                            </div>
                            <div class="col-md-6">
                                <strong><i class="fas fa-truck me-1"></i>Delivery Date:</strong>
                                <span id="reviewDeliveryDate">-</span>
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
                                <p class="mb-1"><strong>Date:</strong> <span id="confirmOrderDate">-</span></p>
                                <p class="mb-0"><strong>Staff:</strong> <span id="confirmStaffName">-</span></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded h-100">
                                <h6 class="fw-bold text-muted mb-2">
                                    <i class="fas fa-user me-1"></i><span id="confirmPartyTitle">Customer</span> Details
                                </h6>
                                <p class="mb-1"><strong>Name:</strong> <span id="confirmPartyName">-</span></p>
                                <p class="mb-1"><strong>Email:</strong> <span id="confirmPartyEmail">-</span></p>
                                <p class="mb-0"><strong>Phone:</strong> <span id="confirmPartyPhone">-</span></p>
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
                                    <span>Subtotal:</span>
                                    <span class="fw-semibold">$<span id="confirmSubtotal">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-muted">
                                    <span>Tax (5%):</span>
                                    <span>$<span id="confirmTax">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-success">
                                    <span>Discount:</span>
                                    <span>-$<span id="confirmDiscount">0.00</span></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-muted">
                                    <span>Shipping:</span>
                                    <span>$<span id="confirmShipping">0.00</span></span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5">GRAND TOTAL:</span>
                                    <span class="fw-bold fs-4 text-primary">$<span
                                            id="confirmGrandTotal">0.00</span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    <div class="mt-4 p-3 bg-info bg-opacity-10 rounded border border-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle text-info me-2 fs-5"></i>
                            <div>
                                <strong>Payment Method:</strong> <span id="confirmPaymentMethod">Cash on Delivery</span>
                                <span class="mx-2">|</span>
                                <strong>Expected Delivery:</strong> <span id="confirmDeliveryDate">-</span>
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
    <script src="../../Frontend/common.js"></script>
    <!-- Orders Page Logic -->
    <script src="../../Frontend/orders/orders.js"></script>
</body>

</html>