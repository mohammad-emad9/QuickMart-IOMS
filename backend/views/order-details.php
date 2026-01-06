<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Order Details">
    <title>QuickMart IOMS - Order #245</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Dashboard Styles (shared) -->
    <link rel="stylesheet" href="../../Frontend/dashboard/dashboard.css">
    <!-- Order Details Styles -->
    <link rel="stylesheet" href="../../Frontend/order-details/order-details.css">
    <!-- Common Shared Styles -->
    <link rel="stylesheet" href="../../Frontend/common.css">
</head>

<body>
    <!-- Top Navigation Bar (Hidden on Print) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow no-print">
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
                        <a class="nav-link active" href="create-order.php">
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
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user-cog me-2"></i>Profile</a></li>
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
            <!-- Back Navigation (Hidden on Print) -->
            <div class="mb-3 no-print">
                <a href="create-order.php" class="text-decoration-none text-muted back-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Orders
                </a>
            </div>

            <!-- Invoice Box -->
            <div class="invoice-box shadow-sm">

                <!-- Invoice Header -->
                <div class="invoice-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="invoice-title mb-2">
                                <i class="fas fa-file-invoice text-primary me-2"></i>
                                ORDER DETAILS - #<span id="orderID">245</span>
                            </div>
                            <div class="text-muted small">
                                <div>Order Date: <span class="fw-bold text-dark" id="orderDate">2025-10-15 10:30
                                        AM</span></div>
                                <div>Staff: <span id="staffName">Ali (STF001)</span></div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="mb-2">
                                Type: <span class="order-type-badge" id="orderType">SELL ORDER</span>
                            </div>
                            <div>
                                Status: <span class="status-badge status-completed" id="orderStatus">
                                    <i class="fas fa-check-circle me-1"></i>Completed
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer & Payment Info -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6 class="section-label">Customer / Supplier</h6>
                        <h5 class="fw-bold mb-1" id="customerName">John Doe</h5>
                        <p class="text-muted mb-0" id="customerContact">john@email.com</p>
                        <p class="text-muted mb-0" id="customerPhone">+1 234 567 8900</p>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <h6 class="section-label">Payment Info</h6>
                        <p class="mb-1">Method: <span class="fw-semibold" id="paymentMethod">Cash</span></p>
                        <p class="mb-0">Invoice No: <span class="fw-bold" id="invoiceNo">INV-2025-245</span></p>
                    </div>
                </div>

                <!-- Order Items Section -->
                <h6 class="section-label mb-3">
                    <i class="fas fa-list me-1"></i> Order Items
                </h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle invoice-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">Total</th>
                                <th class="text-center stock-after-col">Stock After</th>
                            </tr>
                        </thead>
                        <tbody id="orderItemsBody">
                            <!-- Items will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Notes & Summary Row -->
                <div class="row">
                    <!-- Notes & Actions -->
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="notes-box">
                            <h6 class="fw-bold small mb-2">
                                <i class="fas fa-sticky-note me-1"></i> NOTES & ACTIONS
                            </h6>
                            <p class="text-muted small fst-italic mb-3" id="orderNotes">
                                Customer requested invoice for tax purposes.
                            </p>

                            <!-- Action Buttons (Hidden on Print) -->
                            <div class="d-flex flex-wrap gap-2 no-print">
                                <button class="btn btn-outline-dark btn-sm" id="editOrderBtn">
                                    <i class="fas fa-edit me-1"></i> Edit Order
                                </button>
                                <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> Print Invoice
                                </button>
                                <button class="btn btn-outline-primary btn-sm" id="duplicateOrderBtn">
                                    <i class="fas fa-copy me-1"></i> Duplicate
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="col-lg-6">
                        <div class="summary-box">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Subtotal:</span>
                                <span class="fw-semibold">$<span id="subtotalAmount">770.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tax (5%):</span>
                                <span>$<span id="taxAmount">38.50</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Discount:</span>
                                <span class="text-success">-$<span id="discountAmount">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                <span class="text-muted">Shipping:</span>
                                <span>$<span id="shippingAmount">0.00</span></span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 fw-bold mb-0">TOTAL AMOUNT:</span>
                                <span class="grand-total" id="grandTotalAmount">$808.50</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete Order Link (Hidden on Print) -->
                <div class="text-end mt-4 no-print">
                    <button class="btn btn-link text-danger small fw-bold text-decoration-none p-0" id="deleteOrderBtn">
                        <i class="fas fa-trash-alt me-1"></i> [DELETE ORDER]
                    </button>
                </div>

                <!-- Print Footer (Only visible on print) -->
                <div class="print-footer">
                    <hr>
                    <div class="text-center text-muted small">
                        <p class="mb-1">Thank you for your business!</p>
                        <p class="mb-0">QuickMart IOMS | www.quickmart.com | support@quickmart.com</p>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle me-2"></i>Delete Order
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <p class="mb-0">Are you sure you want to delete Order <strong>#<span
                                id="deleteOrderId">245</span></strong>?</p>
                    <p class="text-muted small">This action cannot be undone.</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" id="confirmDeleteBtn">Delete Order</button>
                </div>
            </div>
        </div>
    </div>

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
    <script src="../../Frontend/common.js"></script>
    <!-- Order Details Logic -->
    <script src="../../Frontend/order-details/order-details.js"></script>
</body>

</html>