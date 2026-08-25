/**
 * Products catalog management and stock updates.
 */

let products = [];
let categories = [];
let editingProductId = null;
let deleteProductId = null;
let deleteProductIds = [];
let productsLoadInFlight = false;
let productMutationInFlight = false;
let deleteMutationInFlight = false;
let productsLoadState = 'loading';
let selectedProducts = new Set();
let lastModalTrigger = null;

const PRODUCT_ICON_PATHS = Object.freeze({
    package: ['M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z', 'm4.5 7.5 7.5 4 7.5-4', 'M12 12v8.5'],
    storage: ['M4 8h16v12H4z', 'M7 8V5h10v3', 'M8 12h8', 'M8 16h5'],
    chip: ['M7 7h10v10H7z', 'M9 3v4M15 3v4M9 17v4M15 17v4M3 9h4M3 15h4M17 9h4M17 15h4'],
    memory: ['M5 5h14v14H5z', 'M8 8h8v8H8z', 'M8 2v3M12 2v3M16 2v3M8 19v3M12 19v3M16 19v3'],
    bolt: ['m13 2-9 11h7l-1 9 9-11h-7l1-9Z'],
    fan: ['M12 12m-2.4 0a2.4 2.4 0 1 0 4.8 0 2.4 2.4 0 1 0-4.8 0', 'M12 9.6C8 3 4 5 7 9', 'M14.4 12c7-4 8-0.5 3 3', 'M12 14.4c4 7 0.5 8-3 3'],
    plug: ['M8 7v5a4 4 0 0 0 8 0V7', 'M10 3v4M14 3v4M12 16v5'],
    tag: ['M4 5v6l9 9 6-6-9-9H4Z', 'M8 8h.01'],
    check: ['m5 12 4 4L19 6'],
    alert: ['M12 3 21 20H3L12 3Z', 'M12 9v5', 'M12 17h.01'],
    close: ['m6 6 12 12', 'M18 6 6 18'],
    edit: ['M4 20h4L19 9l-4-4L4 16v4Z', 'm13.5 6.5 4 4'],
    trash: ['M5 7h14', 'M10 11v6M14 11v6', 'M7 7l1 13h8l1-13', 'M9 7V4h6v3'],
    refresh: ['M20 11a8 8 0 1 0 1 4', 'M20 5v6h-6'],
    shield: ['M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z', 'M12 10v5', 'M12 7.5h.01'],
    info: ['M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z', 'M12 10v6', 'M12 7.5h.01'],
    spinner: ['M12 3a9 9 0 1 0 9 9']
});

document.addEventListener('DOMContentLoaded', async function () {
    initializePage();
    setupEventListeners();

    const authenticated = await checkSession();
    if (!authenticated) {
        if (getSessionProbeState() === 'unavailable') {
            setUnavailableSummary();
            setTableStateLabel('Session unavailable');
        }
        return;
    }

    setAccessNotice();
    loadProductsFromDatabase();
});

function createSvgIcon(name, className = '') {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '1.8');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');
    if (className) svg.setAttribute('class', className);

    const pathData = PRODUCT_ICON_PATHS[name] || PRODUCT_ICON_PATHS.info;
    pathData.forEach(function (data) {
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', data);
        svg.appendChild(path);
    });

    return svg;
}

function getUserRole() {
    return typeof getServerSessionRole === 'function' ? getServerSessionRole() : '';
}

function canManageProducts() {
    return getUserRole() === 'Admin';
}

function notify(message, type = 'success') {
    if (typeof showToast === 'function') {
        showToast(message, type);
    }
}

function getLoginHref() {
    return typeof loginPath === 'function' ? loginPath() : '../../assets/login-signup/login.html';
}

function getDashboardHref() {
    return typeof viewPath === 'function' ? viewPath('dashboard.php') : 'dashboard.php';
}

function setText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = String(value ?? '');
    return element;
}

function formatNumber(value) {
    const number = Number(value);
    return Number.isFinite(number) ? number.toLocaleString('en-US') : '—';
}

function formatMoney(value) {
    const number = Number(value);
    return Number.isFinite(number) ? `$${number.toFixed(2)}` : '—';
}

function normalizeProduct(product) {
    const quantity = Number(product?.Quantity);
    const price = Number(product?.Price);
    const threshold = Number(product?.Threshold);

    return {
        id: String(product?.Product_ID ?? ''),
        name: String(product?.Name ?? ''),
        category: String(product?.Category ?? ''),
        quantity: Number.isFinite(quantity) ? quantity : 0,
        price: Number.isFinite(price) ? price : 0,
        status: typeof product?.Status === 'string' ? product.Status : '',
        threshold: product?.Threshold === null || product?.Threshold === undefined || product?.Threshold === ''
            ? 20
            : (Number.isFinite(threshold) ? threshold : 20)
    };
}

function createApiError(message, status) {
    const error = new Error(message);
    error.status = Number(status) || 0;
    return error;
}

async function parseApiResponse(response) {
    let data;

    try {
        data = await response.json();
    } catch (error) {
        throw createApiError('The server returned an invalid response.', response?.status);
    }

    if (!response.ok || !data || data.success !== true) {
        throw createApiError(
            data && data.message ? data.message : `Request failed (${response.status}).`,
            response.status
        );
    }

    return data;
}

function initializePage() {
    if (typeof loadUserInfo === 'function') loadUserInfo();
    setAccessNotice();
    setLoadingSummary();
    setTableStateLabel('Loading inventory');
}

function setupEventListeners() {
    const debounceFunction = typeof debounce === 'function' ? debounce : productsDebounce;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.addEventListener('input', debounceFunction(filterProducts, 250));

    const categoryFilter = document.getElementById('categoryFilter');
    if (categoryFilter) categoryFilter.addEventListener('change', filterProducts);

    const stockFilter = document.getElementById('stockFilter');
    if (stockFilter) stockFilter.addEventListener('change', filterProducts);

    const sortFilter = document.getElementById('sortFilter');
    if (sortFilter) sortFilter.addEventListener('change', filterProducts);

    const addProductButton = document.getElementById('addProductBtn');
    if (addProductButton) addProductButton.addEventListener('click', openAddModal);

    const productForm = document.getElementById('productForm');
    if (productForm) productForm.addEventListener('submit', handleProductSubmit);

    const confirmDeleteButton = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteButton) confirmDeleteButton.addEventListener('click', confirmDelete);

    const exportButton = document.getElementById('exportBtn');
    if (exportButton) exportButton.addEventListener('click', exportToCSV);

    const deleteSelectedButton = document.getElementById('deleteSelectedBtn');
    if (deleteSelectedButton) deleteSelectedButton.addEventListener('click', deleteSelectedProducts);

    const selectAllCheckbox = document.getElementById('selectAllProducts');
    if (selectAllCheckbox) selectAllCheckbox.addEventListener('change', toggleSelectAll);

    setupModalAccessibility('productModal', 'productName');
    setupModalAccessibility('deleteModal', 'confirmDeleteBtn');
}

function setupModalAccessibility(modalId, focusTargetId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) return;

    modalElement.addEventListener('shown.bs.modal', function () {
        const focusTarget = document.getElementById(focusTargetId);
        if (focusTarget && !focusTarget.disabled) focusTarget.focus();
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        modalElement.removeAttribute('aria-busy');
        const trigger = lastModalTrigger;
        lastModalTrigger = null;
        if (trigger && document.contains(trigger)) {
            window.setTimeout(() => trigger.focus(), 0);
        }
    });
}

function openManagedModal(modalId, focusTargetId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) return;

    lastModalTrigger = document.activeElement instanceof HTMLElement ? document.activeElement : null;
    modalElement.dataset.focusTarget = focusTargetId;

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalElement).show();
        return;
    }

    modalElement.classList.add('show');
    modalElement.style.display = 'block';
    modalElement.removeAttribute('aria-hidden');
    modalElement.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
    const focusTarget = document.getElementById(focusTargetId);
    if (focusTarget) focusTarget.focus();
}

function closeManagedModal(modalId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) return;

    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const instance = bootstrap.Modal.getInstance(modalElement);
        if (instance) instance.hide();
        return;
    }

    modalElement.classList.remove('show');
    modalElement.style.display = 'none';
    modalElement.setAttribute('aria-hidden', 'true');
    modalElement.removeAttribute('aria-modal');
    document.body.classList.remove('modal-open');
    modalElement.dispatchEvent(new Event('hidden.bs.modal'));
}

function setAccessNotice() {
    const notice = document.getElementById('accessNotice');
    const noticeText = document.getElementById('accessNoticeText');
    if (!notice || !noticeText) return;

    const role = getUserRole();
    const roleCopy = {
        Manager: 'Manager access is read-only for product records. You can review inventory and export the register.',
        Staff: 'Staff access is read-only for product records. You can review inventory and export the register.'
    };

    const isAdmin = role === 'Admin';
    notice.hidden = isAdmin;
    noticeText.textContent = roleCopy[role] || 'Your current role can review inventory but cannot change product records.';
}

function setLoadingSummary() {
    setText('productCount', 'Loading...');
    setText('summaryProductCount', '—');
    setText('inventoryUnitCount', '—');
    setText('lowStockCount', '—');
    setText('outOfStockCount', '—');
    setText('showingInfo', 'Loading products...');
}

function setUnavailableSummary() {
    setText('productCount', 'Unavailable');
    setText('summaryProductCount', '—');
    setText('inventoryUnitCount', '—');
    setText('lowStockCount', '—');
    setText('outOfStockCount', '—');
    setText('showingInfo', 'Inventory unavailable');
}

function updateProductSummary() {
    const totalUnits = products.reduce((sum, product) => sum + product.quantity, 0);
    const lowStock = products.filter(product => product.status === 'Low Stock').length;
    const outOfStock = products.filter(product => product.status === 'Out of Stock').length;

    setText('productCount', `${products.length} Total Products`);
    setText('summaryProductCount', formatNumber(products.length));
    setText('inventoryUnitCount', formatNumber(totalUnits));
    setText('lowStockCount', formatNumber(lowStock));
    setText('outOfStockCount', formatNumber(outOfStock));
}

function updateProductCount(count) {
    setText('productCount', `${products.length} Total Products`);
    setText('showingInfo', `Showing ${count} of ${products.length} products`);
}

function setTableStateLabel(label) {
    setText('tableStateLabel', label);
}

function renderTableMessage(title, message, options = {}) {
    const tableBody = document.getElementById('productsTableBody');
    if (!tableBody) return;

    const state = options.state || 'empty';
    const row = document.createElement('tr');
    row.className = `products-state-row products-state-row-${state}`;

    const cell = document.createElement('td');
    cell.className = 'products-state-cell';
    cell.colSpan = 8;

    const stateContainer = document.createElement('div');
    stateContainer.className = `products-state products-state-${state}`;
    stateContainer.setAttribute('role', state === 'loading' ? 'status' : 'group');
    if (state === 'loading') stateContainer.setAttribute('aria-live', 'polite');

    const iconWrap = document.createElement('span');
    iconWrap.className = 'products-state-icon';
    iconWrap.appendChild(createSvgIcon(options.icon || getStateIcon(state)));

    const heading = document.createElement('h3');
    heading.textContent = title;

    const detail = document.createElement('p');
    detail.textContent = message;

    stateContainer.append(iconWrap, heading, detail);

    if (options.action) {
        const action = createStateAction(options.action);
        if (action) stateContainer.appendChild(action);
    }

    cell.appendChild(stateContainer);
    row.appendChild(cell);
    tableBody.replaceChildren(row);
    setText('showingInfo', state === 'loading' ? 'Loading products...' : 'No products to show');
    setupCheckboxListeners();
}

function getStateIcon(state) {
    if (state === 'loading') return 'spinner';
    if (state === 'empty') return 'package';
    if (state === 'unauthorized' || state === 'stale-session') return 'shield';
    return 'alert';
}

function createStateAction(action) {
    if (!action || !action.type) return null;

    if (action.type === 'login') {
        const link = document.createElement('a');
        link.className = 'btn btn-outline-primary products-state-action';
        link.href = getLoginHref();
        link.textContent = action.label || 'Sign in again';
        return link;
    }

    if (action.type === 'dashboard') {
        const link = document.createElement('a');
        link.className = 'btn btn-outline-primary products-state-action';
        link.href = getDashboardHref();
        link.textContent = action.label || 'Return to dashboard';
        return link;
    }

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'btn btn-outline-primary products-state-action';
    button.textContent = action.label || 'Retry';

    if (action.type === 'retry') {
        button.id = 'retryProductsBtn';
        button.addEventListener('click', loadProductsFromDatabase);
    } else if (action.type === 'clear-filters') {
        button.id = 'clearProductFiltersBtn';
        button.addEventListener('click', clearFilters);
    }

    return button;
}

async function loadProductsFromDatabase() {
    if (productsLoadInFlight) return;

    productsLoadInFlight = true;
    productsLoadState = 'loading';
    setLoadingSummary();
    setTableStateLabel('Loading inventory');
    renderTableMessage('Loading products', 'Connecting to the inventory register…', {
        state: 'loading',
        icon: 'spinner'
    });

    try {
        const response = await apiFetch('products/list.php');
        const data = await parseApiResponse(response);
        const payload = data.data || {};
        const productRows = Array.isArray(payload.products) ? payload.products : [];

        products = productRows.map(normalizeProduct);
        populateCategoryFilter(payload.categories);
        productsLoadState = 'success';
        updateProductSummary();
        filterProducts();
        setTableStateLabel(products.length === 0 ? 'No records' : 'Register ready');
    } catch (error) {
        products = [];
        selectedProducts.clear();
        productsLoadState = getErrorState(error);
        setUnavailableSummary();

        if (productsLoadState === 'stale-session') {
            setTableStateLabel('Session expired');
            renderTableMessage('Session expired', 'Sign in again to refresh this inventory register.', {
                state: 'stale-session',
                action: { type: 'login', label: 'Sign in again' }
            });
        } else if (productsLoadState === 'unauthorized') {
            setTableStateLabel('Access restricted');
            renderTableMessage('Access not authorized', 'Your account is not authorized to view this inventory register.', {
                state: 'unauthorized',
                action: { type: 'dashboard', label: 'Return to dashboard' }
            });
        } else {
            setTableStateLabel('Unable to load');
            renderTableMessage('Could not load products', error.message || 'Check the connection and try again.', {
                state: 'error',
                action: { type: 'retry', label: 'Retry loading' }
            });
        }

        if (error.message) notify(error.message, 'error');
    } finally {
        productsLoadInFlight = false;
    }
}

function getErrorState(error) {
    if (Number(error?.status) === 401) return 'stale-session';
    if (Number(error?.status) === 403) return 'unauthorized';
    return 'error';
}

function collectCategories(apiCategories) {
    const source = Array.isArray(apiCategories) && apiCategories.length > 0
        ? apiCategories
        : products.map(product => product.category);
    const seen = new Set();
    const result = [];

    source.forEach(function (category) {
        const value = String(category ?? '').trim();
        if (!value || seen.has(value)) return;
        seen.add(value);
        result.push(value);
    });

    return result;
}

function appendOption(select, value, label) {
    const option = document.createElement('option');
    option.value = value;
    option.textContent = label;
    select.appendChild(option);
}

function populateCategoryFilter(apiCategories) {
    categories = collectCategories(apiCategories);

    const categoryFilter = document.getElementById('categoryFilter');
    const productCategory = document.getElementById('productCategory');
    const selectedFilter = categoryFilter?.value || 'All';
    const selectedProductCategory = productCategory?.value || '';

    if (categoryFilter) {
        categoryFilter.replaceChildren();
        appendOption(categoryFilter, 'All', 'All categories');
        categories.forEach(category => appendOption(categoryFilter, category, category));
        categoryFilter.value = categories.includes(selectedFilter) ? selectedFilter : 'All';
    }

    if (productCategory) {
        productCategory.replaceChildren();
        appendOption(productCategory, '', 'Select category');
        categories.forEach(category => appendOption(productCategory, category, category));
        productCategory.value = categories.includes(selectedProductCategory) ? selectedProductCategory : '';
    }
}

function ensureProductCategoryOption(category) {
    const productCategory = document.getElementById('productCategory');
    const value = String(category ?? '');
    if (!productCategory || !value) return;

    const hasOption = Array.from(productCategory.options).some(option => option.value === value);
    if (!hasOption) appendOption(productCategory, value, value);
}

function hasActiveFilters() {
    return Boolean(
        document.getElementById('searchInput')?.value.trim() ||
        (document.getElementById('categoryFilter')?.value || 'All') !== 'All' ||
        (document.getElementById('stockFilter')?.value || 'All') !== 'All'
    );
}

function clearFilters() {
    const searchInput = document.getElementById('searchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const stockFilter = document.getElementById('stockFilter');
    const sortFilter = document.getElementById('sortFilter');

    if (searchInput) searchInput.value = '';
    if (categoryFilter) categoryFilter.value = 'All';
    if (stockFilter) stockFilter.value = 'All';
    if (sortFilter) sortFilter.value = 'name';
    filterProducts();
}

function filterProducts() {
    if (productsLoadState !== 'success') return;

    const searchTerm = (document.getElementById('searchInput')?.value || '').trim().toLowerCase();
    const categoryValue = document.getElementById('categoryFilter')?.value || 'All';
    const stockValue = document.getElementById('stockFilter')?.value || 'All';
    const sortValue = document.getElementById('sortFilter')?.value || 'name';

    const filtered = sortProducts(products.filter(function (product) {
        const matchesSearch = product.name.toLowerCase().includes(searchTerm) ||
            product.id.toLowerCase().includes(searchTerm);
        const matchesCategory = categoryValue === 'All' || product.category === categoryValue;
        let matchesStock = true;

        if (stockValue === 'low') {
            matchesStock = product.status === 'Low Stock' || product.status === 'Out of Stock';
        } else if (stockValue === 'normal') {
            matchesStock = product.status === 'Normal';
        } else if (stockValue === 'out-of-stock') {
            matchesStock = product.status === 'Out of Stock';
        }

        return matchesSearch && matchesCategory && matchesStock;
    }), sortValue);

    renderProducts(filtered);
    updateProductCount(filtered.length);
    setTableStateLabel(filtered.length === 0 ? 'No matching records' : `${filtered.length} records shown`);
}

function sortProducts(productList, sortBy) {
    return [...productList].sort(function (a, b) {
        switch (sortBy) {
            case 'name':
                return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
            case 'name-desc':
                return b.name.localeCompare(a.name, undefined, { sensitivity: 'base' });
            case 'price-asc':
                return a.price - b.price;
            case 'price-desc':
                return b.price - a.price;
            case 'quantity-asc':
                return a.quantity - b.quantity;
            case 'quantity-desc':
                return b.quantity - a.quantity;
            default:
                return 0;
        }
    });
}

function appendLabeledCell(row, className, label, content) {
    const cell = document.createElement('td');
    cell.className = className;
    cell.dataset.label = label;
    if (content instanceof Node) {
        cell.appendChild(content);
    } else {
        cell.textContent = String(content ?? '');
    }
    row.appendChild(cell);
    return cell;
}

function createProductSelectionCell(product) {
    const cell = document.createElement('td');
    cell.className = 'products-selection-cell';
    cell.dataset.label = 'Select';

    if (!canManageProducts()) {
        cell.hidden = true;
        return cell;
    }

    const label = document.createElement('label');
    label.className = 'checkbox-hit-area';
    label.setAttribute('aria-label', `Select ${product.name}`);

    const checkbox = document.createElement('input');
    checkbox.type = 'checkbox';
    checkbox.className = 'form-check-input product-checkbox';
    checkbox.dataset.id = product.id;
    checkbox.setAttribute('aria-label', `Select ${product.name}`);

    label.appendChild(checkbox);
    cell.appendChild(label);
    return cell;
}

function createCategoryBadge(category) {
    const badge = document.createElement('span');
    badge.className = 'category-badge';
    badge.title = category;
    badge.append(createSvgIcon(getCategoryIcon(category)), document.createTextNode(category || 'Uncategorized'));
    return badge;
}

function createStatusBadge(status) {
    const label = String(status || 'Status unavailable');
    const presentation = {
        'Normal': ['badge-normal', 'check'],
        'Low Stock': ['badge-low', 'alert'],
        'Out of Stock': ['badge-out-of-stock', 'close']
    }[label] || ['badge-unknown', 'info'];

    const badge = document.createElement('span');
    badge.className = `status-badge ${presentation[0]}`;
    badge.setAttribute('role', 'status');
    badge.append(createSvgIcon(presentation[1]), document.createTextNode(label));
    return badge;
}

function getCategoryIcon(category) {
    const iconByCategory = {
        GPU: 'chip',
        CPU: 'chip',
        RAM: 'memory',
        Storage: 'storage',
        Power: 'bolt',
        Cooling: 'fan',
        Cables: 'plug'
    };
    return iconByCategory[category] || 'tag';
}

function getQuantityClass(status) {
    if (status === 'Out of Stock') return 'quantity-out';
    if (status === 'Low Stock') return 'quantity-low';
    return 'quantity-normal';
}

function createActionButton(type, product) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `action-btn action-btn-${type}`;
    button.setAttribute('aria-label', `${type === 'edit' ? 'Edit' : 'Delete'} ${product.name}`);
    button.title = type === 'edit' ? 'Edit product' : 'Delete product';
    button.appendChild(createSvgIcon(type === 'edit' ? 'edit' : 'trash'));
    button.addEventListener('click', function () {
        if (type === 'edit') editProduct(product.id);
        else deleteProduct(product.id);
    });
    return button;
}

function renderProducts(productList) {
    const tableBody = document.getElementById('productsTableBody');
    if (!tableBody) return;

    if (productList.length === 0) {
        const filtered = hasActiveFilters();
        renderTableMessage(
            filtered ? 'No matching products' : 'Inventory is empty',
            filtered ? 'Try clearing a filter or searching for a different product.' : 'Products created through the inventory workflow will appear here.',
            {
                state: 'empty',
                action: filtered ? { type: 'clear-filters', label: 'Clear filters' } : null
            }
        );
        return;
    }

    const rows = document.createDocumentFragment();

    productList.forEach(function (product) {
        const row = document.createElement('tr');
        row.className = 'products-row';
        row.dataset.id = product.id;
        row.appendChild(createProductSelectionCell(product));

        const idText = document.createElement('span');
        idText.className = 'product-id identifier-value';
        idText.textContent = product.id;
        appendLabeledCell(row, 'products-cell-id', 'ID', idText);

        const nameText = document.createElement('span');
        nameText.className = 'product-name';
        nameText.textContent = product.name;
        appendLabeledCell(row, 'products-cell-name', 'Product name', nameText);

        appendLabeledCell(row, 'products-cell-category', 'Category', createCategoryBadge(product.category));

        const quantityText = document.createElement('span');
        quantityText.className = `quantity-display ${getQuantityClass(product.status)}`;
        quantityText.textContent = String(product.quantity);
        appendLabeledCell(row, 'products-cell-quantity', 'Quantity', quantityText);

        const priceText = document.createElement('span');
        priceText.className = 'price-display money-value';
        priceText.textContent = formatMoney(product.price);
        appendLabeledCell(row, 'products-cell-price', 'Price', priceText);

        appendLabeledCell(row, 'products-cell-status', 'Status', createStatusBadge(product.status));

        const actionCell = document.createElement('td');
        actionCell.className = 'products-cell-actions';
        actionCell.dataset.label = 'Actions';
        actionCell.hidden = !canManageProducts();
        if (canManageProducts()) {
            const actions = document.createElement('div');
            actions.className = 'action-buttons';
            actions.append(createActionButton('edit', product), createActionButton('delete', product));
            actionCell.appendChild(actions);
        }
        row.appendChild(actionCell);
        rows.appendChild(row);
    });

    tableBody.replaceChildren(rows);
    setupCheckboxListeners();
}

function setupCheckboxListeners() {
    selectedProducts.clear();
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }

    document.querySelectorAll('.product-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const productId = this.dataset.id;
            const row = this.closest('tr');
            if (this.checked) selectedProducts.add(productId);
            else selectedProducts.delete(productId);
            if (row) row.classList.toggle('selected-row', this.checked);
            updateDeleteSelectedButton();
            updateSelectAllCheckbox();
        });
    });

    updateDeleteSelectedButton();
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    if (!selectAllCheckbox || !canManageProducts()) return;

    document.querySelectorAll('.product-checkbox').forEach(function (checkbox) {
        checkbox.checked = selectAllCheckbox.checked;
        const row = checkbox.closest('tr');
        if (selectAllCheckbox.checked) selectedProducts.add(checkbox.dataset.id);
        else selectedProducts.delete(checkbox.dataset.id);
        if (row) row.classList.toggle('selected-row', selectAllCheckbox.checked);
    });

    updateDeleteSelectedButton();
}

function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    if (!selectAllCheckbox) return;

    const checkboxes = Array.from(document.querySelectorAll('.product-checkbox'));
    const checkedCount = checkboxes.filter(checkbox => checkbox.checked).length;
    selectAllCheckbox.checked = checkboxes.length > 0 && checkedCount === checkboxes.length;
    selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
}

function setButtonContent(button, iconName, label) {
    if (!button) return;
    button.replaceChildren(createSvgIcon(iconName), document.createElement('span'));
    const textElement = button.querySelector('span');
    if (textElement) textElement.textContent = label;
}

function updateDeleteSelectedButton() {
    const deleteSelectedButton = document.getElementById('deleteSelectedBtn');
    if (!deleteSelectedButton) return;

    const count = selectedProducts.size;
    deleteSelectedButton.disabled = count === 0 || !canManageProducts();
    setButtonContent(deleteSelectedButton, 'trash', count > 0 ? `Delete selected (${count})` : 'Delete selected');
}

function setModalTitle(modalTitle, iconName, label) {
    if (!modalTitle) return;
    modalTitle.replaceChildren(createSvgIcon(iconName, 'products-modal-title-icon'), document.createTextNode(label));
}

function clearFormError() {
    const formError = document.getElementById('productFormError');
    if (formError) {
        formError.hidden = true;
        formError.textContent = '';
    }
}

function setFormError(message) {
    const formError = document.getElementById('productFormError');
    if (!formError) return;
    formError.hidden = false;
    formError.textContent = message;
}

function clearDeleteError() {
    const errorElement = document.getElementById('deleteModalError');
    if (errorElement) {
        errorElement.hidden = true;
        errorElement.textContent = '';
    }
}

function setDeleteError(message) {
    const errorElement = document.getElementById('deleteModalError');
    if (!errorElement) return;
    errorElement.hidden = false;
    errorElement.textContent = message;
}

function openAddModal() {
    if (!canManageProducts()) {
        notify('Your role has read-only access to products.', 'info');
        return;
    }

    editingProductId = null;
    const form = document.getElementById('productForm');
    if (form) {
        form.reset();
        form.classList.remove('was-validated');
        form.removeAttribute('aria-busy');
    }
    const productId = document.getElementById('productId');
    if (productId) productId.value = '';
    populateCategoryFilter(categories);
    clearFormError();
    setModalTitle(document.getElementById('modalTitle'), 'package', 'Add product');
    openManagedModal('productModal', 'productName');
}

function editProduct(productId) {
    if (!canManageProducts()) {
        notify('Your role has read-only access to products.', 'info');
        return;
    }

    const product = products.find(item => item.id === productId);
    if (!product) {
        notify('Product not found.', 'error');
        return;
    }

    editingProductId = product.id;
    ensureProductCategoryOption(product.category);
    const productIdInput = document.getElementById('productId');
    const nameInput = document.getElementById('productName');
    const categoryInput = document.getElementById('productCategory');
    const priceInput = document.getElementById('productPrice');
    const quantityInput = document.getElementById('productQuantity');
    const thresholdInput = document.getElementById('lowStockThreshold');

    if (productIdInput) productIdInput.value = product.id;
    if (nameInput) nameInput.value = product.name;
    if (categoryInput) categoryInput.value = product.category;
    if (priceInput) priceInput.value = String(product.price);
    if (quantityInput) quantityInput.value = String(product.quantity);
    if (thresholdInput) thresholdInput.value = String(product.threshold);

    const form = document.getElementById('productForm');
    if (form) {
        form.classList.remove('was-validated');
        form.removeAttribute('aria-busy');
    }
    clearFormError();
    setModalTitle(document.getElementById('modalTitle'), 'edit', 'Edit product');
    openManagedModal('productModal', 'productName');
}

function getProductFormData() {
    const name = document.getElementById('productName')?.value.trim() || '';
    const category = document.getElementById('productCategory')?.value || '';
    const price = Number(document.getElementById('productPrice')?.value);
    const quantity = Number(document.getElementById('productQuantity')?.value);
    const thresholdValue = document.getElementById('lowStockThreshold')?.value.trim() || '';
    const threshold = thresholdValue === '' ? 20 : Number(thresholdValue);

    return { name, category, price, quantity, threshold };
}

function validateProductFormData(formData) {
    if (!formData.name) return 'Enter a product name.';
    if (!formData.category) return 'Select a product category.';
    if (!Number.isFinite(formData.price) || formData.price < 0) return 'Enter a valid non-negative price.';
    if (!Number.isInteger(formData.quantity) || formData.quantity < 0) return 'Enter a valid whole-number quantity.';
    if (!Number.isInteger(formData.threshold) || formData.threshold < 0) return 'Enter a valid whole-number threshold.';
    return '';
}

async function handleProductSubmit(event) {
    event.preventDefault();
    if (!canManageProducts() || productMutationInFlight) return;

    const form = document.getElementById('productForm');
    if (!form) return;
    form.classList.add('was-validated');

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = getProductFormData();
    const validationMessage = validateProductFormData(formData);
    if (validationMessage) {
        setFormError(validationMessage);
        return;
    }

    productMutationInFlight = true;
    form.setAttribute('aria-busy', 'true');
    const saveButton = document.getElementById('saveProductBtn');
    if (saveButton) {
        saveButton.disabled = true;
        setButtonContent(saveButton, 'spinner', 'Saving...');
    }
    clearFormError();

    const isEditing = Boolean(editingProductId);

    try {
        let response;
        if (isEditing) {
            response = await apiFetch('products/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ ...formData, product_id: editingProductId })
            });
        } else {
            response = await apiFetch('products/create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        }

        const data = await parseApiResponse(response);
        notify(data.message || (isEditing ? 'Product updated successfully.' : 'Product created successfully.'), 'success');
        closeManagedModal('productModal');
        await loadProductsFromDatabase();
    } catch (error) {
        setFormError(error.message || 'Unable to save this product. Try again.');
        notify(error.message || 'Unable to save this product. Try again.', 'error');
    } finally {
        productMutationInFlight = false;
        form.removeAttribute('aria-busy');
        if (saveButton) {
            saveButton.disabled = false;
            setButtonContent(saveButton, isEditing ? 'edit' : 'package', isEditing ? 'Save changes' : 'Save product');
        }
    }
}

function openDeleteModal(productIds, title, description, nameLabel) {
    if (!canManageProducts() || !Array.isArray(productIds) || productIds.length === 0) return;

    deleteProductIds = productIds.filter(Boolean);
    deleteProductId = deleteProductIds.length === 1 ? deleteProductIds[0] : null;
    clearDeleteError();
    setText('deleteProductName', nameLabel);
    setText('deleteModalDescription', description);
    setModalTitle(document.getElementById('deleteModalTitle'), 'alert', title);

    const confirmButton = document.getElementById('confirmDeleteBtn');
    if (confirmButton) {
        confirmButton.disabled = false;
        setButtonContent(confirmButton, 'trash', deleteProductIds.length > 1 ? 'Delete selected' : 'Delete record');
    }

    openManagedModal('deleteModal', 'confirmDeleteBtn');
}

function deleteProduct(productId) {
    if (!canManageProducts()) {
        notify('Your role has read-only access to products.', 'info');
        return;
    }

    const product = products.find(item => item.id === productId);
    if (!product) {
        notify('Product not found.', 'error');
        return;
    }

    openDeleteModal(
        [product.id],
        'Delete product?',
        'This removes the selected record from the catalog. Confirm only if you are sure.',
        product.name
    );
}

function deleteSelectedProducts() {
    if (!canManageProducts() || deleteMutationInFlight) return;

    const productIds = Array.from(selectedProducts);
    if (productIds.length === 0) return;

    openDeleteModal(
        productIds,
        'Delete selected products?',
        'This will remove every selected record from the catalog. Confirm only if you are sure.',
        `${productIds.length} selected products`
    );
}

async function confirmDelete() {
    if (!canManageProducts() || deleteMutationInFlight || deleteProductIds.length === 0) return;

    deleteMutationInFlight = true;
    const deleteCount = deleteProductIds.length;
    const confirmButton = document.getElementById('confirmDeleteBtn');
    const modalElement = document.getElementById('deleteModal');
    if (modalElement) modalElement.setAttribute('aria-busy', 'true');
    if (confirmButton) {
        confirmButton.disabled = true;
        setButtonContent(confirmButton, 'spinner', 'Deleting...');
    }
    clearDeleteError();

    let successCount = 0;
    const failureMessages = [];

    try {
        for (const productId of deleteProductIds) {
            try {
                const response = await apiFetch('products/delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId })
                });
                await parseApiResponse(response);
                successCount += 1;
            } catch (error) {
                failureMessages.push(error.message || 'A product could not be deleted.');
            }
        }

        if (successCount === 0) {
            const message = failureMessages[0] || 'No products were deleted.';
            setDeleteError(message);
            notify(message, 'error');
            return;
        }

        const failedCount = deleteProductIds.length - successCount;
        const resultMessage = failedCount > 0
            ? `Deleted ${successCount} product(s); ${failedCount} could not be deleted.`
            : `Deleted ${successCount} product(s) successfully.`;
        notify(resultMessage, failedCount > 0 ? 'info' : 'success');
        closeManagedModal('deleteModal');
        await loadProductsFromDatabase();
    } finally {
        deleteMutationInFlight = false;
        deleteProductId = null;
        deleteProductIds = [];
        selectedProducts.clear();
        const modalElementAfter = document.getElementById('deleteModal');
        if (modalElementAfter) modalElementAfter.removeAttribute('aria-busy');
        if (confirmButton) {
            confirmButton.disabled = false;
            setButtonContent(confirmButton, 'trash', deleteCount > 1 ? 'Delete selected' : 'Delete record');
        }
        updateDeleteSelectedButton();
    }
}

function escapeCsvValue(value) {
    const stringValue = String(value ?? '');
    return /[",\r\n]/.test(stringValue)
        ? `"${stringValue.replace(/"/g, '""')}"`
        : stringValue;
}

function exportToCSV() {
    const headers = ['ID', 'Product Name', 'Category', 'Quantity', 'Price', 'Status'];
    const rows = products.map(function (product) {
        return [
            product.id,
            product.name,
            product.category,
            product.quantity,
            formatMoney(product.price),
            product.status || 'Status unavailable'
        ];
    });

    const csvContent = [headers, ...rows]
        .map(row => row.map(escapeCsvValue).join(','))
        .join('\r\n') + '\r\n';
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const objectUrl = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = objectUrl;
    link.download = `QuickMart_Products_${new Date().toISOString().split('T')[0]}.csv`;
    link.hidden = true;
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.setTimeout(() => URL.revokeObjectURL(objectUrl), 0);
    notify('Products exported to CSV.', 'success');
}

function productsDebounce(func, wait) {
    let timeout;
    return function (...args) {
        window.clearTimeout(timeout);
        timeout = window.setTimeout(() => func(...args), wait);
    };
}
