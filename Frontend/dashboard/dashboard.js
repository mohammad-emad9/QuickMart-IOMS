/**
 * QuickMart IOMS - Dashboard JavaScript
 * Handles dashboard data loading and UI interactions
 * Note: Session management and common utilities are in common.js
 */

// Wait for DOM to be fully loaded
document.addEventListener("DOMContentLoaded", function () {

    // Check session on page load (uses common.js)
    // Uncomment the line below to enable session protection
    // if (!checkSession()) return;

    // Note: loadUserInfo() is now provided by common.js

    // ===========================================
    // Update Date and Time
    // ===========================================
    function updateDateTime() {
        const now = new Date();

        const dateOptions = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };

        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };

        const currentDateEl = document.getElementById("currentDate");
        const currentTimeEl = document.getElementById("currentTime");

        if (currentDateEl) {
            currentDateEl.textContent = now.toLocaleDateString('en-US', dateOptions);
        }

        if (currentTimeEl) {
            currentTimeEl.textContent = now.toLocaleTimeString('en-US', timeOptions);
        }
    }

    // Update time every second
    updateDateTime();
    setInterval(updateDateTime, 1000);

    // ===========================================
    // Load Dashboard Statistics from Database
    // ===========================================
    async function loadDashboardStats() {
        try {
            const response = await fetch('/QuickMart code/backend/api/reports/stats.php');
            const data = await response.json();

            if (data.success && data.data.summary) {
                const stats = data.data.summary;

                // Update KPI cards
                animateNumber('totalProducts', stats.total_products);
                animateNumber('lowStock', stats.low_stock_count);
                animateNumber('totalOrders', stats.total_orders);

                // Financial calculations
                const totalSales = parseFloat(stats.total_sales || 0);
                const totalPurchases = parseFloat(stats.total_purchases || 0);
                const netProfit = totalSales - totalPurchases;

                // Update displays
                const salesEl = document.getElementById('totalSales');
                if (salesEl) {
                    salesEl.textContent = totalSales.toFixed(2);
                }

                const purchasesEl = document.getElementById('totalPurchases');
                if (purchasesEl) {
                    purchasesEl.textContent = totalPurchases.toFixed(2);
                }

                const netProfitEl = document.getElementById('netProfitValue');
                if (netProfitEl) {
                    netProfitEl.textContent = netProfit.toFixed(2);

                    // Color based on profit/loss
                    const parentSpan = netProfitEl.parentElement;
                    if (parentSpan) {
                        if (netProfit >= 0) {
                            parentSpan.className = 'text-success';
                        } else {
                            parentSpan.className = 'text-danger';
                            netProfitEl.textContent = netProfit.toFixed(2); // Shows negative
                        }
                    }
                }
            }
        } catch (error) {
            console.error('Error loading dashboard stats:', error);
            // Fallback to zero values if API fails
            animateNumber('totalProducts', 0);
            animateNumber('lowStock', 0);
            animateNumber('totalOrders', 0);
        }
    }

    // Animate number counting effect
    function animateNumber(elementId, targetValue) {
        const element = document.getElementById(elementId);
        if (!element) return;

        let current = 0;
        const increment = Math.ceil(targetValue / 30);
        const duration = 1000; // 1 second
        const stepTime = duration / (targetValue / increment);

        const timer = setInterval(() => {
            current += increment;
            if (current >= targetValue) {
                current = targetValue;
                clearInterval(timer);
            }
            element.textContent = current;
        }, stepTime);
    }

    // ===========================================
    // Load Low Stock Alerts from Database
    // ===========================================
    async function loadLowStockAlerts() {
        const lowStockList = document.getElementById("lowStockList");
        if (!lowStockList) return;

        // Show loading state
        lowStockList.innerHTML = `
            <div class="text-center py-3 text-muted">
                <i class="fas fa-spinner fa-spin me-2"></i>Loading...
            </div>
        `;

        try {
            const response = await fetch('/QuickMart code/backend/api/products/list.php');
            const data = await response.json();

            if (data.success && data.data.products) {
                // Filter low stock products (Status = 'Low Stock' or 'Out of Stock')
                const lowStockProducts = data.data.products.filter(p =>
                    p.Status === 'Low Stock' || p.Status === 'Out of Stock'
                );

                if (lowStockProducts.length === 0) {
                    lowStockList.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            All products are in stock!
                        </div>
                    `;
                    return;
                }

                lowStockList.innerHTML = lowStockProducts.map(product => `
                    <div class="low-stock-item">
                        <div class="product-info">
                            <h6>${product.Name}</h6>
                            <small><i class="fas fa-tag me-1"></i>Category: ${product.Category}</small>
                        </div>
                        <div class="stock-count ${product.Status === 'Out of Stock' ? 'text-danger' : ''}">
                            <i class="fas fa-exclamation-circle"></i>
                            ${product.Quantity} units left
                        </div>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading low stock alerts:', error);
            lowStockList.innerHTML = `
                <div class="text-center py-3 text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error loading data
                </div>
            `;
        }
    }

    // ===========================================
    // Load Recent Orders (From Database)
    // ===========================================
    async function loadRecentOrders() {
        const ordersTable = document.getElementById("recentOrdersTable");
        if (!ordersTable) return;

        // Show loading state
        ordersTable.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <i class="fas fa-spinner fa-spin me-2"></i>Loading orders...
                </td>
            </tr>
        `;

        try {
            const response = await fetch('/QuickMart code/backend/api/orders/list.php');
            const data = await response.json();

            if (data.success && data.data.orders && data.data.orders.length > 0) {
                // Take only the 5 most recent orders
                const recentOrders = data.data.orders.slice(0, 5);

                ordersTable.innerHTML = recentOrders.map(order => `
                    <tr>
                        <td><strong>${order.Order_ID}</strong></td>
                        <td>
                            <small class="text-muted">
                                <i class="fas fa-user-tie me-1"></i>${order.Staff_Name || 'Unknown'}
                            </small>
                        </td>
                        <td>${order.Party_Name || '-'}</td>
                        <td class="fw-bold">$${parseFloat(order.Total_Amount || 0).toFixed(2)}</td>
                        <td>
                            <span class="status-badge status-${order.Order_Type === 'Sell' ? 'completed' : 'confirmed'}">
                                <i class="fas ${order.Order_Type === 'Sell' ? 'fa-arrow-down' : 'fa-arrow-up'} me-1"></i>
                                ${order.Order_Type}
                            </span>
                        </td>
                        <td class="text-muted">${formatDate(order.Order_Date)}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary btn-view" onclick="viewOrderFromDB('${order.Order_ID}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            } else {
                ordersTable.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox me-2"></i>No orders yet. <a href="/QuickMart code/backend/views/create-order.php">Create your first order!</a>
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error loading recent orders:', error);
            ordersTable.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>Error loading orders
                    </td>
                </tr>
            `;
        }
    }

    // View order from database (for Recent Orders section)
    window.viewOrderFromDB = async function (orderId) {
        try {
            const response = await fetch(`/QuickMart code/backend/api/orders/get.php?id=${orderId}`);
            const data = await response.json();

            if (data.success && data.data) {
                const order = data.data.order;
                const details = data.data.details;

                document.getElementById("modalOrderId").textContent = order.Order_ID;
                document.getElementById("modalCustomerName").textContent = order.Party_Name || 'N/A';
                document.getElementById("modalCustomerContact").textContent = `Staff: ${order.Staff_Name || order.Staff_ID}`;
                document.getElementById("modalOrderDate").textContent = formatDate(order.Order_Date);
                document.getElementById("modalOrderType").textContent = order.Order_Type;

                const statusEl = document.getElementById("modalOrderStatus");
                statusEl.textContent = order.Order_Type;
                statusEl.className = `badge bg-${order.Order_Type === 'Sell' ? 'success' : 'info'}`;

                const itemsBody = document.getElementById("modalItemsBody");
                if (details && details.length > 0) {
                    itemsBody.innerHTML = details.map(item => `
                        <tr>
                            <td>${item.Product_Name || item.Product_ID}</td>
                            <td class="text-center">${item.Ordered_Qty}</td>
                            <td class="text-end">$${parseFloat(item.Sold_Price).toFixed(2)}</td>
                            <td class="text-end fw-bold">$${parseFloat(item.Line_Total).toFixed(2)}</td>
                        </tr>
                    `).join('');

                    const subtotal = data.data.total_amount;
                    const tax = subtotal * 0.05;
                    const total = subtotal + tax;

                    document.getElementById("modalSubtotal").textContent = subtotal.toFixed(2);
                    document.getElementById("modalTax").textContent = tax.toFixed(2);
                    document.getElementById("modalTotal").textContent = total.toFixed(2);
                } else {
                    itemsBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No items</td></tr>';
                }

                const orderModal = new bootstrap.Modal(document.getElementById("orderDetailsModal"));
                orderModal.show();
            }
        } catch (error) {
            console.error('Error loading order details:', error);
            alert('Error loading order details');
        }
    };

    // Helper function to get status icon
    function getStatusIcon(status) {
        const icons = {
            pending: 'fa-clock',
            confirmed: 'fa-check',
            completed: 'fa-check-circle',
            cancelled: 'fa-times-circle'
        };
        return icons[status] || 'fa-info-circle';
    }

    // Note: capitalizeFirst(), formatDate(), and logout handler are now in common.js

    // ===========================================
    // Initialize Dashboard
    // ===========================================
    function initDashboard() {
        loadUserInfo();
        loadDashboardStats();
        loadLowStockAlerts();
    }

    // Start loading dashboard data
    initDashboard();

    console.log("QuickMart IOMS - Dashboard initialized");
});

// ===========================================
// All Orders Modal - Global Functions
// ===========================================
let allOrdersCache = [];

// Load all orders when modal opens
document.getElementById('allOrdersModal')?.addEventListener('show.bs.modal', function () {
    loadAllOrders();
});

// Load All Orders from Database
async function loadAllOrders() {
    const tableBody = document.getElementById('allOrdersTableBody');

    try {
        const response = await fetch('/QuickMart code/backend/api/orders/list.php');
        const data = await response.json();

        if (data.success && data.data.orders) {
            allOrdersCache = data.data.orders;
            renderAllOrdersTable(allOrdersCache);
        } else {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="fas fa-inbox me-2"></i>No orders found
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error loading orders
                </td>
            </tr>
        `;
    }
}

// Render Orders Table
function renderAllOrdersTable(orders) {
    const tableBody = document.getElementById('allOrdersTableBody');

    if (orders.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <i class="fas fa-inbox me-2"></i>No orders found
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = orders.map(order => `
        <tr>
            <td><strong>${order.Order_ID}</strong></td>
            <td>
                <span class="badge ${order.Order_Type === 'Sell' ? 'bg-success' : 'bg-info'}">
                    <i class="fas ${order.Order_Type === 'Sell' ? 'fa-arrow-down' : 'fa-arrow-up'} me-1"></i>
                    ${order.Order_Type}
                </span>
            </td>
            <td>${order.Party_Name || '-'}</td>
            <td>${order.Item_Count || 0} items</td>
            <td class="text-success fw-bold">$${parseFloat(order.Total_Amount || 0).toFixed(2)}</td>
            <td>${formatOrderDate(order.Order_Date)}</td>
            <td>
                <button class="btn btn-sm btn-outline-light" onclick="viewOrderDetails('${order.Order_ID}')">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// Filter Orders
function filterAllOrders(type) {
    // Update active button
    document.querySelectorAll('#allOrdersModal .btn-outline-light, #allOrdersModal .btn-outline-success, #allOrdersModal .btn-outline-info').forEach(btn => {
        btn.classList.remove('active');
    });

    if (type === 'all') {
        document.getElementById('filterAll').classList.add('active');
        renderAllOrdersTable(allOrdersCache);
    } else if (type === 'Sell') {
        document.getElementById('filterSell').classList.add('active');
        const filtered = allOrdersCache.filter(o => o.Order_Type === 'Sell');
        renderAllOrdersTable(filtered);
    } else if (type === 'Purchase') {
        document.getElementById('filterPurchase').classList.add('active');
        const filtered = allOrdersCache.filter(o => o.Order_Type === 'Purchase');
        renderAllOrdersTable(filtered);
    }
}

// Format date helper
function formatOrderDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// View Order Details (from All Orders Modal)
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`/QuickMart code/backend/api/orders/get.php?id=${orderId}`);
        const data = await response.json();

        if (data.success && data.data) {
            const order = data.data.order;
            const details = data.data.details;

            // Close all orders modal
            bootstrap.Modal.getInstance(document.getElementById('allOrdersModal'))?.hide();

            // Populate and show order details modal
            document.getElementById("modalOrderId").textContent = order.Order_ID;
            document.getElementById("modalCustomerName").textContent = order.Party_Name || 'N/A';
            document.getElementById("modalCustomerContact").textContent = `Staff: ${order.Staff_Name || order.Staff_ID}`;
            document.getElementById("modalOrderDate").textContent = formatOrderDate(order.Order_Date);
            document.getElementById("modalOrderType").textContent = order.Order_Type;

            // Set status badge
            const statusEl = document.getElementById("modalOrderStatus");
            statusEl.textContent = order.Order_Type;
            statusEl.className = `badge bg-${order.Order_Type === 'Sell' ? 'success' : 'info'}`;

            // Populate items table
            const itemsBody = document.getElementById("modalItemsBody");
            if (details && details.length > 0) {
                itemsBody.innerHTML = details.map(item => `
                    <tr>
                        <td>${item.Product_Name || item.Product_ID}</td>
                        <td class="text-center">${item.Ordered_Qty}</td>
                        <td class="text-end">$${parseFloat(item.Sold_Price).toFixed(2)}</td>
                        <td class="text-end fw-bold">$${parseFloat(item.Line_Total).toFixed(2)}</td>
                    </tr>
                `).join('');

                // Calculate totals
                const subtotal = data.data.total_amount;
                const tax = subtotal * 0.05;
                const total = subtotal + tax;

                document.getElementById("modalSubtotal").textContent = subtotal.toFixed(2);
                document.getElementById("modalTax").textContent = tax.toFixed(2);
                document.getElementById("modalTotal").textContent = total.toFixed(2);
            } else {
                itemsBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No items</td></tr>';
            }

            // Show the modal
            setTimeout(() => {
                const orderModal = new bootstrap.Modal(document.getElementById("orderDetailsModal"));
                orderModal.show();
            }, 300);
        }
    } catch (error) {
        console.error('Error loading order details:', error);
        alert('Error loading order details');
    }
}
