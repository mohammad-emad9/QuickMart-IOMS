<?php
/**
 * Shared QuickMart Operations Ledger navigation.
 *
 * IDs, classes, Bootstrap hooks, and role data attributes are intentionally
 * stable because common.js and page scripts depend on them.
 */
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow no-print qm-shell-nav" aria-label="Primary navigation">
    <div class="qm-shell-frame">
        <a class="navbar-brand qm-shell-brand" href="dashboard.php" aria-label="QuickMart IOMS dashboard">
            <span class="qm-brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 5h14v14H5zM8 3v4M16 3v4M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01" />
                </svg>
            </span>
            <span class="qm-brand-copy">
                <span class="qm-brand-name">QuickMart</span>
                <span class="qm-brand-sub">Operations Ledger</span>
            </span>
        </a>

        <button class="navbar-toggler border-0 qm-nav-toggle" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Open navigation">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
        </button>

        <div class="collapse navbar-collapse qm-nav-drawer" id="navbarNav">
            <div class="qm-nav-section-label">Workspace</div>
            <ul class="navbar-nav qm-nav-list" aria-label="Workspace">
                <li class="nav-item">
                    <a class="nav-link qm-nav-link" data-nav-key="dashboard" href="dashboard.php">
                        <span class="qm-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 13h4V5H4v8Zm6 6h4V3h-4v16Zm6-4h4V8h-4v7Z" /></svg></span>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link qm-nav-link" data-nav-key="products" href="products.php">
                        <span class="qm-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 6h16v12H4zM8 6V4h8v2" /></svg></span>
                        <span>Products</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link qm-nav-link" data-nav-key="orders" href="orders.php">
                        <span class="qm-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h9l3 3v15H6zM9 12h6M9 16h6M9 8h3" /></svg></span>
                        <span>Orders</span>
                    </a>
                </li>
                <li class="nav-item" data-admin-only>
                    <a class="nav-link qm-nav-link" data-nav-key="reports" href="reports.php">
                        <span class="qm-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5h16v14H4Zm3-3v-4m4 4V8m4 8V6" /></svg></span>
                        <span>Reports</span>
                    </a>
                </li>
                <li class="nav-item" data-admin-only>
                    <a class="nav-link qm-nav-link" data-nav-key="staff" href="staff.php">
                        <span class="qm-nav-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 8a3 3 0 1 1 0 6M20 21v-2a4 4 0 0 0-2.5-3.7" /></svg></span>
                        <span>Staff</span>
                    </a>
                </li>
            </ul>

            <div class="qm-nav-footer">
                <div class="dropdown dropup qm-user-menu">
                    <a class="nav-link dropdown-toggle qm-user-button" href="#" id="userDropdown" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="qm-user-avatar" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21a8 8 0 0 0-16 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg></span>
                        <span class="qm-user-copy"><span id="userName">Checking session…</span><span class="qm-user-role" id="userRoleLabel">Verifying account</span></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="profile.php">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" /></svg>
                                <span>Profile</span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="#" id="logoutBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" /></svg>
                                <span>Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
