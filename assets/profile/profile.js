/**
 * QuickMart IOMS - Profile
 * Current-user profile, order statistics, and password flows.
 */

(function () {
    "use strict";

    const state = {
        profileLoading: false,
        profileRequestId: 0,
        profileSubmitting: false,
        statsLoading: false,
        statsRequestId: 0,
        passwordSubmitting: false,
        redirecting: false
    };

    function getElement(id) {
        return document.getElementById(id);
    }

    function displayValue(value, fallback = "-") {
        if (value === null || value === undefined || String(value).trim() === "") {
            return fallback;
        }

        return String(value);
    }

    function setVisible(element, visible) {
        if (element) {
            element.classList.toggle("d-none", !visible);
        }
    }

    function createIcon(iconClass) {
        const icon = document.createElement("i");
        icon.className = `fas ${iconClass} me-2`;
        return icon;
    }

    function setButtonState(button, busy, iconClass, busyLabel, idleLabel) {
        if (!button) {
            return;
        }

        button.replaceChildren(
            createIcon(iconClass),
            document.createTextNode(busy ? busyLabel : idleLabel)
        );
        button.disabled = Boolean(busy);
    }

    function setFormControlsDisabled(form, disabled, immutableIds = []) {
        if (!form) {
            return;
        }

        form.querySelectorAll("input, select, textarea, button").forEach((control) => {
            control.disabled = immutableIds.includes(control.id) || Boolean(disabled);
        });
    }

    function showFormMessage(element, message, type = "danger") {
        if (!element) {
            return;
        }

        element.className = `alert alert-${type} mt-3`;
        element.textContent = String(message || "");
        setVisible(element, true);
    }

    function hideFormMessage(element) {
        if (!element) {
            return;
        }

        element.textContent = "";
        setVisible(element, false);
    }

    function showProfileState(message, type = "info", retry = false) {
        const stateElement = getElement("profileState");
        const retryButton = getElement("profileRetryBtn");

        if (!stateElement) {
            return;
        }

        stateElement.className = `alert alert-${type} d-flex align-items-center justify-content-between`;
        getElement("profileStateMessage").textContent = String(message || "");
        setVisible(retryButton, retry);
        setVisible(stateElement, true);
    }

    function hideProfileState() {
        setVisible(getElement("profileState"), false);
    }

    function showStatsState(message, retry = true) {
        const stateElement = getElement("statsState");

        if (!stateElement) {
            return;
        }

        stateElement.className = "alert alert-danger d-flex align-items-center justify-content-between";
        getElement("statsStateMessage").textContent = String(message || "");
        setVisible(getElement("statsRetryBtn"), retry);
        setVisible(stateElement, true);
    }

    function hideStatsState() {
        setVisible(getElement("statsState"), false);
    }

    function clearClientSessionAndRedirect() {
        if (state.redirecting) {
            return;
        }

        state.redirecting = true;
        sessionStorage.clear();
        window.location.href = loginPath();
    }

    function responseMessage(response, data, fallback) {
        if (data && typeof data.message === "string" && data.message.trim() !== "") {
            return data.message;
        }

        switch (response.status) {
            case 401:
                return "Your session has expired. Please sign in again.";
            case 403:
                return "You are not authorized to access this information.";
            case 404:
                return "The requested staff member was not found.";
            case 409:
                return "The request conflicts with existing data.";
            case 422:
                return "Please check the submitted values.";
            default:
                return fallback;
        }
    }

    async function readApiResponse(response) {
        try {
            return await response.json();
        } catch (error) {
            return null;
        }
    }

    function handleUnauthorized(response) {
        if (response.status !== 401) {
            return false;
        }

        clearClientSessionAndRedirect();
        return true;
    }

    function getCurrentStaffId() {
        const staffId = sessionStorage.getItem("staffId");

        if (typeof staffId !== "string" || staffId.trim() === "") {
            return null;
        }

        return staffId.trim();
    }

    function renderProfile(staff) {
        const fullName = displayValue(staff.Full_Name);
        const email = displayValue(staff.Email);
        const phone = displayValue(staff.Phone_Number, "Not set");
        const staffId = displayValue(staff.Staff_ID);
        const role = displayValue(staff.Role, "Staff");

        getElement("profileName").textContent = fullName;
        getElement("profileEmail").textContent = email;
        getElement("profilePhone").textContent = phone;
        getElement("profileId").textContent = staffId;
        getElement("profileRole").textContent = role;

        getElement("editName").value = fullName === "-" ? "" : fullName;
        getElement("editEmail").value = email === "-" ? "" : email;
        getElement("editPhone").value = phone === "Not set" ? "" : phone;
        getElement("editRole").value = role;
        getElement("editRole").disabled = true;
    }

    function updateUiSession(staff) {
        if (typeof staff.Email === "string") {
            sessionStorage.setItem("userEmail", staff.Email);
        }

        if (typeof staff.Full_Name === "string") {
            sessionStorage.setItem("userName", staff.Full_Name);
        }
    }

    function setStatsValues(total, sell, purchase) {
        getElement("totalOrders").textContent = String(total);
        getElement("sellOrders").textContent = String(sell);
        getElement("purchaseOrders").textContent = String(purchase);
    }

    async function loadUserProfile() {
        if (state.profileLoading) {
            return;
        }

        const staffId = getCurrentStaffId();
        if (!staffId) {
            clearClientSessionAndRedirect();
            return;
        }

        state.profileLoading = true;
        const requestId = ++state.profileRequestId;
        showProfileState("Loading profile...", "info", false);

        try {
            const response = await apiFetch(`staff/get.php?id=${encodeURIComponent(staffId)}`);
            const data = await readApiResponse(response);

            if (requestId !== state.profileRequestId) {
                return;
            }

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true || !data.data) {
                showProfileState(responseMessage(response, data, "Unable to load your profile."),
                    response.status === 403 ? "warning" : "danger", true);
                return;
            }

            renderProfile(data.data);
            updateUiSession(data.data);
            hideProfileState();
        } catch (error) {
            if (requestId === state.profileRequestId) {
                showProfileState("Unable to reach the profile service. Please retry.", "danger", true);
            }
        } finally {
            if (requestId === state.profileRequestId) {
                state.profileLoading = false;
            }
        }
    }

    async function loadUserStats() {
        if (state.statsLoading) {
            return;
        }

        const staffId = getCurrentStaffId();
        if (!staffId) {
            return;
        }

        state.statsLoading = true;
        const requestId = ++state.statsRequestId;
        showStatsState("Loading order statistics...", false);

        try {
            const response = await apiFetch(`orders/list.php?staff_id=${encodeURIComponent(staffId)}`);
            const data = await readApiResponse(response);

            if (requestId !== state.statsRequestId) {
                return;
            }

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true) {
                showStatsState(responseMessage(response, data, "Unable to load order statistics."));
                setStatsValues("-", "-", "-");
                return;
            }

            const orders = Array.isArray(data.data?.orders) ? data.data.orders : [];
            const sellOrders = orders.filter((order) => order && order.Order_Type === "Sell").length;
            const purchaseOrders = orders.filter((order) => order && order.Order_Type === "Purchase").length;

            setStatsValues(orders.length, sellOrders, purchaseOrders);
            hideStatsState();
        } catch (error) {
            if (requestId === state.statsRequestId) {
                setStatsValues("-", "-", "-");
                showStatsState("Unable to reach the order service. Please retry.");
            }
        } finally {
            if (requestId === state.statsRequestId) {
                state.statsLoading = false;
            }
        }
    }

    async function handleEditProfile(event) {
        event.preventDefault();

        if (state.profileSubmitting) {
            return;
        }

        const form = event.currentTarget;
        const submitButton = form.querySelector('button[type="submit"]');
        const body = {
            full_name: getElement("editName").value.trim(),
            email: getElement("editEmail").value.trim(),
            phone_number: getElement("editPhone").value.trim()
        };

        state.profileSubmitting = true;
        setFormControlsDisabled(form, true, ["editRole"]);
        setButtonState(submitButton, true, "fa-save", "Saving...", "Save Changes");

        try {
            const response = await apiFetch("staff/update-profile.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(body)
            });
            const data = await readApiResponse(response);

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true || !data.data) {
                showProfileState(
                    responseMessage(response, data, "Unable to update your profile."),
                    response.status === 409 ? "warning" : "danger",
                    false
                );
                return;
            }

            renderProfile(data.data);
            updateUiSession(data.data);
            hideProfileState();
            showToast("Profile updated successfully.", "success");
        } catch (error) {
            showProfileState("Unable to reach the profile service. Please retry.", "danger", true);
        } finally {
            state.profileSubmitting = false;
            setFormControlsDisabled(form, false, ["editRole"]);
            setButtonState(submitButton, false, "fa-save", "Saving...", "Save Changes");
            getElement("editRole").disabled = true;
        }
    }

    async function handleChangePassword(event) {
        event.preventDefault();

        if (state.passwordSubmitting) {
            return;
        }

        const form = event.currentTarget;
        const submitButton = form.querySelector('button[type="submit"]');
        const currentPassword = getElement("currentPassword").value;
        const newPassword = getElement("newPassword").value;
        const confirmPassword = getElement("confirmPassword").value;
        const passwordMessage = getElement("passwordMessage");

        hideFormMessage(passwordMessage);

        if (!currentPassword || !newPassword || !confirmPassword) {
            showFormMessage(passwordMessage, "Please fill all password fields.");
            return;
        }

        if (newPassword !== confirmPassword) {
            showFormMessage(passwordMessage, "New passwords do not match.");
            return;
        }

        if (newPassword.length < 6) {
            showFormMessage(passwordMessage, "New password must be at least 6 characters.");
            return;
        }

        state.passwordSubmitting = true;
        setFormControlsDisabled(form, true);
        setButtonState(submitButton, true, "fa-key", "Updating...", "Update Password");

        try {
            const response = await apiFetch("staff/change-password.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword
                })
            });
            const data = await readApiResponse(response);

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true) {
                showFormMessage(
                    passwordMessage,
                    responseMessage(response, data, "Unable to change the password."),
                    response.status === 409 ? "warning" : "danger"
                );
                return;
            }

            form.reset();
            hideFormMessage(passwordMessage);
            showToast("Password changed successfully.", "success");
        } catch (error) {
            showFormMessage(passwordMessage, "Unable to reach the password service.");
        } finally {
            state.passwordSubmitting = false;
            setFormControlsDisabled(form, false);
            setButtonState(submitButton, false, "fa-key", "Updating...", "Update Password");
        }
    }

    function initializeProfilePage() {
        const profileForm = getElement("editProfileForm");
        const passwordForm = getElement("changePasswordForm");

        if (profileForm) {
            profileForm.addEventListener("submit", handleEditProfile);
        }

        if (passwordForm) {
            passwordForm.addEventListener("submit", handleChangePassword);
        }

        getElement("profileRetryBtn")?.addEventListener("click", loadUserProfile);
        getElement("statsRetryBtn")?.addEventListener("click", loadUserStats);

        loadUserProfile();
        loadUserStats();
    }

    document.addEventListener("DOMContentLoaded", initializeProfilePage);
}());
