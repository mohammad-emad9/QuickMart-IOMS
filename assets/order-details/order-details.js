/**
 * QuickMart IOMS - Order Details
 * Loads and renders one backend-authoritative order.
 */

let orderDetailsRequestVersion = 0;
let orderDetailsRequestInFlight = false;

document.addEventListener('DOMContentLoaded', function () {
    loadUserInfo();
    document.getElementById('printDetailsBtn')?.addEventListener('click', () => window.print());
    loadOrderDetails();
});

async function loadOrderDetails() {
    const requestVersion = ++orderDetailsRequestVersion;
    const orderId = new URLSearchParams(window.location.search).get('id');
    const invoice = document.getElementById('invoiceBox');

    if (!orderId || !orderId.trim()) {
        showOrderDetailsState('An order ID is required to load this record.', 'error', false);
        if (invoice) invoice.hidden = true;
        return;
    }

    orderDetailsRequestInFlight = true;
    showOrderDetailsState('Connecting to the order service…', 'loading', false);
    if (invoice) {
        invoice.hidden = false;
        invoice.setAttribute('aria-busy', 'true');
    }
    renderItemsState('Loading order items…');

    try {
        const response = await apiFetch(`orders/get.php?id=${encodeURIComponent(orderId.trim())}`);
        const data = await readOrderApiResponse(response);

        if (requestVersion !== orderDetailsRequestVersion) return;
        if (!data.data || !data.data.order) throw createOrderApiError(response.status, 'Order details were not returned.');

        renderOrderDetails(data.data);
        hideOrderDetailsState();
        if (invoice) invoice.setAttribute('aria-busy', 'false');
    } catch (error) {
        if (requestVersion !== orderDetailsRequestVersion) return;
        showOrderDetailsState(getOrderDetailsErrorMessage(error), getOrderDetailsStateType(error), true);
        if (invoice) invoice.hidden = true;
    } finally {
        if (requestVersion === orderDetailsRequestVersion) orderDetailsRequestInFlight = false;
    }
}

async function readOrderApiResponse(response) {
    let data = null;
    try {
        data = await response.json();
    } catch (error) {
        throw createOrderApiError(response.status, 'The server returned an invalid response.');
    }

    if (!response.ok || !data || data.success !== true) throw createOrderApiError(response.status, data && data.message);
    return data;
}

function createOrderApiError(status, serverMessage) {
    const error = new Error(typeof serverMessage === 'string' ? serverMessage : 'Order request failed.');
    error.status = Number(status) || 0;
    error.serverMessage = typeof serverMessage === 'string' ? serverMessage : '';
    return error;
}

function getOrderDetailsErrorMessage(error) {
    if (error && error.status === 401) return 'Your session has expired. Please sign in again.';
    if (error && error.status === 403) return 'You are not allowed to view this order.';
    if (error && error.status === 404) return 'Order not found or unavailable for this account.';
    if (error && error.status === 422) return error.serverMessage || 'The order ID is invalid.';
    if (error && error.status >= 500) return 'The order service is temporarily unavailable. Please retry.';
    if (error && error.message === 'Failed to fetch') return 'The order service could not be reached. Please retry.';
    return error && error.message ? error.message : 'Unable to load order details.';
}

function getOrderDetailsStateType(error) {
    if (error && error.status === 401) return 'stale';
    if (error && error.status === 403) return 'unauthorized';
    if (error && error.status === 404) return 'empty';
    return 'error';
}

function renderOrderDetails(data) {
    const order = data.order || {};
    const details = Array.isArray(data.details) ? data.details : [];
    const orderId = textValue(order.Order_ID, '-');
    const orderType = textValue(order.Order_Type, '');

    document.title = `QuickMart IOMS - Order ${orderId}`;
    setText('orderID', orderId);
    setText('printOrderReference', orderId);
    setText('orderDate', formatOrderDate(order.Order_Date));
    setText('staffName', order.Staff_Name || order.Staff_ID || '-');
    setText('customerName', order.Party_Name || '-');

    const orderTypeElement = document.getElementById('orderType');
    if (orderTypeElement) {
        orderTypeElement.classList.remove('order-type-sell', 'order-type-purchase');
        orderTypeElement.textContent = orderType ? `${orderType.toUpperCase()} ORDER` : '-';
        orderTypeElement.classList.add(orderType === 'Sell' ? 'order-type-sell' : 'order-type-purchase');
    }

    renderOrderItems(details);
    setText('itemCount', data.item_count ?? details.length);
    setText('grandTotalAmount', formatCurrency(data.total_amount));
}

function renderOrderItems(details) {
    const tableBody = document.getElementById('orderItemsBody');
    if (!tableBody) return;

    if (details.length === 0) {
        renderItemsState('No order items were returned.');
        return;
    }

    const fragment = document.createDocumentFragment();
    details.forEach(item => {
        const row = document.createElement('tr');
        appendCell(row, item.Product_Name || item.Product_ID || '-', 'product-name', 'Product');
        appendCell(row, item.Ordered_Qty ?? '-', 'qty-cell numeric-cell', 'Quantity');
        appendCell(row, `$${formatCurrency(item.Sold_Price)}`, 'price-cell money-cell', 'Stored unit price');
        appendCell(row, `$${formatCurrency(item.Line_Total)}`, 'total-cell money-cell', 'Line total');
        fragment.appendChild(row);
    });
    tableBody.replaceChildren(fragment);
}

function renderItemsState(message) {
    const tableBody = document.getElementById('orderItemsBody');
    if (!tableBody) return;
    const row = document.createElement('tr');
    row.className = 'details-table-state';
    const cell = document.createElement('td');
    cell.colSpan = 4;
    cell.textContent = textValue(message, 'Order items unavailable.');
    row.appendChild(cell);
    tableBody.replaceChildren(row);
}

function appendCell(row, value, className, label) {
    const cell = document.createElement('td');
    if (className) cell.className = className;
    if (label) cell.dataset.label = label;
    cell.textContent = textValue(value, '-');
    row.appendChild(cell);
}

function showOrderDetailsState(message, type, retry) {
    const state = document.getElementById('orderDetailsState');
    if (!state) return;
    state.className = `order-details-state order-details-state--${type || 'error'}`;
    state.replaceChildren();
    state.setAttribute('role', type === 'loading' ? 'status' : 'alert');

    if (type === 'loading') {
        const spinner = document.createElement('span');
        spinner.className = 'state-spinner';
        spinner.setAttribute('aria-hidden', 'true');
        state.appendChild(spinner);
    }

    const content = document.createElement('div');
    const title = document.createElement('strong');
    title.textContent = type === 'loading' ? 'Loading order details' : type === 'stale' ? 'Session needs attention' : type === 'unauthorized' ? 'Access restricted' : type === 'empty' ? 'Order unavailable' : 'Order details unavailable';
    const description = document.createElement('span');
    description.textContent = textValue(message, 'Unable to load this order.');
    content.append(title, description);
    state.appendChild(content);

    if (retry) {
        const retryButton = document.createElement('button');
        retryButton.type = 'button';
        retryButton.className = 'qm-button';
        retryButton.textContent = 'Retry';
        retryButton.addEventListener('click', () => {
            if (!orderDetailsRequestInFlight) loadOrderDetails();
        });
        state.appendChild(retryButton);
    }
    state.hidden = false;
}

function hideOrderDetailsState() {
    const state = document.getElementById('orderDetailsState');
    if (state) state.hidden = true;
}

function setText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = textValue(value, '-');
}

function textValue(value, fallback) {
    if (value === null || value === undefined || String(value) === '') return fallback;
    return String(value);
}

function formatCurrency(value) {
    const number = Number(value);
    return Number.isFinite(number) ? number.toFixed(2) : '0.00';
}

function formatOrderDate(value) {
    if (!value) return '-';
    const date = new Date(String(value).replace(' ', 'T'));
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
}
