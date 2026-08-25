/**
 * QuickMart Operations Ledger - Staff Management
 * Admin staff directory, details, update, and delete flows.
 */

(function () {
    "use strict";

    const STAFF_LIST_PATH = "staff/list.php";
    const STAFF_DETAILS_PATH = "staff/get.php";
    const STAFF_CREATE_PATH = "staff/create.php";
    const STAFF_UPDATE_PATH = "staff/update.php";
    const STAFF_DELETE_PATH = "staff/delete.php";
    const STAFF_ROLES = ["Admin", "Manager", "Staff"];

    const iconPaths = {
        users: [
            "M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2",
            "M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8",
            "M17 8a3 3 0 1 1 0 6",
            "M20 21v-2a4 4 0 0 0-2.5-3.7"
        ],
        userPlus: [
            "M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2",
            "M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8",
            "M19 8v6",
            "M22 11h-6"
        ],
        id: ["M4 5h16v14H4z", "M8 9h8M8 13h5"],
        mail: ["M4 6h16v12H4z", "m4 7 8 6 8-6"],
        phone: [
            "M7 4h3l1.2 4-2 1.6a14 14 0 0 0 5.2 5.2L16 13l4 1.2v3c0 1-1 1.8-2 1.8C10.8 19 5 13.2 5 6c0-1 .8-2 2-2Z"
        ],
        eye: ["M2.5 12s3.2-5 9.5-5 9.5 5 9.5 5-3.2 5-9.5 5-9.5-5-9.5-5Z", "M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"],
        edit: ["m4 16-.7 4.7L8 20l10.8-10.8a2.2 2.2 0 0 0-3.1-3.1L4 16Z", "m13.9 7.6 3.1 3.1"],
        trash: ["M5 7h14m-9 4v5m4-5v5M9 7V4h6v3m-8 0 1 13h8l1-13"],
        refresh: ["M20 11a8 8 0 0 0-14.9-4M4 5v4h4M4 13a8 8 0 0 0 14.9 4M20 19v-4h-4"],
        save: ["M5 4h12l2 2v14H5zM8 4v6h8V4M8 20v-6h8v6"],
        close: ["m6 6 12 12M18 6 6 18"],
        warning: ["M12 3 2.8 20h18.4L12 3Z", "M12 9v5m0 3h.01"],
        restricted: ["M7 11V8a5 5 0 0 1 10 0v3M5 11h14v9H5zM12 15v2"],
        info: ["M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z", "M12 10v6", "M12 7.5h.01"],
        loading: ["M12 3a9 9 0 1 0 9 9", "M12 3v3"],
        logout: ["m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5"]
    };

    const roleClasses = {
        Admin: "staff-role-badge--admin",
        Manager: "staff-role-badge--manager",
        Staff: "staff-role-badge--staff"
    };

    const state = {
        staffList: [],
        listLoading: false,
        listRequestId: 0,
        listController: null,
        detailsRequestId: 0,
        detailsController: null,
        detailsStaffId: null,
        createSubmitting: false,
        editLoading: false,
        editSubmitting: false,
        deleteSubmitting: false,
        focusTargets: new Map()
    };

    function byId(id) {
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
            svg.classList.add("staff-loading-icon");
        }

        return svg;
    }

    function displayValue(value, fallback = "—") {
        if (value === null || value === undefined || String(value).trim() === "") {
            return fallback;
        }

        return String(value);
    }

    function isSupportedRole(role) {
        return STAFF_ROLES.includes(String(role));
    }

    function roleLabel(role) {
        return isSupportedRole(role) ? String(role) : "Role unavailable";
    }

    function roleClass(role) {
        return roleClasses[String(role)] || "staff-role-badge--unknown";
    }

    function setVisible(element, visible) {
        if (element) {
            element.classList.toggle("d-none", !visible);
        }
    }

    function setButtonContent(button, iconName, label, disabled) {
        if (!button) {
            return;
        }

        button.replaceChildren(createIcon(iconName), document.createTextNode(label));
        button.disabled = Boolean(disabled);
    }

    function showInlineMessage(element, message, type = "danger") {
        if (!element) {
            return;
        }

        element.className = "staff-inline-message";
        if (type === "info" || type === "warning") {
            element.classList.add(`staff-inline-message--${type}`);
        }
        element.textContent = String(message || "");
        setVisible(element, true);
    }

    function hideInlineMessage(element) {
        if (!element) {
            return;
        }

        element.className = "staff-inline-message d-none";
        element.textContent = "";
    }

    function clearClientSessionAndRedirect() {
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
                return "You are not authorized to manage staff.";
            case 404:
                return "The staff member was not found.";
            case 409:
                return "The request conflicts with existing staff or order data.";
            case 422:
                return "Please check the submitted staff details.";
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

    function isClientAdmin() {
        return sessionStorage.getItem("userRole") === "Admin";
    }

    function showStaffLoading() {
        setVisible(byId("loadingSpinner"), true);
        setVisible(byId("staffErrorState"), false);
        setVisible(byId("accessDenied"), false);
        setVisible(byId("adminContent"), false);
        setButtonContent(byId("refreshStaffBtn"), "loading", "Loading directory…", true);
        setButtonContent(byId("addStaffBtn"), "userPlus", "Add staff member", true);
    }

    function showStaffError(message) {
        setVisible(byId("loadingSpinner"), false);
        setVisible(byId("staffErrorState"), true);
        setVisible(byId("accessDenied"), false);
        setVisible(byId("adminContent"), false);
        const errorMessage = byId("staffErrorMessage");
        if (errorMessage) {
            errorMessage.textContent = String(message || "Unable to load the staff directory.");
        }
        setButtonContent(byId("staffRetryBtn"), "refresh", "Retry loading", false);
        setButtonContent(byId("refreshStaffBtn"), "refresh", "Refresh directory", false);
    }

    function showStaffAccessDenied(message) {
        setVisible(byId("loadingSpinner"), false);
        setVisible(byId("staffErrorState"), false);
        setVisible(byId("accessDenied"), true);
        setVisible(byId("adminContent"), false);
        const description = byId("accessDenied")?.querySelector(".staff-state-card__copy p:not(.staff-eyebrow)");
        if (description && message) {
            description.textContent = String(message);
        }
        setButtonContent(byId("refreshStaffBtn"), "refresh", "Refresh directory", true);
    }

    function showStaffContent() {
        setVisible(byId("loadingSpinner"), false);
        setVisible(byId("staffErrorState"), false);
        setVisible(byId("accessDenied"), false);
        setVisible(byId("adminContent"), true);
        setButtonContent(byId("refreshStaffBtn"), "refresh", "Refresh directory", false);
        setButtonContent(byId("addStaffBtn"), "userPlus", "Add staff member", false);
    }

    function createCardDetail(iconName, value, ltr = false) {
        const detail = createElement("div", "staff-card__detail");
        const valueElement = createElement("span", "staff-card__detail-value", value);
        if (ltr) {
            valueElement.classList.add("staff-ltr");
            valueElement.dir = "ltr";
        }
        detail.append(createIcon(iconName), valueElement);
        return detail;
    }

    function createActionButton(label, iconName, className, handler) {
        const button = createElement("button", `staff-button staff-action-button ${className}`);
        button.type = "button";
        button.append(createIcon(iconName), createElement("span", "", label));
        button.addEventListener("click", handler);
        return button;
    }

    function createEmptyState() {
        const emptyState = createElement("div", "staff-empty-state");
        emptyState.append(
            createIcon("users"),
            createElement("h3", "", "No staff records yet"),
            createElement("p", "", "The directory is currently empty. Staff accounts can be managed here once they are available through the existing administration workflow.")
        );
        return emptyState;
    }

    function createStaffCard(staff) {
        const staffId = displayValue(staff && staff.Staff_ID, "");
        const fullName = displayValue(staff && staff.Full_Name, "Unnamed staff");
        const email = displayValue(staff && staff.Email, "Not provided");
        const phone = displayValue(staff && staff.Phone_Number, "Not provided");
        const role = roleLabel(staff && staff.Role);
        const card = createElement("article", "staff-card");
        card.setAttribute("role", "listitem");
        card.dataset.staffId = staffId;

        const header = createElement("div", "staff-card__header");
        const avatar = createElement("span", "staff-avatar", Array.from(fullName.trim())[0]?.toUpperCase() || "?");
        avatar.setAttribute("aria-hidden", "true");
        const identity = createElement("div", "staff-card__identity");
        const name = createElement("h3", "staff-card__name", fullName);
        const roleBadge = createElement("span", `staff-role-badge ${roleClass(role)}`, role);
        identity.append(name, roleBadge);
        const id = createElement("span", "staff-card__id staff-ltr", staffId || "—");
        id.dir = "ltr";
        header.append(avatar, identity, id);

        const details = createElement("div", "staff-card__details");
        details.append(
            createCardDetail("id", staffId || "Not provided", true),
            createCardDetail("mail", email, true),
            createCardDetail("phone", phone, true)
        );

        const actions = createElement("div", "staff-card__actions");
        const detailsButton = createActionButton("Details", "eye", "staff-button--secondary staff-details-action", () => {
            openStaffDetails(staffId, detailsButton);
        });
        const editButton = createActionButton("Edit", "edit", "staff-button--primary staff-edit-action", () => {
            openEditModal(staffId, editButton);
        });
        const deleteButton = createActionButton("Delete", "trash", "staff-button--danger staff-delete-action", () => {
            openDeleteModal(staffId, fullName, deleteButton);
        });

        if (staffId === "STF001") {
            deleteButton.disabled = true;
            deleteButton.title = "The primary admin account cannot be removed.";
            deleteButton.setAttribute("aria-label", "The primary admin account cannot be removed");
            deleteButton.classList.remove("staff-button--danger");
            deleteButton.classList.add("staff-action-button--muted");
        }

        actions.append(detailsButton, editButton, deleteButton);
        card.append(header, details, actions);
        return card;
    }

    function renderStaffGrid(staffList) {
        const grid = byId("staffGrid");
        if (!grid) {
            return;
        }

        grid.replaceChildren();

        if (!Array.isArray(staffList) || staffList.length === 0) {
            grid.append(createEmptyState());
            return;
        }

        staffList.forEach((staff) => {
            grid.append(createStaffCard(staff));
        });
    }

    function updateStaffCount(total) {
        const countElement = byId("staffCount");
        if (!countElement) {
            return;
        }

        const count = Number.isFinite(Number(total)) && Number(total) >= 0 ? Number(total) : 0;
        countElement.replaceChildren(
            createElement("span", "staff-ltr", count),
            document.createTextNode(count === 1 ? " staff member" : " staff members")
        );
    }

    async function loadStaffList() {
        if (state.listLoading) {
            return;
        }

        if (!isClientAdmin()) {
            showStaffAccessDenied("This workspace is only available to administrators.");
            return;
        }

        state.listLoading = true;
        const requestId = ++state.listRequestId;
        state.listController?.abort();
        state.listController = new AbortController();
        showStaffLoading();

        try {
            const response = await apiFetch(STAFF_LIST_PATH, {
                signal: state.listController.signal
            });
            const data = await readApiResponse(response);

            if (requestId !== state.listRequestId) {
                return;
            }

            if (handleUnauthorized(response)) {
                return;
            }

            if (response.status === 403) {
                showStaffAccessDenied(responseMessage(response, data, "This workspace is only available to administrators."));
                return;
            }

            if (!response.ok || !data || data.success !== true) {
                showStaffError(responseMessage(response, data, "Unable to load the staff directory. Please retry."));
                return;
            }

            const responseData = data.data && typeof data.data === "object" ? data.data : {};
            const staffList = Array.isArray(responseData.staff) ? responseData.staff : [];
            const total = Number.isFinite(Number(responseData.total)) && Number(responseData.total) >= 0
                ? Number(responseData.total)
                : staffList.length;

            state.staffList = staffList;
            renderStaffGrid(staffList);
            updateStaffCount(total);
            showStaffContent();
        } catch (error) {
            if (error && error.name === "AbortError") {
                return;
            }

            if (requestId === state.listRequestId) {
                showStaffError("Unable to reach the staff service. Please retry.");
            }
        } finally {
            if (requestId === state.listRequestId) {
                state.listLoading = false;
                state.listController = null;
            }
        }
    }

    function setDetailsState(message, type = "info", canRetry = false) {
        const stateElement = byId("staffDetailsState");
        const messageElement = byId("staffDetailsStateMessage");
        const retryButton = byId("staffDetailsRetryBtn");

        if (stateElement) {
            stateElement.className = "staff-modal-state";
            stateElement.classList.add(type === "error" ? "staff-modal-state--error" : "staff-modal-state--info");
            setVisible(stateElement, true);
        }

        if (messageElement) {
            messageElement.textContent = String(message || "");
        }

        if (retryButton) {
            setVisible(retryButton, canRetry);
            setButtonContent(retryButton, "refresh", "Retry", !canRetry);
        }

        setVisible(byId("staffDetailsList"), false);
        const editButton = byId("staffDetailsEditBtn");
        if (editButton) {
            editButton.disabled = true;
        }
    }

    function resetDetailsState() {
        setDetailsState("Loading staff details…", "info", false);
    }

    function renderStaffDetails(staff) {
        byId("detailsStaffId").textContent = displayValue(staff.Staff_ID);
        byId("detailsFullName").textContent = displayValue(staff.Full_Name);
        byId("detailsEmail").textContent = displayValue(staff.Email);
        byId("detailsPhone").textContent = displayValue(staff.Phone_Number, "Not provided");
        byId("detailsRole").textContent = roleLabel(staff.Role);
        setVisible(byId("staffDetailsState"), false);
        setVisible(byId("staffDetailsList"), true);
        byId("staffDetailsEditBtn").disabled = false;
    }

    function showModal(id, focusTarget) {
        const modalElement = byId(id);
        if (!modalElement || typeof bootstrap === "undefined" || !bootstrap.Modal) {
            return;
        }

        if (focusTarget) {
            state.focusTargets.set(id, focusTarget);
        }

        bootstrap.Modal.getOrCreateInstance(modalElement).show();
    }

    function hideModal(id) {
        const modalElement = byId(id);
        if (modalElement && typeof bootstrap !== "undefined" && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

            // A fast API response can arrive while Bootstrap is still
            // finishing the opening transition. Queue the close until the
            // modal is fully shown so the hide call is not ignored.
            if (modal._isTransitioning) {
                let closeHandled = false;
                const runClose = () => {
                    if (closeHandled) {
                        return;
                    }

                    closeHandled = true;
                    modal.hide();
                };

                modalElement.addEventListener("shown.bs.modal", runClose, { once: true });
                window.setTimeout(runClose, 400);
                return;
            }

            modal.hide();
        }
    }

    async function loadStaffDetails(staffId) {
        const requestId = ++state.detailsRequestId;
        state.detailsController?.abort();
        state.detailsController = new AbortController();
        resetDetailsState();

        try {
            const response = await apiFetch(`${STAFF_DETAILS_PATH}?id=${encodeURIComponent(staffId)}`, {
                signal: state.detailsController.signal
            });
            const data = await readApiResponse(response);

            if (requestId !== state.detailsRequestId) {
                return;
            }

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true || !data.data) {
                setDetailsState(responseMessage(response, data, "Unable to load staff details."), "error", true);
                return;
            }

            renderStaffDetails(data.data);
        } catch (error) {
            if (error && error.name === "AbortError") {
                return;
            }

            if (requestId === state.detailsRequestId) {
                setDetailsState("Unable to reach the staff service.", "error", true);
            }
        } finally {
            if (requestId === state.detailsRequestId) {
                state.detailsController = null;
            }
        }
    }

    function openStaffDetails(staffId, trigger) {
        if (!staffId || !isClientAdmin()) {
            return;
        }

        state.detailsStaffId = staffId;
        resetDetailsState();
        showModal("staffDetailsModal", trigger);
        loadStaffDetails(staffId);
    }

    function setEditFormDisabled(disabled) {
        const form = byId("editStaffForm");
        if (!form) {
            return;
        }

        form.querySelectorAll("input, select, button").forEach((control) => {
            control.disabled = Boolean(disabled);
        });
    }

    function resetEditForm() {
        byId("editStaffId").value = "";
        byId("editFullName").value = "";
        byId("editEmail").value = "";
        byId("editPhone").value = "";
        byId("editRole").value = "Staff";
        byId("editPassword").value = "";
        byId("editStaffForm").classList.remove("was-validated");
        hideInlineMessage(byId("editMessage"));
    }

    function resetCreateForm() {
        const form = byId("createStaffForm");
        if (!form) {
            return;
        }

        byId("createFullName").value = "";
        byId("createEmail").value = "";
        byId("createPhone").value = "";
        byId("createRole").value = "Staff";
        byId("createPassword").value = "";
        byId("createPasswordConfirm").value = "";
        byId("createPasswordConfirm").setCustomValidity("");
        form.classList.remove("was-validated");
        hideInlineMessage(byId("createMessage"));
    }

    function setCreateFormDisabled(disabled) {
        const form = byId("createStaffForm");
        if (!form) {
            return;
        }

        form.querySelectorAll("input, select, button").forEach((control) => {
            control.disabled = Boolean(disabled);
        });
    }

    function openCreateModal(trigger) {
        if (state.createSubmitting || !isClientAdmin()) {
            return;
        }

        resetCreateForm();
        const modal = byId("createStaffModal");
        const firstField = byId("createFullName");
        if (modal && firstField) {
            modal.addEventListener("shown.bs.modal", () => firstField.focus(), { once: true });
        }
        showModal("createStaffModal", trigger);
    }

    async function handleCreateSubmit(event) {
        event.preventDefault();

        if (state.createSubmitting) {
            return;
        }

        const form = event.currentTarget;
        const passwordInput = byId("createPassword");
        const confirmInput = byId("createPasswordConfirm");
        const submitButton = byId("createStaffSubmitBtn");
        let password = passwordInput.value;
        let confirmedPassword = confirmInput.value;

        confirmInput.setCustomValidity(
            password === confirmedPassword ? "" : "The passwords must match."
        );
        form.classList.add("was-validated");

        if (!form.checkValidity()) {
            showInlineMessage(byId("createMessage"), "Review the highlighted fields before creating the account.", "danger");
            form.reportValidity();
            return;
        }

        let payload = JSON.stringify({
            full_name: byId("createFullName").value.trim(),
            email: byId("createEmail").value.trim(),
            phone_number: byId("createPhone").value.trim(),
            role: byId("createRole").value,
            password
        });

        state.createSubmitting = true;
        setCreateFormDisabled(true);
        setButtonContent(submitButton, "loading", "Creating account…", true);

        try {
            const response = await apiFetch(STAFF_CREATE_PATH, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: payload
            });
            const data = await readApiResponse(response);

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true) {
                showInlineMessage(
                    byId("createMessage"),
                    responseMessage(response, data, "Unable to create the staff account."),
                    response.status === 409 ? "warning" : "danger"
                );
                return;
            }

            hideModal("createStaffModal");
            showToast("Staff member created successfully.", "success");
            loadStaffList();
        } catch (error) {
            showInlineMessage(byId("createMessage"), "Unable to reach the staff service.", "danger");
        } finally {
            // Password values are cleared after every attempt and never enter
            // browser storage, URLs, logs, or API response rendering.
            password = "";
            confirmedPassword = "";
            payload = "";
            passwordInput.value = "";
            confirmInput.value = "";
            confirmInput.setCustomValidity("");
            state.createSubmitting = false;
            setCreateFormDisabled(false);
            setButtonContent(submitButton, "userPlus", "Create account", false);
        }
    }

    async function openEditModal(staffId, trigger) {
        if (!staffId || state.editLoading || state.editSubmitting || !isClientAdmin()) {
            return;
        }

        state.editLoading = true;
        resetEditForm();
        byId("editStaffId").value = staffId;
        showInlineMessage(byId("editMessage"), "Loading staff record…", "info");
        setEditFormDisabled(true);
        showModal("editStaffModal", trigger);

        try {
            const response = await apiFetch(`${STAFF_DETAILS_PATH}?id=${encodeURIComponent(staffId)}`);
            const data = await readApiResponse(response);

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true || !data.data) {
                showInlineMessage(byId("editMessage"), responseMessage(response, data, "Unable to load staff details."), "danger");
                return;
            }

            const staff = data.data;
            byId("editStaffId").value = displayValue(staff.Staff_ID, staffId);
            byId("editFullName").value = displayValue(staff.Full_Name, "");
            byId("editEmail").value = displayValue(staff.Email, "");
            byId("editPhone").value = staff.Phone_Number === null || staff.Phone_Number === undefined
                ? ""
                : String(staff.Phone_Number);
            byId("editRole").value = isSupportedRole(staff.Role) ? String(staff.Role) : "Staff";
            // Never copy a password from an API response into the page.
            byId("editPassword").value = "";
            hideInlineMessage(byId("editMessage"));
        } catch (error) {
            showInlineMessage(byId("editMessage"), "Unable to reach the staff service.", "danger");
        } finally {
            state.editLoading = false;
            if (!state.editSubmitting) {
                setEditFormDisabled(false);
            }
        }
    }

    async function handleEditSubmit(event) {
        event.preventDefault();

        if (state.editSubmitting || state.editLoading) {
            return;
        }

        const form = event.currentTarget;
        const saveButton = byId("saveEditBtn");
        const staffId = byId("editStaffId").value.trim();
        form.classList.add("was-validated");

        if (!form.checkValidity()) {
            showInlineMessage(byId("editMessage"), "Review the highlighted fields before saving.", "danger");
            form.reportValidity();
            return;
        }

        if (!staffId) {
            showInlineMessage(byId("editMessage"), "The staff record identifier is missing.", "danger");
            return;
        }

        const body = {
            staff_id: staffId,
            full_name: byId("editFullName").value.trim(),
            email: byId("editEmail").value.trim(),
            phone_number: byId("editPhone").value.trim(),
            role: byId("editRole").value
        };
        const password = byId("editPassword").value;

        if (password !== "") {
            body.password = password;
        }

        state.editSubmitting = true;
        setEditFormDisabled(true);
        setButtonContent(saveButton, "loading", "Saving changes…", true);

        try {
            const response = await apiFetch(STAFF_UPDATE_PATH, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(body)
            });
            const data = await readApiResponse(response);

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true) {
                showInlineMessage(
                    byId("editMessage"),
                    responseMessage(response, data, "Unable to update the staff member."),
                    response.status === 409 ? "warning" : "danger"
                );
                return;
            }

            hideModal("editStaffModal");
            showToast("Staff member updated successfully.", "success");
            loadStaffList();
        } catch (error) {
            showInlineMessage(byId("editMessage"), "Unable to reach the staff service.", "danger");
        } finally {
            state.editSubmitting = false;
            setEditFormDisabled(false);
            setButtonContent(saveButton, "save", "Save changes", false);
        }
    }

    function openDeleteModal(staffId, staffName, trigger) {
        if (!staffId || !isClientAdmin()) {
            return;
        }

        byId("deleteStaffId").value = staffId;
        byId("deleteStaffName").textContent = displayValue(staffName, "this staff member");
        hideInlineMessage(byId("deleteMessage"));
        setButtonContent(byId("confirmDeleteBtn"), "trash", "Remove account", false);
        showModal("deleteStaffModal", trigger);
    }

    async function handleDelete() {
        if (state.deleteSubmitting) {
            return;
        }

        const deleteButton = byId("confirmDeleteBtn");
        const staffId = byId("deleteStaffId").value.trim();

        if (!staffId) {
            showInlineMessage(byId("deleteMessage"), "The staff record identifier is missing.", "danger");
            return;
        }

        state.deleteSubmitting = true;
        setButtonContent(deleteButton, "loading", "Removing account…", true);

        try {
            const response = await apiFetch(STAFF_DELETE_PATH, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ staff_id: staffId })
            });
            const data = await readApiResponse(response);

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true) {
                showInlineMessage(
                    byId("deleteMessage"),
                    responseMessage(response, data, "Unable to remove the staff member."),
                    response.status === 409 ? "warning" : "danger"
                );
                return;
            }

            hideModal("deleteStaffModal");
            showToast("Staff member removed successfully.", "success");
            loadStaffList();
        } catch (error) {
            showInlineMessage(byId("deleteMessage"), "Unable to reach the staff service.", "danger");
        } finally {
            state.deleteSubmitting = false;
            setButtonContent(deleteButton, "trash", "Remove account", false);
        }
    }

    function setupModalFocusRestoration() {
        const modalIds = ["createStaffModal", "editStaffModal", "staffDetailsModal", "deleteStaffModal"];

        modalIds.forEach((modalId) => {
            const modalElement = byId(modalId);
            if (!modalElement) {
                return;
            }

            modalElement.addEventListener("hidden.bs.modal", () => {
                const target = state.focusTargets.get(modalId);
                state.focusTargets.delete(modalId);
                if (modalId === "createStaffModal") {
                    resetCreateForm();
                }
                if (target && target.isConnected && !target.disabled) {
                    target.focus();
                }
            });

            modalElement.addEventListener("keydown", (event) => {
                if (event.key === "Escape") {
                    event.preventDefault();
                    hideModal(modalId);
                }
            });
        });

        // Bootstrap keeps focus on the trigger until the opening transition
        // completes. Handle Escape at the document level as well so a quick
        // keyboard dismissal still closes the visible dialog.
        document.addEventListener("keydown", (event) => {
            if (event.key !== "Escape") {
                return;
            }

            const openModal = modalIds
                .map((modalId) => byId(modalId))
                .find((modalElement) => modalElement && modalElement.classList.contains("show"));

            if (openModal) {
                event.preventDefault();
                hideModal(openModal.id);
            }
        });
    }

    function initializeStaffPage() {
        const createForm = byId("createStaffForm");
        const addStaffButton = byId("addStaffBtn");
        const editForm = byId("editStaffForm");
        const retryButton = byId("staffRetryBtn");
        const refreshButton = byId("refreshStaffBtn");
        const detailsEditButton = byId("staffDetailsEditBtn");
        const detailsRetryButton = byId("staffDetailsRetryBtn");
        const deleteButton = byId("confirmDeleteBtn");

        if (createForm) {
            createForm.addEventListener("submit", handleCreateSubmit);
        }

        if (addStaffButton) {
            addStaffButton.addEventListener("click", () => openCreateModal(addStaffButton));
        }

        if (editForm) {
            editForm.addEventListener("submit", handleEditSubmit);
        }

        if (retryButton) {
            retryButton.addEventListener("click", loadStaffList);
        }

        if (refreshButton) {
            refreshButton.addEventListener("click", loadStaffList);
        }

        if (detailsRetryButton) {
            detailsRetryButton.addEventListener("click", () => {
                if (state.detailsStaffId) {
                    loadStaffDetails(state.detailsStaffId);
                }
            });
        }

        if (detailsEditButton) {
            detailsEditButton.addEventListener("click", () => {
                const staffId = state.detailsStaffId;
                const returnTarget = state.focusTargets.get("staffDetailsModal");
                hideModal("staffDetailsModal");

                if (staffId) {
                    openEditModal(staffId, returnTarget);
                }
            });
        }

        if (deleteButton) {
            deleteButton.addEventListener("click", handleDelete);
        }

        setupModalFocusRestoration();

        if (!isClientAdmin()) {
            showStaffAccessDenied("This workspace is only available to administrators.");
            return;
        }

        loadStaffList();
    }

    document.addEventListener("DOMContentLoaded", initializeStaffPage);
}());
