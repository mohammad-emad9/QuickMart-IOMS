/**
 * QuickMart IOMS - Common JavaScript Utilities
 * Shared functions used across all pages
 */

// ===========================================
// Session Management
// ===========================================

/**
 * Check if user is logged in, redirect to login if not
 * @returns {boolean} True if logged in, false otherwise
 */
function checkSession() {
    const isLoggedIn = sessionStorage.getItem("isLoggedIn");
    if (isLoggedIn !== "true") {
        window.location.href = "/QuickMart code/assets/login-signup/login.html";
        return false;
    }
    return true;
}

/**
 * Load user information from session storage and update navbar
 */
function loadUserInfo() {
    const userName = sessionStorage.getItem("userName") || sessionStorage.getItem("userEmail")?.split("@")[0] || "Admin";
    const userRole = sessionStorage.getItem("userRole") || "Staff";

    const userNameElement = document.getElementById("userName");
    const welcomeMessage = document.getElementById("welcomeMessage");

    if (userNameElement) {
        userNameElement.textContent = userName.charAt(0).toUpperCase() + userName.slice(1);
    }

    if (welcomeMessage) {
        welcomeMessage.textContent = `Welcome back, ${userName}! Here's what's happening today.`;
    }

    // Show/Hide Manage Staff button based on role (Admin only)
    const manageStaffAction = document.getElementById("manageStaffAction");
    if (manageStaffAction && userRole !== 'Admin') {
        manageStaffAction.style.display = 'none';
    }
}

// ===========================================
// Logout Handler
// ===========================================

/**
 * Handle logout - clears session and redirects to login
 */
function performLogout() {
    sessionStorage.clear();
    window.location.href = "/QuickMart code/assets/login-signup/login.html";
}

/**
 * Show logout confirmation modal (or fallback to confirm if modal doesn't exist)
 * @param {Event} e - Click event
 */
function handleLogout(e) {
    if (e) e.preventDefault();

    const logoutModal = document.getElementById("logoutModal");
    if (logoutModal && typeof bootstrap !== 'undefined') {
        // Show the beautiful modal
        const modal = new bootstrap.Modal(logoutModal);
        modal.show();
    } else {
        // Fallback to browser confirm
        if (confirm("Are you sure you want to logout?")) {
            performLogout();
        }
    }
}

/**
 * Setup logout button and confirmation event listeners
 */
function setupLogoutHandler() {
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", handleLogout);
    }

    // Setup confirm logout button in modal
    const confirmLogoutBtn = document.getElementById("confirmLogoutBtn");
    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener("click", performLogout);
    }
}

// ===========================================
// Toast Notification System
// ===========================================

/**
 * Show a toast notification
 * @param {string} message - Message to display
 * @param {string} type - Type: 'success', 'error', or 'info'
 */
function showToast(message, type = "success") {
    const toastContainer = document.querySelector(".toast-container");
    if (!toastContainer) {
        console.warn("Toast container not found");
        return;
    }

    const toastId = `toast-${Date.now()}`;
    const iconClass = type === 'success' ? 'fa-check-circle' :
        type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';

    const toastHtml = `
        <div id="${toastId}" class="toast toast-${type}" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-body d-flex align-items-center py-3 px-4">
                <i class="fas ${iconClass} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    toastContainer.insertAdjacentHTML("beforeend", toastHtml);

    const toastEl = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
    toast.show();

    // Remove toast element after hidden
    toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
}

// ===========================================
// Utility Functions
// ===========================================

/**
 * Debounce function to limit rapid function calls
 * @param {Function} func - Function to debounce
 * @param {number} wait - Milliseconds to wait
 * @returns {Function} Debounced function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Format date string to readable format
 * @param {string} dateStr - Date string to format
 * @returns {string} Formatted date string
 */
function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Capitalize first letter of a string
 * @param {string} str - String to capitalize
 * @returns {string} Capitalized string
 */
function capitalizeFirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

// ===========================================
// Auto-initialize common features on DOM ready
// ===========================================
document.addEventListener("DOMContentLoaded", function () {
    // Setup logout handler on all pages
    setupLogoutHandler();
});
