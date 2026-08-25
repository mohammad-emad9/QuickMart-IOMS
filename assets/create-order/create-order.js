/**
 * Create-order workflow and invoice preview.
 */

let availableProducts = [];
let orderItems = [];
let selectedProducts = {};
let recentOrdersCache = [];
let recentOrderFilter = 'all';
let productsLoadInFlight = null;
let recentOrdersLoadInFlight = null;
let productsRequestVersion = 0;
let recentOrdersRequestVersion = 0;
let productsReloadQueued = false;
let recentOrdersReloadQueued = false;
let createOrderInFlight = false;
let productsLoadState = 'loading';
let rowCounter = 0;
let suggestionIndex = -1;
let reviewMode = 'draft';
let lastCreatedOrder = null;
const modalFocusReturn = new Map();

document.addEventListener('DOMContentLoaded', async function () {
    setupCreateOrderEvents();
    updateOrderTypeLabel();
    renderOrderTable();
    renderProductAvailabilityState('loading');
    renderModalProducts([]);

    const authenticated = await checkSession();
    if (!authenticated) {
        return;
    }

    loadUserInfo();
    loadProductsFromDatabase();
    requestRecentOrdersReload();
});

function setupCreateOrderEvents() {
    document.querySelectorAll('input[name="orderType"]').forEach((radio) => {
        radio.addEventListener('change', function () {
            updateOrderTypeLabel();
            reconcileSelectionsForOrderType();
            renderOrderTable();
            updateSelectedPreview();
            renderModalProducts(getFilteredModalProducts());
        });
    });

    document.getElementById('orderForm')?.addEventListener('submit', function (event) {
        event.preventDefault();
        showInvoicePreview();
    });

    const productSearch = document.getElementById('productSearch');
    productSearch?.addEventListener('input', orderDebounce(handleProductSearch, 180));
    productSearch?.addEventListener('focus', handleProductSearch);
    productSearch?.addEventListener('keydown', handleProductSearchKeydown);

    document.getElementById('addItemBtn')?.addEventListener('click', function () {
        if (typeof bootstrap === 'undefined') openModal('selectProductModal');
    });
    document.getElementById('addProductLink')?.addEventListener('click', function () {
        openModal('selectProductModal');
    });
    document.getElementById('selectProductModal')?.addEventListener('show.bs.modal', loadModalProducts);
    document.getElementById('modalProductSearch')?.addEventListener('input', orderDebounce(filterModalProducts, 180));
    document.getElementById('confirmAddProductsBtn')?.addEventListener('click', addSelectedProducts);
    document.getElementById('confirmOrderBtn')?.addEventListener('click', processFinalOrder);
    document.getElementById('printInvoiceBtn')?.addEventListener('click', printInvoice);
    document.getElementById('successInvoiceBtn')?.addEventListener('click', openConfirmedInvoicePreview);
    document.getElementById('newOrderBtn')?.addEventListener('click', startNewOrder);
    document.getElementById('cancelOrderBtn')?.addEventListener('click', function () {
        window.location.href = 'dashboard.php';
    });

    document.getElementById('customerName')?.addEventListener('input', function () {
        clearFieldError('customerName', 'customerNameError');
        clearOrderItemsError();
    });
    document.getElementById('customerName')?.addEventListener('blur', function () {
        if (this.value.trim()) clearFieldError('customerName', 'customerNameError');
    });

    document.getElementById('filterAllBtn')?.addEventListener('click', () => filterRecentOrders('all'));
    document.getElementById('filterSellBtn')?.addEventListener('click', () => filterRecentOrders('Sell'));
    document.getElementById('filterPurchaseBtn')?.addEventListener('click', () => filterRecentOrders('Purchase'));

    document.getElementById('selectProductModal')?.addEventListener('shown.bs.modal', function () {
        document.getElementById('modalProductSearch')?.focus();
    });
    document.getElementById('invoicePreviewModal')?.addEventListener('shown.bs.modal', function () {
        const focusTarget = reviewMode === 'confirmed' ? document.getElementById('printInvoiceBtn') : document.getElementById('confirmOrderBtn');
        focusTarget?.focus();
    });
    document.getElementById('successModal')?.addEventListener('shown.bs.modal', function () {
        document.getElementById('newOrderBtn')?.focus();
    });

    bindModalFocusReturn('selectProductModal');
    bindModalFocusReturn('invoicePreviewModal');
    bindModalFocusReturn('successModal');

    document.addEventListener('click', function (event) {
        const suggestions = document.getElementById('productSuggestions');
        const searchInput = document.getElementById('productSearch');
        if (suggestions && !suggestions.contains(event.target) && event.target !== searchInput) {
            closeProductSuggestions();
        }
    });
}

function bindModalFocusReturn(id) {
    const element = document.getElementById(id);
    if (!element) return;
    element.addEventListener('show.bs.modal', function () {
        modalFocusReturn.set(id, document.activeElement);
    });
    element.addEventListener('hidden.bs.modal', function () {
        const previous = modalFocusReturn.get(id);
        if (previous && document.contains(previous) && !previous.disabled) previous.focus();
        modalFocusReturn.delete(id);
    });
}

function openModal(id) {
    const element = document.getElementById(id);
    if (!element) return;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(element).show();
        return;
    }
    element.classList.add('show');
    element.style.display = 'block';
    element.removeAttribute('aria-hidden');
}

function hideModal(id) {
    const element = document.getElementById(id);
    if (!element) return;
    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        bootstrap.Modal.getInstance(element)?.hide();
        return;
    }
    element.classList.remove('show');
    element.style.display = 'none';
    element.setAttribute('aria-hidden', 'true');
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
    if (productsLoadInFlight) {
        productsReloadQueued = true;
        return productsLoadInFlight;
    }

    const requestVersion = ++productsRequestVersion;
    productsLoadState = 'loading';
    renderProductAvailabilityState('loading');
    renderModalProducts([]);

    const request = (async function () {
        try {
            const response = await apiFetch('products/list.php');
            const data = await readOrderApiResponse(response);
            if (requestVersion !== productsRequestVersion) return availableProducts;
            const products = data.data && Array.isArray(data.data.products) ? data.data.products : [];
            availableProducts = products.map(normalizeProduct).filter((product) => product.id);
            productsLoadState = availableProducts.length ? 'ready' : 'empty';
            reconcileSelectionsForOrderType();
            renderProductAvailabilityState(productsLoadState);
            renderProductSuggestionsForCurrentSearch();
            renderModalProducts(getFilteredModalProducts());
            updateSelectedPreview();
            return availableProducts;
        } catch (error) {
            if (requestVersion !== productsRequestVersion) return availableProducts;
            availableProducts = [];
            productsLoadState = 'error';
            renderProductAvailabilityState('error', getOrderApiErrorMessage(error, 'Unable to load products.'));
            renderProductSuggestionsForCurrentSearch();
            renderModalProducts([]);
            showWorkflowApiError(error, 'Unable to load products.');
            return [];
        } finally {
            if (productsLoadInFlight === request) {
                productsLoadInFlight = null;
                if (productsReloadQueued) {
                    productsReloadQueued = false;
                    loadProductsFromDatabase();
                }
            }
        }
    })();

    productsLoadInFlight = request;
    return request;
}

function normalizeProduct(product) {
    const available = Math.max(0, Number.parseInt(product && product.Quantity, 10) || 0);
    const threshold = Math.max(0, Number.parseInt(product && product.Threshold, 10) || 0);
    return {
        id: textValue(product && product.Product_ID, ''),
        name: textValue(product && product.Name, 'Unnamed product'),
        category: textValue(product && product.Category, 'Uncategorized'),
        available,
        price: safeNumber(product && product.Price),
        threshold,
        status: normalizeStockStatus(product && product.Status, available, threshold)
    };
}

function normalizeStockStatus(status, available, threshold) {
    const value = String(status || '').toLowerCase().replace(/[_-]+/g, ' ').trim();
    if (value === 'out of stock' || value === 'outofstock') return 'out';
    if (value === 'low stock' || value === 'lowstock') return 'low';
    if (value === 'normal') return 'normal';
    if (available <= 0) return 'out';
    if (available <= threshold) return 'low';
    return 'normal';
}

function stockStatusLabel(status) {
    if (status === 'out') return 'Out of stock';
    if (status === 'low') return 'Low stock';
    return 'Normal';
}

function renderProductAvailabilityState(state, message) {
    const element = document.getElementById('productAvailabilityState');
    if (!element) return;
    element.replaceChildren();
    element.dataset.state = state;

    const text = document.createElement('span');
    if (state === 'loading') text.textContent = 'Loading product availability…';
    else if (state === 'error') text.textContent = textValue(message, 'Unable to load products.');
    else if (state === 'empty') text.textContent = 'No products are available right now.';
    else text.textContent = `${availableProducts.length} products available in the catalog.`;
    element.appendChild(text);

    if (state === 'error') {
        const retry = document.createElement('button');
        retry.type = 'button';
        retry.className = 'btn btn-quiet action-button';
        retry.id = 'retryProductsBtn';
        retry.textContent = 'Retry';
        retry.addEventListener('click', loadProductsFromDatabase);
        element.appendChild(retry);
    }
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

    setRecentOrdersState('Loading recent orders…', 'muted');
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
            showWorkflowApiError(error, 'Unable to load recent orders.');
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
    document.querySelectorAll('#filterAllBtn, #filterSellBtn, #filterPurchaseBtn').forEach((button) => {
        const isActive = (filter === 'all' && button.id === 'filterAllBtn') ||
            (filter === 'Sell' && button.id === 'filterSellBtn') ||
            (filter === 'Purchase' && button.id === 'filterPurchaseBtn');
        button.classList.toggle('is-active', isActive);
        button.setAttribute('aria-pressed', String(isActive));
    });
    renderRecentOrders(getVisibleRecentOrders());
}

function getVisibleRecentOrders() {
    if (recentOrderFilter === 'all') return recentOrdersCache;
    return recentOrdersCache.filter((order) => order.Order_Type === recentOrderFilter);
}

function renderRecentOrders(orders) {
    const body = document.getElementById('recentOrdersTable');
    if (!body) return;
    if (!orders.length) {
        setRecentOrdersState(recentOrdersCache.length ? 'No orders match this filter.' : 'No orders have been created yet.', 'muted');
        return;
    }

    const fragment = document.createDocumentFragment();
    orders.forEach((order) => fragment.appendChild(createRecentOrderRow(order)));
    body.replaceChildren(fragment);
}

function createRecentOrderRow(order) {
    const row = document.createElement('tr');
    appendCell(row, order.Order_ID, 'order-id-cell numeric-value', 'Order ID');
    appendCell(row, order.Staff_Name || order.Staff_ID, 'muted-cell', 'Staff');
    appendCell(row, order.Party_Name, '', 'Customer / supplier');
    appendCell(row, order.Item_Count ?? 0, 'numeric-value', 'Items');
    appendCurrencyCell(row, order.Total_Amount, 'Amount');

    const typeCell = document.createElement('td');
    typeCell.dataset.label = 'Type';
    const type = textValue(order.Order_Type, '—');
    const typeBadge = document.createElement('span');
    typeBadge.className = `order-type-badge ${type === 'Sell' ? 'order-type-badge-sell' : 'order-type-badge-purchase'}`;
    typeBadge.textContent = type;
    typeCell.appendChild(typeBadge);
    row.appendChild(typeCell);

    appendCell(row, formatOrderDate(order.Order_Date), 'muted-cell numeric-value', 'Date');

    const actionCell = document.createElement('td');
    actionCell.dataset.label = 'Action';
    const link = document.createElement('a');
    link.className = 'view-order-link';
    link.href = `${viewPath('order-details.php')}?id=${encodeURIComponent(textValue(order.Order_ID, ''))}`;
    link.setAttribute('aria-label', `View order ${textValue(order.Order_ID, '')}`);
    link.append(createIcon('eye'), document.createTextNode('View'));
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
    cell.className = 'table-state-cell';
    cell.dataset.state = color || 'muted';
    cell.textContent = textValue(message, '');
    if (retry) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-quiet action-button';
        button.textContent = 'Retry';
        button.addEventListener('click', requestRecentOrdersReload);
        cell.appendChild(document.createTextNode(' '));
        cell.appendChild(button);
    }
    row.appendChild(cell);
    body.replaceChildren(row);
}

function updateOrderTypeLabel() {
    const isSell = isSellOrder();
    setText('customerLabel', isSell ? 'Customer' : 'Supplier');
    setText('customerLabelText', isSell ? 'Customer' : 'Supplier');
    setText('workflowStatus', orderItems.length ? `${orderItems.length} ${orderItems.length === 1 ? 'line' : 'lines'} ready for review` : 'Ready for products');
}

function handleProductSearch() {
    renderProductSuggestionsForCurrentSearch();
}

function renderProductSuggestionsForCurrentSearch() {
    const input = document.getElementById('productSearch');
    if (!input) return;
    const term = input.value.trim().toLowerCase();
    if (!term) {
        closeProductSuggestions();
        return;
    }
    if (productsLoadState === 'loading') {
        renderProductSuggestionsState('Loading product availability…');
        openProductSuggestions();
        return;
    }
    if (productsLoadState === 'error') {
        renderProductSuggestionsState('Product availability is unavailable. Use Retry above.');
        openProductSuggestions();
        return;
    }
    const products = availableProducts.filter((product) => product.name.toLowerCase().includes(term) || product.category.toLowerCase().includes(term) || product.id.toLowerCase().includes(term));
    renderProductSuggestions(products, term);
    openProductSuggestions();
}

function renderProductSuggestions(products, term) {
    const suggestions = document.getElementById('productSuggestions');
    if (!suggestions) return;
    suggestions.replaceChildren();
    suggestionIndex = -1;
    if (!products.length) {
        renderProductSuggestionsState(`No products found for “${term}”.`);
        return;
    }

    const fragment = document.createDocumentFragment();
    products.forEach((product) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'suggestion-item';
        button.setAttribute('role', 'option');
        button.setAttribute('aria-selected', 'false');
        button.dataset.productId = product.id;
        const unavailableForSell = isSellOrder() && product.available < 1;
        button.disabled = unavailableForSell;
        if (unavailableForSell) button.title = 'Unavailable for a Sell order';
        button.addEventListener('click', () => addProductToOrder(product.id));

        const copy = document.createElement('span');
        copy.className = 'suggestion-copy';
        const title = document.createElement('span');
        title.className = 'suggestion-title';
        title.textContent = product.name;
        const meta = document.createElement('span');
        meta.className = 'suggestion-meta';
        meta.textContent = `${product.category} • ${product.available} available · ${stockStatusLabel(product.status)}`;
        copy.append(title, meta);

        const price = document.createElement('span');
        price.className = 'suggestion-price currency-value has-value';
        price.textContent = formatCurrency(product.price);
        button.append(copy, price);
        fragment.appendChild(button);
    });
    suggestions.appendChild(fragment);
}

function renderProductSuggestionsState(message) {
    const suggestions = document.getElementById('productSuggestions');
    if (!suggestions) return;
    suggestions.replaceChildren();
    const state = document.createElement('div');
    state.className = 'suggestion-item';
    state.textContent = textValue(message, 'No products found.');
    suggestions.appendChild(state);
    suggestionIndex = -1;
}

function openProductSuggestions() {
    const suggestions = document.getElementById('productSuggestions');
    const input = document.getElementById('productSearch');
    if (!suggestions || !input) return;
    suggestions.hidden = false;
    input.setAttribute('aria-expanded', 'true');
}

function closeProductSuggestions() {
    const suggestions = document.getElementById('productSuggestions');
    const input = document.getElementById('productSearch');
    if (suggestions) suggestions.hidden = true;
    if (input) input.setAttribute('aria-expanded', 'false');
    suggestionIndex = -1;
}

function handleProductSearchKeydown(event) {
    const items = Array.from(document.querySelectorAll('#productSuggestions .suggestion-item:not(:disabled)[data-product-id]'));
    if (event.key === 'Escape') {
        closeProductSuggestions();
        return;
    }
    if (!items.length) return;
    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        suggestionIndex = event.key === 'ArrowDown' ? (suggestionIndex + 1) % items.length : (suggestionIndex - 1 + items.length) % items.length;
        items.forEach((item, index) => item.classList.toggle('is-active', index === suggestionIndex));
        items[suggestionIndex].scrollIntoView({ block: 'nearest' });
    } else if (event.key === 'Enter' && suggestionIndex >= 0) {
        event.preventDefault();
        items[suggestionIndex].click();
    }
}

function addProductToOrder(productId) {
    const product = availableProducts.find((item) => item.id === productId);
    if (!product) return;
    if (isSellOrder() && product.available < 1) {
        showToast('This product is out of stock for a Sell order.', 'error');
        return;
    }

    const existing = orderItems.find((item) => item.id === productId);
    if (existing) {
        if (isSellOrder() && existing.quantity >= existing.available) {
            showToast('The selected quantity already reaches available stock.', 'error');
            return;
        }
        existing.quantity += 1;
    } else {
        rowCounter += 1;
        orderItems.push({ ...product, rowId: rowCounter, quantity: 1 });
    }
    clearOrderItemsError();
    renderOrderTable();
    const input = document.getElementById('productSearch');
    if (input) input.value = '';
    closeProductSuggestions();
}

function renderOrderTable() {
    const body = document.getElementById('orderTableBody');
    const empty = document.getElementById('emptyMessage');
    if (!body) return;
    body.replaceChildren();
    if (!orderItems.length) {
        if (empty) empty.hidden = false;
        setText('cartItemCount', '0 items');
        setCurrencyText('grandTotalDisplay', '', false);
        const addLink = document.getElementById('addProductLink');
        if (addLink) addLink.hidden = true;
        updateOrderTypeLabel();
        return;
    }

    if (empty) empty.hidden = true;
    const addLink = document.getElementById('addProductLink');
    if (addLink) addLink.hidden = false;
    setText('cartItemCount', `${orderItems.length} ${orderItems.length === 1 ? 'item' : 'items'}`);

    const fragment = document.createDocumentFragment();
    orderItems.forEach((item) => {
        const row = document.createElement('tr');
        row.id = `row-${item.rowId}`;

        const productCell = document.createElement('td');
        productCell.className = 'product-cell';
        productCell.dataset.label = 'Product';
        productCell.textContent = item.name;
        const productSubline = document.createElement('span');
        productSubline.className = 'product-subline';
        productSubline.textContent = `${item.id} · ${item.category}`;
        productCell.appendChild(productSubline);
        row.appendChild(productCell);

        appendCell(row, `${item.available} units`, 'available-cell numeric-value', 'Available');
        appendCurrencyCell(row, item.price, 'Unit price', 'price-cell');

        const quantityCell = document.createElement('td');
        quantityCell.dataset.label = 'Quantity';
        quantityCell.appendChild(createQuantityControl(item));
        row.appendChild(quantityCell);

        appendCurrencyCell(row, null, 'Line total after confirmation', 'total-cell');

        const actionCell = document.createElement('td');
        actionCell.dataset.label = 'Actions';
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'remove-btn';
        removeButton.setAttribute('aria-label', `Remove ${item.name} from order`);
        removeButton.append(createIcon('trash'), document.createTextNode('Remove'));
        removeButton.addEventListener('click', () => removeFromOrder(item.rowId));
        actionCell.appendChild(removeButton);
        row.appendChild(actionCell);
        fragment.appendChild(row);
    });
    body.appendChild(fragment);
    clearPendingDraftTotal();
    clearOrderItemsError();
    updateOrderTypeLabel();
}

function createQuantityControl(item) {
    const wrapper = document.createElement('div');
    wrapper.className = 'quantity-control';

    const decrease = document.createElement('button');
    decrease.type = 'button';
    decrease.className = 'quantity-button';
    decrease.setAttribute('aria-label', `Decrease quantity for ${item.name}`);
    decrease.textContent = '−';
    decrease.addEventListener('click', () => {
        if (item.quantity <= 1) return;
        item.quantity -= 1;
        renderOrderTable();
    });

    const input = document.createElement('input');
    input.type = 'number';
    input.className = 'quantity-input qty-input';
    input.min = '1';
    if (isSellOrder()) input.max = String(item.available);
    input.value = String(item.quantity);
    input.inputMode = 'numeric';
    input.setAttribute('aria-label', `Quantity for ${item.name}`);
    input.addEventListener('change', (event) => updateQuantity(item.rowId, event.target.value));

    const increase = document.createElement('button');
    increase.type = 'button';
    increase.className = 'quantity-button';
    increase.setAttribute('aria-label', `Increase quantity for ${item.name}`);
    increase.textContent = '+';
    increase.addEventListener('click', () => {
        if (isSellOrder() && item.quantity >= item.available) {
            showToast('Quantity cannot exceed available stock.', 'error');
            return;
        }
        item.quantity += 1;
        renderOrderTable();
    });

    wrapper.append(decrease, input, increase);
    return wrapper;
}

function updateQuantity(rowId, value) {
    const item = orderItems.find((entry) => entry.rowId === rowId);
    if (!item) return;
    const quantity = Number(value);
    if (!Number.isInteger(quantity) || quantity < 1) {
        showToast('Quantity must be a whole number greater than zero.', 'error');
        renderOrderTable();
        return;
    }
    if (isSellOrder() && quantity > item.available) {
        showToast('Quantity cannot exceed available stock.', 'error');
        item.quantity = item.available;
        if (item.quantity < 1) removeFromOrder(rowId);
        else renderOrderTable();
        return;
    }
    item.quantity = quantity;
    renderOrderTable();
}

function removeFromOrder(rowId) {
    orderItems = orderItems.filter((item) => item.rowId !== rowId);
    renderOrderTable();
}

function calculateEstimatedTotal() {
    clearPendingDraftTotal();
}

function clearPendingDraftTotal() {
    setCurrencyText('grandTotalDisplay', '', false);
}

function validateDraft() {
    clearFieldError('customerName', 'customerNameError');
    clearOrderItemsError();
    let valid = true;
    const partyName = document.getElementById('customerName')?.value.trim() || '';

    if (!partyName) {
        setFieldError('customerName', 'customerNameError', `Enter a ${isSellOrder() ? 'customer' : 'supplier'} name.`);
        valid = false;
    }
    if (!orderItems.length) {
        setOrderItemsError('Add at least one product before reviewing the order.');
        valid = false;
    }
    if (isSellOrder() && orderItems.some((item) => item.quantity < 1 || item.quantity > item.available)) {
        setOrderItemsError('One or more quantities exceed the latest available stock. Adjust the cart and retry.');
        valid = false;
    }
    return valid;
}

function showInvoicePreview() {
    clearWorkflowAlert();
    if (!validateDraft()) {
        showToast('Review the highlighted order fields before continuing.', 'error');
        return;
    }
    reviewMode = 'draft';
    renderDraftReview();
    openModal('invoicePreviewModal');
}

function renderDraftReview() {
    setText('reviewModeLabel', 'Final review');
    setText('orderReviewModalTitle', 'Review this order');
    setText('prevOrderNumber', '—');
    setText('prevStaffId', '—');
    setText('prevCustomerName', document.getElementById('customerName')?.value.trim() || '—');
    setText('prevOrderType', getOrderType());
    setText('prevDate', formatOrderDate(new Date().toISOString()));
    renderReviewItems(orderItems, false);
    setCurrencyText('prevGrandTotal', '', false);
    setText('reviewAuthorityNote', 'The backend will confirm stored prices, stock, line totals, and the final total when the order is submitted.');
    setReviewStatus('', '');
    setReviewControls('draft');
}

function renderConfirmedInvoice() {
    if (!lastCreatedOrder) return;
    reviewMode = 'confirmed';
    setText('reviewModeLabel', 'Confirmed invoice');
    setText('orderReviewModalTitle', 'Invoice preview');
    setText('prevOrderNumber', textValue(lastCreatedOrder.order_id, '—'));
    setText('prevStaffId', textValue(lastCreatedOrder.staff_id, '—'));
    setText('prevCustomerName', lastCreatedOrder.party_name || '—');
    setText('prevOrderType', lastCreatedOrder.order_type || '—');
    setText('prevDate', formatOrderDate(new Date().toISOString()));
    const items = Array.isArray(lastCreatedOrder.items) ? lastCreatedOrder.items : [];
    renderReviewItems(items, true);
    setCurrencyText('prevGrandTotal', formatCurrency(lastCreatedOrder.total_amount), true);
    setText('reviewAuthorityNote', 'Confirmed order values returned by the backend. This invoice preview is ready to print.');
    setReviewStatus('Backend-confirmed values', 'success');
    setReviewControls('confirmed');
}

function renderReviewItems(items, confirmed) {
    const body = document.getElementById('prevItemsBody');
    if (!body) return;
    body.replaceChildren();
    if (!items.length) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 4;
        cell.className = 'table-state-cell';
        cell.textContent = 'No items to display.';
        row.appendChild(cell);
        body.appendChild(row);
        return;
    }
    const fragment = document.createDocumentFragment();
    items.forEach((item) => {
        const row = document.createElement('tr');
        appendCell(row, confirmed ? item.product_name : item.name, '', 'Product');
        appendCell(row, item.quantity, 'numeric-value', 'Quantity');
        appendCurrencyCell(row, confirmed ? item.price : item.price, 'Unit price');
        appendCurrencyCell(row, confirmed ? item.line_total : null, 'Line total after confirmation');
        fragment.appendChild(row);
    });
    body.appendChild(fragment);
}

function setReviewControls(mode) {
    const editButton = document.getElementById('editOrderBtn');
    const confirmButton = document.getElementById('confirmOrderBtn');
    const printButton = document.getElementById('printInvoiceBtn');
    const title = document.getElementById('reviewTotalRow');
    if (editButton) editButton.hidden = mode === 'confirmed';
    if (confirmButton) confirmButton.hidden = mode === 'confirmed';
    if (printButton) printButton.hidden = mode !== 'confirmed';
    if (title) title.hidden = false;
}

async function processFinalOrder() {
    if (createOrderInFlight || reviewMode !== 'draft') return;
    if (!validateDraft()) {
        showReviewError('Review the highlighted order fields before confirming.');
        return;
    }

    createOrderInFlight = true;
    const button = document.getElementById('confirmOrderBtn');
    setButtonBusy(button, true, 'Submitting…');
    setReviewStatus('Submitting order…', 'info');

    try {
        const response = await apiFetch('orders/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                order_type: getOrderType(),
                party_name: document.getElementById('customerName')?.value.trim() || '',
                items: orderItems.map((item) => ({ product_id: item.id, quantity: item.quantity }))
            })
        });
        const data = await readOrderApiResponse(response);
        const created = data.data;
        if (!created || !textValue(created.order_id, '') || !Array.isArray(created.items)) {
            throw new Error('The backend did not return the created order.');
        }

        lastCreatedOrder = created;
        hideModal('invoicePreviewModal');
        resetOrderForm(true);
        renderSuccess(created);
        openModal('successModal');
        loadProductsFromDatabase();
        requestRecentOrdersReload();
    } catch (error) {
        const message = isInsufficientStockError(error)
            ? 'Stock changed while you were reviewing. Refresh the catalog and adjust the quantity.'
            : getOrderApiErrorMessage(error, 'Order could not be created. Please retry.');
        showReviewError(message);
        showWorkflowApiError(error, message);
        openModal('invoicePreviewModal');
    } finally {
        createOrderInFlight = false;
        setButtonBusy(button, false, 'Confirm order');
    }
}

function isInsufficientStockError(error) {
    const message = `${error?.serverMessage || ''} ${error?.message || ''}`;
    return error?.status === 422 && /(stock|available|requested|quantity)/i.test(message);
}

function renderSuccess(created) {
    setText('orderNumber', textValue(created.order_id, '—'));
    setText('successOrderType', textValue(created.order_type, '—'));
    setText('successPartyName', textValue(created.party_name, '—'));
    setText('confirmItemsCount', Array.isArray(created.items) ? created.items.length : '—');
    setCurrencyText('orderTotal', created.total_amount, created.total_amount !== null && created.total_amount !== undefined && created.total_amount !== '');
    renderConfirmedItems(created.items);
}

function renderConfirmedItems(items) {
    const body = document.getElementById('confirmItemsBody');
    if (!body) return;
    body.replaceChildren();
    if (!Array.isArray(items) || !items.length) return;
    const fragment = document.createDocumentFragment();
    items.forEach((item) => {
        const row = document.createElement('tr');
        appendCell(row, item.product_name, '', 'Product');
        appendCell(row, item.quantity, 'numeric-value', 'Quantity');
        appendCurrencyCell(row, item.price, 'Unit price');
        appendCurrencyCell(row, item.line_total, 'Line total');
        fragment.appendChild(row);
    });
    body.appendChild(fragment);
}

function startNewOrder() {
    const successModal = document.getElementById('successModal');
    lastCreatedOrder = null;
    resetOrderForm(false);
    const focusNewOrder = function () {
        window.requestAnimationFrame(() => document.getElementById('customerName')?.focus());
    };
    if (successModal?.classList.contains('show')) {
        successModal.addEventListener('hidden.bs.modal', focusNewOrder, { once: true });
        hideModal('successModal');
    } else {
        focusNewOrder();
    }
}

function resetOrderForm(preserveConfirmed) {
    orderItems = [];
    selectedProducts = {};
    rowCounter = 0;
    const customerName = document.getElementById('customerName');
    if (customerName) customerName.value = '';
    document.getElementById('productSearch')?.replaceChildren();
    const search = document.getElementById('productSearch');
    if (search) search.value = '';
    const sell = document.getElementById('sellOrder');
    if (sell) sell.checked = true;
    clearFieldError('customerName', 'customerNameError');
    clearOrderItemsError();
    updateOrderTypeLabel();
    renderOrderTable();
    closeProductSuggestions();
    setReviewStatus('', '');
    if (!preserveConfirmed) {
        reviewMode = 'draft';
        setReviewControls('draft');
        const confirmedBody = document.getElementById('confirmItemsBody');
        confirmedBody?.replaceChildren();
        setText('orderNumber', '—');
        setText('successOrderType', '—');
        setText('successPartyName', '—');
        setText('confirmItemsCount', '—');
        setCurrencyText('orderTotal', '', false);
    }
}

function openConfirmedInvoicePreview() {
    if (!lastCreatedOrder) return;
    const successModal = document.getElementById('successModal');
    const showInvoice = function () {
        renderConfirmedInvoice();
        openModal('invoicePreviewModal');
    };
    if (successModal?.classList.contains('show')) {
        successModal.addEventListener('hidden.bs.modal', showInvoice, { once: true });
        hideModal('successModal');
    } else {
        showInvoice();
    }
}

function printInvoice() {
    if (typeof window.print === 'function') window.print();
}

function loadModalProducts() {
    selectedProducts = {};
    const search = document.getElementById('modalProductSearch');
    if (search) search.value = '';
    renderModalProducts(getFilteredModalProducts());
    updateSelectedCount();
    updateSelectedPreview();
    if (productsLoadState === 'loading') loadProductsFromDatabase();
}

function filterModalProducts() {
    renderModalProducts(getFilteredModalProducts());
}

function getFilteredModalProducts() {
    const term = document.getElementById('modalProductSearch')?.value.trim().toLowerCase() || '';
    if (!term) return availableProducts;
    return availableProducts.filter((product) => product.name.toLowerCase().includes(term) || product.id.toLowerCase().includes(term) || product.category.toLowerCase().includes(term));
}

function renderModalProducts(products) {
    const body = document.getElementById('modalProductsBody');
    if (!body) return;
    body.replaceChildren();
    if (productsLoadState === 'loading') {
        appendTableState(body, 5, 'Loading product availability…');
        return;
    }
    if (!products.length) {
        appendTableState(body, 5, availableProducts.length ? 'No products match this search.' : 'No products are available.');
        return;
    }

    const fragment = document.createDocumentFragment();
    products.forEach((product) => {
        const selected = selectedProducts[product.id];
        const disabled = isSellOrder() && product.available < 1;
        const row = document.createElement('tr');
        if (selected) row.classList.add('is-selected');
        if (disabled) row.classList.add('is-disabled');

        const checkboxCell = document.createElement('td');
        checkboxCell.dataset.label = 'Select';
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'form-check-input product-checkbox';
        checkbox.checked = Boolean(selected);
        checkbox.disabled = disabled;
        checkbox.dataset.id = product.id;
        checkbox.setAttribute('aria-label', `Select ${product.name}`);
        checkbox.addEventListener('change', (event) => toggleProductSelection(event.target));
        checkboxCell.appendChild(checkbox);
        row.appendChild(checkboxCell);

        const productCell = document.createElement('td');
        productCell.dataset.label = 'Product';
        const name = document.createElement('strong');
        name.className = 'modal-product-name';
        name.textContent = product.name;
        const id = document.createElement('small');
        id.className = 'modal-product-id numeric-value';
        id.textContent = product.id;
        productCell.append(name, id);
        row.appendChild(productCell);

        appendCell(row, product.category, '', 'Category');
        const availableCell = document.createElement('td');
        availableCell.dataset.label = 'Available';
        availableCell.className = 'numeric-value';
        availableCell.textContent = String(product.available);
        const badge = document.createElement('span');
        badge.className = `stock-status stock-status-${product.status}`;
        badge.textContent = stockStatusLabel(product.status);
        availableCell.appendChild(document.createTextNode(' '));
        availableCell.appendChild(badge);
        row.appendChild(availableCell);
        appendCurrencyCell(row, product.price, 'Unit price');
        fragment.appendChild(row);
    });
    body.appendChild(fragment);
}

function toggleProductSelection(checkbox) {
    const product = availableProducts.find((item) => item.id === checkbox.dataset.id);
    if (!product) return;
    if (checkbox.checked) selectedProducts[product.id] = { product, quantity: 1 };
    else delete selectedProducts[product.id];
    checkbox.closest('tr')?.classList.toggle('is-selected', checkbox.checked);
    updateSelectedCount();
    updateSelectedPreview();
}

function updateSelectedCount() {
    const count = Object.keys(selectedProducts).length;
    setText('selectedCount', `${count} ${count === 1 ? 'product' : 'products'} selected`);
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
    selections.forEach((selection) => {
        const product = selection.product;
        const wrapper = document.createElement('div');
        wrapper.className = 'selected-product-row';
        const label = document.createElement('strong');
        label.className = 'selected-product-name';
        label.textContent = product.name;
        const controls = document.createElement('label');
        controls.className = 'selected-product-quantity';
        const controlsText = document.createElement('span');
        controlsText.textContent = 'Quantity';
        const quantity = document.createElement('input');
        quantity.type = 'number';
        quantity.min = '1';
        if (isSellOrder()) quantity.max = String(product.available);
        quantity.className = 'numeric-value';
        quantity.value = String(selection.quantity);
        quantity.setAttribute('aria-label', `Quantity for ${product.name}`);
        quantity.addEventListener('change', (event) => {
            const value = Number(event.target.value);
            if (!Number.isInteger(value) || value < 1 || (isSellOrder() && value > product.available)) {
                showToast('Choose a valid quantity within available stock.', 'error');
                event.target.value = String(selection.quantity);
                return;
            }
            selection.quantity = value;
        });
        controls.append(controlsText, quantity);
        wrapper.append(label, controls);
        list.appendChild(wrapper);
    });
}

function addSelectedProducts() {
    const selections = Object.values(selectedProducts);
    if (!selections.length) {
        showToast('Select at least one product.', 'error');
        return;
    }
    selections.forEach((selection) => {
        const product = selection.product;
        const existing = orderItems.find((item) => item.id === product.id);
        if (existing) {
            const requested = existing.quantity + selection.quantity;
            if (isSellOrder() && requested > existing.available) {
                existing.quantity = existing.available;
                showToast(`${product.name} is limited to available stock.`, 'error');
            } else {
                existing.quantity = requested;
            }
        } else {
            rowCounter += 1;
            orderItems.push({ ...product, rowId: rowCounter, quantity: selection.quantity });
        }
    });
    selectedProducts = {};
    updateSelectedCount();
    updateSelectedPreview();
    renderOrderTable();
    hideModal('selectProductModal');
}

function reconcileSelectionsForOrderType() {
    if (isSellOrder()) {
        Object.keys(selectedProducts).forEach((id) => {
            const selection = selectedProducts[id];
            if (!selection || selection.product.available < 1) delete selectedProducts[id];
            else selection.quantity = Math.min(selection.quantity, selection.product.available);
        });
        orderItems = orderItems.filter((item) => item.available > 0);
        orderItems.forEach((item) => {
            item.quantity = Math.min(item.quantity, item.available);
        });
    }
}

function getOrderType() {
    return document.getElementById('sellOrder')?.checked ? 'Sell' : 'Purchase';
}

function isSellOrder() {
    return getOrderType() === 'Sell';
}

function setFieldError(inputId, errorId, message) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    input?.classList.add('is-invalid');
    input?.setAttribute('aria-invalid', 'true');
    if (error) {
        error.hidden = false;
        error.textContent = textValue(message, 'Please review this field.');
    }
}

function clearFieldError(inputId, errorId) {
    const input = document.getElementById(inputId);
    const error = document.getElementById(errorId);
    input?.classList.remove('is-invalid');
    input?.setAttribute('aria-invalid', 'false');
    if (error) {
        error.hidden = true;
        error.textContent = '';
    }
}

function setOrderItemsError(message) {
    const error = document.getElementById('orderItemsError');
    if (!error) return;
    error.hidden = false;
    error.textContent = textValue(message, 'Review the selected products.');
}

function clearOrderItemsError() {
    const error = document.getElementById('orderItemsError');
    if (!error) return;
    error.hidden = true;
    error.textContent = '';
}

function showReviewError(message) {
    setReviewStatus(message, 'error');
    showToast(message, 'error');
}

function setReviewStatus(message, state) {
    const status = document.getElementById('reviewStatus');
    if (!status) return;
    status.hidden = !message;
    status.dataset.state = state || '';
    status.textContent = textValue(message, '');
}

function showWorkflowApiError(error, fallback) {
    const message = getOrderApiErrorMessage(error, fallback);
    const state = error?.status === 401 || error?.status === 403 || error?.status >= 400 ? 'error' : 'info';
    showWorkflowAlert(message, state);
    if (error?.status === 401) setText('workflowStatus', 'Session needs attention');
}

function showWorkflowAlert(message, state) {
    const alert = document.getElementById('workflowAlert');
    if (!alert) return;
    alert.hidden = !message;
    alert.dataset.state = state || 'info';
    alert.textContent = textValue(message, '');
}

function clearWorkflowAlert() {
    const alert = document.getElementById('workflowAlert');
    if (!alert) return;
    alert.hidden = true;
    alert.textContent = '';
    alert.dataset.state = '';
}

function setButtonBusy(button, busy, label) {
    if (!button) return;
    button.disabled = busy;
    button.setAttribute('aria-busy', String(busy));
    const labelElement = document.getElementById('confirmOrderLabel');
    if (button.id === 'confirmOrderBtn' && labelElement) labelElement.textContent = label;
}

function appendCell(row, value, className, label) {
    const cell = document.createElement('td');
    if (className) cell.className = className;
    if (label) cell.dataset.label = label;
    cell.textContent = textValue(value, '—');
    row.appendChild(cell);
    return cell;
}

function appendCurrencyCell(row, value, label, className) {
    const cell = appendCell(row, value === null || value === undefined || value === '' ? '—' : formatCurrency(value), `${className || ''} currency-value${value === null || value === undefined || value === '' ? '' : ' has-value'}`.trim(), label);
    return cell;
}

function appendTableState(body, colSpan, message) {
    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = colSpan;
    cell.className = 'table-state-cell';
    cell.textContent = textValue(message, '');
    row.appendChild(cell);
    body.appendChild(row);
}

function setText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = textValue(value, '—');
}

function setCurrencyText(id, value, hasValue) {
    const element = document.getElementById(id);
    if (!element) return;
    element.classList.toggle('has-value', Boolean(hasValue));
    element.textContent = hasValue ? formatCurrency(value) : '—';
}

function createIcon(name) {
    const paths = {
        eye: ['M3 12s3.2-5 9-5 9 5 9 5-3.2 5-9 5-9-5-9-5Z', 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z'],
        trash: ['M5 7h14M10 11v6M14 11v6M9 7V4h6v3M7 7l1 13h8l1-13']
    };
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '1.8');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');
    (paths[name] || []).forEach((pathData) => {
        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('d', pathData);
        svg.appendChild(path);
    });
    return svg;
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
    if (!value) return '—';
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

function orderDebounce(callback, wait) {
    let timeout;
    return function (...args) {
        window.clearTimeout(timeout);
        timeout = window.setTimeout(() => callback.apply(this, args), wait);
    };
}
