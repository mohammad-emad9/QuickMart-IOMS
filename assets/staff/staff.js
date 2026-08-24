/**
 * QuickMart IOMS - Staff Management
 * Admin staff list, details, update, and delete flows.
 */

(function () {
    "use strict";

    const state = {
        listLoading: false,
        listRequestId: 0,
        listController: null,
        detailsRequestId: 0,
        detailsController: null,
        detailsStaffId: null,
        editLoading: false,
        editSubmitting: false,
        deleteSubmitting: false
    };

    const roleClasses = {
        Admin: "role-Admin",
        Manager: "role-Manager",
        Staff: "role-Staff"
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

    function setButtonLabel(button, label, disabled) {
        if (!button) {
            return;
        }

        button.replaceChildren(document.createTextNode(label));
        button.disabled = Boolean(disabled);
    }

    function showInlineMessage(element, message, type = "danger") {
        if (!element) {
            return;
        }

        element.className = `alert alert-${type}`;
        element.textContent = String(message || "");
        setVisible(element, true);
    }

    function hideInlineMessage(element) {
        if (!element) {
            return;
        }

        element.textContent = "";
        setVisible(element, false);
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

    function showStaffLoading() {
        setVisible(getElement("loadingSpinner"), true);
        setVisible(getElement("staffErrorState"), false);
        setVisible(getElement("accessDenied"), false);
        setVisible(getElement("adminContent"), false);
    }

    function showStaffError(message) {
        setVisible(getElement("loadingSpinner"), false);
        setVisible(getElement("accessDenied"), false);
        setVisible(getElement("adminContent"), false);
        getElement("staffErrorMessage").textContent = String(message || "Unable to load staff.");
        setVisible(getElement("staffErrorState"), true);
    }

    function showStaffAccessDenied(message) {
        setVisible(getElement("loadingSpinner"), false);
        setVisible(getElement("staffErrorState"), false);
        setVisible(getElement("adminContent"), false);
        const accessDenied = getElement("accessDenied");
        const description = accessDenied ? accessDenied.querySelector("p") : null;

        if (description) {
            description.textContent = String(message || "This page is only accessible to Administrators.");
        }

        setVisible(accessDenied, true);
    }

    function showStaffContent() {
        setVisible(getElement("loadingSpinner"), false);
        setVisible(getElement("staffErrorState"), false);
        setVisible(getElement("accessDenied"), false);
        setVisible(getElement("adminContent"), true);
    }

    function createInfoRow(iconClass, value, last = false) {
        const row = createElement("p", `text-muted mb-${last ? "0" : "1"}`);
        const icon = createElement("i", `fas ${iconClass} me-2 text-primary`);
        row.append(icon, document.createTextNode(displayValue(value, "Not set")));
        return row;
    }

    function createActionButton(label, iconClass, className, handler) {
        const button = createElement("button", className);
        button.type = "button";
        button.append(
            createElement("i", `fas ${iconClass} me-1`),
            document.createTextNode(label)
        );
        button.addEventListener("click", handler);
        return button;
    }

    function createEmptyState() {
        const wrapper = createElement("div", "col-12 text-center py-5");
        wrapper.append(
            createElement("i", "fas fa-users fa-3x text-muted mb-3"),
            createElement("p", "text-muted", "No staff members found.")
        );
        return wrapper;
    }

    function renderStaffGrid(staffList) {
        const grid = getElement("staffGrid");
        grid.replaceChildren();

        if (!Array.isArray(staffList) || staffList.length === 0) {
            grid.append(createEmptyState());
            return;
        }

        staffList.forEach((staff) => {
            const staffId = displayValue(staff && staff.Staff_ID, "");
            const fullName = displayValue(staff && staff.Full_Name, "Unnamed staff");
            const email = displayValue(staff && staff.Email);
            const phone = displayValue(staff && staff.Phone_Number, "Not set");
            const role = displayValue(staff && staff.Role, "Staff");

            const column = createElement("div", "col-xl-4 col-lg-6 col-md-6");
            const card = createElement("div", "staff-card p-4");
            const heading = createElement("div", "d-flex align-items-center mb-3");
            const avatar = createElement("div", "staff-avatar me-3");
            const nameBlock = createElement("div", "flex-grow-1");
            const name = createElement("h5", "text-white mb-1", fullName);
            const roleBadge = createElement("span", "badge role-badge");
            const roleClass = roleClasses[role];

            if (roleClass) {
                roleBadge.classList.add(roleClass);
            }

            roleBadge.textContent = role;
            avatar.textContent = Array.from(fullName.trim())[0]?.toUpperCase() || "?";
            nameBlock.append(name, roleBadge);
            heading.append(avatar, nameBlock);

            const information = createElement("div", "mb-3");
            information.append(
                createInfoRow("fa-id-badge", staffId),
                createInfoRow("fa-envelope", email),
                createInfoRow("fa-phone", phone, true)
            );

            const actions = createElement("div", "d-flex gap-2");
            actions.append(
                createActionButton(
                    "View",
                    "fa-eye",
                    "btn btn-outline-light btn-sm flex-grow-1",
                    () => openStaffDetails(staffId)
                ),
                createActionButton(
                    "Edit",
                    "fa-edit",
                    "btn btn-outline-primary btn-sm flex-grow-1",
                    () => openEditModal(staffId)
                )
            );

            const deleteButton = createActionButton(
                "Delete",
                "fa-trash",
                "btn btn-outline-danger btn-sm",
                () => openDeleteModal(staffId, fullName)
            );

            if (staffId === "STF001") {
                deleteButton.disabled = true;
                deleteButton.title = "Cannot delete the primary admin account";
                deleteButton.classList.remove("btn-outline-danger");
                deleteButton.classList.add("btn-outline-secondary");
            }

            actions.append(deleteButton);
            card.append(heading, information, actions);
            column.append(card);
            grid.append(column);
        });
    }

    async function loadStaffList() {
        if (state.listLoading) {
            return;
        }

        state.listLoading = true;
        const requestId = ++state.listRequestId;
        state.listController?.abort();
        state.listController = new AbortController();
        showStaffLoading();

        try {
            const response = await apiFetch("staff/list.php", {
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
                showStaffAccessDenied(responseMessage(response, data, "This page is only accessible to Administrators."));
                return;
            }

            if (!response.ok || !data || data.success !== true) {
                showStaffError(responseMessage(response, data, "Unable to load the staff list. Please retry."));
                return;
            }

            const staffList = Array.isArray(data.data?.staff) ? data.data.staff : [];
            renderStaffGrid(staffList);
            getElement("staffCount").textContent = `${staffList.length} Staff Members`;
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

    function resetDetailsState() {
        const detailsState = getElement("staffDetailsState");
        detailsState.className = "alert alert-info";
        detailsState.textContent = "Loading staff details...";
        setVisible(detailsState, true);
        setVisible(getElement("staffDetailsList"), false);
        getElement("staffDetailsEditBtn").disabled = true;
    }

    function renderStaffDetails(staff) {
        getElement("detailsStaffId").textContent = displayValue(staff.Staff_ID);
        getElement("detailsFullName").textContent = displayValue(staff.Full_Name);
        getElement("detailsEmail").textContent = displayValue(staff.Email);
        getElement("detailsPhone").textContent = displayValue(staff.Phone_Number, "Not set");
        getElement("detailsRole").textContent = displayValue(staff.Role);
        setVisible(getElement("staffDetailsState"), false);
        setVisible(getElement("staffDetailsList"), true);
        getElement("staffDetailsEditBtn").disabled = false;
    }

    async function loadStaffDetails(staffId) {
        const requestId = ++state.detailsRequestId;
        state.detailsController?.abort();
        state.detailsController = new AbortController();
        resetDetailsState();

        try {
            const response = await apiFetch(`staff/get.php?id=${encodeURIComponent(staffId)}`, {
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
                const detailsState = getElement("staffDetailsState");
                detailsState.className = "alert alert-danger";
                detailsState.textContent = responseMessage(response, data, "Unable to load staff details.");
                setVisible(detailsState, true);
                return;
            }

            renderStaffDetails(data.data);
        } catch (error) {
            if (error && error.name === "AbortError") {
                return;
            }

            const detailsState = getElement("staffDetailsState");
            detailsState.className = "alert alert-danger";
            detailsState.textContent = "Unable to reach the staff service.";
            setVisible(detailsState, true);
        } finally {
            if (requestId === state.detailsRequestId) {
                state.detailsController = null;
            }
        }
    }

    function showModal(id) {
        const modalElement = getElement(id);

        if (modalElement && typeof bootstrap !== "undefined") {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    }

    function hideModal(id) {
        const modalElement = getElement(id);

        if (modalElement && typeof bootstrap !== "undefined") {
            bootstrap.Modal.getOrCreateInstance(modalElement).hide();
        }
    }

    function openStaffDetails(staffId) {
        state.detailsStaffId = staffId;
        resetDetailsState();
        showModal("staffDetailsModal");
        loadStaffDetails(staffId);
    }

    function setEditFormDisabled(disabled) {
        const form = getElement("editStaffForm");
        form.querySelectorAll("input, select, button").forEach((control) => {
            control.disabled = Boolean(disabled);
        });
    }

    function resetEditForm() {
        getElement("editStaffId").value = "";
        getElement("editFullName").value = "";
        getElement("editEmail").value = "";
        getElement("editPhone").value = "";
        getElement("editRole").value = "Staff";
        getElement("editPassword").value = "";
        hideInlineMessage(getElement("editMessage"));
    }

    async function openEditModal(staffId) {
        if (state.editLoading || state.editSubmitting) {
            return;
        }

        state.editLoading = true;
        resetEditForm();
        getElement("editStaffId").value = staffId;
        showInlineMessage(getElement("editMessage"), "Loading staff details...", "info");
        setEditFormDisabled(true);
        showModal("editStaffModal");

        try {
            const response = await apiFetch(`staff/get.php?id=${encodeURIComponent(staffId)}`);
            const data = await readApiResponse(response);

            if (handleUnauthorized(response)) {
                return;
            }

            if (!response.ok || !data || data.success !== true || !data.data) {
                showInlineMessage(
                    getElement("editMessage"),
                    responseMessage(response, data, "Unable to load staff details."),
                    "danger"
                );
                return;
            }

            const staff = data.data;
            getElement("editStaffId").value = displayValue(staff.Staff_ID, staffId);
            getElement("editFullName").value = displayValue(staff.Full_Name, "");
            getElement("editEmail").value = displayValue(staff.Email, "");
            getElement("editPhone").value = staff.Phone_Number ? String(staff.Phone_Number) : "";
            getElement("editRole").value = roleClasses[staff.Role] ? staff.Role : "Staff";
            getElement("editPassword").value = "";
            hideInlineMessage(getElement("editMessage"));
        } catch (error) {
            showInlineMessage(getElement("editMessage"), "Unable to reach the staff service.", "danger");
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
        const saveButton = getElement("saveEditBtn");
        const staffId = getElement("editStaffId").value.trim();
        const body = {
            staff_id: staffId,
            full_name: getElement("editFullName").value.trim(),
            email: getElement("editEmail").value.trim(),
            phone_number: getElement("editPhone").value.trim(),
            role: getElement("editRole").value
        };
        const password = getElement("editPassword").value;

        if (password !== "") {
            body.password = password;
        }

        state.editSubmitting = true;
        setEditFormDisabled(true);
        setButtonLabel(saveButton, "Saving...", true);

        try {
            const response = await apiFetch("staff/update.php", {
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
                    getElement("editMessage"),
                    responseMessage(response, data, "Unable to update the staff member."),
                    response.status === 409 ? "warning" : "danger"
                );
                return;
            }

            hideModal("editStaffModal");
            showToast("Staff member updated successfully.", "success");
            loadStaffList();
        } catch (error) {
            showInlineMessage(getElement("editMessage"), "Unable to reach the staff service.", "danger");
        } finally {
            state.editSubmitting = false;
            setEditFormDisabled(false);
            setButtonLabel(saveButton, "Save Changes", false);
            form.querySelectorAll("input, select").forEach((control) => {
                control.disabled = false;
            });
        }
    }

    function openDeleteModal(staffId, staffName) {
        getElement("deleteStaffId").value = staffId;
        getElement("deleteStaffName").textContent = displayValue(staffName, "this staff member");
        hideInlineMessage(getElement("deleteMessage"));
        setButtonLabel(getElement("confirmDeleteBtn"), "Delete", false);
        showModal("deleteStaffModal");
    }

    async function handleDelete() {
        if (state.deleteSubmitting) {
            return;
        }

        const deleteButton = getElement("confirmDeleteBtn");
        const staffId = getElement("deleteStaffId").value.trim();

        state.deleteSubmitting = true;
        setButtonLabel(deleteButton, "Deleting...", true);

        try {
            const response = await apiFetch("staff/delete.php", {
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
                    getElement("deleteMessage"),
                    responseMessage(response, data, "Unable to delete the staff member."),
                    response.status === 409 ? "warning" : "danger"
                );
                return;
            }

            hideModal("deleteStaffModal");
            showToast("Staff member deleted successfully.", "success");
            loadStaffList();
        } catch (error) {
            showInlineMessage(getElement("deleteMessage"), "Unable to reach the staff service.", "danger");
        } finally {
            state.deleteSubmitting = false;
            setButtonLabel(deleteButton, "Delete", false);
        }
    }

    function initializeStaffPage() {
        const editForm = getElement("editStaffForm");
        const retryButton = getElement("staffRetryBtn");
        const detailsEditButton = getElement("staffDetailsEditBtn");
        const deleteButton = getElement("confirmDeleteBtn");

        if (editForm) {
            editForm.addEventListener("submit", handleEditSubmit);
        }

        if (retryButton) {
            retryButton.addEventListener("click", loadStaffList);
        }

        if (detailsEditButton) {
            detailsEditButton.addEventListener("click", () => {
                const staffId = state.detailsStaffId;
                hideModal("staffDetailsModal");

                if (staffId) {
                    openEditModal(staffId);
                }
            });
        }

        if (deleteButton) {
            deleteButton.addEventListener("click", handleDelete);
        }

        loadStaffList();
    }

    document.addEventListener("DOMContentLoaded", initializeStaffPage);
}());
