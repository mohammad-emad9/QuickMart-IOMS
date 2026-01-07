/**
 * QuickMart IOMS - Staff Management
 * Admin-only access to view, edit, and delete staff
 */

document.addEventListener("DOMContentLoaded", function () {
    checkAdminAccess();
});

// ===========================================
// Check Admin Access
// ===========================================
async function checkAdminAccess() {
    const userRole = sessionStorage.getItem('userRole');
    const userName = sessionStorage.getItem('userName');

    // Update username display
    if (userName) {
        document.getElementById('userName').textContent = userName;
    }

    // Hide loading spinner
    document.getElementById('loadingSpinner').classList.add('d-none');

    // Check if user is admin
    if (userRole !== 'Admin') {
        document.getElementById('accessDenied').classList.remove('d-none');
        return;
    }

    // Show admin content
    document.getElementById('adminContent').classList.remove('d-none');

    // Load staff list
    loadStaffList();
}

// ===========================================
// Load Staff List
// ===========================================
async function loadStaffList() {
    try {
        const response = await fetch('/QuickMart code/backend/api/staff/list.php');
        const data = await response.json();

        if (data.success) {
            renderStaffGrid(data.data.staff);
            document.getElementById('staffCount').textContent = `${data.data.total} Staff Members`;
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        console.error('Error loading staff:', error);
        showToast('Failed to load staff list', 'error');
    }
}

// ===========================================
// Render Staff Grid
// ===========================================
function renderStaffGrid(staffList) {
    const grid = document.getElementById('staffGrid');

    if (staffList.length === 0) {
        grid.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <p class="text-muted">No staff members found</p>
            </div>
        `;
        return;
    }

    grid.innerHTML = staffList.map(staff => `
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="staff-card p-4">
                <div class="d-flex align-items-center mb-3">
                    <div class="staff-avatar me-3">
                        ${staff.Full_Name.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="text-white mb-1">${staff.Full_Name}</h5>
                        <span class="badge role-badge role-${staff.Role}">${staff.Role}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <p class="text-muted mb-1">
                        <i class="fas fa-id-badge me-2 text-primary"></i>${staff.Staff_ID}
                    </p>
                    <p class="text-muted mb-1">
                        <i class="fas fa-envelope me-2 text-primary"></i>${staff.Email}
                    </p>
                    <p class="text-muted mb-0">
                        <i class="fas fa-phone me-2 text-primary"></i>${staff.Phone_Number || 'Not set'}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm flex-grow-1" onclick="openEditModal('${staff.Staff_ID}', '${staff.Full_Name}', '${staff.Email}', '${staff.Phone_Number || ''}', '${staff.Role}')">
                        <i class="fas fa-edit me-1"></i>Edit
                    </button>
                    ${staff.Staff_ID !== 'STF001' ? `
                        <button class="btn btn-outline-danger btn-sm" onclick="openDeleteModal('${staff.Staff_ID}', '${staff.Full_Name}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    ` : `
                        <button class="btn btn-outline-secondary btn-sm" disabled title="Cannot delete primary admin">
                            <i class="fas fa-lock"></i>
                        </button>
                    `}
                </div>
            </div>
        </div>
    `).join('');
}

// ===========================================
// Edit Modal Functions
// ===========================================
function openEditModal(staffId, fullName, email, phone, role) {
    document.getElementById('editStaffId').value = staffId;
    document.getElementById('editFullName').value = fullName;
    document.getElementById('editEmail').value = email;
    document.getElementById('editPhone').value = phone;
    document.getElementById('editRole').value = role;
    document.getElementById('editPassword').value = '';
    document.getElementById('editMessage').classList.add('d-none');

    new bootstrap.Modal(document.getElementById('editStaffModal')).show();
}

document.getElementById('editStaffForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const staffId = document.getElementById('editStaffId').value;
    const fullName = document.getElementById('editFullName').value;
    const email = document.getElementById('editEmail').value;
    const phone = document.getElementById('editPhone').value;
    const role = document.getElementById('editRole').value;
    const password = document.getElementById('editPassword').value;
    const messageBox = document.getElementById('editMessage');
    const saveBtn = document.getElementById('saveEditBtn');

    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    saveBtn.disabled = true;

    try {
        const body = {
            staff_id: staffId,
            full_name: fullName,
            email: email,
            phone_number: phone,
            role: role
        };

        if (password) {
            body.password = password;
        }

        const response = await fetch('/QuickMart code/backend/api/staff/update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        });

        const data = await response.json();

        if (data.success) {
            messageBox.className = 'alert alert-success';
            messageBox.textContent = data.message;
            messageBox.classList.remove('d-none');

            setTimeout(() => {
                bootstrap.Modal.getInstance(document.getElementById('editStaffModal')).hide();
                loadStaffList();
            }, 1500);
        } else {
            messageBox.className = 'alert alert-danger';
            messageBox.textContent = data.message;
            messageBox.classList.remove('d-none');
        }
    } catch (error) {
        messageBox.className = 'alert alert-danger';
        messageBox.textContent = 'An error occurred';
        messageBox.classList.remove('d-none');
    } finally {
        saveBtn.innerHTML = '<i class="fas fa-save me-2"></i>Save Changes';
        saveBtn.disabled = false;
    }
});

// ===========================================
// Delete Modal Functions
// ===========================================
function openDeleteModal(staffId, staffName) {
    document.getElementById('deleteStaffId').value = staffId;
    document.getElementById('deleteStaffName').textContent = staffName;

    new bootstrap.Modal(document.getElementById('deleteStaffModal')).show();
}

document.getElementById('confirmDeleteBtn').addEventListener('click', async function () {
    const staffId = document.getElementById('deleteStaffId').value;
    const deleteBtn = this;

    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
    deleteBtn.disabled = true;

    try {
        const response = await fetch('/QuickMart code/backend/api/staff/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ staff_id: staffId })
        });

        const data = await response.json();

        bootstrap.Modal.getInstance(document.getElementById('deleteStaffModal')).hide();

        if (data.success) {
            showToast('Staff deleted successfully', 'success');
            loadStaffList();
        } else {
            showToast(data.message, 'error');
        }
    } catch (error) {
        showToast('An error occurred', 'error');
    } finally {
        deleteBtn.innerHTML = '<i class="fas fa-trash me-2"></i>Delete';
        deleteBtn.disabled = false;
    }
});

// End of file
