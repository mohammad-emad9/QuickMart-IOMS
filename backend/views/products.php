<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Products Management">
    <title>QuickMart IOMS - Products</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Dashboard Styles (shared) -->
    <link rel="stylesheet" href="../../assets/dashboard/dashboard.css">
    <!-- Products Page Styles -->
    <link rel="stylesheet" href="../../assets/products/products.css">
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

            <!-- Mobile Toggle Button -->
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
                        <a class="nav-link active" href="list.php">
                            <i class="fas fa-boxes me-1"></i> Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
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
            <div class="page-header bg-white p-3 rounded shadow-sm mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h4 class="page-title mb-0 text-uppercase fw-bold">
                            <i class="fas fa-boxes text-primary me-2"></i>Products Management
                        </h4>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-muted small" id="productCount">Loading...</span>
                    </div>
                </div>
            </div>

            <!-- Filters & Search Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body py-3">
                    <div class="row g-3 align-items-center">
                        <!-- Search Box -->
                        <div class="col-lg-4 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fas fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="searchInput"
                                    placeholder="Search products by name or ID...">
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="col-lg-2 col-md-3 col-6">
                            <select id="categoryFilter" class="form-select">
                                <option value="All">All Categories</option>
                                <option value="GPU">GPU</option>
                                <option value="CPU">CPU</option>
                                <option value="RAM">RAM</option>
                                <option value="Storage">Storage</option>
                                <option value="Power">Power</option>
                                <option value="Cooling">Cooling</option>
                                <option value="Cables">Cables</option>
                            </select>
                        </div>

                        <!-- Stock Filter -->
                        <div class="col-lg-2 col-md-3 col-6">
                            <select id="stockFilter" class="form-select">
                                <option value="All">All Stock</option>
                                <option value="low">Low Stock Only</option>
                                <option value="normal">In Stock Only</option>
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div class="col-lg-2 col-md-6">
                            <select id="sortFilter" class="form-select">
                                <option value="name">Sort: A-Z</option>
                                <option value="name-desc">Sort: Z-A</option>
                                <option value="price-asc">Price: Low to High</option>
                                <option value="price-desc">Price: High to Low</option>
                                <option value="quantity-asc">Stock: Low to High</option>
                                <option value="quantity-desc">Stock: High to Low</option>
                            </select>
                        </div>

                        <!-- Add Product Button -->
                        <div class="col-lg-2 col-md-6">
                            <button class="btn btn-primary w-100 fw-bold" id="addProductBtn">
                                <i class="fas fa-plus me-1"></i> ADD NEW
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Table Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle products-table mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th class="text-center">Quantity</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="productsTableBody">
                                <!-- Products will be loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Table Footer -->
                <div
                    class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center py-3 gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <small class="text-muted" id="showingInfo">Showing 0 products</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary" id="exportBtn">
                            <i class="fas fa-file-export me-1"></i> Export CSV
                        </button>
                        <button class="btn btn-sm btn-outline-danger" id="deleteSelectedBtn" disabled>
                            <i class="fas fa-trash-alt me-1"></i> Delete Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Add/Edit Product Modal -->
    <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalTitle">
                        <i class="fas fa-plus-circle me-2"></i>Add New Product
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="productForm">
                    <div class="modal-body">
                        <input type="hidden" id="productId">

                        <div class="mb-3">
                            <label for="productName" class="form-label fw-semibold">Product Name</label>
                            <input type="text" class="form-control" id="productName" placeholder="Enter product name"
                                required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="productCategory" class="form-label fw-semibold">Category</label>
                                <select id="productCategory" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="GPU">GPU</option>
                                    <option value="CPU">CPU</option>
                                    <option value="RAM">RAM</option>
                                    <option value="Storage">Storage</option>
                                    <option value="Power">Power</option>
                                    <option value="Cooling">Cooling</option>
                                    <option value="Cables">Cables</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="productPrice" class="form-label fw-semibold">Price ($)</label>
                                <input type="number" class="form-control" id="productPrice" placeholder="0.00"
                                    step="0.01" min="0" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="productQuantity" class="form-label fw-semibold">Quantity in Stock</label>
                                <input type="number" class="form-control" id="productQuantity" placeholder="0" min="0"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="lowStockThreshold" class="form-label fw-semibold">
                                    Low Stock Threshold
                                    <i class="fas fa-info-circle text-muted ms-1" data-bs-toggle="tooltip"
                                        title="Alert will show when quantity falls below this value"></i>
                                </label>
                                <input type="number" class="form-control" id="lowStockThreshold" placeholder="20"
                                    min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="productDescription" class="form-label fw-semibold">Description
                                (Optional)</label>
                            <textarea class="form-control" id="productDescription" rows="3"
                                placeholder="Enter product description..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-0">Are you sure you want to delete<br><strong id="deleteProductName"></strong>?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
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
    <!-- Products Page Logic -->
    <script src="../../assets/products/products.js"></script>
</body>

</html>