<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Order Details">
    <title>QuickMart IOMS - Order Details</title>
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
    <!-- Order Details Styles -->
    <link rel="stylesheet" href="../../assets/order-details/order-details.css">
    <!-- Common Shared Styles -->
    <link rel="stylesheet" href="../../assets/common.css">
</head>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid px-4 py-4">
            <!-- Back Navigation (Hidden on Print) -->
            <div class="mb-3 no-print">
                <a href="orders.php" class="text-decoration-none text-muted back-link">
                    <i class="fas fa-arrow-left me-1"></i> Back to Orders
                </a>
            </div>

            <div id="orderDetailsState" class="alert alert-info" role="status">Loading order details...</div>

            <!-- Invoice Box -->
            <div class="invoice-box shadow-sm" id="invoiceBox">

                <!-- Invoice Header -->
                <div class="invoice-header">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="invoice-title mb-2">
                                <i class="fas fa-file-invoice text-primary me-2"></i>
                                ORDER DETAILS - #<span id="orderID"></span>
                            </div>
                            <div class="text-muted small">
                                <div>Order Date: <span class="fw-bold text-dark" id="orderDate">-</span></div>
                                <div>Staff: <span id="staffName">-</span></div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <div class="mb-2">
                                Type: <span class="order-type-badge" id="orderType">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer / Supplier Info -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <h6 class="section-label">Customer / Supplier</h6>
                        <h5 class="fw-bold mb-1" id="customerName">-</h5>
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
                            </tr>
                        </thead>
                        <tbody id="orderItemsBody">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Loading order items...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Notes & Summary Row -->
                <div class="row">
                    <!-- Read-only information -->
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <div class="notes-box">
                            <h6 class="fw-bold small mb-2">
                                <i class="fas fa-info-circle me-1"></i> ORDER INFORMATION
                            </h6>
                            <p class="text-muted small mb-3">This order is read-only. The backend does not provide edit, duplicate, delete, payment, tax, discount, or delivery fields.</p>

                            <div class="d-flex flex-wrap gap-2 no-print">
                                <button class="btn btn-outline-dark btn-sm" onclick="window.print()">
                                    <i class="fas fa-print me-1"></i> Print Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Summary -->
                    <div class="col-lg-6">
                        <div class="summary-box">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Items:</span>
                                    <span class="fw-semibold" id="itemCount">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="h5 fw-bold mb-0">TOTAL AMOUNT:</span>
                                    <span class="grand-total" id="grandTotalAmount">$0.00</span>
                                </div>
                        </div>
                    </div>
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
    <!-- Order Details Logic -->
    <script src="../../assets/order-details/order-details.js"></script>
</body>

</html>
