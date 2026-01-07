/**
 * QuickMart IOMS - Profile
 * Handles profile display, edit, password change, and statistics
 */

// ===========================================
// DOM Ready
// ===========================================
document.addEventListener("DOMContentLoaded", function () {
    initializePage();
    loadUserProfile();
    loadUserStats();
    setupEventListeners();
    console.log("QuickMart IOMS - Profile page initialized");
});

// ===========================================
// Initialize Page
// ===========================================
function initializePage() {
    // Load user name in navbar
    const userEmail = sessionStorage.getItem("userEmail") || "Admin";
    const userName = userEmail.split("@")[0];
    const userNameEl = document.getElementById("userName");
    if (userNameEl) {
        userNameEl.textContent = userName.charAt(0).toUpperCase() + userName.slice(1);
    }
}

// ===========================================
// Load User Profile from Database
// ===========================================
async function loadUserProfile() {
    const staffId = sessionStorage.getItem("staffId");

    if (!staffId) {
        // Use session data if no staff ID
        const email = sessionStorage.getItem("userEmail") || "-";
        const name = email.split("@")[0];

        document.getElementById("profileName").textContent = name.charAt(0).toUpperCase() + name.slice(1);
        document.getElementById("profileEmail").textContent = email;
        document.getElementById("profilePhone").textContent = "-";
        document.getElementById("profileId").textContent = "-";
        document.getElementById("profileRole").textContent = "Staff";

        // Populate edit form
        document.getElementById("editName").value = name;
        document.getElementById("editEmail").value = email;
        document.getElementById("editRole").value = "Staff";
        return;
    }

    try {
        const response = await fetch(`/QuickMart code/backend/api/staff/get.php?id=${staffId}`);
        const data = await response.json();

        if (data.success && data.data) {
            const staff = data.data;

            // Display profile info
            document.getElementById("profileName").textContent = staff.Full_Name || "-";
            document.getElementById("profileEmail").textContent = staff.Email || "-";
            document.getElementById("profilePhone").textContent = staff.Phone_Number || "-";
            document.getElementById("profileId").textContent = staff.Staff_ID || "-";
            document.getElementById("profileRole").textContent = staff.Role || "Staff";

            // Populate edit form
            document.getElementById("editName").value = staff.Full_Name || "";
            document.getElementById("editEmail").value = staff.Email || "";
            document.getElementById("editPhone").value = staff.Phone_Number || "";
            document.getElementById("editRole").value = staff.Role || "Staff";
        }
    } catch (error) {
        console.error("Error loading profile:", error);
    }
}

// ===========================================
// Load User Statistics
// ===========================================
async function loadUserStats() {
    const staffId = sessionStorage.getItem("staffId");

    try {
        // Get orders for this staff member
        let url = '/QuickMart code/backend/api/orders/list.php';
        if (staffId) {
            url += `?staff_id=${staffId}`;
        }

        const response = await fetch(url);
        const data = await response.json();

        if (data.success && data.data.orders) {
            const orders = data.data.orders;
            const sellOrders = orders.filter(o => o.Order_Type === 'Sell').length;
            const purchaseOrders = orders.filter(o => o.Order_Type === 'Purchase').length;

            document.getElementById("totalOrders").textContent = orders.length;
            document.getElementById("sellOrders").textContent = sellOrders;
            document.getElementById("purchaseOrders").textContent = purchaseOrders;
        }
    } catch (error) {
        console.error("Error loading stats:", error);
    }
}

// ===========================================
// Event Listeners Setup
// ===========================================
function setupEventListeners() {
    // Edit profile form
    const editForm = document.getElementById("editProfileForm");
    if (editForm) {
        editForm.addEventListener("submit", handleEditProfile);
    }

    // Change password form
    const passwordForm = document.getElementById("changePasswordForm");
    if (passwordForm) {
        passwordForm.addEventListener("submit", handleChangePassword);
    }

    // Logout button
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", handleLogout);
    }
}

// ===========================================
// Handle Edit Profile
// ===========================================
async function handleEditProfile(e) {
    e.preventDefault();

    const staffId = sessionStorage.getItem("staffId");
    if (!staffId) {
        showToast("Cannot update profile - not logged in properly", "error");
        return;
    }

    const formData = {
        staff_id: staffId,
        full_name: document.getElementById("editName").value.trim(),
        email: document.getElementById("editEmail").value.trim(),
        phone_number: document.getElementById("editPhone").value.trim()
    };

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    submitBtn.disabled = true;

    try {
        const response = await fetch('/QuickMart code/backend/api/staff/update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });

        const data = await response.json();

        if (data.success) {
            showToast("Profile updated successfully!", "success");
            // Update display
            document.getElementById("profileName").textContent = formData.full_name;
            document.getElementById("profileEmail").textContent = formData.email;
            document.getElementById("profilePhone").textContent = formData.phone_number || "-";

            // Update session
            sessionStorage.setItem("userEmail", formData.email);
        } else {
            showToast(data.message || "Error updating profile", "error");
        }
    } catch (error) {
        console.error("Error updating profile:", error);
        showToast("Error updating profile", "error");
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// ===========================================
// Handle Change Password
// ===========================================
async function handleChangePassword(e) {
    e.preventDefault();

    const currentPassword = document.getElementById("currentPassword").value;
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;

    // Validate
    if (!currentPassword || !newPassword || !confirmPassword) {
        showToast("Please fill all password fields", "error");
        return;
    }

    if (newPassword !== confirmPassword) {
        showToast("New passwords do not match", "error");
        return;
    }

    if (newPassword.length < 6) {
        showToast("Password must be at least 6 characters", "error");
        return;
    }

    const staffId = sessionStorage.getItem("staffId");
    if (!staffId) {
        showToast("Cannot change password - not logged in properly", "error");
        return;
    }

    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';
    submitBtn.disabled = true;

    try {
        const response = await fetch('/QuickMart code/backend/api/staff/change-password.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                staff_id: staffId,
                current_password: currentPassword,
                new_password: newPassword
            })
        });

        const data = await response.json();

        if (data.success) {
            showToast("Password changed successfully!", "success");
            // Clear form
            document.getElementById("currentPassword").value = "";
            document.getElementById("newPassword").value = "";
            document.getElementById("confirmPassword").value = "";
        } else {
            showToast(data.message || "Error changing password", "error");
        }
    } catch (error) {
        console.error("Error changing password:", error);
        showToast("Error changing password", "error");
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// End of file
