<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Reports & Analytics">
    <title>QuickMart IOMS - Reports</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Dashboard Styles (shared) -->
    <link rel="stylesheet" href="../../assets/dashboard/dashboard.css">
    <!-- Reports Page Styles -->
    <link rel="stylesheet" href="../../assets/reports/reports.css">
    <!-- Common Shared Styles -->
    <link rel="stylesheet" href="../../assets/common.css">
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
                        <a class="nav-link" href="create-order.php">
                            <i class="fas fa-file-invoice me-1"></i> Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reports.php">
                            <i class="fas fa-chart-bar me-1"></i> Reports
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
                        <i class="fas fa-chart-line text-primary me-2"></i>Reports & Analytics
                    </h1>
                    <p class="text-muted mb-0">Business insights and performance metrics</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-light btn-sm" onclick="loadReports()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="exportReport()">
                        <i class="fas fa-download me-1"></i> Export
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <section class="row g-3 mb-4" id="summaryCards">
                <!-- Total Sales Card -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="report-card card h-100 bg-gradient-primary">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-label text-white-50 mb-1">Total Sales</p>
                                    <h2 class="card-value text-white mb-0">$<span id="totalSales">0.00</span></h2>
                                    <small class="text-white-50">
                                        <span id="sellOrderCount">0</span> sell orders
                                    </small>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Purchases Card -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="report-card card h-100 bg-gradient-info">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-label text-white-50 mb-1">Total Purchases</p>
                                    <h2 class="card-value text-white mb-0">$<span id="totalPurchases">0.00</span></h2>
                                    <small class="text-white-50">
                                        <span id="purchaseOrderCount">0</span> purchase orders
                                    </small>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Net Revenue Card -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="report-card card h-100 bg-gradient-success">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-label text-white-50 mb-1">Net Revenue</p>
                                    <h2 class="card-value text-white mb-0">$<span id="netRevenue">0.00</span></h2>
                                    <small class="text-white-50">
                                        Sales - Purchases
                                    </small>
                                </div>
                                <div class="card-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory Status Card -->
                <div class="col-xl-3 col-lg-6 col-md-6">
                    <div class="report-card card h-100 bg-gradient-warning">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <p class="card-label text-dark-50 mb-1">Inventory</p>
                                    <h2 class="card-value text-dark mb-0"><span id="totalProducts">0</span></h2>
                                    <small class="text-dark-50">
                                        <span id="lowStockCount">0</span> low stock alerts
                                    </small>
                                </div>
                                <div class="card-icon text-dark">
                                    <i class="fas fa-boxes"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Charts Row -->
            <div class="row g-4 mb-4">
                <!-- Top Products -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-transparent border-0 pt-4">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-trophy text-warning me-2"></i>Top Selling Products
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
                                            <th>Product</th>
                                            <th class="text-center">Sold</th>
                                            <th class="text-end">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody id="topProductsBody">
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">
                                                <i class="fas fa-spinner fa-spin me-2"></i>Loading...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products by Category -->
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-transparent border-0 pt-4">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-tags text-info me-2"></i>Products by Category
                            </h5>
                        </div>
                        <div class="card-body">
                            <div id="categoryChart">
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-spinner fa-spin me-2"></i>Loading...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Section -->
            <section class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-history text-primary me-2"></i>Recent Orders
                    </h5>
                    <a href="create-order.php" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Type</th>
                                    <th>Staff</th>
                                    <th>Customer/Supplier</th>
                                    <th class="text-end">Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody id="recentOrdersBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="fas fa-spinner fa-spin me-2"></i>Loading...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <!-- Monthly Trend Section -->
            <section class="card shadow-sm border-0">
                <div class="card-header bg-transparent border-0 pt-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fas fa-chart-area text-success me-2"></i>Monthly Trend (Last 6 Months)
                    </h5>
                </div>
                <div class="card-body">
                    <div id="monthlyTrendChart">
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-spinner fa-spin me-2"></i>Loading...
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

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
    <!-- Reports Page Logic -->
    <script src="../../assets/reports/reports.js"></script>
</body>

</html>