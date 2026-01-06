<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Dashboard Overview">
    <title>QuickMart IOMS - Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Dashboard Styles -->
    <link rel="stylesheet" href="../../Frontend/dashboard/dashboard.css">
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

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            <i class="fas fa-boxes me-1"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create-order.php">
                            <i class="fas fa-file-invoice me-1"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2">
                                <i class="fas fa-user"></i>
                            </div>
                            <span id="userName">Admin</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="userDropdown">
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
                    <h1 class="page-title mb-1">Dashboard Overview</h1>
                    <p class="text-muted mb-0" id="welcomeMessage">Welcome back! Here's what's happening today.</p>
                </div>
                <div class="header-date text-end">
                    <span class="text-muted small" id="currentDate"></span>
                    <br>
                    <span class="text-primary fw-bold" id="currentTime"></span>
                </div>
            </div>

            <!-- KPI Stats Cards -->
            <section class="row g-4 mb-4">
                <!-- Total Products Card -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="kpi-card card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="kpi-label text-uppercase mb-1">Total Products</p>
                                    <h2 class="kpi-value mb-0" id="totalProducts">150</h2>
                                    <small class="kpi-trend text-success">
                                        <i class="fas fa-arrow-up me-1"></i>+12% this month
                                    </small>
                                </div>
                                <div class="kpi-icon bg-primary-soft">
                                    <i class="fas fa-boxes text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Card (Red Warning) -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="kpi-card card h-100 kpi-danger">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="kpi-label text-uppercase mb-1">Low Stock</p>
                                    <h2 class="kpi-value mb-0" id="lowStock">12</h2>
                                    <small class="kpi-trend text-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Needs attention
                                    </small>
                                </div>
                                <div class="kpi-icon bg-danger-soft">
                                    <i class="fas fa-exclamation-circle text-danger"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Orders Card -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="kpi-card card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="kpi-label text-uppercase mb-1">Total Orders</p>
                                    <h2 class="kpi-value mb-0" id="totalOrders">245</h2>
                                    <small class="kpi-trend text-success">
                                        <i class="fas fa-arrow-up me-1"></i>+8% this week
                                    </small>
                                </div>
                                <div class="kpi-icon bg-info-soft">
                                    <i class="fas fa-shopping-bag text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenue & Profit Card -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="kpi-card card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="w-100">
                                    <p class="kpi-label text-uppercase mb-1">Net Profit</p>
                                    <h2 class="kpi-value mb-2" id="netProfit">
                                        <span class="text-success">$<span id="netProfitValue">0.00</span></span>
                                    </h2>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-success">
                                            <i class="fas fa-arrow-up me-1"></i>Sales: $<span id="totalSales">0</span>
                                        </span>
                                        <span class="text-danger">
                                            <i class="fas fa-arrow-down me-1"></i>Costs: $<span
                                                id="totalPurchases">0</span>
                                        </span>
                                    </div>
                                </div>
                                <div class="kpi-icon bg-success-soft">
                                    <i class="fas fa-chart-line text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Quick Actions Section -->
            <section class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-bolt text-warning me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-lg-3 col-md-6">
                            <a href="products.php" class="quick-action-btn">
                                <div class="action-icon bg-primary-soft">
                                    <i class="fas fa-plus text-primary"></i>
                                </div>
                                <span>Add Product</span>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="create-order.php" class="quick-action-btn">
                                <div class="action-icon bg-success-soft">
                                    <i class="fas fa-file-invoice text-success"></i>
                                </div>
                                <span>Create Order</span>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <a href="reports.php" class="quick-action-btn">
                                <div class="action-icon bg-info-soft">
                                    <i class="fas fa-chart-bar text-info"></i>
                                </div>
                                <span>View Reports</span>
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 admin-only-action" id="manageStaffAction">
                            <a href="staff.php" class="quick-action-btn">
                                <div class="action-icon bg-warning-soft">
                                    <i class="fas fa-users text-warning"></i>
                                </div>
                                <span>Manage Staff</span>
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Low Stock Alerts Section -->
            <section class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Low Stock Alerts
                    </h5>
                    <a href="products.php" class="btn btn-sm btn-outline-danger">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="low-stock-list" id="lowStockList">
                        <!-- Low stock items will be loaded dynamically -->
                    </div>
                </div>
            </section>

            <!-- Recent Orders Section -->
            <section class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-clock me-2 text-primary"></i>Recent Orders
                    </h5>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#allOrdersModal">
                        View All Orders <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Staff</th>
                                    <th>Customer</th>
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
                                <h6 class="text-muted mb-2"><i class="fas fa-user me-1"></i>Customer Information</h6>
                                <p class="fw-bold mb-1" id="modalCustomerName">-</p>
                                <small class="text-muted" id="modalCustomerContact">-</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100">
                                <h6 class="text-muted mb-2"><i class="fas fa-info-circle me-1"></i>Order Info</h6>
                                <p class="mb-1"><strong>Status:</strong> <span id="modalOrderStatus"
                                        class="badge">-</span></p>
                                <p class="mb-1"><strong>Date:</strong> <span id="modalOrderDate">-</span></p>
                                <p class="mb-0"><strong>Type:</strong> <span id="modalOrderType">Sell</span></p>
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

    <!-- All Orders Modal -->
    <div class="modal fade" id="allOrdersModal" tabindex="-1" aria-labelledby="allOrdersModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
            <div class="modal-content"
                style="background: linear-gradient(145deg, #1a1a2e, #16213e); border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="allOrdersModalLabel">
                        <i class="fas fa-receipt me-2 text-primary"></i>All Orders
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 60vh; overflow-y: auto;">
                    <!-- Filter Buttons -->
                    <div class="d-flex gap-2 mb-3 flex-wrap">
                        <button class="btn btn-sm btn-outline-light active" onclick="filterAllOrders('all')"
                            id="filterAll">All</button>
                        <button class="btn btn-sm btn-outline-success" onclick="filterAllOrders('Sell')"
                            id="filterSell">Sell Orders</button>
                        <button class="btn btn-sm btn-outline-info" onclick="filterAllOrders('Purchase')"
                            id="filterPurchase">Purchase Orders</button>
                    </div>
                    <!-- Orders Table -->
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Type</th>
                                    <th>Party Name</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="allOrdersTableBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Loading orders...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <a href="create-order.php" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Create New Order
                    </a>
                    <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Close</button>
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
    <script src="../../Frontend/common.js"></script>
    <!-- Dashboard Logic Script -->
    <script src="../../Frontend/dashboard/dashboard.js"></script>
</body>

</html>