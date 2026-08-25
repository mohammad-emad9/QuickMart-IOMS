/* Reports metrics and analytics views. */

(function () {
    'use strict';

    const MISSING = '—';
    const REPORT_PATH = 'reports/stats.php';
    const MONTH_NAMES = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const iconPaths = {
        loading: ['M12 3a9 9 0 1 0 9 9', 'M12 3v3'],
        info: ['M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z', 'M12 10v6', 'M12 7.5h.01'],
        empty: ['M4 5h16v14H4z', 'M8 9h8M8 13h5'],
        error: ['M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z', 'm9 9 6 6', 'm15 9-6 6'],
        restricted: ['M7 11V8a5 5 0 0 1 10 0v3', 'M5 11h14v9H5z', 'M12 15v2']
    };

    class ReportsApiError extends Error {
        constructor(status, message) {
            super(message);
            this.name = 'ReportsApiError';
            this.status = status;
        }
    }

    let reportsRequest = null;
    let reportsGeneration = 0;
    let currentReportData = null;
    let reportsAccess = false;
    let redirected = false;

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

    function createIcon(name) {
        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        svg.setAttribute('viewBox', '0 0 24 24');
        svg.setAttribute('fill', 'none');
        svg.setAttribute('stroke', 'currentColor');
        svg.setAttribute('stroke-width', '1.8');
        svg.setAttribute('stroke-linecap', 'round');
        svg.setAttribute('stroke-linejoin', 'round');
        svg.setAttribute('aria-hidden', 'true');

        (iconPaths[name] || iconPaths.info).forEach((pathData) => {
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', pathData);
            svg.appendChild(path);
        });

        if (name === 'loading') {
            svg.classList.add('reports-loading-icon');
        }

        return svg;
    }

    function valueFrom(source, keys) {
        if (!source || typeof source !== 'object') {
            return undefined;
        }

        for (const key of keys) {
            if (Object.prototype.hasOwnProperty.call(source, key)
                && source[key] !== null
                && source[key] !== undefined) {
                return source[key];
            }
        }

        return undefined;
    }

    function displayText(value, fallback = MISSING) {
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

    function formatCurrency(value) {
        const number = numericValue(value);
        if (number === null) {
            return MISSING;
        }

        return `$${number.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })}`;
    }

    function formatInteger(value) {
        const number = numericValue(value);
        return number === null
            ? MISSING
            : Math.round(number).toLocaleString('en-US', { maximumFractionDigits: 0 });
    }

    function formatDate(value) {
        if (typeof value !== 'string' && typeof value !== 'number') {
            return MISSING;
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return MISSING;
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

    function getSessionRole() {
        return typeof getServerSessionRole === 'function' ? getServerSessionRole() : '';
    }

    function isAdminSession() {
        return getSessionRole() === 'Admin';
    }

    function handleUnauthorized() {
        if (redirected) {
            return;
        }

        redirected = true;
        try {
            sessionStorage.clear();
        } catch (error) {
            // The server remains authoritative for the stale-session redirect.
        }

        window.location.href = typeof loginPath === 'function'
            ? loginPath()
            : '../../assets/login-signup/login.html';
    }

    function messageForStatus(status) {
        if (status === 401) {
            return 'Your session has expired. Please sign in again.';
        }
        if (status === 403) {
            return 'Reports are available to Administrators only.';
        }
        if (status === 404) {
            return 'The reports resource was not found.';
        }
        if (status === 0) {
            return 'The server could not be reached. Please try again.';
        }
        return 'Reports could not be loaded. Please try again.';
    }

    async function requestJson(path, signal) {
        let response;
        try {
            response = await apiFetch(path, signal ? { signal } : {});
        } catch (error) {
            if (error && error.name === 'AbortError') {
                throw error;
            }
            throw new ReportsApiError(0, messageForStatus(0));
        }

        let payload = null;
        try {
            payload = await response.json();
        } catch (error) {
            payload = null;
        }

        if (response.status === 401) {
            handleUnauthorized();
            throw new ReportsApiError(401, messageForStatus(401));
        }

        if (!response.ok || !payload || payload.success !== true) {
            const status = response.status || 500;
            throw new ReportsApiError(status, messageForStatus(status));
        }

        return payload.data && typeof payload.data === 'object' ? payload.data : {};
    }

    function setReportState(kind, message, canRetry) {
        const stateElement = byId('reportsState');
        const messageElement = byId('reportsStateMessage');
        const retryButton = byId('reportsRetryBtn');
        const iconHost = stateElement?.querySelector('[data-report-state-icon]');

        if (!stateElement || !messageElement) {
            return;
        }

        if (!message) {
            stateElement.hidden = true;
            stateElement.className = 'reports-state';
            messageElement.textContent = '';
            if (retryButton) {
                retryButton.hidden = true;
                retryButton.disabled = false;
                retryButton.classList.add('d-none');
            }
            return;
        }

        const allowedKinds = ['loading', 'info', 'danger', 'warning', 'success'];
        const stateKind = allowedKinds.includes(kind) ? kind : 'info';
        stateElement.hidden = false;
        stateElement.className = `reports-state reports-state--${stateKind}`;
        messageElement.textContent = message;

        if (iconHost) {
            const iconName = stateKind === 'danger'
                ? 'error'
                : stateKind === 'warning'
                    ? 'restricted'
                    : stateKind === 'loading'
                        ? 'loading'
                        : stateKind === 'success'
                            ? 'info'
                            : 'info';
            iconHost.replaceChildren(createIcon(iconName));
        }

        if (retryButton) {
            retryButton.hidden = !canRetry;
            retryButton.disabled = false;
            retryButton.classList.toggle('d-none', !canRetry);
        }
    }

    function setReportControlsBusy(isBusy) {
        const refreshButton = byId('refreshReportsBtn');
        const exportButton = byId('exportReportBtn');
        if (refreshButton) {
            refreshButton.disabled = isBusy || !reportsAccess;
            refreshButton.setAttribute('aria-busy', String(isBusy));
        }
        if (exportButton) {
            const disabled = isBusy || !reportsAccess || currentReportData === null;
            exportButton.disabled = disabled;
            exportButton.setAttribute('aria-disabled', String(disabled));
        }
    }

    function createTableStateRow(colspan, kind, message) {
        const row = createElement('tr', `table-state-row table-state-row--${kind}`);
        const cell = createElement('td');
        cell.colSpan = colspan;
        const icon = createElement('span', 'table-state-icon');
        const iconName = kind === 'loading'
            ? 'loading'
            : kind === 'error'
                ? 'error'
                : kind === 'restricted'
                    ? 'restricted'
                    : 'empty';
        icon.appendChild(createIcon(iconName));
        cell.append(icon, document.createTextNode(message));
        row.appendChild(cell);
        return row;
    }

    function setContainerState(container, kind, message) {
        if (!container) {
            return;
        }

        const state = createElement('div', `chart-state chart-state--${kind}`);
        const iconName = kind === 'loading'
            ? 'loading'
            : kind === 'error'
                ? 'error'
                : kind === 'restricted'
                    ? 'restricted'
                    : 'empty';
        state.append(createIcon(iconName), document.createTextNode(message));
        container.replaceChildren(state);
    }

    function textCell(label, text, direction) {
        const cell = createElement('td', '', text);
        cell.setAttribute('data-label', label);
        if (direction === 'ltr') {
            cell.setAttribute('dir', 'ltr');
        }
        return cell;
    }

    function createHeaderRow(labels) {
        const row = document.createElement('tr');
        labels.forEach((label) => {
            const cell = createElement('th', '', label);
            cell.scope = 'col';
            row.appendChild(cell);
        });
        return row;
    }

    function createAlternativeTable(labels, caption, rows, tableClass) {
        const table = createElement('table', `reports-table reports-table--compact ${tableClass || ''}`.trim());
        const captionElement = createElement('caption', 'visually-hidden', caption);
        const thead = document.createElement('thead');
        const tbody = document.createElement('tbody');
        thead.appendChild(createHeaderRow(labels));
        rows.forEach((row) => tbody.appendChild(row));
        table.append(captionElement, thead, tbody);
        return table;
    }

    function renderSummary(summary) {
        const source = summary && typeof summary === 'object' ? summary : {};
        const values = {
            totalSales: formatCurrency(valueFrom(source, ['total_sales', 'totalSales'])),
            totalPurchases: formatCurrency(valueFrom(source, ['total_purchases', 'totalPurchases'])),
            netRevenue: formatCurrency(valueFrom(source, ['net_revenue', 'netRevenue', 'net_profit'])),
            totalProducts: formatInteger(valueFrom(source, ['total_products', 'totalProducts'])),
            lowStockCount: formatInteger(valueFrom(source, ['low_stock_count', 'low_stock_products', 'lowStockCount', 'low_stock'])),
            sellOrderCount: formatInteger(valueFrom(source, ['sell_orders', 'sell_order_count', 'sellOrderCount'])),
            purchaseOrderCount: formatInteger(valueFrom(source, ['purchase_orders', 'purchase_order_count', 'purchaseOrderCount']))
        };

        Object.entries(values).forEach(([id, value]) => {
            const element = byId(id);
            if (element) {
                element.textContent = value;
            }
        });
    }

    function renderTopProducts(products) {
        const body = byId('topProductsBody');
        if (!body) {
            return;
        }

        const validProducts = products.filter((product) => product && typeof product === 'object');
        if (validProducts.length === 0) {
            body.replaceChildren(createTableStateRow(4, 'empty', 'No top-selling products found.'));
            return;
        }

        const fragment = document.createDocumentFragment();
        validProducts.forEach((product, index) => {
            const row = document.createElement('tr');
            row.appendChild(textCell('Rank', String(index + 1), 'ltr'));
            row.appendChild(textCell('Product', displayText(valueFrom(product, ['Name', 'Product_Name', 'product_name']), 'Unnamed product')));
            row.appendChild(textCell('Sold', formatInteger(valueFrom(product, ['total_sold', 'Total_Sold', 'Sold_Qty', 'sold_qty'])), 'ltr'));
            row.appendChild(textCell('Revenue', formatCurrency(valueFrom(product, ['revenue', 'Total_Revenue', 'total_revenue', 'Revenue'])), 'ltr'));
            fragment.appendChild(row);
        });
        body.replaceChildren(fragment);
    }

    function renderCategories(categories) {
        const container = byId('categoryChart');
        if (!container) {
            return;
        }

        const validCategories = categories.filter((category) => category && typeof category === 'object');
        if (validCategories.length === 0) {
            setContainerState(container, 'empty', 'No category data found.');
            return;
        }

        const counts = validCategories.map((category) => numberFrom(category, ['count', 'Product_Count', 'product_count', 'Count', 'total_products']));
        const maximum = Math.max(...counts.map((count) => count === null ? 0 : count), 0);
        const visual = createElement('div', 'category-visual');
        visual.setAttribute('aria-hidden', 'true');
        const alternativeRows = [];

        validCategories.forEach((category, index) => {
            const categoryName = displayText(valueFrom(category, ['Category', 'category', 'Category_Name', 'category_name']), 'Uncategorized');
            const count = counts[index];
            const stock = numberFrom(category, ['total_stock', 'Total_Stock', 'stock']);
            const scaleValue = count === null ? 0 : Math.max(0, count);
            const percentage = maximum > 0 ? Math.max(0, Math.min(100, (scaleValue / maximum) * 100)) : 0;

            const bar = createElement('div', 'category-bar');
            bar.appendChild(createElement('span', 'category-bar__label', categoryName));
            const track = createElement('span', 'category-bar__track');
            const fill = createElement('span', 'category-bar__fill');
            fill.style.width = `${percentage.toFixed(1)}%`;
            track.appendChild(fill);
            bar.appendChild(track);
            bar.appendChild(createElement('span', 'category-bar__value', `${formatInteger(count)} products`));
            visual.appendChild(bar);

            const row = document.createElement('tr');
            row.appendChild(textCell('Category', categoryName));
            row.appendChild(textCell('Products', formatInteger(count), 'ltr'));
            row.appendChild(textCell('Stock units', formatInteger(stock), 'ltr'));
            alternativeRows.push(row);
        });

        const alternative = createElement('div', 'chart-alternative');
        alternative.appendChild(createElement('p', 'chart-alternative__label', 'Readable data table'));
        alternative.appendChild(createAlternativeTable(
            ['Category', 'Products', 'Stock units'],
            'Products by category data table',
            alternativeRows,
            ''
        ));
        container.replaceChildren(visual, alternative);
    }

    function renderRecentOrders(orders) {
        const body = byId('recentOrdersBody');
        if (!body) {
            return;
        }

        const validOrders = orders.filter((order) => order && typeof order === 'object');
        if (validOrders.length === 0) {
            body.replaceChildren(createTableStateRow(6, 'empty', 'No recent orders found.'));
            return;
        }

        const fragment = document.createDocumentFragment();
        validOrders.forEach((order) => {
            const type = displayText(valueFrom(order, ['Order_Type', 'order_type']));
            const row = document.createElement('tr');
            row.appendChild(textCell('Order ID', displayText(valueFrom(order, ['Order_ID', 'order_id'])), 'ltr'));

            const typeCell = createElement('td');
            typeCell.setAttribute('data-label', 'Type');
            const typeClass = type === 'Purchase' ? 'purchase' : 'sell';
            typeCell.appendChild(createElement('span', `order-type order-type--${typeClass}`, type));
            row.appendChild(typeCell);

            row.appendChild(textCell('Staff', displayText(valueFrom(order, ['Staff_Name', 'staff_name']))));
            row.appendChild(textCell('Customer or supplier', displayText(valueFrom(order, ['Party_Name', 'party_name']))));
            row.appendChild(textCell('Amount', formatCurrency(valueFrom(order, ['Total_Amount', 'total_amount'])), 'ltr'));
            row.appendChild(textCell('Date', formatDate(valueFrom(order, ['Order_Date', 'order_date'])), 'ltr'));
            fragment.appendChild(row);
        });
        body.replaceChildren(fragment);
    }

    function monthLabel(value) {
        const raw = displayText(value);
        const match = /^(\d{4})-(\d{1,2})/.exec(raw);
        if (!match) {
            return { month: raw, year: '' };
        }

        const monthNumber = Number(match[2]);
        return {
            month: MONTH_NAMES[monthNumber - 1] || raw,
            year: match[1]
        };
    }

    function createTrendBar(value, maximum, className) {
        const numeric = numericValue(value);
        const safeValue = numeric === null ? 0 : Math.max(0, numeric);
        const percent = maximum > 0 ? Math.max(0, Math.min(100, (safeValue / maximum) * 100)) : 0;
        const bar = createElement('span', `trend-bar ${className}`);
        bar.style.height = `${percent.toFixed(1)}%`;
        return bar;
    }

    function renderMonthlyTrend(trend) {
        const container = byId('monthlyTrendChart');
        if (!container) {
            return;
        }

        const validTrend = trend.filter((item) => item && typeof item === 'object');
        if (validTrend.length === 0) {
            setContainerState(container, 'empty', 'No monthly activity found.');
            return;
        }

        const sales = validTrend.map((item) => numberFrom(item, ['sales', 'Sales', 'total_sales']));
        const purchases = validTrend.map((item) => numberFrom(item, ['purchases', 'Purchases', 'total_purchases']));
        const scaleValues = sales.concat(purchases).map((value) => value === null ? 0 : Math.max(0, value));
        const maximum = Math.max(...scaleValues, 0);
        const visual = createElement('div', 'trend-visual');
        visual.setAttribute('aria-hidden', 'true');
        const columns = createElement('div', 'trend-columns');
        const alternativeRows = [];

        validTrend.forEach((item, index) => {
            const label = monthLabel(valueFrom(item, ['month', 'Month', 'period']));
            const column = createElement('div', 'trend-column');
            const bars = createElement('div', 'trend-column__bars');
            bars.appendChild(createTrendBar(sales[index], maximum, 'trend-bar--sales'));
            bars.appendChild(createTrendBar(purchases[index], maximum, 'trend-bar--purchases'));
            column.appendChild(bars);

            const values = createElement('div', 'trend-column__values');
            values.appendChild(createElement('span', '', `S ${formatCurrency(sales[index])}`));
            values.appendChild(createElement('span', '', `P ${formatCurrency(purchases[index])}`));
            column.appendChild(values);

            const labelElement = createElement('span', 'trend-column__label', label.month);
            if (label.year) {
                const yearElement = createElement('span', 'trend-column__year', label.year);
                yearElement.setAttribute('dir', 'ltr');
                labelElement.appendChild(yearElement);
            }
            column.appendChild(labelElement);
            columns.appendChild(column);

            const row = document.createElement('tr');
            row.appendChild(textCell('Month', displayText(valueFrom(item, ['month', 'Month', 'period'])), 'ltr'));
            row.appendChild(textCell('Orders', formatInteger(valueFrom(item, ['order_count', 'Order_Count', 'count'])), 'ltr'));
            row.appendChild(textCell('Sales', formatCurrency(sales[index]), 'ltr'));
            row.appendChild(textCell('Purchases', formatCurrency(purchases[index]), 'ltr'));
            alternativeRows.push(row);
        });

        visual.appendChild(columns);
        const legend = createElement('div', 'trend-legend');
        const salesLegend = createElement('span', 'trend-legend__item');
        salesLegend.appendChild(createElement('span', 'trend-legend__swatch trend-legend__swatch--sales'));
        salesLegend.appendChild(document.createTextNode('Sales'));
        const purchasesLegend = createElement('span', 'trend-legend__item');
        purchasesLegend.appendChild(createElement('span', 'trend-legend__swatch trend-legend__swatch--purchases'));
        purchasesLegend.appendChild(document.createTextNode('Purchases'));
        legend.append(salesLegend, purchasesLegend);
        visual.appendChild(legend);

        const alternative = createElement('div', 'chart-alternative');
        alternative.appendChild(createElement('p', 'chart-alternative__label', 'Readable data table; months without activity are omitted by the API.'));
        alternative.appendChild(createAlternativeTable(
            ['Month', 'Orders', 'Sales', 'Purchases'],
            'Monthly sales and purchases data table',
            alternativeRows,
            ''
        ));
        container.replaceChildren(visual, alternative);
    }

    function renderReport(data) {
        const report = data && typeof data === 'object' ? data : {};
        const summary = report.summary && typeof report.summary === 'object' ? report.summary : report;
        renderSummary(summary);
        renderTopProducts(extractArray(report, ['top_products', 'topProducts', 'products']));
        renderCategories(extractArray(report, ['products_by_category', 'categories', 'category_data']));
        renderRecentOrders(extractArray(report, ['recent_orders', 'recentOrders', 'orders']));
        renderMonthlyTrend(extractArray(report, ['monthly_trend', 'monthlyTrend', 'trends']));
    }

    function resetReportSections(kind, message) {
        const topProducts = byId('topProductsBody');
        if (topProducts) {
            topProducts.replaceChildren(createTableStateRow(4, kind, message));
        }

        const recentOrders = byId('recentOrdersBody');
        if (recentOrders) {
            recentOrders.replaceChildren(createTableStateRow(6, kind, message));
        }

        setContainerState(byId('categoryChart'), kind, message);
        setContainerState(byId('monthlyTrendChart'), kind, message);
    }

    function setRestrictedState() {
        reportsAccess = false;
        currentReportData = null;
        renderSummary({});
        resetReportSections('restricted', 'Reports are available to Administrators only.');
        setReportControlsBusy(false);
        setReportState('warning', 'Reports are available to Administrators only.', false);
    }

    function setErrorState(error) {
        const status = error && typeof error.status === 'number' ? error.status : 0;
        renderSummary({});
        resetReportSections(status === 403 ? 'restricted' : 'error', status === 403
            ? 'Reports are available to Administrators only.'
            : 'Report data is unavailable.');
        setReportControlsBusy(false);

        if (status === 401) {
            setReportState('danger', 'Your session has expired. Please sign in again.', false);
        } else if (status === 403) {
            setReportState('warning', 'Reports are available to Administrators only.', false);
        } else {
            setReportState('danger', error?.message || 'Reports could not be loaded. Please try again.', true);
        }
    }

    async function loadReports() {
        if (typeof getServerSessionUser === 'function' && !getServerSessionUser()) {
            const authenticated = await checkSession();
            if (!authenticated) {
                if (getSessionProbeState() === 'unavailable') {
                    setErrorState(new ReportsApiError(0, 'Your session could not be verified. Please retry.'));
                }
                return null;
            }
        }

        if (!isAdminSession()) {
            setRestrictedState();
            return null;
        }

        if (reportsRequest) {
            return reportsRequest;
        }

        reportsAccess = true;
        const generation = reportsGeneration + 1;
        reportsGeneration = generation;
        currentReportData = null;
        setReportState('loading', 'Loading report data…', false);
        setReportControlsBusy(true);
        renderSummary({});
        resetReportSections('loading', 'Loading report data…');

        const request = requestJson(REPORT_PATH).then((data) => {
            if (generation !== reportsGeneration) {
                return data;
            }

            currentReportData = data && typeof data === 'object' ? data : {};
            renderReport(currentReportData);
            setReportControlsBusy(false);
            setReportState('', '');
            return currentReportData;
        }).catch((error) => {
            if (generation !== reportsGeneration || (error && error.name === 'AbortError')) {
                return null;
            }

            currentReportData = null;
            setErrorState(error);
            return null;
        }).finally(() => {
            if (reportsRequest === trackedRequest) {
                reportsRequest = null;
            }
        });

        const trackedRequest = request;
        reportsRequest = trackedRequest;
        return trackedRequest;
    }

    function csvValue(value) {
        const text = value === null || value === undefined ? '' : String(value);
        return `"${text.replace(/"/g, '""')}"`;
    }

    function exportReport() {
        if (!currentReportData || typeof currentReportData !== 'object') {
            return;
        }

        const report = currentReportData;
        const summary = report.summary && typeof report.summary === 'object' ? report.summary : report;
        const rows = [
            ['Metric', 'Value'],
            ['Total Sales', valueFrom(summary, ['total_sales', 'totalSales'])],
            ['Total Purchases', valueFrom(summary, ['total_purchases', 'totalPurchases'])],
            ['Net Revenue', valueFrom(summary, ['net_revenue', 'netRevenue', 'net_profit'])],
            ['Total Products', valueFrom(summary, ['total_products', 'totalProducts'])],
            ['Low Stock Count', valueFrom(summary, ['low_stock_count', 'low_stock_products', 'lowStockCount', 'low_stock'])],
            ['Sell Orders', valueFrom(summary, ['sell_orders', 'sell_order_count', 'sellOrderCount'])],
            ['Purchase Orders', valueFrom(summary, ['purchase_orders', 'purchase_order_count', 'purchaseOrderCount'])],
            [],
            ['Top Products', 'Sold', 'Revenue']
        ];

        extractArray(report, ['top_products', 'topProducts', 'products']).forEach((product) => {
            if (product && typeof product === 'object') {
                rows.push([
                    valueFrom(product, ['Name', 'Product_Name', 'product_name']),
                    valueFrom(product, ['total_sold', 'Total_Sold', 'Sold_Qty', 'sold_qty']),
                    valueFrom(product, ['revenue', 'Total_Revenue', 'total_revenue', 'Revenue'])
                ]);
            }
        });

        rows.push([], ['Category', 'Products', 'Stock units']);
        extractArray(report, ['products_by_category', 'categories', 'category_data']).forEach((category) => {
            if (category && typeof category === 'object') {
                rows.push([
                    valueFrom(category, ['Category', 'category', 'Category_Name', 'category_name']),
                    valueFrom(category, ['count', 'Product_Count', 'product_count', 'Count', 'total_products']),
                    valueFrom(category, ['total_stock', 'Total_Stock', 'stock'])
                ]);
            }
        });

        rows.push([], ['Order ID', 'Type', 'Staff', 'Customer or supplier', 'Amount', 'Date']);
        extractArray(report, ['recent_orders', 'recentOrders', 'orders']).forEach((order) => {
            if (order && typeof order === 'object') {
                rows.push([
                    valueFrom(order, ['Order_ID', 'order_id']),
                    valueFrom(order, ['Order_Type', 'order_type']),
                    valueFrom(order, ['Staff_Name', 'staff_name']),
                    valueFrom(order, ['Party_Name', 'party_name']),
                    valueFrom(order, ['Total_Amount', 'total_amount']),
                    valueFrom(order, ['Order_Date', 'order_date'])
                ]);
            }
        });

        rows.push([], ['Month', 'Orders', 'Sales', 'Purchases']);
        extractArray(report, ['monthly_trend', 'monthlyTrend', 'trends']).forEach((item) => {
            if (item && typeof item === 'object') {
                rows.push([
                    valueFrom(item, ['month', 'Month', 'period']),
                    valueFrom(item, ['order_count', 'Order_Count', 'count']),
                    valueFrom(item, ['sales', 'Sales', 'total_sales']),
                    valueFrom(item, ['purchases', 'Purchases', 'total_purchases'])
                ]);
            }
        });

        const csv = `\uFEFF${rows.map((row) => row.map(csvValue).join(',')).join('\r\n')}`;
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = createElement('a');
        link.href = url;
        link.download = 'quickmart-report.csv';
        link.hidden = true;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(url), 0);
    }

    function setupReportInteractions() {
        const refreshButton = byId('refreshReportsBtn');
        if (refreshButton) {
            refreshButton.addEventListener('click', () => loadReports());
        }

        const exportButton = byId('exportReportBtn');
        if (exportButton) {
            exportButton.addEventListener('click', exportReport);
        }

        const retryButton = byId('reportsRetryBtn');
        if (retryButton) {
            retryButton.addEventListener('click', () => loadReports());
        }
    }

    async function initReports() {
        setupReportInteractions();

        const authenticated = await checkSession();
        if (!authenticated) {
            if (getSessionProbeState() === 'unavailable') {
                setReportState('danger', 'Your session could not be verified. Please retry.', true);
            }
            return;
        }

        loadUserInfo();
        if (!isAdminSession()) {
            setRestrictedState();
            return;
        }

        loadReports();
    }

    window.loadReports = loadReports;
    window.exportReport = exportReport;
    document.addEventListener('DOMContentLoaded', initReports);
})();
