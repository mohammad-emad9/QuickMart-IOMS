<?php
// Authentication Guard - Redirect to login if not authenticated
// Admin Only - This page requires Admin role
require_once __DIR__ . '/../core/auth_check.php';
requireAdmin(); // Only admins can access this page
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Manage Staff">
    <title>QuickMart IOMS - Manage Staff</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="../../assets/icons/icon-512.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Dashboard Styles -->
    <link rel="stylesheet" href="../../assets/dashboard/dashboard.css">
    <!-- Common Shared Styles -->
    <link rel="stylesheet" href="../../assets/common.css">
    <style>
        .staff-card {
            background: linear-gradient(145deg, #3d3d46, #545151);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .staff-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .staff-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: rgb(216, 0, 0);
        }

        .role-badge {
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 20px;
        }

        .role-Admin {
            background: linear-gradient(135deg, #121111, #ee5a24);
        }

        .role-Manager {
            background: linear-gradient(135deg, #4834d4, #686de0);
        }

        .role-Staff {
            background: linear-gradient(135deg, #22a6b3, #7ed6df);
        }

        .access-denied {
            text-align: center;
            padding: 100px 20px;
        }

        .access-denied i {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="container-fluid px-4 py-4">
            <!-- Access Denied Message (Hidden by default) -->
            <div id="accessDenied" class="access-denied d-none">
                <i class="fas fa-lock"></i>
                <h2 class="text-white mb-3">Access Denied</h2>
                <p class="text-muted mb-4">This page is only accessible to Administrators.</p>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>

            <div id="staffErrorState" class="alert alert-danger d-none" role="alert" aria-live="polite">
                <span id="staffErrorMessage"></span>
                <button type="button" class="btn btn-sm btn-outline-danger ms-3" id="staffRetryBtn">Retry</button>
            </div>

            <!-- Admin Content (Hidden until verified) -->
            <div id="adminContent" class="d-none">
                <!-- Page Header -->
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="page-title mb-1">
                            <i class="fas fa-users-cog text-warning me-2"></i>Manage Staff
                        </h1>
                        <p class="text-muted mb-0">View, edit, and manage staff members</p>
                    </div>
                    <div>
                        <span class="badge bg-primary fs-6" id="staffCount">0 Staff Members</span>
                    </div>
                </div>

                <!-- Staff Grid -->
                <div class="row g-4" id="staffGrid">
                    <!-- Staff cards will be loaded here -->
                </div>
            </div>

            <!-- Loading Spinner -->
            <div id="loadingSpinner" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3">Verifying access...</p>
            </div>
        </div>
    </main>

    <!-- Edit Staff Modal -->
    <div class="modal fade" id="editStaffModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="background: linear-gradient(145deg, #1a1a2e, #16213e); border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-user-edit me-2 text-primary"></i>Edit Staff
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editStaffForm" autocomplete="off">
                        <input type="hidden" id="editStaffId">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="editFullName" required>
                            <label><i class="fas fa-user me-2"></i>Full Name</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="editEmail" required>
                            <label><i class="fas fa-envelope me-2"></i>Email</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="tel" class="form-control" id="editPhone">
                            <label><i class="fas fa-phone me-2"></i>Phone Number</label>
                        </div>
                        <div class="form-floating mb-3">
                            <select class="form-select" id="editRole">
                                <option value="Staff">Staff</option>
                                <option value="Manager">Manager</option>
                                <option value="Admin">Admin</option>
                            </select>
                            <label><i class="fas fa-shield-alt me-2"></i>Role</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="editPassword"
                                placeholder="Leave empty to keep current">
                            <label><i class="fas fa-lock me-2"></i>New Password (optional)</label>
                        </div>
                        <div id="editMessage" class="alert d-none"></div>
                        <button type="submit" class="btn btn-primary w-100 py-2" id="saveEditBtn">
                            <i class="fas fa-save me-2"></i>Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Staff Details Modal -->
    <div class="modal fade" id="staffDetailsModal" tabindex="-1" aria-labelledby="staffDetailsTitle">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="background: linear-gradient(145deg, #1a1a2e, #16213e); border: 1px solid rgba(255,255,255,0.1);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="staffDetailsTitle">
                        <i class="fas fa-user me-2 text-primary"></i>Staff Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="staffDetailsState" class="alert alert-info" role="status" aria-live="polite">
                        Loading staff details...
                    </div>
                    <dl id="staffDetailsList" class="row mb-0 d-none">
                        <dt class="col-sm-4 text-muted">Staff ID</dt>
                        <dd class="col-sm-8 text-white" id="detailsStaffId">-</dd>
                        <dt class="col-sm-4 text-muted">Full Name</dt>
                        <dd class="col-sm-8 text-white" id="detailsFullName">-</dd>
                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8 text-white" id="detailsEmail">-</dd>
                        <dt class="col-sm-4 text-muted">Phone</dt>
                        <dd class="col-sm-8 text-white" id="detailsPhone">-</dd>
                        <dt class="col-sm-4 text-muted">Role</dt>
                        <dd class="col-sm-8 text-white" id="detailsRole">-</dd>
                    </dl>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="staffDetailsEditBtn" disabled>
                        <i class="fas fa-edit me-2"></i>Edit Staff
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteStaffModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content"
                style="background: linear-gradient(145deg, #b9b9be, #c5c6c6); border: 1px solid rgba(237, 234, 234, 0.1);">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Confirm Delete
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p class="text-white mb-2">Are you sure you want to delete this staff member?</p>
                    <h5 class="text-danger" id="deleteStaffName">-</h5>
                    <input type="hidden" id="deleteStaffId">
                    <div id="deleteMessage" class="alert alert-danger d-none text-start" role="alert"
                        aria-live="polite"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-2"></i>Delete
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
    <script src="../../assets/staff/staff.js"></script>
</body>

</html>
