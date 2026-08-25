/**
 * Order list filtering and order details modal presentation.
 */

let availableProducts = [];
let orderItems = [];
let selectedProducts = {};
let allOrdersCache = [];
let currentOrderTypeFilter = null;
let currentOrderSearch = '';
let productsLoadInFlight = null;
let productsLoadError = null;
let ordersLoadInFlight = null;
let ordersReloadQueued = false;
let ordersRequestVersion = 0;
let createOrderInFlight = false;
let rowCounter = 0;
let lastModalTrigger = null;

const orderIconPaths = Object.freeze({
    check: ['m5 12 4 4L19 6'],
    eye: ['M3 12s3.5-6 9-6 9 6 9 6-3.5 6-9 6-9-6-9-6Z', 'M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z'],
    refresh: ['M20 11a8 8 0 1 0 1 4', 'M20 5v6h-6'],
    spinner: ['M12 3a9 9 0 1 0 9 9'],
    plus: ['M12 5v14', 'M5 12h14'],
    trash: ['M5 7h14', 'M10 11v6', 'M14 11v6', 'M8 7l1-3h6l1 3', 'M7 7l1 14h8l1-14'],
    search: ['M11 17.5a6.5 6.5 0 1 0 0-13 6.5 6.5 0 0 0 0 13Z', 'm16 16 4 4']
});

document.addEventListener('DOMContentLoaded', async function () {
    setupOrderPageEvents();
    setupOrderModalAccessibility();
    updateOrderTypeLabel();

    const authenticated = await checkSession();
    if (!authenticated) {
        return;
    }

    loadUserInfo();
    loadProductsFromDatabase();
    requestOrdersReload();
});

function setupOrderPageEvents() {
    document.getElementById('filterAllBtn')?.addEventListener('click', () => filterOrders('all'));
    document.getElementById('filterSellBtn')?.addEventListener('click', () => filterOrders('Sell'));
    document.getElementById('filterPurchaseBtn')?.addEventListener('click', () => filterOrders('Purchase'));
    document.getElementById('applyFiltersBtn')?.addEventListener('click', requestOrdersReload);
    document.getElementById('clearFiltersBtn')?.addEventListener('click', clearOrderFilters);

    const orderSearch = document.getElementById('searchOrders');
    orderSearch?.addEventListener('input', createPageDebounce(function (event) {
        currentOrderSearch = event.target.value.trim().toLowerCase();
        renderOrdersTable(getVisibleOrders());
    }, 220));

    ['dateFromFilter', 'dateToFilter', 'staffFilter'].forEach(id => {
        document.getElementById(id)?.addEventListener('keydown', event => {
            if (event.key === 'Enter') {
                event.preventDefault();
                requestOrdersReload();
            }
        });
    });

    document.querySelectorAll('input[name="orderType"]').forEach(radio => {
        radio.addEventListener('change', () => {
            updateOrderTypeLabel();
            renderOrderTable();
            if (document.getElementById('selectProductModal')?.classList.contains('show')) renderModalProducts(availableProducts);
        });
    });

    const productSearch = document.getElementById('productSearch');
    productSearch?.addEventListener('input', createPageDebounce(handleProductSearch, 220));
    productSearch?.addEventListener('focus', handleProductSearch);
    productSearch?.addEventListener('keydown', handleProductSearchKeyboard);
    document.getElementById('addItemBtn')?.addEventListener('click', () => openModal('selectProductModal'));
    document.getElementById('selectProductModal')?.addEventListener('show.bs.modal', loadModalProducts);
    document.getElementById('modalProductSearch')?.addEventListener('input', createPageDebounce(filterModalProducts, 220));
    document.getElementById('confirmAddProductsBtn')?.addEventListener('click', addSelectedProducts);
    document.getElementById('createOrderBtn')?.addEventListener('click', showOrderReview);
    document.getElementById('confirmOrderBtn')?.addEventListener('click', submitOrderToServer);
    document.getElementById('backToEditBtn')?.addEventListener('click', returnToOrderEditor);
    document.getElementById('newOrderBtn')?.addEventListener('click', startAnotherOrder);
    document.getElementById('printInvoiceBtn')?.addEventListener('click', printOrderInvoice);

    document.addEventListener('click', function (event) {
        const suggestions = document.getElementById('productSuggestions');
        const searchInput = document.getElementById('productSearch');
        if (suggestions && !suggestions.contains(event.target) && event.target !== searchInput) {
            setSuggestionsOpen(false);
        }
    });
}

function setupOrderModalAccessibility() {
    const configs = [
        ['createOrderModal', 'customerName'],
        ['selectProductModal', 'modalProductSearch'],
        ['orderReviewModal', 'confirmOrderBtn'],
        ['successModal', 'printInvoiceBtn']
    ];

    configs.forEach(([modalId, focusId]) => {
        const modal = document.getElementById(modalId);
        if (!modal) return;
        modal.addEventListener('show.bs.modal', () => {
            if (!modal.contains(document.activeElement)) lastModalTrigger = document.activeElement;
        });
        modal.addEventListener('shown.bs.modal', () => {
            document.getElementById(focusId)?.focus();
        });
        modal.addEventListener('hidden.bs.modal', () => {
            window.setTimeout(() => {
                if (lastModalTrigger && document.contains(lastModalTrigger) && !lastModalTrigger.disabled) {
                    lastModalTrigger.focus();
                }
            }, 0);
        });
    });
}

function openModal(id) {
    const element = document.getElementById(id);
    if (element && typeof bootstrap !== 'undefined') {
        lastModalTrigger = document.activeElement;
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
    if (error && error.status === 404) return 'The requested order record was not found.';
    if (error && error.status === 409) return 'The order conflicted with another update. Please retry.';
    if (error && error.status === 422) return error.serverMessage || 'Please review the order fields and try again.';
    if (error && error.status >= 500) return 'The order service is temporarily unavailable. Please retry.';
    if (error && error.message === 'Failed to fetch') return 'The order service could not be reached. Please retry.';
    return error && error.message ? error.message : fallback;
}

function loadProductsFromDatabase() {
    if (productsLoadInFlight) return productsLoadInFlight;

    productsLoadError = null;
    productsLoadInFlight = (async function () {
        try {
            const response = await apiFetch('products/list.php');
            const data = await readOrderApiResponse(response);
            const products = data.data && Array.isArray(data.data.products) ? data.data.products : [];
            availableProducts = products.map(normalizeProduct).filter(product => product.id);
            return availableProducts;
        } catch (error) {
            availableProducts = [];
            productsLoadError = error;
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
    setOrdersTableState('loading', 'Loading orders…');
    updateOrderStats(null);

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
            setOrdersTableState(getOrdersStateType(error), getOrderApiErrorMessage(error, 'Unable to load orders.'), true);
            updateOrderStats(null);
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

    const isAdmin = typeof getServerSessionRole === 'function'
        && getServerSessionRole() === 'Admin';
    const staffFilter = document.getElementById('staffFilter');
    if (isAdmin && staffFilter && staffFilter.value.trim()) params.set('staff_id', staffFilter.value.trim());

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
    const buttons = document.querySelectorAll('#filterAllBtn, #filterSellBtn, #filterPurchaseBtn');
    buttons.forEach(button => {
        button.classList.remove('active');
        button.setAttribute('aria-pressed', 'false');
    });
    const activeId = currentOrderTypeFilter === 'Sell' ? 'filterSellBtn' : currentOrderTypeFilter === 'Purchase' ? 'filterPurchaseBtn' : 'filterAllBtn';
    const activeButton = document.getElementById(activeId);
    activeButton?.classList.add('active');
    activeButton?.setAttribute('aria-pressed', 'true');
}

function getVisibleOrders() {
    if (!currentOrderSearch) return allOrdersCache;
    return allOrdersCache.filter(order => {
        const values = [order.Order_ID, order.Party_Name, order.Staff_Name, order.Staff_ID, order.Order_Type];
        return values.some(value => textValue(value, '').toLowerCase().includes(currentOrderSearch));
    });
}

function renderOrdersTable(orders) {
    const tableBody = document.getElementById('ordersTableBody');
    if (!tableBody) return;

    if (!orders.length) {
        const message = currentOrderSearch || currentOrderTypeFilter || hasDateOrStaffFilter() ? 'No orders match the selected filters.' : 'No orders have been created yet.';
        setOrdersTableState('empty', message, false);
        return;
    }

    const fragment = document.createDocumentFragment();
    orders.forEach(order => fragment.appendChild(createOrderListRow(order)));
    tableBody.replaceChildren(fragment);
    setOrdersResultSummary(`${orders.length} ${orders.length === 1 ? 'order' : 'orders'} shown`);
}

function createOrderListRow(order) {
    const row = document.createElement('tr');
    appendOrderTextCell(row, order.Order_ID || '-', 'identifier-cell', 'Order ID');
    appendOrderTextCell(row, order.Staff_Name || order.Staff_ID || '-', 'muted-cell', 'Staff');
    appendOrderTextCell(row, order.Party_Name || '-', 'party-cell', 'Customer / supplier');
    appendOrderTextCell(row, order.Item_Count ?? 0, 'numeric-cell', 'Items');
    appendOrderTextCell(row, `$${formatCurrency(order.Total_Amount)}`, 'money-cell amount-cell', 'Amount');

    const typeCell = document.createElement('td');
    typeCell.dataset.label = 'Type';
    const typeBadge = document.createElement('span');
    typeBadge.className = `order-type-badge ${order.Order_Type === 'Sell' ? 'order-type-sell' : 'order-type-purchase'}`;
    typeBadge.textContent = textValue(order.Order_Type, '-');
    typeCell.appendChild(typeBadge);
    row.appendChild(typeCell);

    appendOrderTextCell(row, formatOrderDate(order.Order_Date), 'muted-cell', 'Date');

    const actionCell = document.createElement('td');
    actionCell.dataset.label = 'Action';
    const detailsLink = document.createElement('a');
    detailsLink.className = 'order-view-link';
    detailsLink.href = `${viewPath('order-details.php')}?id=${encodeURIComponent(textValue(order.Order_ID, ''))}`;
    detailsLink.setAttribute('aria-label', `View order ${textValue(order.Order_ID, '')}`);
    detailsLink.appendChild(createOrderIcon('eye'));
    const viewText = document.createElement('span');
    viewText.textContent = 'View';
    detailsLink.appendChild(viewText);
    actionCell.appendChild(detailsLink);
    row.appendChild(actionCell);
    return row;
}

function appendOrderTextCell(row, value, className, label) {
    const cell = document.createElement('td');
    if (className) cell.className = className;
    if (label) cell.dataset.label = label;
    cell.textContent = textValue(value, '-');
    row.appendChild(cell);
}

function setOrdersTableState(type, message, retry) {
    const tableBody = document.getElementById('ordersTableBody');
    if (!tableBody) return;

    const row = document.createElement('tr');
    row.className = 'table-state-row';
    const cell = document.createElement('td');
    cell.colSpan = 8;
    const state = document.createElement('div');
    state.className = `table-state table-state--${type}`;
    state.setAttribute('role', type === 'loading' ? 'status' : 'alert');

    if (type === 'loading') state.appendChild(createOrderIcon('spinner', 'state-spinner-icon'));
    if (type === 'error' || type === 'stale' || type === 'unauthorized') state.appendChild(createOrderIcon('refresh'));

    const title = document.createElement('strong');
    title.textContent = type === 'empty' ? 'No matching records' : type === 'loading' ? 'Loading order records' : type === 'stale' ? 'Session needs attention' : type === 'unauthorized' ? 'Access restricted' : 'Order records unavailable';
    state.appendChild(title);
    const description = document.createElement('p');
    description.textContent = message;
    state.appendChild(description);

    if (retry) {
        const retryButton = document.createElement('button');
        retryButton.type = 'button';
        retryButton.className = 'qm-button qm-button--quiet';
        retryButton.appendChild(createOrderIcon('refresh'));
        const retryText = document.createElement('span');
        retryText.textContent = 'Retry';
        retryButton.appendChild(retryText);
        retryButton.addEventListener('click', requestOrdersReload);
        state.appendChild(retryButton);
    }

    cell.appendChild(state);
    row.appendChild(cell);
    tableBody.replaceChildren(row);
    setOrdersResultSummary(type === 'loading' ? 'Loading order records' : message);
}

function getOrdersStateType(error) {
    if (error && error.status === 401) return 'stale';
    if (error && error.status === 403) return 'unauthorized';
    if (error && error.status >= 500) return 'error';
    return 'error';
}

function setOrdersResultSummary(message) {
    const summary = document.getElementById('ordersResultSummary');
    if (summary) summary.textContent = textValue(message, 'Order records');
}

function hasDateOrStaffFilter() {
    return Boolean(document.getElementById('dateFromFilter')?.value || document.getElementById('dateToFilter')?.value || document.getElementById('staffFilter')?.value.trim());
}

function updateOrderStats(orders) {
    if (!Array.isArray(orders)) {
        setText('totalOrdersCount', '—');
        setText('sellOrdersCount', '—');
        setText('purchaseOrdersCount', '—');
        setText('totalRevenue', '—');
        return;
    }
    setText('totalOrdersCount', orders.length);
    setText('sellOrdersCount', orders.filter(order => order.Order_Type === 'Sell').length);
    setText('purchaseOrdersCount', orders.filter(order => order.Order_Type === 'Purchase').length);
    const revenue = orders.filter(order => order.Order_Type === 'Sell').reduce((total, order) => total + safeNumber(order.Total_Amount), 0);
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
        setSuggestionsOpen(false);
        suggestions.replaceChildren();
        return;
    }

    const filtered = availableProducts.filter(product => product.name.toLowerCase().includes(term) || product.category.toLowerCase().includes(term) || product.id.toLowerCase().includes(term));
    renderProductSuggestions(filtered, term);
    setSuggestionsOpen(true);
}

function handleProductSearchKeyboard(event) {
    const suggestions = document.getElementById('productSuggestions');
    if (!suggestions || !suggestions.classList.contains('show')) return;
    const options = [...suggestions.querySelectorAll('button.suggestion-item')];
    if (event.key === 'ArrowDown' && options.length) {
        event.preventDefault();
        options[0].focus();
    } else if (event.key === 'Escape') {
        event.preventDefault();
        setSuggestionsOpen(false);
    }
}

function setSuggestionsOpen(isOpen) {
    const suggestions = document.getElementById('productSuggestions');
    const input = document.getElementById('productSearch');
    suggestions?.classList.toggle('show', isOpen);
    input?.setAttribute('aria-expanded', String(isOpen));
}

function renderProductSuggestions(products, term) {
    const suggestions = document.getElementById('productSuggestions');
    if (!suggestions) return;
    suggestions.replaceChildren();

    if (!products.length) {
        const empty = document.createElement('div');
        empty.className = 'suggestion-item text-muted';
        empty.setAttribute('role', 'option');
        empty.textContent = `No products found matching "${term}"`;
        suggestions.appendChild(empty);
        return;
    }

    const fragment = document.createDocumentFragment();
    products.forEach(product => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'suggestion-item';
        item.setAttribute('role', 'option');
        item.addEventListener('click', () => addProductToOrder(product.id));

        const details = document.createElement('span');
        const name = document.createElement('span');
        name.className = 'product-name';
        name.textContent = product.name;
        const meta = document.createElement('span');
        meta.className = 'product-meta';
        meta.textContent = `${product.category} · ${product.available} available`;
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
    if (existing) existing.quantity = clampQuantity(existing.quantity + 1, existing.available, isSellOrder());
    else {
        rowCounter += 1;
        orderItems.push({ ...product, rowId: rowCounter, quantity: 1 });
    }
    renderOrderTable();
    const input = document.getElementById('productSearch');
    if (input) input.value = '';
    setSuggestionsOpen(false);
    input?.focus();
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
        appendOrderTextCell(row, item.name, 'product-cell', 'Product');
        appendOrderTextCell(row, `${item.available} units`, 'available-cell numeric-cell', 'Available');
        appendOrderTextCell(row, `$${formatCurrency(item.price)}`, 'price-cell money-cell', 'Current price');

        const quantityCell = document.createElement('td');
        quantityCell.dataset.label = 'Quantity';
        const quantityInput = document.createElement('input');
        quantityInput.type = 'number';
        quantityInput.className = 'form-control qty-input';
        quantityInput.min = '1';
        if (isSellOrder()) quantityInput.max = String(item.available);
        quantityInput.value = String(item.quantity);
        quantityInput.setAttribute('aria-label', `Quantity for ${item.name}`);
        quantityInput.addEventListener('change', event => updateQuantity(item.rowId, event.target.value));
        quantityCell.appendChild(quantityInput);
        row.appendChild(quantityCell);

        appendOrderTextCell(row, `$${formatCurrency(item.price * item.quantity)}`, 'total-cell money-cell amount-cell', 'Line total');

        const actionCell = document.createElement('td');
        actionCell.dataset.label = 'Action';
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-btn';
        removeButton.setAttribute('aria-label', `Remove ${item.name} from order`);
        removeButton.appendChild(createOrderIcon('trash'));
        const removeText = document.createElement('span');
        removeText.textContent = 'Remove';
        removeButton.appendChild(removeText);
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
    const form = document.getElementById('createOrderForm');
    const partyName = document.getElementById('customerName')?.value.trim() || '';
    if (form && !form.reportValidity()) return;
    if (!partyName) {
        showToast('Please enter a customer or supplier name.', 'error');
        document.getElementById('customerName')?.focus();
        return;
    }
    if (!orderItems.length) {
        showToast('Please add at least one product to the order.', 'error');
        document.getElementById('productSearch')?.focus();
        return;
    }

    const orderType = getOrderType();
    setText('reviewOrderType', orderType);
    const reviewType = document.getElementById('reviewOrderType');
    reviewType?.classList.toggle('order-type-sell', orderType === 'Sell');
    reviewType?.classList.toggle('order-type-purchase', orderType === 'Purchase');
    setText('reviewStaffName', (typeof getServerSessionRole === 'function'
        && getServerSessionRole()) || 'Authenticated staff');
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
        appendOrderTextCell(row, item.name, '', 'Product');
        appendOrderTextCell(row, `x${item.quantity}`, 'numeric-cell', 'Qty');
        appendOrderTextCell(row, `$${formatCurrency(item.price)}`, 'money-cell', 'Unit price');
        appendOrderTextCell(row, `$${formatCurrency(item.price * item.quantity)}`, 'money-cell amount-cell', 'Total');
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
    setButtonBusy(button, true);

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
        if (!created || !created.order_id || !Array.isArray(created.items)) throw new Error('The backend did not return the created order.');

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
        setButtonBusy(button, false);
    }
}

function populateOrderConfirmation(created) {
    setText('orderNumber', created.order_id);
    setText('confirmOrderType', created.order_type);
    const confirmationType = document.getElementById('confirmOrderType');
    confirmationType?.classList.toggle('order-type-sell', created.order_type === 'Sell');
    confirmationType?.classList.toggle('order-type-purchase', created.order_type === 'Purchase');
    setText('confirmStaffName', created.staff_id || '-');
    setText('confirmPartyTitle', created.order_type === 'Sell' ? 'Customer' : 'Supplier');
    setText('confirmPartyName', created.party_name || '-');
    setText('confirmGrandTotal', formatCurrency(created.total_amount));

    const body = document.getElementById('confirmItemsBody');
    if (!body) return;
    const fragment = document.createDocumentFragment();
    created.items.forEach(item => {
        const row = document.createElement('tr');
        appendOrderTextCell(row, item.product_name || item.product_id || '-', '', 'Product');
        appendOrderTextCell(row, item.quantity ?? '-', 'numeric-cell', 'Qty');
        appendOrderTextCell(row, `$${formatCurrency(item.price)}`, 'money-cell', 'Unit price');
        appendOrderTextCell(row, `$${formatCurrency(item.line_total)}`, 'money-cell amount-cell', 'Total');
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
    setSuggestionsOpen(false);
    const productSearch = document.getElementById('productSearch');
    if (productSearch) productSearch.value = '';
}

async function loadModalProducts() {
    selectedProducts = {};
    const search = document.getElementById('modalProductSearch');
    if (search) search.value = '';
    if (!availableProducts.length && productsLoadInFlight) await productsLoadInFlight;
    renderModalProducts(availableProducts);
    updateSelectedCount();
}

function filterModalProducts() {
    const term = document.getElementById('modalProductSearch')?.value.trim().toLowerCase() || '';
    const products = availableProducts.filter(product => product.name.toLowerCase().includes(term) || product.id.toLowerCase().includes(term) || product.category.toLowerCase().includes(term));
    renderModalProducts(products);
}

function renderModalProducts(products) {
    const body = document.getElementById('modalProductsBody');
    if (!body) return;
    if (!products.length) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 5;
        const state = document.createElement('div');
        state.className = 'table-state';
        if (productsLoadError) {
            state.appendChild(createOrderIcon('refresh'));
            const title = document.createElement('strong');
            title.textContent = 'Products unavailable';
            state.appendChild(title);
            const message = document.createElement('p');
            message.textContent = getOrderApiErrorMessage(productsLoadError, 'Unable to load products.');
            state.appendChild(message);
            const retryButton = document.createElement('button');
            retryButton.type = 'button';
            retryButton.className = 'qm-button qm-button--quiet';
            retryButton.textContent = 'Retry';
            retryButton.addEventListener('click', async () => {
                state.replaceChildren(createOrderIcon('spinner', 'state-spinner-icon'));
                await loadProductsFromDatabase();
                renderModalProducts(availableProducts);
            });
            state.appendChild(retryButton);
        } else {
            const title = document.createElement('strong');
            title.textContent = availableProducts.length ? 'No matching products' : 'No products are available';
            state.appendChild(title);
            const message = document.createElement('p');
            message.textContent = availableProducts.length ? 'Try a different name, ID, or category.' : 'The product service returned no selectable records.';
            state.appendChild(message);
        }
        cell.appendChild(state);
        row.appendChild(cell);
        body.replaceChildren(row);
        return;
    }

    const fragment = document.createDocumentFragment();
    products.forEach(product => {
        const selected = selectedProducts[product.id];
        const disabled = isSellOrder() && product.available < 1;
        const row = document.createElement('tr');
        if (selected) row.classList.add('selected-row');
        if (disabled) row.classList.add('unavailable-row');

        const checkboxCell = document.createElement('td');
        checkboxCell.dataset.label = 'Select';
        const checkboxLabel = document.createElement('label');
        checkboxLabel.className = 'checkbox-hit-area';
        checkboxLabel.setAttribute('aria-label', `Select ${product.name}`);
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'form-check-input product-checkbox';
        checkbox.checked = Boolean(selected);
        checkbox.disabled = disabled;
        checkbox.dataset.id = product.id;
        checkbox.addEventListener('change', event => toggleProductSelection(event.target));
        checkboxLabel.appendChild(checkbox);
        checkboxCell.appendChild(checkboxLabel);
        row.appendChild(checkboxCell);

        const productCell = document.createElement('td');
        productCell.dataset.label = 'Product';
        const name = document.createElement('strong');
        name.textContent = product.name;
        const id = document.createElement('small');
        id.className = 'd-block text-muted';
        id.textContent = product.id;
        productCell.append(name, id);
        row.appendChild(productCell);
        appendOrderTextCell(row, product.category, '', 'Category');
        appendOrderTextCell(row, product.available, 'numeric-cell', 'Available');
        appendOrderTextCell(row, `$${formatCurrency(product.price)}`, 'money-cell', 'Price');
        fragment.appendChild(row);
    });
    body.replaceChildren(fragment);
}

function toggleProductSelection(checkbox) {
    const product = availableProducts.find(item => item.id === checkbox.dataset.id);
    if (!product) return;
    if (checkbox.checked) selectedProducts[product.id] = { product, quantity: 1 };
    else delete selectedProducts[product.id];
    checkbox.closest('tr')?.classList.toggle('selected-row', checkbox.checked);
    updateSelectedCount();
    updateSelectedPreview();
}

function updateSelectedCount() {
    const count = Object.keys(selectedProducts).length;
    const label = `${count} product${count === 1 ? '' : 's'} selected`;
    setText('selectedCount', label);
    setText('selectedCountFooter', label);
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
        wrapper.className = 'selected-product-row';
        const label = document.createElement('strong');
        label.textContent = product.name;
        const controls = document.createElement('span');
        controls.className = 'selected-product-row__controls';
        const text = document.createElement('span');
        text.textContent = 'Qty';
        const quantity = document.createElement('input');
        quantity.type = 'number';
        quantity.className = 'form-control';
        quantity.min = '1';
        if (isSellOrder()) quantity.max = String(product.available);
        quantity.value = String(selection.quantity);
        quantity.setAttribute('aria-label', `Quantity for ${product.name}`);
        quantity.addEventListener('change', event => {
            selection.quantity = clampQuantity(event.target.value, product.available, isSellOrder());
            event.target.value = String(selection.quantity);
        });
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
        if (existing) existing.quantity = clampQuantity(existing.quantity + selection.quantity, existing.available, isSellOrder());
        else {
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

function setButtonBusy(button, isBusy) {
    if (!button) return;
    button.disabled = isBusy;
    button.setAttribute('aria-busy', String(isBusy));
    button.replaceChildren(createOrderIcon(isBusy ? 'spinner' : 'check', isBusy ? 'button-spinner' : ''), document.createTextNode(isBusy ? 'Processing…' : 'Confirm order'));
}

function returnToOrderEditor() {
    bootstrap.Modal.getInstance(document.getElementById('orderReviewModal'))?.hide();
    window.setTimeout(() => openModal('createOrderModal'), 160);
}

function startAnotherOrder() {
    bootstrap.Modal.getInstance(document.getElementById('successModal'))?.hide();
    resetOrderForm();
    window.setTimeout(() => openModal('createOrderModal'), 160);
}

function printOrderInvoice() {
    document.body.classList.add('orders-print-invoice');
    window.print();
    window.setTimeout(() => document.body.classList.remove('orders-print-invoice'), 500);
}

function createOrderIcon(name, className) {
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '1.8');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');
    if (className) svg.setAttribute('class', `qm-icon ${className}`);
    else svg.setAttribute('class', 'qm-icon');
    (orderIconPaths[name] || orderIconPaths.check).forEach(pathData => {
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', pathData);
        svg.appendChild(path);
    });
    return svg;
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
    return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function createPageDebounce(callback, wait) {
    if (typeof debounce === 'function') return debounce(callback, wait);
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => callback.apply(this, args), wait);
    };
}
