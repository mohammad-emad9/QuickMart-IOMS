/**
 * QuickMart Operations Ledger - Profile and Account Settings
 * Current-user profile, order snapshot, and password flows.
 */

(function () {
    "use strict";

    const PROFILE_PATH = "staff/get.php";
    const STATS_PATH = "orders/list.php";
    const UPDATE_PROFILE_PATH = "staff/update-profile.php";
    const CHANGE_PASSWORD_PATH = "staff/change-password.php";

    const iconPaths = {
        loading: ["M12 3a9 9 0 1 0 9 9", "M12 3v3"],
        refresh: ["M20 11a8 8 0 0 0-14.9-4M4 5v4h4M4 13a8 8 0 0 0 14.9 4M20 19v-4h-4"],
        save: ["M5 4h12l2 2v14H5zM8 4v6h8V4M8 20v-6h8v6"],
        lock: ["M7 11V8a5 5 0 0 1 10 0v3M5 11h14v9H5zM12 15v2"],
        logout: ["m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5"],
        check: ["M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z", "m8 12 2.5 2.5L16 9"],
        error: ["M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z", "m9 9 6 6", "m15 9-6 6"],
        info: ["M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z", "M12 10v6", "M12 7.5h.01"]
    };

    const state = {
        profileLoading: false,
        profileRequestId: 0,
        profileReady: false,
        profileSubmitting: false,
        statsLoading: false,
        statsRequestId: 0,
        passwordSubmitting: false,
        redirecting: false
    };

    function getElement(id) {
        return document.getElementById(id);
    }

    function createElement(tagName, className, text) {
        const element = document.createElement(tagName);

        if (className) {
            element.className = className;
        }

        if (text !== undefined) {
            element.textContent = String(text);
        }

        return element;
    }

    function createIcon(name) {
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("viewBox", "0 0 24 24");
        svg.setAttribute("fill", "none");
        svg.setAttribute("stroke", "currentColor");
        svg.setAttribute("stroke-width", "1.8");
        svg.setAttribute("stroke-linecap", "round");
        svg.setAttribute("stroke-linejoin", "round");
        svg.setAttribute("aria-hidden", "true");

        (iconPaths[name] || iconPaths.info).forEach((pathData) => {
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", pathData);
            svg.appendChild(path);
        });

        if (name === "loading") {
            svg.classList.add("profile-loading-icon");
        }

        return svg;
    }

    function displayValue(value, fallback = "—") {
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

    function setButtonState(button, busy, iconName, busyLabel, idleLabel) {
        if (!button) {
            return;
        }

        button.replaceChildren(
            createIcon(busy ? "loading" : iconName),
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

        element.className = "profile-form-message";
        if (["success", "warning", "info"].includes(type)) {
            element.classList.add(`profile-form-message--${type}`);
        }
        element.textContent = String(message || "");
        setVisible(element, true);
    }

    function hideFormMessage(element) {
        if (!element) {
            return;
        }

        element.className = "profile-form-message d-none";
        element.textContent = "";
    }

    function showProfileState(message, type = "info", retry = false) {
        const stateElement = getElement("profileState");
        const stateMessage = getElement("profileStateMessage");
        const retryButton = getElement("profileRetryBtn");

        if (!stateElement) {
            return;
        }

        stateElement.className = "profile-state";
        stateElement.classList.add(type === "warning" ? "profile-state--warning" : type === "error" ? "profile-state--error" : "profile-state--info");
        if (stateMessage) {
            stateMessage.textContent = String(message || "");
        }
        setVisible(retryButton, retry);
        setButtonState(retryButton, false, "refresh", "Retry", "Retry");
        setVisible(stateElement, true);
    }

    function hideProfileState() {
        setVisible(getElement("profileState"), false);
    }

    function showStatsState(message, type = "error", retry = true) {
        const stateElement = getElement("statsState");
        const stateMessage = getElement("statsStateMessage");
        const retryButton = getElement("statsRetryBtn");

        if (!stateElement) {
            return;
        }

        stateElement.className = "profile-state";
        stateElement.classList.add(type === "warning" ? "profile-state--warning" : type === "info" ? "profile-state--info" : "profile-state--error");
        if (stateMessage) {
            stateMessage.textContent = String(message || "");
        }
        setVisible(retryButton, retry);
        setButtonState(retryButton, false, "refresh", "Retry", "Retry");
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

    function isWrongCurrentPasswordResponse(response, data) {
        return response.status === 401
            && data
            && typeof data.message === "string"
            && data.message.trim().toLowerCase() === "current password is incorrect.";
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
        const role = displayValue(staff.Role);

        getElement("profileName").textContent = fullName;
        getElement("profileEmail").textContent = email;
        getElement("profilePhone").textContent = phone;
        getElement("profileId").textContent = staffId;
        getElement("profileRole").textContent = role;

        getElement("editName").value = fullName === "—" ? "" : fullName;
        getElement("editEmail").value = email === "—" ? "" : email;
        getElement("editPhone").value = phone === "Not set" ? "" : phone;
        getElement("editRole").value = role === "—" ? "" : role;
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
        state.profileReady = false;
        const requestId = ++state.profileRequestId;
        setFormControlsDisabled(getElement("editProfileForm"), true, ["editRole"]);
        showProfileState("Loading profile…", "info", false);

        try {
            const response = await apiFetch(`${PROFILE_PATH}?id=${encodeURIComponent(staffId)}`);
            const data = await readApiResponse(response);

            if (requestId !== state.profileRequestId) {
                return;
            }

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true || !data.data) {
                const type = response.status === 403 || response.status === 409 ? "warning" : "error";
                const retry = ![403, 404, 409, 422].includes(response.status);
                showProfileState(responseMessage(response, data, "Unable to load your profile."), type, retry);
                return;
            }

            renderProfile(data.data);
            updateUiSession(data.data);
            state.profileReady = true;
            setFormControlsDisabled(getElement("editProfileForm"), false, ["editRole"]);
            hideFormMessage(getElement("profileFormMessage"));
            hideProfileState();
        } catch (error) {
            if (requestId === state.profileRequestId) {
                showProfileState("Unable to reach the profile service. Please retry.", "error", true);
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
        showStatsState("Loading order activity…", "info", false);

        try {
            const response = await apiFetch(`${STATS_PATH}?staff_id=${encodeURIComponent(staffId)}`);
            const data = await readApiResponse(response);

            if (requestId !== state.statsRequestId) {
                return;
            }

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true) {
                const type = response.status === 403 ? "warning" : "error";
                const retry = ![401, 403, 404, 422].includes(response.status);
                showStatsState(responseMessage(response, data, "Unable to load order activity."), type, retry);
                setStatsValues("—", "—", "—");
                return;
            }

            const orders = Array.isArray(data.data?.orders) ? data.data.orders : [];
            const sellOrders = orders.filter((order) => order && order.Order_Type === "Sell").length;
            const purchaseOrders = orders.filter((order) => order && order.Order_Type === "Purchase").length;

            setStatsValues(orders.length, sellOrders, purchaseOrders);
            hideStatsState();
        } catch (error) {
            if (requestId === state.statsRequestId) {
                setStatsValues("—", "—", "—");
                showStatsState("Unable to reach the order service. Please retry.", "error", true);
            }
        } finally {
            if (requestId === state.statsRequestId) {
                state.statsLoading = false;
            }
        }
    }

    async function handleEditProfile(event) {
        event.preventDefault();

        if (state.profileSubmitting || !state.profileReady) {
            if (!state.profileReady) {
                showFormMessage(getElement("profileFormMessage"), "Your profile is still loading. Please retry if this continues.", "info");
            }
            return;
        }

        const form = event.currentTarget;
        const submitButton = form.querySelector('button[type="submit"]');
        const profileMessage = getElement("profileFormMessage");
        form.classList.add("was-validated");

        if (!form.checkValidity()) {
            showFormMessage(profileMessage, "Review the highlighted profile fields before saving.");
            form.reportValidity();
            return;
        }

        const body = {
            full_name: getElement("editName").value.trim(),
            email: getElement("editEmail").value.trim(),
            phone_number: getElement("editPhone").value.trim()
        };

        state.profileSubmitting = true;
        hideFormMessage(profileMessage);
        setFormControlsDisabled(form, true, ["editRole"]);
        setButtonState(submitButton, true, "save", "Saving profile…", "Save profile");

        try {
            const response = await apiFetch(UPDATE_PROFILE_PATH, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(body)
            });
            const data = await readApiResponse(response);

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true || !data.data) {
                const type = response.status === 409 ? "warning" : response.status === 403 ? "warning" : "danger";
                const stateType = response.status === 409 || response.status === 403 ? "warning" : "error";
                const retry = ![403, 404, 409, 422].includes(response.status);
                const message = responseMessage(response, data, "Unable to update your profile.");
                showFormMessage(profileMessage, message, type === "danger" ? "danger" : type);
                showProfileState(message, stateType, retry);
                return;
            }

            renderProfile(data.data);
            updateUiSession(data.data);
            state.profileReady = true;
            hideProfileState();
            showFormMessage(profileMessage, "Profile updated successfully.", "success");
            showToast("Profile updated successfully.", "success");
        } catch (error) {
            const message = "Unable to reach the profile service. Please retry.";
            showFormMessage(profileMessage, message, "danger");
            showProfileState(message, "error", true);
        } finally {
            state.profileSubmitting = false;
            setFormControlsDisabled(form, false, ["editRole"]);
            setButtonState(submitButton, false, "save", "Saving profile…", "Save profile");
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
        form.classList.add("was-validated");

        if (!form.checkValidity() || !currentPassword || !newPassword || !confirmPassword) {
            showFormMessage(passwordMessage, "Complete all password fields before submitting.");
            form.reportValidity();
            return;
        }

        if (newPassword !== confirmPassword) {
            showFormMessage(passwordMessage, "New passwords do not match.");
            return;
        }

        state.passwordSubmitting = true;
        setFormControlsDisabled(form, true);
        setButtonState(submitButton, true, "lock", "Updating password…", "Update password");

        try {
            const response = await apiFetch(CHANGE_PASSWORD_PATH, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    current_password: currentPassword,
                    new_password: newPassword
                })
            });
            const data = await readApiResponse(response);

            // The password endpoint uses 401 for both a wrong current
            // password and a stale/deleted session. Only the explicit
            // backend wrong-password message stays inline; all other 401s
            // retain the existing stale-session redirect behavior.
            if (isWrongCurrentPasswordResponse(response, data)) {
                showFormMessage(passwordMessage, responseMessage(response, data, "Current password is incorrect."), "warning");
                return;
            }

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
            form.classList.remove("was-validated");
            showFormMessage(passwordMessage, "Password changed successfully.", "success");
            showToast("Password changed successfully.", "success");
        } catch (error) {
            showFormMessage(passwordMessage, "Unable to reach the password service. Please retry.", "danger");
        } finally {
            state.passwordSubmitting = false;
            setFormControlsDisabled(form, false);
            setButtonState(submitButton, false, "lock", "Updating password…", "Update password");
        }
    }

    function initializeProfilePage() {
        const profileForm = getElement("editProfileForm");
        const passwordForm = getElement("changePasswordForm");

        if (profileForm) {
            profileForm.addEventListener("submit", handleEditProfile);
            setFormControlsDisabled(profileForm, true, ["editRole"]);
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
