/* QuickMart IOMS - Dashboard */

(function () {
    'use strict';

    class DashboardApiError extends Error {
        constructor(status, message) {
            super(message);
            this.name = 'DashboardApiError';
            this.status = status;
        }
    }

    const dashboardState = {
        dashboardPromise: null,
        dashboardGeneration: 0,
        productsPromise: null,
        productsGeneration: 0,
        products: null,
        ordersPromise: null,
        ordersGeneration: 0,
        orders: null,
        reportPromise: null,
        reportGeneration: 0,
        report: null,
        activeOrderFilter: 'all',
        detailController: null,
        detailGeneration: 0,
        retryQueued: false,
        redirected: false
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
            element.textContent = text;
        }
        return element;
    }

    const iconDefinitions = {
        alert: [
            ['path', {
                d: 'M10.3 3.4 2.9 19a2 2 0 0 0 1.7 3h14.8a2 2 0 0 0 1.7-3L13.7 3.4a2 2 0 0 0-3.4 0Z'
            }],
            ['path', { d: 'M12 8v4m0 4h.01' }]
        ],
        box: [
            ['path', { d: 'm12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z' }],
            ['path', { d: 'm4.4 7.7 7.6 4.4 7.6-4.4M12 12.1V21' }]
        ],
        eye: [
            ['path', { d: 'M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z' }],
            ['circle', { cx: '12', cy: '12', r: '2.5' }]
        ],
        inbox: [
            ['path', { d: 'M4 5h16v14H4z' }],
            ['path', { d: 'M4 14h4l1.5 2h5L16 14h4' }]
        ],
        loader: [
            ['circle', { cx: '12', cy: '12', r: '8' }]
        ]
    };

    function createIcon(name, className = '') {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '1.8');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');
        svg.setAttribute('aria-hidden', 'true');
        svg.setAttribute('class', 'dashboard-icon' + (className ? ' ' + className : ''));

        const elements = iconDefinitions[name] || iconDefinitions.inbox;
        elements.forEach(([tagName, attributes]) => {
            const child = document.createElementNS('http://www.w3.org/2000/svg', tagName);
            Object.entries(attributes).forEach(([attribute, value]) => {
                child.setAttribute(attribute, value);
            });
            svg.appendChild(child);
        });

        if (name === 'loader') {
            svg.classList.add('dashboard-icon-loader');
        }

        return svg;
    }

    function setText(id, value) {
        const element = byId(id);
        if (element) {
            element.textContent = value;
        }
    }

    function valueFrom(source, keys) {
        if (!source || typeof source !== 'object') {
            return undefined;
        }

        for (const key of keys) {
            if (Object.prototype.hasOwnProperty.call(source, key) && source[key] !== null && source[key] !== undefined) {
                return source[key];
            }
        }

        return undefined;
    }

    function displayText(value, fallback = '—') {
        if (typeof value === 'string') {
            const trimmed = value.trim();
            return trimmed === '' ? fallback : trimmed;
        }

        if (typeof value === 'number' && Number.isFinite(value)) {
            return String(value);
        }

        return fallback;
    }

    function numericValue(value) {
        if (typeof value === 'number') {
            return Number.isFinite(value) ? value : null;
        }

        if (typeof value === 'string' && value.trim() !== '') {
            const parsed = Number(value);
            return Number.isFinite(parsed) ? parsed : null;
        }

        return null;
    }

    function numberFrom(source, keys) {
        return numericValue(valueFrom(source, keys));
    }

    function formatInteger(value) {
        const number = numericValue(value);
        return number === null ? '—' : Math.round(number).toLocaleString('en-US');
    }

    function formatCurrency(value) {
        const number = numericValue(value);
        return number === null ? '—' : `$${number.toFixed(2)}`;
    }

    function formatDate(value) {
        if (typeof value !== 'string' && typeof value !== 'number') {
            return '—';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '—';
        }

        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function extractArray(source, keys) {
        if (Array.isArray(source)) {
            return source;
        }

        if (!source || typeof source !== 'object') {
            return [];
        }

        for (const key of keys) {
            if (Array.isArray(source[key])) {
                return source[key];
            }
        }

        return [];
    }

    function getRoleHint() {
        const role = sessionStorage.getItem('userRole');
        return role === 'Admin' || role === 'Manager' || role === 'Staff' ? role : null;
    }

    function handleUnauthorized() {
        if (dashboardState.redirected) {
            return;
        }

        dashboardState.redirected = true;
        sessionStorage.clear();
        window.location.href = typeof loginPath === 'function'
            ? loginPath()
            : '../../assets/login-signup/login.html';
    }

    function messageForStatus(status, fallback) {
        if (status === 403) {
            return 'You are not authorized to view this data.';
        }
        if (status === 404) {
            return 'The requested resource was not found.';
        }
        if (status === 422) {
            return 'The request could not be validated.';
        }
        if (status === 409) {
            return 'The request conflicts with current data.';
        }
        if (status >= 500) {
            return 'The server could not load this data. Please try again.';
        }
        if (status === 0) {
            return 'The server could not be reached. Please try again.';
        }
        return fallback || 'The data could not be loaded. Please try again.';
    }

    async function requestJson(path, signal) {
        let response;
        try {
            response = await apiFetch(path, signal ? { signal } : {});
        } catch (error) {
            if (error && error.name === 'AbortError') {
                throw error;
            }
            throw new DashboardApiError(0, messageForStatus(0));
        }

        let payload = null;
        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (response.status === 401) {
            handleUnauthorized();
            throw new DashboardApiError(401, 'Your session has expired.');
        }

        if (!response.ok || !payload || payload.success !== true) {
            const status = response.status || 500;
            throw new DashboardApiError(status, messageForStatus(status));
        }

        return payload.data && typeof payload.data === 'object' ? payload.data : {};
    }

    function setDashboardState(kind, message, canRetry) {
        const stateElement = byId('dashboardState');
        const messageElement = byId('dashboardStateMessage');
        const retryButton = byId('dashboardRetryBtn');

        if (!stateElement || !messageElement) {
            return;
        }

        if (!message) {
        stateElement.className = 'dashboard-state alert d-none align-items-center justify-content-between gap-3';
            stateElement.hidden = true;
            messageElement.textContent = '';
            if (retryButton) {
                retryButton.hidden = true;
                retryButton.disabled = false;
                retryButton.classList.add('d-none');
            }
            return;
        }

        const allowedKinds = ['info', 'danger', 'warning', 'success'];
        const alertKind = allowedKinds.includes(kind) ? kind : 'info';
        stateElement.className = `dashboard-state alert alert-${alertKind} d-flex align-items-center justify-content-between gap-3`;
        stateElement.hidden = false;
        messageElement.textContent = message;
        if (retryButton) {
            retryButton.hidden = !canRetry;
            retryButton.disabled = false;
            retryButton.classList.toggle('d-none', !canRetry);
        }
    }

    function setReportingRestriction(visible, message) {
        const element = byId('reportingRestriction');
        const messageElement = byId('reportingRestrictionMessage') || element;
        if (!element) {
            return;
        }

        element.hidden = !visible;
        element.classList.toggle('d-none', !visible);
        messageElement.textContent = message || 'Reporting KPIs are available to Administrators only. The dashboard is showing data available to your role.';
    }

    function setListState(container, kind, message, retry) {
        if (!container) {
            return;
        }

        const wrapper = createElement('div', 'dashboard-inline-state dashboard-inline-state-' + kind);
        const iconName = kind === 'loading' ? 'loader' : kind === 'error' ? 'alert' : 'inbox';
        wrapper.appendChild(createIcon(iconName, kind === 'error' ? 'dashboard-icon-danger' : 'dashboard-icon-muted'));
        wrapper.appendChild(createElement('span', kind === 'error' ? 'text-danger' : 'text-muted', message));

        if (typeof retry === 'function') {
            const retryButton = createElement('button', 'btn btn-sm dashboard-state-retry', 'Retry');
            retryButton.type = 'button';
            retryButton.addEventListener('click', retry);
            wrapper.appendChild(retryButton);
        }

        container.replaceChildren(wrapper);
    }

    function createTableStateRow(colspan, kind, message, retry) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = colspan;
        cell.className = `text-center py-4 ${kind === 'error' ? 'text-danger' : 'text-muted'}`;

        if (kind === 'loading') {
            cell.appendChild(createIcon('loader', 'dashboard-icon-muted'));
        } else if (kind === 'error') {
            cell.appendChild(createIcon('alert', 'dashboard-icon-danger'));
        } else {
            cell.appendChild(createIcon('inbox', 'dashboard-icon-muted'));
        }

        cell.appendChild(createElement('span', '', message));
        if (typeof retry === 'function') {
            const retryButton = createElement('button', 'btn btn-sm dashboard-state-retry', 'Retry');
            retryButton.type = 'button';
            retryButton.addEventListener('click', retry);
            cell.appendChild(retryButton);
        }

        row.appendChild(cell);
        return row;
    }

    function getStockState(product) {
        const status = displayText(valueFrom(product, ['Status', 'status']), '').toLowerCase();
        const quantity = numberFrom(product, ['Quantity', 'quantity', 'Stock_Quantity', 'stock_quantity']);

        if (quantity !== null && quantity <= 0) {
            return 'out';
        }
        if (status === 'out of stock') {
            return 'out';
        }
        if (status === 'low stock') {
            return 'low';
        }
        if (status === 'normal') {
            return 'normal';
        }
        return null;
    }

    function isLowStockProduct(product) {
        const stockState = getStockState(product);
        return stockState === 'low' || stockState === 'out';
    }

    function renderInventoryHealth(products) {
        const counts = { normal: 0, low: 0, out: 0, unknown: 0 };
        products.forEach(product => {
            const stockState = getStockState(product);
            if (stockState && Object.prototype.hasOwnProperty.call(counts, stockState)) {
                counts[stockState] += 1;
            } else {
                counts.unknown += 1;
            }
        });

        setText('inventoryNormalCount', formatInteger(counts.normal));
        setText('inventoryLowCount', formatInteger(counts.low));
        setText('inventoryOutCount', formatInteger(counts.out));

        const note = byId('inventoryHealthNote');
        if (note) {
            if (products.length === 0) {
                note.textContent = 'No products are currently recorded.';
            } else if (counts.unknown > 0) {
                note.textContent = counts.unknown + ' product' + (counts.unknown === 1 ? '' : 's') + ' have no recognized stock status.';
            } else {
                note.textContent = products.length.toLocaleString('en-US') + ' product' + (products.length === 1 ? '' : 's') + ' classified from live inventory status.';
            }
        }
    }

    function renderLowStock(products) {
        const container = byId('lowStockList');
        if (!container) {
            return;
        }

        renderInventoryHealth(products);
        const lowStockProducts = products.filter(product => product && typeof product === 'object' && isLowStockProduct(product));
        if (lowStockProducts.length === 0) {
            setListState(container, 'empty', 'No low-stock or out-of-stock products.');
            return;
        }

        const fragment = document.createDocumentFragment();
        lowStockProducts.forEach(product => {
            const item = createElement('div', 'low-stock-item');
            const info = createElement('div', 'product-info');
            const name = displayText(valueFrom(product, ['Name', 'Product_Name', 'name']), 'Unnamed product');
            const category = displayText(valueFrom(product, ['Category', 'category']), 'Category unavailable');
            const stockState = getStockState(product) || 'low';
            const status = stockState === 'out' ? 'Out of stock' : 'Low stock';
            const quantity = numberFrom(product, ['Quantity', 'quantity', 'Stock_Quantity', 'stock_quantity']);

            info.appendChild(createElement('h6', '', name));
            info.appendChild(createElement('small', '', category));

            const stock = createElement('div', 'stock-count');
            stock.appendChild(createIcon('box'));
            stock.appendChild(createElement('span', '', String(quantity === null ? '—' : quantity) + ' · ' + status));

            item.classList.add('stock-state-' + stockState);
            item.appendChild(info);
            item.appendChild(stock);
            fragment.appendChild(item);
        });

        container.replaceChildren(fragment);
    }

    function renderSummary(summary) {
        const totalProducts = numberFrom(summary, ['total_products', 'totalProducts']);
        const lowStock = numberFrom(summary, ['low_stock_products', 'low_stock_count', 'lowStockCount', 'low_stock']);
        const totalOrders = numberFrom(summary, ['total_orders', 'totalOrders']);
        const totalSales = numberFrom(summary, ['total_sales', 'totalSales']);
        const totalPurchases = numberFrom(summary, ['total_purchases', 'totalPurchases']);
        const netRevenue = numberFrom(summary, ['net_revenue', 'netRevenue', 'net_profit']);

        setText('totalProducts', formatInteger(totalProducts));
        setText('lowStock', formatInteger(lowStock));
        setText('totalOrders', formatInteger(totalOrders));
        setText('totalSales', formatCurrency(totalSales));
        setText('totalPurchases', formatCurrency(totalPurchases));
        setText('netProfitValue', formatCurrency(netRevenue));

        const productTrend = byId('totalProductsTrend');
        if (productTrend) {
            productTrend.className = 'dashboard-kpi-foot ' + (totalProducts === null ? 'text-muted' : 'text-success');
            productTrend.textContent = totalProducts === null ? 'Unavailable' : 'Live inventory count';
        }

        const lowStockTrend = byId('lowStockTrend');
        if (lowStockTrend) {
            lowStockTrend.className = 'dashboard-kpi-foot ' + (lowStock === null ? 'text-muted' : lowStock > 0 ? 'text-danger' : 'text-success');
            lowStockTrend.textContent = lowStock === null
                ? 'Unavailable'
                : lowStock > 0
                    ? 'Needs attention'
                    : 'No alerts';
        }

        const orderTrend = byId('totalOrdersTrend');
        if (orderTrend) {
            orderTrend.className = 'dashboard-kpi-foot ' + (totalOrders === null ? 'text-muted' : 'text-success');
            orderTrend.textContent = totalOrders === null ? 'Unavailable' : 'Orders visible to your role';
        }
    }

    function buildLimitedSummary(products, orders) {
        const summary = {
            total_products: products.length,
            low_stock_products: products.filter(isLowStockProduct).length,
            total_orders: orders.length,
            total_sales: 0,
            total_purchases: 0,
            net_revenue: 0
        };
        let validOrderTotals = true;

        orders.forEach(order => {
            const total = numberFrom(order, ['Total_Amount', 'total_amount', 'TotalAmount']);
            const type = displayText(valueFrom(order, ['Order_Type', 'order_type']), '');
            if (total === null) {
                validOrderTotals = false;
                return;
            }

            if (type === 'Sell') {
                summary.total_sales += total;
            } else if (type === 'Purchase') {
                summary.total_purchases += total;
            }
        });

        if (!validOrderTotals) {
            summary.total_sales = null;
            summary.total_purchases = null;
            summary.net_revenue = null;
        } else {
            summary.net_revenue = summary.total_sales - summary.total_purchases;
        }

        return summary;
    }

    function orderTypeClass(type) {
        if (type === 'Sell') {
            return 'order-type-sell';
        }
        if (type === 'Purchase') {
            return 'order-type-purchase';
        }
        return 'order-type-unknown';
    }

    function createOrderTypeBadge(type) {
        const badge = createElement('span', 'order-type-badge ' + orderTypeClass(type), displayText(type, '—'));
        return badge;
    }

    function setOrderTypeElement(id, type) {
        const element = byId(id);
        if (!element) {
            return;
        }
        element.className = 'order-type-badge ' + orderTypeClass(type);
        element.textContent = displayText(type, '—');
    }

    function renderOrderRows(orders, target, emptyMessage) {
        const tableBody = target || byId('allOrdersTableBody');
        if (!tableBody) {
            return;
        }

        const filteredOrders = orders.filter(order => {
            if (!order || typeof order !== 'object') {
                return false;
            }
            if (dashboardState.activeOrderFilter === 'all') {
                return true;
            }
            return displayText(valueFrom(order, ['Order_Type', 'order_type']), '') === dashboardState.activeOrderFilter;
        });

        if (filteredOrders.length === 0) {
            const message = emptyMessage || (dashboardState.activeOrderFilter === 'all'
                ? 'No orders found.'
                : 'No ' + dashboardState.activeOrderFilter.toLowerCase() + ' orders found.');
            tableBody.replaceChildren(createTableStateRow(7, 'empty', message));
            return;
        }

        const fragment = document.createDocumentFragment();
        filteredOrders.forEach(order => {
            const row = document.createElement('tr');
            const orderId = displayText(valueFrom(order, ['Order_ID', 'order_id']), '—');
            const type = displayText(valueFrom(order, ['Order_Type', 'order_type']), '—');
            const partyName = displayText(valueFrom(order, ['Party_Name', 'party_name']), '—');
            const itemCount = formatInteger(valueFrom(order, ['Item_Count', 'item_count']));
            const total = formatCurrency(valueFrom(order, ['Total_Amount', 'total_amount']));
            const orderDate = formatDate(valueFrom(order, ['Order_Date', 'order_date']));

            row.appendChild(createElement('td', 'qm-ltr', orderId));
            const typeCell = createElement('td');
            typeCell.appendChild(createOrderTypeBadge(type));
            row.appendChild(typeCell);
            row.appendChild(createElement('td', '', partyName));
            row.appendChild(createElement('td', 'qm-ltr', itemCount));
            row.appendChild(createElement('td', 'qm-ltr', total));
            row.appendChild(createElement('td', 'qm-ltr', orderDate));

            const actionCell = document.createElement('td');
            const viewButton = createElement('button', 'dashboard-table-action', 'View');
            viewButton.type = 'button';
            viewButton.appendChild(createIcon('eye'));
            if (orderId !== '—') {
                viewButton.dataset.orderId = orderId;
                viewButton.addEventListener('click', () => viewOrderDetails(orderId));
            } else {
                viewButton.disabled = true;
            }
            actionCell.appendChild(viewButton);
            row.appendChild(actionCell);
            fragment.appendChild(row);
        });

        tableBody.replaceChildren(fragment);
    }

    function renderRecentOrders(orders) {
        const tableBody = byId('recentOrdersTableBody');
        if (!tableBody) {
            return;
        }
        if (!Array.isArray(orders)) {
            tableBody.replaceChildren(createTableStateRow(7, 'empty', 'No recent orders found.'));
            return;
        }
        renderOrderRows(orders.slice(0, 6), tableBody, 'No recent orders yet.');
    }

    function renderAllOrders(orders) {
        const tableBody = byId('allOrdersTableBody');
        if (!tableBody) {
            return;
        }

        if (!Array.isArray(orders)) {
            tableBody.replaceChildren(createTableStateRow(7, 'empty', 'No orders found.'));
            return;
        }

        renderOrderRows(orders, tableBody);
    }

    function setAllOrdersLoading() {
        const tableBody = byId('allOrdersTableBody');
        if (tableBody) {
            tableBody.replaceChildren(createTableStateRow(7, 'loading', 'Loading orders...'));
        }
    }

    function setAllOrdersError(error) {
        const tableBody = byId('allOrdersTableBody');
        if (!tableBody) {
            return;
        }

        if (error && error.status === 403) {
            tableBody.replaceChildren(createTableStateRow(7, 'error', 'You are not authorized to view these orders.'));
            return;
        }

        if (error && error.status === 401) {
            tableBody.replaceChildren(createTableStateRow(7, 'error', 'Your session has expired.'));
            return;
        }

        tableBody.replaceChildren(createTableStateRow(7, 'error', 'Orders could not be loaded.', () => loadAllOrders(true)));
    }

    function setRecentOrdersLoading() {
        const tableBody = byId('recentOrdersTableBody');
        if (tableBody) {
            tableBody.replaceChildren(createTableStateRow(7, 'loading', 'Loading recent orders...'));
        }
    }

    function setRecentOrdersError(error) {
        const tableBody = byId('recentOrdersTableBody');
        if (!tableBody) {
            return;
        }

        if (error && error.status === 401) {
            tableBody.replaceChildren(createTableStateRow(7, 'error', 'Your session has expired.'));
            return;
        }
        if (error && error.status === 403) {
            tableBody.replaceChildren(createTableStateRow(7, 'error', 'Orders are restricted for this account.'));
            return;
        }
        tableBody.replaceChildren(createTableStateRow(7, 'error', 'Recent orders could not be loaded.', retryDashboardData));
    }

    function setModalState(kind, message) {
        const stateElement = byId('modalOrderState');
        if (!stateElement) {
            return;
        }

        if (!message) {
            stateElement.className = 'dashboard-modal-state alert d-none';
            stateElement.hidden = true;
            stateElement.textContent = '';
            return;
        }

        const allowedKinds = ['info', 'danger', 'warning'];
        const alertKind = allowedKinds.includes(kind) ? kind : 'info';
        stateElement.className = `dashboard-modal-state alert alert-${alertKind}`;
        stateElement.hidden = false;
        stateElement.textContent = message;
    }

    function setModalLoading() {
        setText('modalOrderId', '—');
        setText('modalCustomerName', '—');
        setText('modalCustomerContact', 'Staff: —');
        setText('modalOrderDate', '—');
        setOrderTypeElement('modalOrderType', '—');
        setText('modalItemCount', '—');
        setText('modalTotal', '—');
        setModalState('info', 'Loading order details...');

        const itemsBody = byId('modalItemsBody');
        if (itemsBody) {
            itemsBody.replaceChildren(createTableStateRow(4, 'loading', 'Loading items...'));
        }
    }

    function renderOrderDetails(data) {
        const order = data && data.order && typeof data.order === 'object' ? data.order : null;
        const details = extractArray(data, ['details', 'order_details', 'items']).filter(item => item && typeof item === 'object');
        if (!order) {
            throw new DashboardApiError(500, 'The order details response was incomplete.');
        }

        setText('modalOrderId', displayText(valueFrom(order, ['Order_ID', 'order_id']), '—'));
        setText('modalCustomerName', displayText(valueFrom(order, ['Party_Name', 'party_name']), '—'));
        setText('modalCustomerContact', `Staff: ${displayText(valueFrom(order, ['Staff_Name', 'staff_name']), '—')}`);
        setText('modalOrderDate', formatDate(valueFrom(order, ['Order_Date', 'order_date'])));
        setOrderTypeElement('modalOrderType', displayText(valueFrom(order, ['Order_Type', 'order_type']), '—'));

        const itemCount = numberFrom(data, ['item_count', 'Item_Count']);
        setText('modalItemCount', formatInteger(itemCount === null ? details.length : itemCount));
        setText('modalTotal', formatCurrency(valueFrom(data, ['total_amount', 'Total_Amount'])));

        const itemsBody = byId('modalItemsBody');
        if (!itemsBody) {
            return;
        }

        if (details.length === 0) {
            itemsBody.replaceChildren(createTableStateRow(4, 'empty', 'No item details are recorded for this order.'));
            return;
        }

        const fragment = document.createDocumentFragment();
        details.forEach(detail => {
            const row = document.createElement('tr');
            const product = displayText(valueFrom(detail, ['Product_Name', 'Name', 'product_name']), 'Unnamed product');
            const quantity = formatInteger(valueFrom(detail, ['Ordered_Qty', 'ordered_qty', 'Quantity', 'quantity']));
            const soldPrice = formatCurrency(valueFrom(detail, ['Sold_Price', 'sold_price']));
            const lineTotal = formatCurrency(valueFrom(detail, ['Line_Total', 'line_total']));

            [product, quantity, soldPrice, lineTotal].forEach((value, index) => {
                const cell = createElement('td', index === 1 ? 'text-center' : index > 1 ? 'text-end' : '', value);
                row.appendChild(cell);
            });
            fragment.appendChild(row);
        });

        itemsBody.replaceChildren(fragment);
    }

    async function viewOrderDetails(orderId) {
        const normalizedOrderId = displayText(orderId, '').trim();
        if (!normalizedOrderId) {
            return;
        }

        setModalLoading();
        const modalElement = byId('orderDetailsModal');
        if (modalElement && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }

        if (dashboardState.detailController) {
            dashboardState.detailController.abort();
        }

        const generation = ++dashboardState.detailGeneration;
        const controller = new AbortController();
        dashboardState.detailController = controller;

        try {
            const data = await requestJson(`orders/get.php?id=${encodeURIComponent(normalizedOrderId)}`, controller.signal);
            if (generation !== dashboardState.detailGeneration) {
                return;
            }
            renderOrderDetails(data);
            setModalState('', '');
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }
            if (generation !== dashboardState.detailGeneration) {
                return;
            }
            const message = error && error.status === 403
                ? 'You are not authorized to view this order.'
                : error && error.status === 404
                    ? 'This order was not found.'
                    : error && error.status === 401
                        ? 'Your session has expired.'
                        : 'Order details could not be loaded.';
            setModalState('danger', message);
            const itemsBody = byId('modalItemsBody');
            if (itemsBody) {
                itemsBody.replaceChildren(createTableStateRow(4, 'error', message));
            }
        }
    }

    function viewOrderFromDB(orderId) {
        return viewOrderDetails(orderId);
    }

    async function loadAllOrders(force = false) {
        if (!force && dashboardState.orders !== null) {
            renderAllOrders(dashboardState.orders);
            return dashboardState.orders;
        }

        setAllOrdersLoading();
        try {
            const data = await fetchOrders(force);
            renderAllOrders(data);
            return data;
        } catch (error) {
            if (!(error && error.name === 'AbortError')) {
                setAllOrdersError(error);
            }
            return null;
        }
    }

    function filterAllOrders(type) {
        const allowedFilters = ['all', 'Sell', 'Purchase'];
        dashboardState.activeOrderFilter = allowedFilters.includes(type) ? type : 'all';

        [['filterAll', 'all'], ['filterSell', 'Sell'], ['filterPurchase', 'Purchase']].forEach(([id, filter]) => {
            const button = byId(id);
            if (!button) {
                return;
            }
            const active = filter === dashboardState.activeOrderFilter;
            button.classList.toggle('active', active);
            button.setAttribute('aria-pressed', String(active));
        });

        if (dashboardState.orders !== null) {
            renderAllOrders(dashboardState.orders);
        }
    }

    function createResourceFetcher(resource) {
        const promiseKey = `${resource}Promise`;
        const generationKey = `${resource}Generation`;
        const dataKey = resource;
        const endpoint = resource === 'products'
            ? 'products/list.php'
            : resource === 'orders'
                ? 'orders/list.php'
                : 'reports/stats.php';

        return function (force = false) {
            if (dashboardState[promiseKey] && !force) {
                return dashboardState[promiseKey];
            }

            const generation = dashboardState[generationKey] + 1;
            dashboardState[generationKey] = generation;
            const controller = new AbortController();
            const request = requestJson(endpoint, controller.signal).then(data => {
                if (generation === dashboardState[generationKey]) {
                    dashboardState[dataKey] = data;
                }
                return data;
            });
            const trackedRequest = request.finally(() => {
                if (dashboardState[promiseKey] === trackedRequest) {
                    dashboardState[promiseKey] = null;
                }
            });

            dashboardState[promiseKey] = trackedRequest;
            return trackedRequest;
        };
    }

    const fetchProducts = createResourceFetcher('products');
    const fetchOrders = createResourceFetcher('orders');
    const fetchReport = createResourceFetcher('report');

    function retryDashboardData() {
        const activeRequest = dashboardState.dashboardPromise;
        if (!activeRequest) {
            return loadDashboardData(true);
        }

        if (dashboardState.retryQueued) {
            return activeRequest;
        }

        dashboardState.retryQueued = true;
        return activeRequest.then(
            () => {
                dashboardState.retryQueued = false;
                return loadDashboardData(true);
            },
            () => {
                dashboardState.retryQueued = false;
                return loadDashboardData(true);
            }
        );
    }

    async function loadDashboardData(force = false) {
        if (dashboardState.dashboardPromise && !force) {
            return dashboardState.dashboardPromise;
        }

        const generation = dashboardState.dashboardGeneration + 1;
        dashboardState.dashboardGeneration = generation;
        setDashboardState('info', 'Loading dashboard data...', false);
        setReportingRestriction(false);
        setListState(byId('lowStockList'), 'loading', 'Loading inventory alerts...');
        setRecentOrdersLoading();
        const reportAllowed = getRoleHint() === 'Admin';

        const request = Promise.allSettled([
            fetchProducts(force),
            fetchOrders(force),
            reportAllowed ? fetchReport(force) : Promise.resolve(null)
        ]).then(results => {
            if (generation !== dashboardState.dashboardGeneration) {
                return;
            }

            const productsResult = results[0];
            const ordersResult = results[1];
            const reportResult = results[2];
            const products = productsResult.status === 'fulfilled'
                ? extractArray(productsResult.value, ['products', 'Products', 'items']).filter(item => item && typeof item === 'object')
                : null;
            const orders = ordersResult.status === 'fulfilled'
                ? extractArray(ordersResult.value, ['orders', 'Orders', 'items']).filter(item => item && typeof item === 'object')
                : null;

            if (products) {
                dashboardState.products = products;
                renderLowStock(products);
            } else {
                const productError = productsResult.reason;
                const message = productError && productError.status === 403
                    ? 'Inventory alerts are restricted for this account.'
                    : 'Inventory alerts could not be loaded.';
                setListState(byId('lowStockList'), 'error', message, retryDashboardData);
            }

            if (orders) {
                dashboardState.orders = orders;
                renderRecentOrders(orders);
                const allOrdersModal = byId('allOrdersModal');
                if (allOrdersModal && allOrdersModal.classList.contains('show')) {
                    renderAllOrders(orders);
                }
            } else {
                setRecentOrdersError(ordersResult.reason);
            }

            if (reportAllowed && reportResult.status === 'fulfilled') {
                const reportData = reportResult.value && typeof reportResult.value === 'object' ? reportResult.value : {};
                const summary = reportData.summary && typeof reportData.summary === 'object' ? reportData.summary : reportData;
                dashboardState.report = reportData;
                renderSummary(summary);
            } else if (products && orders) {
                renderSummary(buildLimitedSummary(products, orders));
                if (!reportAllowed || (reportResult.reason && reportResult.reason.status === 403)) {
                    setReportingRestriction(true);
                } else {
                    setReportingRestriction(true, 'Reporting data is temporarily unavailable. The dashboard is showing data available to your role.');
                }
            } else {
                renderSummary({});
                if (!reportAllowed || (reportResult.reason && reportResult.reason.status === 403)) {
                    setReportingRestriction(true);
                }
            }

            const failures = [productsResult, ordersResult, reportResult].filter(result => result.status === 'rejected');
            const meaningfulFailures = failures.filter(result => !(result.reason && result.reason.name === 'AbortError'));
            if (meaningfulFailures.length > 0 && meaningfulFailures.length === (reportAllowed ? 3 : 2)) {
                setDashboardState('danger', 'Dashboard data could not be loaded.', true);
            } else if (meaningfulFailures.some(result => result.reason && result.reason.status === 401)) {
                setDashboardState('danger', 'Your session has expired.', false);
            } else if (meaningfulFailures.some(result => result.reason && result.reason.status >= 500)) {
                setDashboardState('warning', 'Some dashboard data is temporarily unavailable.', true);
            } else {
                setDashboardState('', '');
            }
        });

        const trackedRequest = request.finally(() => {
            if (dashboardState.dashboardPromise === trackedRequest) {
                dashboardState.dashboardPromise = null;
            }
        });
        dashboardState.dashboardPromise = trackedRequest;
        return trackedRequest;
    }

    function updateDateTime() {
        const now = new Date();
        const dateElement = byId('currentDate');
        const timeElement = byId('currentTime');
        if (dateElement) {
            dateElement.textContent = now.toLocaleDateString([], {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        if (timeElement) {
            timeElement.textContent = now.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    }

    function setupDashboardInteractions() {
        const retryButton = byId('dashboardRetryBtn');
        if (retryButton) {
            retryButton.addEventListener('click', retryDashboardData);
        }

        const printButton = byId('printOrderBtn');
        if (printButton) {
            printButton.addEventListener('click', () => window.print());
        }

        [['filterAll', 'all'], ['filterSell', 'Sell'], ['filterPurchase', 'Purchase']].forEach(([id, filter]) => {
            const button = byId(id);
            if (button) {
                button.addEventListener('click', () => filterAllOrders(filter));
            }
        });

        const allOrdersModal = byId('allOrdersModal');
        if (allOrdersModal) {
            allOrdersModal.addEventListener('show.bs.modal', () => loadAllOrders(false));
        }
    }

    function initDashboard() {
        if (typeof loadUserInfo === 'function') {
            loadUserInfo();
        }
        setupDashboardInteractions();
        updateDateTime();
        window.setInterval(updateDateTime, 1000);
        loadDashboardData();
    }

    window.loadDashboardData = loadDashboardData;
    window.loadAllOrders = loadAllOrders;
    window.filterAllOrders = filterAllOrders;
    window.viewOrderDetails = viewOrderDetails;
    window.viewOrderFromDB = viewOrderFromDB;

    document.addEventListener('DOMContentLoaded', initDashboard);
})();
