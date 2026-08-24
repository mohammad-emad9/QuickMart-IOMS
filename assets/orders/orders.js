/**
 * QuickMart IOMS - Orders Page
 * Order list filters and the supported order creation flow.
 */

let availableProducts = [];
let orderItems = [];
let selectedProducts = {};
let allOrdersCache = [];
let currentOrderTypeFilter = null;
let currentOrderSearch = '';
let productsLoadInFlight = null;
let ordersLoadInFlight = null;
let ordersReloadQueued = false;
let ordersRequestVersion = 0;
let createOrderInFlight = false;
let rowCounter = 0;

document.addEventListener('DOMContentLoaded', function () {
    loadUserInfo();
    setupOrderPageEvents();
    updateOrderTypeLabel();
    loadProductsFromDatabase();
    requestOrdersReload();
});

function setupOrderPageEvents() {
    document.getElementById('filterAllBtn')?.addEventListener('click', () => filterOrders('all'));
    document.getElementById('filterSellBtn')?.addEventListener('click', () => filterOrders('Sell'));
    document.getElementById('filterPurchaseBtn')?.addEventListener('click', () => filterOrders('Purchase'));
    document.getElementById('applyFiltersBtn')?.addEventListener('click', requestOrdersReload);
    document.getElementById('clearFiltersBtn')?.addEventListener('click', clearOrderFilters);

    document.getElementById('searchOrders')?.addEventListener('input', debounce(function () {
        currentOrderSearch = this.value.trim().toLowerCase();
        renderOrdersTable(getVisibleOrders());
    }, 250));

    document.querySelectorAll('input[name="orderType"]').forEach(radio => {
        radio.addEventListener('change', updateOrderTypeLabel);
    });

    document.getElementById('productSearch')?.addEventListener('input', debounce(handleProductSearch, 250));
    document.getElementById('productSearch')?.addEventListener('focus', handleProductSearch);
    document.getElementById('addItemBtn')?.addEventListener('click', () => openModal('selectProductModal'));
    document.getElementById('selectProductModal')?.addEventListener('show.bs.modal', loadModalProducts);
    document.getElementById('modalProductSearch')?.addEventListener('input', debounce(filterModalProducts, 250));
    document.getElementById('confirmAddProductsBtn')?.addEventListener('click', addSelectedProducts);
    document.getElementById('createOrderBtn')?.addEventListener('click', showOrderReview);
    document.getElementById('confirmOrderBtn')?.addEventListener('click', submitOrderToServer);
    document.getElementById('backToEditBtn')?.addEventListener('click', () => {
        bootstrap.Modal.getInstance(document.getElementById('orderReviewModal'))?.hide();
        openModal('createOrderModal');
    });
    document.getElementById('newOrderBtn')?.addEventListener('click', () => {
        bootstrap.Modal.getInstance(document.getElementById('successModal'))?.hide();
        resetOrderForm();
        openModal('createOrderModal');
    });

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
            return availableProducts;
        } catch (error) {
            availableProducts = [];
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

function requestOrdersReload() {
    ordersRequestVersion += 1;
    if (ordersLoadInFlight) {
        ordersReloadQueued = true;
        return ordersLoadInFlight;
    }
    return loadAllOrders(ordersRequestVersion);
}

async function loadAllOrders(requestVersion) {
    if (ordersLoadInFlight) {
        ordersReloadQueued = true;
        return ordersLoadInFlight;
    }

    const query = buildOrderListQuery();
    setOrdersTableState('loading', 'Loading orders...');

    const request = (async function () {
        try {
            const response = await apiFetch(`orders/list.php${query}`);
            const data = await readOrderApiResponse(response);
            if (requestVersion !== ordersRequestVersion) return;

            allOrdersCache = data.data && Array.isArray(data.data.orders) ? data.data.orders : [];
            renderOrdersTable(getVisibleOrders());
            updateOrderStats(allOrdersCache);
        } catch (error) {
            if (requestVersion !== ordersRequestVersion) return;
            setOrdersTableState('error', getOrderApiErrorMessage(error, 'Unable to load orders.'), true);
            updateOrderStats([]);
        }
    })();

    ordersLoadInFlight = request;
    try {
        await request;
    } finally {
        if (ordersLoadInFlight === request) {
            ordersLoadInFlight = null;
            if (ordersReloadQueued) {
                ordersReloadQueued = false;
                loadAllOrders(ordersRequestVersion);
            }
        }
    }
}

function buildOrderListQuery() {
    const params = new URLSearchParams();
    if (currentOrderTypeFilter) params.set('type', currentOrderTypeFilter);

    const dateFrom = document.getElementById('dateFromFilter')?.value || '';
    const dateTo = document.getElementById('dateToFilter')?.value || '';
    if (dateFrom) params.set('date_from', dateFrom);
    if (dateTo) params.set('date_to', dateTo);

    const isAdmin = sessionStorage.getItem('userRole') === 'Admin';
    const staffFilter = document.getElementById('staffFilter');
    if (isAdmin && staffFilter && staffFilter.value.trim()) {
        params.set('staff_id', staffFilter.value.trim());
    }

    const query = params.toString();
    return query ? `?${query}` : '';
}

function filterOrders(type) {
    currentOrderTypeFilter = type === 'all' ? null : type;
    updateOrderFilterButtons();
    requestOrdersReload();
}

function clearOrderFilters() {
    currentOrderTypeFilter = null;
    currentOrderSearch = '';
    const search = document.getElementById('searchOrders');
    const dateFrom = document.getElementById('dateFromFilter');
    const dateTo = document.getElementById('dateToFilter');
    const staff = document.getElementById('staffFilter');
    if (search) search.value = '';
    if (dateFrom) dateFrom.value = '';
    if (dateTo) dateTo.value = '';
    if (staff) staff.value = '';
    updateOrderFilterButtons();
    requestOrdersReload();
}

function updateOrderFilterButtons() {
    document.querySelectorAll('#filterAllBtn, #filterSellBtn, #filterPurchaseBtn').forEach(button => {
        button.classList.remove('active');
    });
    const activeId = currentOrderTypeFilter === 'Sell' ? 'filterSellBtn' :
        currentOrderTypeFilter === 'Purchase' ? 'filterPurchaseBtn' : 'filterAllBtn';
    document.getElementById(activeId)?.classList.add('active');
}

function getVisibleOrders() {
    if (!currentOrderSearch) return allOrdersCache;
    return allOrdersCache.filter(order => {
        const values = [order.Order_ID, order.Party_Name, order.Staff_Name, order.Order_Type];
        return values.some(value => textValue(value, '').toLowerCase().includes(currentOrderSearch));
    });
}

function renderOrdersTable(orders) {
    const tableBody = document.getElementById('ordersTableBody');
    if (!tableBody) return;

    if (!orders.length) {
        const message = currentOrderSearch || currentOrderTypeFilter || hasDateOrStaffFilter()
            ? 'No orders match the selected filters.'
            : 'No orders have been created yet.';
        setOrdersTableState('empty', message);
        return;
    }

    const fragment = document.createDocumentFragment();
    orders.forEach(order => fragment.appendChild(createOrderListRow(order)));
    tableBody.replaceChildren(fragment);
}

function createOrderListRow(order) {
    const row = document.createElement('tr');
    appendOrderTextCell(row, order.Order_ID || '-', 'fw-bold text-primary');
    appendOrderTextCell(row, order.Staff_Name || order.Staff_ID || '-', 'text-muted');
    appendOrderTextCell(row, order.Party_Name || '-');
    appendOrderTextCell(row, order.Item_Count ?? 0, 'text-center');
    appendOrderTextCell(row, `$${formatCurrency(order.Total_Amount)}`, 'fw-bold text-success');

    const typeCell = document.createElement('td');
    const typeBadge = document.createElement('span');
    typeBadge.className = `badge ${order.Order_Type === 'Sell' ? 'bg-success' : 'bg-info'}`;
    typeBadge.textContent = textValue(order.Order_Type, '-');
    typeCell.appendChild(typeBadge);
    row.appendChild(typeCell);

    appendOrderTextCell(row, formatOrderDate(order.Order_Date), 'text-muted');

    const actionCell = document.createElement('td');
    const detailsLink = document.createElement('a');
    detailsLink.className = 'btn btn-sm btn-outline-primary';
    detailsLink.href = `${viewPath('order-details.php')}?id=${encodeURIComponent(textValue(order.Order_ID, ''))}`;
    detailsLink.setAttribute('aria-label', `View order ${textValue(order.Order_ID, '')}`);
    const icon = document.createElement('i');
    icon.className = 'fas fa-eye';
    detailsLink.appendChild(icon);
    actionCell.appendChild(detailsLink);
    row.appendChild(actionCell);
    return row;
}

function appendOrderTextCell(row, value, className) {
    const cell = document.createElement('td');
    if (className) cell.className = className;
    cell.textContent = textValue(value, '-');
    row.appendChild(cell);
}

function setOrdersTableState(type, message, retry) {
    const tableBody = document.getElementById('ordersTableBody');
    if (!tableBody) return;

    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 8;
    cell.className = `text-center py-5 ${type === 'error' ? 'text-danger' : 'text-muted'}`;
    cell.textContent = message;
    row.appendChild(cell);

    if (retry) {
        const retryButton = document.createElement('button');
        retryButton.type = 'button';
        retryButton.className = 'btn btn-sm btn-outline-primary d-block mx-auto mt-3';
        retryButton.textContent = 'Retry';
        retryButton.addEventListener('click', requestOrdersReload);
        cell.appendChild(retryButton);
    }
    tableBody.replaceChildren(row);
}

function hasDateOrStaffFilter() {
    return Boolean(
        document.getElementById('dateFromFilter')?.value ||
        document.getElementById('dateToFilter')?.value ||
        document.getElementById('staffFilter')?.value.trim()
    );
}

function updateOrderStats(orders) {
    setText('totalOrdersCount', orders.length);
    setText('sellOrdersCount', orders.filter(order => order.Order_Type === 'Sell').length);
    setText('purchaseOrdersCount', orders.filter(order => order.Order_Type === 'Purchase').length);
    const revenue = orders
        .filter(order => order.Order_Type === 'Sell')
        .reduce((total, order) => total + safeNumber(order.Total_Amount), 0);
    setText('totalRevenue', formatCurrency(revenue));
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

    const filtered = availableProducts.filter(product =>
        product.name.toLowerCase().includes(term) || product.category.toLowerCase().includes(term)
    );
    renderProductSuggestions(filtered, term);
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
    if (product.available < 1 && isSellOrder()) {
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
    const suggestions = document.getElementById('productSuggestions');
    if (input) input.value = '';
    suggestions?.classList.remove('show');
}

function renderOrderTable() {
    const tableBody = document.getElementById('orderTableBody');
    const emptyMessage = document.getElementById('emptyMessage');
    if (!tableBody) return;

    if (!orderItems.length) {
        tableBody.replaceChildren();
        if (emptyMessage) emptyMessage.hidden = false;
        calculateEstimatedTotal();
        return;
    }

    if (emptyMessage) emptyMessage.hidden = true;
    const fragment = document.createDocumentFragment();
    orderItems.forEach(item => {
        const row = document.createElement('tr');
        row.id = `row-${item.rowId}`;
        appendOrderTextCell(row, item.name, 'product-cell');
        appendOrderTextCell(row, `${item.available} units`, 'available-cell');
        appendOrderTextCell(row, `$${formatCurrency(item.price)}`, 'price-cell');

        const quantityCell = document.createElement('td');
        const quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.className = 'form-control form-control-sm qty-input';
        quantityInput.min = '1';
        if (isSellOrder()) quantityInput.max = String(item.available);
        quantityInput.value = String(item.quantity);
        quantityInput.addEventListener('change', event => updateQuantity(item.rowId, event.target.value));
        quantityCell.appendChild(quantityInput);
        row.appendChild(quantityCell);

        appendOrderTextCell(row, `$${formatCurrency(item.price * item.quantity)}`, 'total-cell');

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
    tableBody.replaceChildren(fragment);
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

function showOrderReview() {
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

    const orderType = getOrderType();
    setText('reviewOrderType', orderType);
    setText('reviewStaffName', 'Authenticated staff');
    setText('reviewPartyTitle', orderType === 'Sell' ? 'Customer' : 'Supplier');
    setText('reviewPartyName', partyName);
    setText('reviewItemCount', orderItems.length);
    setText('reviewGrandTotal', formatCurrency(orderItems.reduce((sum, item) => sum + item.price * item.quantity, 0)));
    renderReviewItems();
    openModal('orderReviewModal');
}

function renderReviewItems() {
    const body = document.getElementById('reviewItemsBody');
    if (!body) return;
    const fragment = document.createDocumentFragment();
    orderItems.forEach(item => {
        const row = document.createElement('tr');
        appendOrderTextCell(row, item.name);
        appendOrderTextCell(row, `x${item.quantity}`, 'text-center');
        appendOrderTextCell(row, `$${formatCurrency(item.price)}`, 'text-end');
        appendOrderTextCell(row, `$${formatCurrency(item.price * item.quantity)}`, 'text-end fw-bold');
        fragment.appendChild(row);
    });
    body.replaceChildren(fragment);
}

async function submitOrderToServer() {
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

        bootstrap.Modal.getInstance(document.getElementById('orderReviewModal'))?.hide();
        populateOrderConfirmation(created);
        resetOrderForm();
        openModal('successModal');
        await loadProductsFromDatabase();
        requestOrdersReload();
    } catch (error) {
        showToast(getOrderApiErrorMessage(error, 'Order could not be created. Please retry.'), 'error');
        openModal('orderReviewModal');
    } finally {
        createOrderInFlight = false;
        setButtonBusy(button, 'Confirm Order');
    }
}

function populateOrderConfirmation(created) {
    setText('orderNumber', `#${created.order_id}`);
    setText('confirmOrderType', created.order_type);
    setText('confirmStaffName', created.staff_id || '-');
    setText('confirmPartyTitle', created.order_type === 'Sell' ? 'Customer' : 'Supplier');
    setText('confirmPartyName', created.party_name || '-');
    setText('confirmGrandTotal', formatCurrency(created.total_amount));

    const body = document.getElementById('confirmItemsBody');
    if (!body) return;
    const fragment = document.createDocumentFragment();
    created.items.forEach(item => {
        const row = document.createElement('tr');
        appendOrderTextCell(row, item.product_name || item.product_id || '-');
        appendOrderTextCell(row, item.quantity ?? '-', 'text-center');
        appendOrderTextCell(row, `$${formatCurrency(item.price)}`, 'text-end');
        appendOrderTextCell(row, `$${formatCurrency(item.line_total)}`, 'text-end fw-bold');
        fragment.appendChild(row);
    });
    body.replaceChildren(fragment);
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
    const products = availableProducts.filter(product =>
        product.name.toLowerCase().includes(term) ||
        product.id.toLowerCase().includes(term) ||
        product.category.toLowerCase().includes(term)
    );
    renderModalProducts(products);
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

        appendOrderTextCell(row, product.category);
        appendOrderTextCell(row, product.available, 'text-center');
        appendOrderTextCell(row, `$${formatCurrency(product.price)}`, 'text-end fw-bold');
        fragment.appendChild(row);
    });
    body.replaceChildren(fragment);
}

function toggleProductSelection(checkbox) {
    const product = availableProducts.find(item => item.id === checkbox.dataset.id);
    if (!product) return;
    if (checkbox.checked) {
        selectedProducts[product.id] = { product, quantity: 1 };
    } else {
        delete selectedProducts[product.id];
    }
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
        const text = document.createElement('span');
        text.className = 'small text-muted';
        text.textContent = 'Qty:';
        controls.append(text, quantity);
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
    button.disabled = label !== 'Confirm Order';
    button.textContent = label;
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
