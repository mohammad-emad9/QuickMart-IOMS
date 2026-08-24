/* QuickMart IOMS - Reports */

(function () {
    'use strict';

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

    function createIcon(className) {
        return createElement('i', className);
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

    function formatCurrency(value) {
        const number = numericValue(value);
        return number === null ? '—' : `$${number.toFixed(2)}`;
    }

    function formatInteger(value) {
        const number = numericValue(value);
        return number === null ? '—' : Math.round(number).toLocaleString();
    }

    function formatDate(value) {
        if (typeof value !== 'string' && typeof value !== 'number') {
            return '—';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return '—';
        }

        return date.toLocaleString([], {
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

    function handleUnauthorized() {
        if (redirected) {
            return;
        }

        redirected = true;
        sessionStorage.clear();
        window.location.href = typeof loginPath === 'function'
            ? loginPath()
            : '../../assets/login-signup/login.html';
    }

    function messageForStatus(status) {
        if (status === 403) {
            return 'Reports are available to Administrators only.';
        }
        if (status === 404) {
            return 'The reports resource was not found.';
        }
        if (status === 500 || status === 502 || status === 503) {
            return 'Reports could not be loaded. Please try again.';
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
            throw new ReportsApiError(401, 'Your session has expired.');
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
        if (!stateElement || !messageElement) {
            return;
        }

        if (!message) {
            stateElement.className = 'alert d-none align-items-center justify-content-between gap-3';
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
        stateElement.className = `alert alert-${alertKind} d-flex align-items-center justify-content-between gap-3`;
        stateElement.hidden = false;
        messageElement.textContent = message;
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
            refreshButton.disabled = isBusy;
        }
        if (exportButton) {
            exportButton.disabled = isBusy || currentReportData === null;
        }
    }

    function createTableStateRow(colspan, kind, message) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = colspan;
        cell.className = `text-center py-4 ${kind === 'error' ? 'text-danger' : 'text-muted'}`;
        if (kind === 'loading') {
            cell.appendChild(createIcon('fas fa-spinner fa-spin me-2'));
        } else if (kind === 'error') {
            cell.appendChild(createIcon('fas fa-exclamation-circle me-2'));
        } else {
            cell.appendChild(createIcon('fas fa-inbox me-2'));
        }
        cell.appendChild(createElement('span', '', message));
        row.appendChild(cell);
        return row;
    }

    function setContainerState(container, kind, message, retry) {
        if (!container) {
            return;
        }

        const state = createElement('div', `text-center py-4 ${kind === 'error' ? 'text-danger' : 'text-muted'}`);
        if (kind === 'loading') {
            state.appendChild(createIcon('fas fa-spinner fa-spin me-2'));
        } else if (kind === 'error') {
            state.appendChild(createIcon('fas fa-exclamation-circle me-2'));
        } else {
            state.appendChild(createIcon('fas fa-inbox me-2'));
        }
        state.appendChild(createElement('span', '', message));

        if (typeof retry === 'function') {
            const retryButton = createElement('button', 'btn btn-sm btn-outline-primary ms-2', 'Retry');
            retryButton.type = 'button';
            retryButton.addEventListener('click', retry);
            state.appendChild(retryButton);
        }

        container.replaceChildren(state);
    }

    function summaryValue(summary, keys) {
        const value = numberFrom(summary, keys);
        return value === null ? 0 : value;
    }

    function renderSummary(summary) {
        const sales = summaryValue(summary, ['total_sales', 'totalSales']);
        const purchases = summaryValue(summary, ['total_purchases', 'totalPurchases']);
        const netRevenue = summaryValue(summary, ['net_revenue', 'netRevenue', 'net_profit']);
        const products = summaryValue(summary, ['total_products', 'totalProducts']);
        const lowStock = summaryValue(summary, ['low_stock_products', 'low_stock_count', 'lowStockCount', 'low_stock']);
        const sellOrders = summaryValue(summary, ['sell_orders', 'sell_order_count', 'sellOrderCount']);
        const purchaseOrders = summaryValue(summary, ['purchase_orders', 'purchase_order_count', 'purchaseOrderCount']);

        const values = {
            totalSales: formatCurrency(sales),
            totalPurchases: formatCurrency(purchases),
            netRevenue: formatCurrency(netRevenue),
            totalProducts: formatInteger(products),
            lowStockCount: formatInteger(lowStock),
            sellOrderCount: formatInteger(sellOrders),
            purchaseOrderCount: formatInteger(purchaseOrders)
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

        const validProducts = products.filter(product => product && typeof product === 'object');
        if (validProducts.length === 0) {
            body.replaceChildren(createTableStateRow(4, 'empty', 'No top-selling products found.'));
            return;
        }

        const fragment = document.createDocumentFragment();
        validProducts.forEach((product, index) => {
            const row = document.createElement('tr');
            row.appendChild(createElement('td', '', String(index + 1)));
            row.appendChild(createElement('td', '', displayText(valueFrom(product, ['Product_Name', 'Name', 'product_name']), 'Unnamed product')));
            row.appendChild(createElement('td', 'text-center', formatInteger(valueFrom(product, ['Total_Sold', 'total_sold', 'Sold_Qty', 'sold_qty']))));
            row.appendChild(createElement('td', 'text-end', formatCurrency(valueFrom(product, ['Total_Revenue', 'total_revenue', 'Revenue', 'revenue']))));
            fragment.appendChild(row);
        });
        body.replaceChildren(fragment);
    }

    function renderCategories(categories) {
        const container = byId('categoryChart');
        if (!container) {
            return;
        }

        const validCategories = categories.filter(category => category && typeof category === 'object');
        if (validCategories.length === 0) {
            setContainerState(container, 'empty', 'No category data found.');
            return;
        }

        const counts = validCategories.map(category => numberFrom(category, ['Product_Count', 'product_count', 'Count', 'count', 'total_products']) || 0);
        const maximum = Math.max(...counts, 0);
        const fragment = document.createDocumentFragment();

        validCategories.forEach((category, index) => {
            const count = counts[index];
            const item = createElement('div', 'category-bar-item');
            const header = createElement('div', 'd-flex justify-content-between align-items-center mb-1');
            header.appendChild(createElement('span', 'fw-semibold', displayText(valueFrom(category, ['Category', 'category', 'Category_Name', 'category_name']), 'Uncategorized')));
            header.appendChild(createElement('span', 'text-muted', `${formatInteger(count)} products`));

            const progress = createElement('div', 'progress');
            progress.style.height = '8px';
            const bar = createElement('div', 'progress-bar bg-info');
            const percentage = maximum > 0 ? Math.max(0, Math.min(100, (count / maximum) * 100)) : 0;
            bar.style.width = `${percentage}%`;
            bar.setAttribute('role', 'progressbar');
            bar.setAttribute('aria-valuenow', String(Math.round(percentage)));
            bar.setAttribute('aria-valuemin', '0');
            bar.setAttribute('aria-valuemax', '100');
            progress.appendChild(bar);

            item.appendChild(header);
            item.appendChild(progress);
            fragment.appendChild(item);
        });

        container.replaceChildren(fragment);
    }

    function renderRecentOrders(orders) {
        const body = byId('recentOrdersBody');
        if (!body) {
            return;
        }

        const validOrders = orders.filter(order => order && typeof order === 'object');
        if (validOrders.length === 0) {
            body.replaceChildren(createTableStateRow(6, 'empty', 'No recent orders found.'));
            return;
        }

        const fragment = document.createDocumentFragment();
        validOrders.forEach(order => {
            const row = document.createElement('tr');
            row.appendChild(createElement('td', '', displayText(valueFrom(order, ['Order_ID', 'order_id']), '—')));
            row.appendChild(createElement('td', '', displayText(valueFrom(order, ['Order_Type', 'order_type']), '—')));
            row.appendChild(createElement('td', '', displayText(valueFrom(order, ['Staff_Name', 'staff_name']), '—')));
            row.appendChild(createElement('td', '', displayText(valueFrom(order, ['Party_Name', 'party_name']), '—')));
            row.appendChild(createElement('td', 'text-end', formatCurrency(valueFrom(order, ['Total_Amount', 'total_amount']))));
            row.appendChild(createElement('td', '', formatDate(valueFrom(order, ['Order_Date', 'order_date']))));
            fragment.appendChild(row);
        });
        body.replaceChildren(fragment);
    }

    function monthLabel(value) {
        const raw = displayText(value, '—');
        const match = /^(\d{4})-(\d{1,2})/.exec(raw);
        if (!match) {
            return { month: raw, year: '' };
        }

        const monthNumber = Number(match[2]);
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return {
            month: monthNames[monthNumber - 1] || raw,
            year: match[1]
        };
    }

    function createTrendBar(value, maxValue, label, barClass, valueClass) {
        const number = Math.max(0, numericValue(value) || 0);
        const wrapper = createElement('div', 'bar-wrapper');
        const bar = createElement('div', `trend-bar-enhanced ${barClass} animated`);
        const height = maxValue > 0 && number > 0 ? Math.max(8, (number / maxValue) * 220) : 0;
        bar.style.height = `${height}px`;
        if (height === 0) {
            bar.style.minHeight = '0';
        }
        bar.setAttribute('role', 'img');
        bar.setAttribute('aria-label', `${label}: ${formatCurrency(number)}`);
        bar.appendChild(createElement('div', 'bar-glow'));

        const valueLabel = createElement('span', `bar-value ${valueClass}`, formatCurrency(number));
        const tooltip = createElement('div', 'bar-tooltip', `${label}: ${formatCurrency(number)}`);
        wrapper.appendChild(bar);
        wrapper.appendChild(valueLabel);
        wrapper.appendChild(tooltip);
        return wrapper;
    }

    function renderMonthlyTrend(trend) {
        const container = byId('monthlyTrendChart');
        if (!container) {
            return;
        }

        const validTrend = trend.filter(item => item && typeof item === 'object');
        if (validTrend.length === 0) {
            setContainerState(container, 'empty', 'No monthly activity found.');
            return;
        }

        const sales = validTrend.map(item => Math.max(0, numberFrom(item, ['Sales', 'sales', 'total_sales']) || 0));
        const purchases = validTrend.map(item => Math.max(0, numberFrom(item, ['Purchases', 'purchases', 'total_purchases']) || 0));
        const maxValue = Math.max(...sales, ...purchases, 0);
        const chart = createElement('div', 'enhanced-chart-container');
        const yAxis = createElement('div', 'y-axis');
        [maxValue, maxValue * 0.75, maxValue * 0.5, maxValue * 0.25, 0].forEach(value => {
            yAxis.appendChild(createElement('span', 'y-axis-label', formatCurrency(value)));
        });

        const chartArea = createElement('div', 'chart-area');
        const gridLines = createElement('div', 'grid-lines');
        [0, 25, 50, 75, 100].forEach(position => {
            const line = createElement('div', 'grid-line');
            line.style.top = `${position}%`;
            gridLines.appendChild(line);
        });

        const bars = createElement('div', 'bars-container');
        validTrend.forEach((item, index) => {
            const group = createElement('div', 'bar-group');
            group.appendChild(createTrendBar(sales[index], maxValue, 'Sales', 'sales-bar', 'sales-value'));
            group.appendChild(createTrendBar(purchases[index], maxValue, 'Purchases', 'purchases-bar', 'purchases-value'));

            const label = monthLabel(valueFrom(item, ['Month', 'month', 'period']));
            const monthElement = createElement('div', 'month-label');
            monthElement.appendChild(createElement('span', 'month-name', label.month));
            if (label.year) {
                monthElement.appendChild(createElement('span', 'year-num', label.year));
            }
            group.appendChild(monthElement);
            bars.appendChild(group);
        });

        chartArea.appendChild(gridLines);
        chartArea.appendChild(bars);
        chart.appendChild(yAxis);
        chart.appendChild(chartArea);

        const legend = createElement('div', 'chart-legend');
        const salesLegend = createElement('div', 'legend-item');
        salesLegend.appendChild(createElement('span', 'legend-color sales-legend'));
        salesLegend.appendChild(createElement('span', 'legend-text', 'Sales'));
        const purchaseLegend = createElement('div', 'legend-item');
        purchaseLegend.appendChild(createElement('span', 'legend-color purchases-legend'));
        purchaseLegend.appendChild(createElement('span', 'legend-text', 'Purchases'));
        legend.appendChild(salesLegend);
        legend.appendChild(purchaseLegend);

        container.replaceChildren(chart, legend);
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

    function resetReportSections() {
        const topProducts = byId('topProductsBody');
        if (topProducts) {
            topProducts.replaceChildren(createTableStateRow(4, 'loading', 'Loading...'));
        }
        setContainerState(byId('categoryChart'), 'loading', 'Loading...');
        const recentOrders = byId('recentOrdersBody');
        if (recentOrders) {
            recentOrders.replaceChildren(createTableStateRow(6, 'loading', 'Loading...'));
        }
        setContainerState(byId('monthlyTrendChart'), 'loading', 'Loading...');
    }

    async function loadReports() {
        if (reportsRequest) {
            return reportsRequest;
        }

        const generation = reportsGeneration + 1;
        reportsGeneration = generation;
        currentReportData = null;
        setReportState('info', 'Loading report data...', false);
        setReportControlsBusy(true);
        resetReportSections();

        const request = requestJson('reports/stats.php').then(data => {
            if (generation !== reportsGeneration) {
                return;
            }
            currentReportData = data && typeof data === 'object' ? data : {};
            renderReport(currentReportData);
            setReportControlsBusy(false);
            setReportState('', '');
        }).catch(error => {
            if (generation !== reportsGeneration || (error && error.name === 'AbortError')) {
                return;
            }

            currentReportData = null;
            setReportControlsBusy(false);
            if (error && error.status === 401) {
                setReportState('danger', 'Your session has expired.', false);
            } else if (error && error.status === 403) {
                setReportState('warning', 'Reports are restricted to Administrators.', false);
            } else {
                setReportState('danger', 'Reports could not be loaded.', true);
            }
            renderReport({});
            if (reportsRequest === trackedRequest) {
                reportsRequest = null;
            }
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
        if (currentReportData === null) {
            return;
        }

        const rows = [
            ['Metric', 'Value'],
            ['Total Sales', byId('totalSales')?.textContent || '—'],
            ['Total Purchases', byId('totalPurchases')?.textContent || '—'],
            ['Net Revenue', byId('netRevenue')?.textContent || '—'],
            ['Total Products', byId('totalProducts')?.textContent || '—'],
            ['Low Stock Alerts', byId('lowStockCount')?.textContent || '—'],
            ['Sell Orders', byId('sellOrderCount')?.textContent || '—'],
            ['Purchase Orders', byId('purchaseOrderCount')?.textContent || '—']
        ];
        const csv = rows.map(row => row.map(csvValue).join(',')).join('\n');
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

    function initReports() {
        if (typeof loadUserInfo === 'function') {
            loadUserInfo();
        }
        setupReportInteractions();
        loadReports();
    }

    window.loadReports = loadReports;
    window.exportReport = exportReport;
    document.addEventListener('DOMContentLoaded', initReports);
})();
