<?php
// Authentication Guard - Redirect to login if not authenticated
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - User Profile">
    <title>QuickMart IOMS - Profile</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Dashboard Styles (shared) -->
    <link rel="stylesheet" href="../../assets/dashboard/dashboard.css">
    <!-- Profile Page Styles -->
    <link rel="stylesheet" href="../../assets/profile/profile.css">
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
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle d-flex align-items-center active" href="#" id="userDropdown"
                            role="button" data-bs-toggle="dropdown">
                            <div class="user-avatar me-2">
                                <i class="fas fa-user"></i>
                            </div>
                            <span id="userName">Admin</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark">
                            <li><a class="dropdown-item active" href="profile.php"><i
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
            <div class="row">
                <!-- Profile Info Card -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm border-0 profile-card">
                        <div class="card-body text-center p-4">
                            <div class="profile-avatar mb-3">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h4 class="fw-bold mb-1" id="profileName">Loading...</h4>
                            <p class="text-muted mb-3" id="profileRole">Staff</p>
                            <div class="badge bg-success mb-3">
                                <i class="fas fa-check-circle me-1"></i>Active
                            </div>
                            <hr>
                            <div class="profile-info text-start">
                                <div class="info-item">
                                    <i class="fas fa-envelope text-primary"></i>
                                    <span id="profileEmail">-</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-phone text-primary"></i>
                                    <span id="profilePhone">-</span>
                                </div>
                                <div class="info-item">
                                    <i class="fas fa-id-badge text-primary"></i>
                                    <span id="profileId">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stats & Actions -->
                <div class="col-lg-8">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm border-0 stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary-soft">
                                            <i class="fas fa-shopping-cart text-primary"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h3 class="mb-0 fw-bold" id="totalOrders">0</h3>
                                            <small class="text-muted">Total Orders</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm border-0 stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-success-soft">
                                            <i class="fas fa-arrow-down text-success"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h3 class="mb-0 fw-bold" id="sellOrders">0</h3>
                                            <small class="text-muted">Sell Orders</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card shadow-sm border-0 stat-card">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-info-soft">
                                            <i class="fas fa-arrow-up text-info"></i>
                                        </div>
                                        <div class="ms-3">
                                            <h3 class="mb-0 fw-bold" id="purchaseOrders">0</h3>
                                            <small class="text-muted">Purchase Orders</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Profile Card -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-edit text-primary me-2"></i>Edit Profile
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form id="editProfileForm">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Full Name</label>
                                        <input type="text" class="form-control" id="editName"
                                            placeholder="Your full name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Email</label>
                                        <input type="email" class="form-control" id="editEmail"
                                            placeholder="your@email.com">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Phone</label>
                                        <input type="tel" class="form-control" id="editPhone"
                                            placeholder="+1 234 567 8900" dir="ltr">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Role</label>
                                        <input type="text" class="form-control" id="editRole" readonly disabled>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save me-2"></i>Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password Card -->
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white border-0 pt-4 px-4">
                            <h5 class="fw-bold mb-0">
                                <i class="fas fa-lock text-primary me-2"></i>Change Password
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <form id="changePasswordForm">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Current Password</label>
                                        <input type="password" class="form-control" id="currentPassword"
                                            placeholder="••••••••">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">New Password</label>
                                        <input type="password" class="form-control" id="newPassword"
                                            placeholder="••••••••">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirmPassword"
                                            placeholder="••••••••">
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-warning px-4">
                                        <i class="fas fa-key me-2"></i>Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
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
    <!-- Profile Logic -->
    <script src="../../assets/profile/profile.js"></script>
</body>

</html>