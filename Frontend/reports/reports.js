/**
 * QuickMart IOMS - Reports
 * Handles reports loading, chart rendering, and data visualization
 */

// ===========================================
// DOM Ready
// ===========================================
document.addEventListener("DOMContentLoaded", function () {
    // Load user info from common.js
    loadUserInfo();

    // Load reports data
    loadReports();

    console.log("QuickMart IOMS - Reports page initialized");
});

// ===========================================
// Load Reports Data
// ===========================================
async function loadReports() {
    try {
        const response = await fetch('/QuickMart code/backend/api/reports/stats.php');
        const data = await response.json();

        if (data.success) {
            renderSummaryCards(data.data.summary);
            renderTopProducts(data.data.top_products);
            renderCategoryChart(data.data.products_by_category);
            renderRecentOrders(data.data.recent_orders);
            renderMonthlyTrend(data.data.monthly_trend);
        } else {
            showToast('Error loading reports: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error loading reports:', error);
        showToast('Failed to load reports data', 'error');
    }
}

// ===========================================
// Render Summary Cards
// ===========================================
function renderSummaryCards(summary) {
    document.getElementById('totalSales').textContent = formatCurrency(summary.total_sales);
    document.getElementById('totalPurchases').textContent = formatCurrency(summary.total_purchases);
    document.getElementById('netRevenue').textContent = formatCurrency(summary.net_revenue);
    document.getElementById('totalProducts').textContent = summary.total_products;
    document.getElementById('lowStockCount').textContent = summary.low_stock_count;
    document.getElementById('sellOrderCount').textContent = summary.sell_orders;
    document.getElementById('purchaseOrderCount').textContent = summary.purchase_orders;
}

// ===========================================
// Render Top Products Table
// ===========================================
function renderTopProducts(products) {
    const tbody = document.getElementById('topProductsBody');

    if (!products || products.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="4" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox me-2"></i>No sales data yet
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = products.map((product, index) => `
        <tr>
            <td>
                <span class="badge ${index === 0 ? 'bg-warning' : index === 1 ? 'bg-secondary' : index === 2 ? 'bg-danger' : 'bg-light text-dark'}">
                    ${index + 1}
                </span>
            </td>
            <td class="fw-semibold">${product.Name}</td>
            <td class="text-center">
                <span class="badge bg-primary-soft text-primary">${product.total_sold} units</span>
            </td>
            <td class="text-end text-success fw-bold">$${formatCurrency(product.revenue)}</td>
        </tr>
    `).join('');
}

// ===========================================
// Render Category Chart (Simple Bar Chart)
// ===========================================
function renderCategoryChart(categories) {
    const container = document.getElementById('categoryChart');

    if (!categories || categories.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox me-2"></i>No category data
            </div>
        `;
        return;
    }

    // Find max count for scaling
    const maxCount = Math.max(...categories.map(c => parseInt(c.count)));

    container.innerHTML = categories.map(cat => {
        const percentage = (parseInt(cat.count) / maxCount) * 100;
        return `
            <div class="category-bar-item mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="fw-semibold">${cat.Category || 'General'}</span>
                    <span class="text-muted">${cat.count} products (${cat.total_stock || 0} units)</span>
                </div>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-primary" role="progressbar" 
                         style="width: ${percentage}%;" 
                         aria-valuenow="${cat.count}" aria-valuemin="0" aria-valuemax="${maxCount}">
                    </div>
                </div>
            </div>
        `;
    }).join('');
}

// ===========================================
// Render Recent Orders Table
// ===========================================
function renderRecentOrders(orders) {
    const tbody = document.getElementById('recentOrdersBody');

    if (!orders || orders.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox me-2"></i>No orders yet
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = orders.map(order => `
        <tr>
            <td><strong>${order.Order_ID}</strong></td>
            <td>
                <span class="badge ${order.Order_Type === 'Sell' ? 'bg-success' : 'bg-info'}">
                    <i class="fas ${order.Order_Type === 'Sell' ? 'fa-arrow-down' : 'fa-arrow-up'} me-1"></i>
                    ${order.Order_Type}
                </span>
            </td>
            <td>
                <i class="fas fa-user-tie me-1 text-muted"></i>
                ${order.Staff_Name || 'Unknown'}
            </td>
            <td>${order.Party_Name || '-'}</td>
            <td class="text-end fw-bold ${order.Order_Type === 'Sell' ? 'text-success' : 'text-info'}">
                $${formatCurrency(order.Total_Amount)}
            </td>
            <td class="text-muted">${formatDate(order.Order_Date)}</td>
        </tr>
    `).join('');
}

// ===========================================
// Render Monthly Trend Chart (Simple)
// ===========================================
function renderMonthlyTrend(trend) {
    const container = document.getElementById('monthlyTrendChart');

    if (!trend || trend.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="fas fa-inbox me-2"></i>Not enough data for trend analysis
            </div>
        `;
        return;
    }

    // Find max for scaling
    const maxValue = Math.max(...trend.map(t => Math.max(parseFloat(t.sales) || 0, parseFloat(t.purchases) || 0)));

    container.innerHTML = `
        <div class="d-flex align-items-end justify-content-around" style="height: 200px;">
            ${trend.map(month => {
        const salesHeight = maxValue > 0 ? (parseFloat(month.sales || 0) / maxValue) * 150 : 0;
        const purchasesHeight = maxValue > 0 ? (parseFloat(month.purchases || 0) / maxValue) * 150 : 0;
        const monthName = new Date(month.month + '-01').toLocaleDateString('en-US', { month: 'short' });

        return `
                    <div class="text-center flex-fill px-2">
                        <div class="d-flex align-items-end justify-content-center gap-1" style="height: 160px;">
                            <div class="trend-bar bg-success" style="width: 20px; height: ${salesHeight}px;" 
                                 title="Sales: $${formatCurrency(month.sales)}"></div>
                            <div class="trend-bar bg-info" style="width: 20px; height: ${purchasesHeight}px;"
                                 title="Purchases: $${formatCurrency(month.purchases)}"></div>
                        </div>
                        <small class="text-muted d-block mt-2">${monthName}</small>
                    </div>
                `;
    }).join('')}
        </div>
        <div class="d-flex justify-content-center gap-4 mt-3">
            <span><span class="badge bg-success">&nbsp;</span> Sales</span>
            <span><span class="badge bg-info">&nbsp;</span> Purchases</span>
        </div>
    `;
}

// ===========================================
// Helper Functions
// ===========================================
function formatCurrency(value) {
    return parseFloat(value || 0).toFixed(2);
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

// ===========================================
// Export Report (CSV)
// ===========================================
function exportReport() {
    // Gather visible data and create CSV
    showToast('Exporting report...', 'info');

    // Simple export - just summary
    const summary = {
        totalSales: document.getElementById('totalSales').textContent,
        totalPurchases: document.getElementById('totalPurchases').textContent,
        netRevenue: document.getElementById('netRevenue').textContent,
        totalProducts: document.getElementById('totalProducts').textContent
    };

    const csvContent = `QuickMart IOMS - Report Export
Generated: ${new Date().toLocaleString()}

Summary
----------------
Total Sales,$${summary.totalSales}
Total Purchases,$${summary.totalPurchases}
Net Revenue,$${summary.netRevenue}
Total Products,${summary.totalProducts}
`;

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `QuickMart_Report_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();

    showToast('Report exported successfully!', 'success');
}
