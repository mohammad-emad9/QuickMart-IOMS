/**
 * QuickMart IOMS - Create Order Page
 * Uses backend products, order creation, and recent-order APIs.
 */

let availableProducts = [];
let orderItems = [];
let selectedProducts = {};
let recentOrdersCache = [];
let recentOrderFilter = 'all';
let productsLoadInFlight = null;
let recentOrdersLoadInFlight = null;
let recentOrdersRequestVersion = 0;
let recentOrdersReloadQueued = false;
let createOrderInFlight = false;
let rowCounter = 0;

document.addEventListener('DOMContentLoaded', function () {
    loadUserInfo();
    setupCreateOrderEvents();
    updateOrderTypeLabel();
    loadProductsFromDatabase();
    requestRecentOrdersReload();
});

function setupCreateOrderEvents() {
    document.querySelectorAll('input[name="orderType"]').forEach(radio => {
        radio.addEventListener('change', function () {
            updateOrderTypeLabel();
            renderOrderTable();
            updateSelectedPreview();
            renderModalProducts(availableProducts);
        });
    });

    document.getElementById('productSearch')?.addEventListener('input', debounce(handleProductSearch, 250));
    document.getElementById('productSearch')?.addEventListener('focus', handleProductSearch);
    document.getElementById('addItemBtn')?.addEventListener('click', () => openModal('selectProductModal'));
    document.getElementById('selectProductModal')?.addEventListener('show.bs.modal', loadModalProducts);
    document.getElementById('modalProductSearch')?.addEventListener('input', debounce(filterModalProducts, 250));
    document.getElementById('confirmAddProductsBtn')?.addEventListener('click', addSelectedProducts);
    document.getElementById('createOrderBtn')?.addEventListener('click', showInvoicePreview);
    document.getElementById('confirmOrderBtn')?.addEventListener('click', processFinalOrder);

    document.getElementById('filterAllBtn')?.addEventListener('click', () => filterRecentOrders('all'));
    document.getElementById('filterSellBtn')?.addEventListener('click', () => filterRecentOrders('Sell'));
    document.getElementById('filterPurchaseBtn')?.addEventListener('click', () => filterRecentOrders('Purchase'));

    document.addEventListener('click', function (event) {
        const suggestions = document.getElementById('productSuggestions');
        const searchInput = document.getElementById('productSearch');
        if (suggestions && !suggestions.contains(event.target) && event.target !== searchInput) {
            suggestions.classList.remove('show');
        }
    });
}

function openModal(id) {
    const element = document.getElementById(id);
    if (element && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(element).show();
    }
}

async function readOrderApiResponse(response) {
    let data = null;
    try {
        data = await response.json();
    } catch (error) {
        throw createOrderApiError(response.status, 'The server returned an invalid response.');
    }
    if (!response.ok || !data || data.success !== true) {
        throw createOrderApiError(response.status, data && data.message);
    }
    return data;
}

function createOrderApiError(status, serverMessage) {
    const error = new Error(typeof serverMessage === 'string' ? serverMessage : 'Order request failed.');
    error.status = Number(status) || 0;
    error.serverMessage = typeof serverMessage === 'string' ? serverMessage : '';
    return error;
}

function getOrderApiErrorMessage(error, fallback) {
    if (error && error.status === 401) return 'Your session has expired. Please sign in again.';
    if (error && error.status === 403) return 'You are not allowed to perform this order action.';
    if (error && error.status === 409) return 'The order conflicted with another update. Please retry.';
    if (error && error.status === 422) return error.serverMessage || 'Please review the order fields and try again.';
    if (error && error.status >= 500) return 'The order service is temporarily unavailable. Please retry.';
    if (error && error.message === 'Failed to fetch') return 'The order service could not be reached. Please retry.';
    return error && error.message ? error.message : fallback;
}

function loadProductsFromDatabase() {
    if (productsLoadInFlight) return productsLoadInFlight;
    productsLoadInFlight = (async function () {
        try {
            const response = await apiFetch('products/list.php');
            const data = await readOrderApiResponse(response);
            const products = data.data && Array.isArray(data.data.products) ? data.data.products : [];
            availableProducts = products.map(normalizeProduct).filter(product => product.id);
            renderModalProducts(availableProducts);
            return availableProducts;
        } catch (error) {
            availableProducts = [];
            renderModalProducts([]);
            showToast(getOrderApiErrorMessage(error, 'Unable to load products.'), 'error');
            return [];
        } finally {
            productsLoadInFlight = null;
        }
    })();
    return productsLoadInFlight;
}

function normalizeProduct(product) {
    return {
        id: textValue(product && product.Product_ID, ''),
        name: textValue(product && product.Name, 'Unnamed product'),
        category: textValue(product && product.Category, 'General'),
        available: Math.max(0, Number.parseInt(product && product.Quantity, 10) || 0),
        price: safeNumber(product && product.Price)
    };
}

function requestRecentOrdersReload() {
    recentOrdersRequestVersion += 1;
    if (recentOrdersLoadInFlight) {
        recentOrdersReloadQueued = true;
        return recentOrdersLoadInFlight;
    }
    return loadRecentOrders(recentOrdersRequestVersion);
}

async function loadRecentOrders(requestVersion) {
    if (recentOrdersLoadInFlight) {
        recentOrdersReloadQueued = true;
        return recentOrdersLoadInFlight;
    }

    setRecentOrdersState('Loading orders...', 'muted');
    const request = (async function () {
        try {
            const response = await apiFetch('orders/list.php');
            const data = await readOrderApiResponse(response);
            if (requestVersion !== recentOrdersRequestVersion) return;
            recentOrdersCache = data.data && Array.isArray(data.data.orders) ? data.data.orders : [];
            renderRecentOrders(getVisibleRecentOrders());
        } catch (error) {
            if (requestVersion !== recentOrdersRequestVersion) return;
            setRecentOrdersState(getOrderApiErrorMessage(error, 'Unable to load recent orders.'), 'danger', true);
        }
    })();
    recentOrdersLoadInFlight = request;

    try {
        await request;
    } finally {
        if (recentOrdersLoadInFlight === request) {
            recentOrdersLoadInFlight = null;
            if (recentOrdersReloadQueued) {
                recentOrdersReloadQueued = false;
                loadRecentOrders(recentOrdersRequestVersion);
            }
        }
    }
}

function filterRecentOrders(filter) {
    recentOrderFilter = filter;
    document.querySelectorAll('#filterAllBtn, #filterSellBtn, #filterPurchaseBtn').forEach(button => {
        button.classList.remove('active');
    });
    const activeId = filter === 'Sell' ? 'filterSellBtn' : filter === 'Purchase' ? 'filterPurchaseBtn' : 'filterAllBtn';
    document.getElementById(activeId)?.classList.add('active');
    renderRecentOrders(getVisibleRecentOrders());
}

function getVisibleRecentOrders() {
    if (recentOrderFilter === 'all') return recentOrdersCache;
    return recentOrdersCache.filter(order => order.Order_Type === recentOrderFilter);
}

function renderRecentOrders(orders) {
    const body = document.getElementById('recentOrdersTable');
    if (!body) return;
    if (!orders.length) {
        setRecentOrdersState(recentOrdersCache.length ? 'No orders match this filter.' : 'No orders have been created yet.', 'muted');
        return;
    }

    const fragment = document.createDocumentFragment();
    orders.forEach(order => fragment.appendChild(createRecentOrderRow(order)));
    body.replaceChildren(fragment);
}

function createRecentOrderRow(order) {
    const row = document.createElement('tr');
    appendCell(row, order.Order_ID || '-', 'fw-bold text-primary');
    appendCell(row, order.Staff_Name || order.Staff_ID || '-', 'text-muted');
    appendCell(row, order.Party_Name || '-');
    appendCell(row, order.Item_Count ?? 0, 'text-center');
    appendCell(row, `$${formatCurrency(order.Total_Amount)}`, 'fw-bold');

    const typeCell = document.createElement('td');
    const typeBadge = document.createElement('span');
    typeBadge.className = `badge ${order.Order_Type === 'Sell' ? 'bg-success' : 'bg-info'}`;
    typeBadge.textContent = textValue(order.Order_Type, '-');
    typeCell.appendChild(typeBadge);
    row.appendChild(typeCell);
    appendCell(row, formatOrderDate(order.Order_Date), 'text-muted');

    const actionCell = document.createElement('td');
    const link = document.createElement('a');
    link.className = 'btn btn-sm btn-outline-primary';
    link.href = `${viewPath('order-details.php')}?id=${encodeURIComponent(textValue(order.Order_ID, ''))}`;
    link.setAttribute('aria-label', `View order ${textValue(order.Order_ID, '')}`);
    const icon = document.createElement('i');
    icon.className = 'fas fa-eye';
    link.appendChild(icon);
    actionCell.appendChild(link);
    row.appendChild(actionCell);
    return row;
}

function setRecentOrdersState(message, color, retry) {
    const body = document.getElementById('recentOrdersTable');
    if (!body) return;
    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 8;
    cell.className = `text-center py-4 text-${color || 'muted'}`;
    cell.textContent = message;
    if (retry) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-outline-primary d-block mx-auto mt-3';
        button.textContent = 'Retry';
        button.addEventListener('click', requestRecentOrdersReload);
        cell.appendChild(button);
    }
    row.appendChild(cell);
    body.replaceChildren(row);
}

function updateOrderTypeLabel() {
    const isSellOrder = document.getElementById('sellOrder')?.checked;
    setText('customerLabel', isSellOrder ? 'Customer' : 'Supplier');
}

function handleProductSearch() {
    const input = document.getElementById('productSearch');
    const suggestions = document.getElementById('productSuggestions');
    if (!input || !suggestions) return;
    const term = input.value.trim().toLowerCase();
    if (!term) {
        suggestions.classList.remove('show');
        suggestions.replaceChildren();
        return;
    }
    const products = availableProducts.filter(product =>
        product.name.toLowerCase().includes(term) || product.category.toLowerCase().includes(term)
    );
    renderProductSuggestions(products, term);
    suggestions.classList.add('show');
}

function renderProductSuggestions(products, term) {
    const suggestions = document.getElementById('productSuggestions');
    if (!suggestions) return;
    suggestions.replaceChildren();
    if (!products.length) {
        const empty = document.createElement('div');
        empty.className = 'suggestion-item text-muted';
        empty.textContent = `No products found matching "${term}"`;
        suggestions.appendChild(empty);
        return;
    }

    const fragment = document.createDocumentFragment();
    products.forEach(product => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'suggestion-item w-100 text-start border-0';
        item.addEventListener('click', () => addProductToOrder(product.id));
        const details = document.createElement('span');
        const name = document.createElement('span');
        name.className = 'product-name d-block';
        name.textContent = product.name;
        const meta = document.createElement('span');
        meta.className = 'product-meta';
        meta.textContent = `${product.category} • ${product.available} available`;
        details.append(name, meta);
        const price = document.createElement('span');
        price.className = 'product-price';
        price.textContent = `$${formatCurrency(product.price)}`;
        item.append(details, price);
        fragment.appendChild(item);
    });
    suggestions.appendChild(fragment);
}

function addProductToOrder(productId) {
    const product = availableProducts.find(item => item.id === productId);
    if (!product) return;
    if (isSellOrder() && product.available < 1) {
        showToast('This product is out of stock for a Sell order.', 'error');
        return;
    }

    const existing = orderItems.find(item => item.id === productId);
    if (existing) {
        existing.quantity = clampQuantity(existing.quantity + 1, existing.available, isSellOrder());
    } else {
        rowCounter += 1;
        orderItems.push({ ...product, rowId: rowCounter, quantity: 1 });
    }
    renderOrderTable();
    const input = document.getElementById('productSearch');
    if (input) input.value = '';
    document.getElementById('productSuggestions')?.classList.remove('show');
}

function renderOrderTable() {
    const body = document.getElementById('orderTableBody');
    const empty = document.getElementById('emptyMessage');
    if (!body) return;
    if (!orderItems.length) {
        body.replaceChildren();
        if (empty) empty.hidden = false;
        calculateEstimatedTotal();
        return;
    }
    if (empty) empty.hidden = true;

    const fragment = document.createDocumentFragment();
    orderItems.forEach(item => {
        const row = document.createElement('tr');
        row.id = `row-${item.rowId}`;
        appendCell(row, item.name, 'product-cell');
        appendCell(row, `${item.available} units`, 'available-cell');
        appendCell(row, `$${formatCurrency(item.price)}`, 'price-cell');

        const quantityCell = document.createElement('td');
        const input = document.createElement('input');
        input.type = 'number';
        input.className = 'form-control form-control-sm qty-input';
        input.min = '1';
        if (isSellOrder()) input.max = String(item.available);
        input.value = String(item.quantity);
        input.addEventListener('change', event => updateQuantity(item.rowId, event.target.value));
        quantityCell.appendChild(input);
        row.appendChild(quantityCell);

        appendCell(row, `$${formatCurrency(item.price * item.quantity)}`, 'total-cell');
        const actionCell = document.createElement('td');
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-btn';
        removeButton.textContent = 'Remove';
        removeButton.addEventListener('click', () => removeFromOrder(item.rowId));
        actionCell.appendChild(removeButton);
        row.appendChild(actionCell);
        fragment.appendChild(row);
    });
    body.replaceChildren(fragment);
    calculateEstimatedTotal();
}

function updateQuantity(rowId, value) {
    const item = orderItems.find(entry => entry.rowId === rowId);
    if (!item) return;
    item.quantity = clampQuantity(value, item.available, isSellOrder());
    renderOrderTable();
}

function removeFromOrder(rowId) {
    orderItems = orderItems.filter(item => item.rowId !== rowId);
    renderOrderTable();
}

function calculateEstimatedTotal() {
    const total = orderItems.reduce((sum, item) => sum + item.price * item.quantity, 0);
    setText('grandTotalDisplay', formatCurrency(total));
}

function showInvoicePreview() {
    const partyName = document.getElementById('customerName')?.value.trim() || '';
    if (!partyName) {
        showToast('Please enter a customer or supplier name.', 'error');
        document.getElementById('customerName')?.focus();
        return;
    }
    if (!orderItems.length) {
        showToast('Please add at least one product to the order.', 'error');
        return;
    }

    setText('prevCustomerName', partyName);
    renderPreviewItems();
    setText('prevGrandTotal', formatCurrency(orderItems.reduce((sum, item) => sum + item.price * item.quantity, 0)));
    openModal('invoicePreviewModal');
}

function renderPreviewItems() {
    const body = document.getElementById('prevItemsBody');
    if (!body) return;
    const fragment = document.createDocumentFragment();
    orderItems.forEach(item => {
        const row = document.createElement('tr');
        appendCell(row, item.name);
        appendCell(row, `x${item.quantity}`, 'text-center');
        appendCell(row, `$${formatCurrency(item.price * item.quantity)}`, 'text-end');
        fragment.appendChild(row);
    });
    body.replaceChildren(fragment);
}

async function processFinalOrder() {
    if (createOrderInFlight) return;
    const partyName = document.getElementById('customerName')?.value.trim() || '';
    if (!partyName || !orderItems.length) {
        showToast('Add a name and at least one item before confirming.', 'error');
        return;
    }

    createOrderInFlight = true;
    const button = document.getElementById('confirmOrderBtn');
    setButtonBusy(button, 'Processing...');

    try {
        const response = await apiFetch('orders/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_type: getOrderType(),
                party_name: partyName,
                items: orderItems.map(item => ({ product_id: item.id, quantity: item.quantity }))
            })
        });
        const data = await readOrderApiResponse(response);
        const created = data.data;
        if (!created || !created.order_id || !Array.isArray(created.items)) {
            throw new Error('The backend did not return the created order.');
        }

        bootstrap.Modal.getInstance(document.getElementById('invoicePreviewModal'))?.hide();
        setText('orderNumber', `#${created.order_id}`);
        setText('orderTotal', formatCurrency(created.total_amount));
        resetOrderForm();
        openModal('successModal');
        await loadProductsFromDatabase();
        requestRecentOrdersReload();
    } catch (error) {
        showToast(getOrderApiErrorMessage(error, 'Order could not be created. Please retry.'), 'error');
        openModal('invoicePreviewModal');
    } finally {
        createOrderInFlight = false;
        setButtonBusy(button, 'Confirm & Process');
    }
}

function resetOrderForm() {
    orderItems = [];
    selectedProducts = {};
    rowCounter = 0;
    const customerName = document.getElementById('customerName');
    if (customerName) customerName.value = '';
    document.getElementById('sellOrder')?.click();
    renderOrderTable();
    updateOrderTypeLabel();
}

function loadModalProducts() {
    selectedProducts = {};
    const search = document.getElementById('modalProductSearch');
    if (search) search.value = '';
    renderModalProducts(availableProducts);
    updateSelectedCount();
}

function filterModalProducts() {
    const term = document.getElementById('modalProductSearch')?.value.trim().toLowerCase() || '';
    renderModalProducts(availableProducts.filter(product =>
        product.name.toLowerCase().includes(term) ||
        product.id.toLowerCase().includes(term) ||
        product.category.toLowerCase().includes(term)
    ));
}

function renderModalProducts(products) {
    const body = document.getElementById('modalProductsBody');
    if (!body) return;
    if (!products.length) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 5;
        cell.className = 'text-center py-4 text-muted';
        cell.textContent = availableProducts.length ? 'No products match this search.' : 'No products are available.';
        row.appendChild(cell);
        body.replaceChildren(row);
        return;
    }

    const fragment = document.createDocumentFragment();
    products.forEach(product => {
        const selected = selectedProducts[product.id];
        const disabled = isSellOrder() && product.available < 1;
        const row = document.createElement('tr');
        if (selected) row.classList.add('table-success');
        if (disabled) row.classList.add('text-muted');

        const checkboxCell = document.createElement('td');
        checkboxCell.className = 'text-center';
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'form-check-input product-checkbox';
        checkbox.checked = Boolean(selected);
        checkbox.disabled = disabled;
        checkbox.dataset.id = product.id;
        checkbox.addEventListener('change', event => toggleProductSelection(event.target));
        checkboxCell.appendChild(checkbox);
        row.appendChild(checkboxCell);

        const productCell = document.createElement('td');
        const name = document.createElement('strong');
        name.textContent = product.name;
        const id = document.createElement('small');
        id.className = 'd-block text-muted';
        id.textContent = product.id;
        productCell.append(name, id);
        row.appendChild(productCell);
        appendCell(row, product.category);
        appendCell(row, product.available, 'text-center');
        appendCell(row, `$${formatCurrency(product.price)}`, 'text-end fw-bold');
        fragment.appendChild(row);
    });
    body.replaceChildren(fragment);
}

function toggleProductSelection(checkbox) {
    const product = availableProducts.find(item => item.id === checkbox.dataset.id);
    if (!product) return;
    if (checkbox.checked) selectedProducts[product.id] = { product, quantity: 1 };
    else delete selectedProducts[product.id];
    checkbox.closest('tr')?.classList.toggle('table-success', checkbox.checked);
    updateSelectedCount();
    updateSelectedPreview();
}

function updateSelectedCount() {
    const count = Object.keys(selectedProducts).length;
    setText('selectedCount', `${count} product${count === 1 ? '' : 's'} selected`);
    const button = document.getElementById('confirmAddProductsBtn');
    if (button) button.disabled = count === 0;
}

function updateSelectedPreview() {
    const preview = document.getElementById('selectedProductsPreview');
    const list = document.getElementById('selectedProductsList');
    if (!preview || !list) return;
    const selections = Object.values(selectedProducts);
    preview.hidden = selections.length === 0;
    list.replaceChildren();
    selections.forEach(selection => {
        const product = selection.product;
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded';
        const label = document.createElement('strong');
        label.textContent = product.name;
        const quantity = document.createElement('input');
        quantity.type = 'number';
        quantity.className = 'form-control form-control-sm ms-2';
        quantity.style.width = '80px';
        quantity.min = '1';
        if (isSellOrder()) quantity.max = String(product.available);
        quantity.value = String(selection.quantity);
        quantity.addEventListener('change', event => {
            selection.quantity = clampQuantity(event.target.value, product.available, isSellOrder());
            event.target.value = String(selection.quantity);
        });
        const controls = document.createElement('div');
        controls.className = 'd-flex align-items-center';
        const prefix = document.createElement('span');
        prefix.className = 'small text-muted';
        prefix.textContent = 'Qty:';
        controls.append(prefix, quantity);
        wrapper.append(label, controls);
        list.appendChild(wrapper);
    });
}

function addSelectedProducts() {
    const selections = Object.values(selectedProducts);
    if (!selections.length) {
        showToast('Please select at least one product.', 'error');
        return;
    }
    selections.forEach(selection => {
        const product = selection.product;
        const existing = orderItems.find(item => item.id === product.id);
        if (existing) {
            existing.quantity = clampQuantity(existing.quantity + selection.quantity, existing.available, isSellOrder());
        } else {
            rowCounter += 1;
            orderItems.push({ ...product, rowId: rowCounter, quantity: selection.quantity });
        }
    });
    selectedProducts = {};
    renderOrderTable();
    bootstrap.Modal.getInstance(document.getElementById('selectProductModal'))?.hide();
}

function getOrderType() {
    return document.getElementById('sellOrder')?.checked ? 'Sell' : 'Purchase';
}

function isSellOrder() {
    return getOrderType() === 'Sell';
}

function clampQuantity(value, available, limitToStock) {
    let quantity = Number.parseInt(value, 10);
    if (!Number.isFinite(quantity) || quantity < 1) quantity = 1;
    if (limitToStock) quantity = Math.min(quantity, Math.max(1, available));
    return quantity;
}

function setButtonBusy(button, label) {
    if (!button) return;
    button.disabled = label !== 'Confirm & Process';
    button.textContent = label;
}

function appendCell(row, value, className) {
    const cell = document.createElement('td');
    if (className) cell.className = className;
    cell.textContent = textValue(value, '-');
    row.appendChild(cell);
}

function setText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = textValue(value, '-');
}

function textValue(value, fallback) {
    if (value === null || value === undefined || String(value) === '') return fallback;
    return String(value);
}

function safeNumber(value) {
    const number = Number(value);
    return Number.isFinite(number) ? number : 0;
}

function formatCurrency(value) {
    return safeNumber(value).toFixed(2);
}

function formatOrderDate(value) {
    if (!value) return '-';
    const date = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function debounce(callback, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback.apply(this, args), wait);
    };
}
