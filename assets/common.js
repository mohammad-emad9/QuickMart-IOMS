/**
 * Shared frontend utilities and navigation helpers.
 */

// All pages that load this file are two directory levels below the project
// root. Relative URLs therefore work under any configured subdirectory.
function appPath(path) {
    const cleanPath = String(path).replace(/^\/+/, '');
    return `../../${cleanPath}`;
}

function apiPath(path) {
    return appPath(`backend/api/${String(path).replace(/^\/+/, '')}`);
}

function loginPath() {
    return appPath('assets/login-signup/login.html');
}

function viewPath(path) {
    return appPath(`backend/views/${String(path).replace(/^\/+/, '')}`);
}

// Frontend role visibility is only a usability aid. Every API still enforces
// the same policy server-side.
function applyRoleBasedUi(userRole) {
    const isAdmin = userRole === 'Admin';

    document.querySelectorAll('[data-admin-only], .admin-only-action').forEach(element => {
        element.hidden = !isAdmin;
        element.setAttribute('aria-hidden', String(!isAdmin));

        element.querySelectorAll('button, input, select, textarea').forEach(control => {
            control.disabled = !isAdmin;
        });
    });
}

let csrfToken = null;
let sessionProbePromise = null;
let serverSessionUser = null;
let sessionProbeState = 'idle';

function getServerSessionUser() {
    return serverSessionUser ? { ...serverSessionUser } : null;
}

function getServerSessionRole() {
    return serverSessionUser?.role || '';
}

function getSessionProbeState() {
    return sessionProbeState;
}

async function getCsrfToken() {
    if (csrfToken) {
        return csrfToken;
    }

    const response = await fetch(apiPath('auth/csrf.php'), {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
    });
    const data = await response.json();

    if (!response.ok || !data.success || !data.data || typeof data.data.csrf_token !== 'string') {
        throw new Error('Unable to establish a secure request token.');
    }

    csrfToken = data.data.csrf_token;
    return csrfToken;
}

async function apiFetch(path, options = {}) {
    const { skipCsrf = false, ...requestOptions } = options;
    const method = String(requestOptions.method || 'GET').toUpperCase();
    const headers = new Headers(requestOptions.headers || {});

    if (!skipCsrf && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
        headers.set('X-CSRF-Token', await getCsrfToken());
    }

    requestOptions.headers = headers;
    requestOptions.credentials = 'same-origin';
    return fetch(apiPath(path), requestOptions);
}

function clearSessionHint() {
    ["isLoggedIn", "staffId", "userName", "userEmail", "userRole"].forEach((key) => {
        sessionStorage.removeItem(key);
    });
}

/**
 * Revalidate the server-side session. sessionStorage is updated only as a
 * UI hint after the protected probe succeeds; it is never an auth decision.
 * @returns {Promise<boolean>} True only after server revalidation succeeds.
 */
async function checkSession(options = {}) {
    const shouldRedirect = options.redirect !== false;

    if (!sessionProbePromise) {
        serverSessionUser = null;
        sessionProbeState = 'loading';
        applyRoleBasedUi(null);
        loadUserInfo();

        sessionProbePromise = fetch(apiPath("auth/session.php"), {
            method: "GET",
            credentials: "same-origin",
            cache: "no-store",
            headers: { Accept: "application/json" }
        }).then(async (response) => {
            let data = null;
            try {
                data = await response.json();
            } catch (error) {
                data = null;
            }

            const user = data?.data?.user;
            const validUser = data?.success === true
                && data?.data?.authenticated === true
                && user
                && typeof user.staff_id === "string" && user.staff_id.trim() !== ""
                && typeof user.full_name === "string" && user.full_name.trim() !== ""
                && typeof user.email === "string" && user.email.trim() !== ""
                && ['Admin', 'Manager', 'Staff'].includes(user.role);

            if (response.ok && validUser) {
                serverSessionUser = Object.freeze({
                    staff_id: user.staff_id,
                    full_name: user.full_name,
                    email: user.email,
                    role: user.role
                });
                sessionStorage.setItem("isLoggedIn", "true");
                sessionStorage.setItem("staffId", serverSessionUser.staff_id);
                sessionStorage.setItem("userName", serverSessionUser.full_name);
                sessionStorage.setItem("userEmail", serverSessionUser.email);
                sessionStorage.setItem("userRole", serverSessionUser.role);
                sessionProbeState = 'authenticated';
                applyRoleBasedUi(user.role);
                loadUserInfo();
                return true;
            }

            if (response.status === 401) {
                sessionProbeState = 'unauthorized';
                clearSessionHint();
                if (shouldRedirect) {
                    window.location.assign(loginPath());
                }
            } else {
                sessionProbeState = 'unavailable';
            }

            return false;
        }).catch(() => {
            sessionProbeState = 'unavailable';
            return false;
        }).finally(() => {
            sessionProbePromise = null;
        });
    }

    return sessionProbePromise;
}

/**
 * Load the latest server-validated user information and update the navbar.
 * Cached sessionStorage values are intentionally ignored here.
 */
function loadUserInfo() {
    const user = getServerSessionUser();
    const userName = user?.full_name || 'Checking session…';
    const userRole = user?.role || 'Verifying account';

    const userNameElement = document.getElementById("userName");
    const welcomeMessage = document.getElementById("welcomeMessage");

    if (userNameElement) {
        userNameElement.textContent = userName.charAt(0).toUpperCase() + userName.slice(1);
    }

    const userRoleElement = document.getElementById("userRoleLabel");
    if (userRoleElement) {
        userRoleElement.textContent = userRole;
    }

    if (welcomeMessage) {
        welcomeMessage.textContent = user
            ? `Welcome back, ${userName}! Here's what's happening today.`
            : 'Verifying your workspace session…';
    }

    const manageStaffAction = document.getElementById("manageStaffAction");
    if (manageStaffAction) {
        manageStaffAction.style.display = user?.role === 'Admin' ? '' : 'none';
    }

    applyRoleBasedUi(user?.role || null);
}

/**
 * Clear session and redirect to login.
 */
async function performLogout() {
    try {
        await apiFetch('auth/logout.php', { method: 'POST' });
    } catch (error) {
        // Client cleanup and redirect still happen if the server is unavailable.
    } finally {
        sessionStorage.clear();
        window.location.href = loginPath();
    }
}

/**
 * Show logout confirmation modal or native confirmation.
 * @param {Event} [e] - Click event
 */
function handleLogout(e) {
    if (e) e.preventDefault();

    const logoutModal = document.getElementById("logoutModal");
    if (logoutModal && typeof bootstrap !== 'undefined') {
        const modal = new bootstrap.Modal(logoutModal);
        modal.show();
    } else {
        if (confirm("Are you sure you want to logout?")) {
            performLogout();
        }
    }
}

/**
 * Set up logout button and confirmation listeners.
 */
function setupLogoutHandler() {
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", handleLogout);
    }

    const confirmLogoutBtn = document.getElementById("confirmLogoutBtn");
    if (confirmLogoutBtn) {
        confirmLogoutBtn.addEventListener("click", performLogout);
    }
}

const sharedIconPaths = Object.freeze({
    success: [
        'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z',
        'm8 12 2.5 2.5L16 9'
    ],
    error: [
        'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z',
        'm9 9 6 6',
        'm15 9-6 6'
    ],
    info: [
        'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z',
        'M12 10v6',
        'M12 7.5h.01'
    ]
});

function createSharedIcon(name) {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '1.8');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');

    const paths = sharedIconPaths[name] || sharedIconPaths.info;
    paths.forEach((pathData) => {
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', pathData);
        svg.appendChild(path);
    });

    return svg;
}

/**
 * Show a toast notification
 * @param {string} message - Message to display
 * @param {string} type - Type: 'success', 'error', or 'info'
 */
function showToast(message, type = "success") {
    const toastContainer = document.querySelector(".toast-container");
    if (!toastContainer) {
        return;
    }

    const toastId = `toast-${Date.now()}`;

    const toastEl = document.createElement('div');
    toastEl.id = toastId;
    toastEl.className = `toast toast-${type}`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');

    const toastBody = document.createElement('div');
    toastBody.className = 'toast-body d-flex align-items-center py-3 px-4';
    toastBody.appendChild(createSharedIcon(type));

    const messageElement = document.createElement('span');
    messageElement.className = 'mx-2';
    messageElement.textContent = String(message ?? '');
    toastBody.appendChild(messageElement);

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'btn-close btn-close-white ms-auto';
    closeButton.setAttribute('aria-label', 'Dismiss notification');
    closeButton.setAttribute('data-bs-dismiss', 'toast');
    toastBody.appendChild(closeButton);

    toastEl.appendChild(toastBody);
    toastContainer.appendChild(toastEl);

    if (typeof bootstrap === 'undefined' || !bootstrap.Toast) {
        return;
    }

    const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
    toast.show();

    toastEl.addEventListener("hidden.bs.toast", () => toastEl.remove());
}

function getCurrentViewKey() {
    const fileName = window.location.pathname.split('/').pop().toLowerCase();

    if (fileName === 'create-order.php' || fileName === 'order-details.php') return 'orders';
    if (fileName === 'dashboard.php' || fileName === '') return 'dashboard';
    if (fileName === 'products.php') return 'products';
    if (fileName === 'orders.php') return 'orders';
    if (fileName === 'reports.php') return 'reports';
    if (fileName === 'staff.php') return 'staff';
    if (fileName === 'profile.php') return 'profile';
    return '';
}

function setActiveNavigation() {
    const currentViewKey = getCurrentViewKey();

    document.querySelectorAll('.qm-nav-link[data-nav-key]').forEach((link) => {
        const isActive = link.dataset.navKey === currentViewKey;
        link.classList.toggle('active', isActive);

        if (isActive) {
            link.setAttribute('aria-current', 'page');
        } else {
            link.removeAttribute('aria-current');
        }
    });
}

function closeMobileNavigation() {
    const drawer = document.getElementById('navbarNav');
    if (!drawer) return;

    if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
        bootstrap.Collapse.getOrCreateInstance(drawer).hide();
    } else {
        drawer.classList.remove('show');
        document.body.classList.remove('qm-drawer-open');
    }
}

function setupShellNavigation() {
    const drawer = document.getElementById('navbarNav');
    const toggle = document.querySelector('.qm-nav-toggle');
    if (!drawer) return;

    setActiveNavigation();

    drawer.addEventListener('shown.bs.collapse', () => {
        document.body.classList.add('qm-drawer-open');
    });

    drawer.addEventListener('hidden.bs.collapse', () => {
        document.body.classList.remove('qm-drawer-open');
    });

    if (toggle && (typeof bootstrap === 'undefined' || !bootstrap.Collapse)) {
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            drawer.classList.toggle('show');
            document.body.classList.toggle('qm-drawer-open', drawer.classList.contains('show'));
            toggle.setAttribute('aria-expanded', String(drawer.classList.contains('show')));
        });
    }

    drawer.querySelectorAll('.qm-nav-link, .dropdown-item').forEach((link) => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 991) closeMobileNavigation();
        });
    });

    document.addEventListener('click', (event) => {
        if (window.innerWidth > 991 || !document.body.classList.contains('qm-drawer-open')) return;
        if (drawer.contains(event.target) || toggle?.contains(event.target)) return;
        closeMobileNavigation();
    });

    window.addEventListener('resize', () => {
        if (window.innerWidth > 991) {
            document.body.classList.remove('qm-drawer-open');
        }
    });
}

function setupShellContextbar() {
    const main = document.querySelector('.main-content');
    if (!main || main.querySelector('[data-shell-contextbar]')) return;

    const labels = {
        dashboard: 'Dashboard',
        products: 'Products',
        orders: 'Orders',
        reports: 'Reports',
        staff: 'Staff',
        profile: 'Profile'
    };
    const viewKey = getCurrentViewKey();
    const viewLabel = labels[viewKey] || document.title.replace(/^QuickMart IOMS\s*-\s*/i, '') || 'Workspace';

    const contextbar = document.createElement('header');
    contextbar.className = 'qm-shell-contextbar no-print';
    contextbar.dataset.shellContextbar = 'true';

    const breadcrumbNav = document.createElement('nav');
    breadcrumbNav.setAttribute('aria-label', 'Breadcrumb');

    const breadcrumbs = document.createElement('ol');
    breadcrumbs.className = 'qm-shell-breadcrumbs';

    const workspaceItem = document.createElement('li');
    workspaceItem.textContent = 'Operations';
    breadcrumbs.appendChild(workspaceItem);

    const pageItem = document.createElement('li');
    pageItem.setAttribute('aria-current', 'page');
    pageItem.textContent = viewLabel;
    breadcrumbs.appendChild(pageItem);

    breadcrumbNav.appendChild(breadcrumbs);
    contextbar.appendChild(breadcrumbNav);

    const contextNote = document.createElement('span');
    contextNote.className = 'qm-shell-context-note';
    const liveDot = document.createElement('span');
    liveDot.className = 'qm-live-dot';
    liveDot.setAttribute('aria-hidden', 'true');
    contextNote.append(liveDot, document.createTextNode('Operations workspace'));
    contextbar.appendChild(contextNote);

    main.insertBefore(contextbar, main.firstChild);
}

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

document.addEventListener("DOMContentLoaded", function () {
    setupShellContextbar();
    setupShellNavigation();
    const isLoginPage = Boolean(document.getElementById("loginForm"));
    if (!isLoginPage) {
        applyRoleBasedUi(null);
        checkSession();
    }
    setupLogoutHandler();
});
