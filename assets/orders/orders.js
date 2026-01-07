/**
 * QuickMart IOMS - Orders Page JavaScript
 * Handles orders listing, filtering, and order creation modal
 */

// ===========================================
// Global Variables
// ===========================================
let availableProducts = [];
let orderItems = [];
let allOrdersCache = [];
let selectedProducts = {};
const TAX_RATE = 0.05;

// ===========================================
// DOM Ready
// ===========================================
document.addEventListener("DOMContentLoaded", function () {
    initializePage();
    setupEventListeners();
    loadProductsFromDatabase();
    loadAllOrders();
    setDefaultDate();
    console.log("QuickMart IOMS - Orders page initialized");
});

// ===========================================
// Initialize Page
// ===========================================
function initializePage() {
    loadUserInfo();
    updateOrderTypeLabel();
}

function setDefaultDate() {
    const dateInput = document.getElementById("deliveryDate");
    if (dateInput) {
        const today = new Date();
        today.setDate(today.getDate() + 3);
        dateInput.value = today.toISOString().split('T')[0];
    }
}

// ===========================================
// Load Products from Database
// ===========================================
async function loadProductsFromDatabase() {
    try {
        const response = await fetch('/QuickMart code/backend/api/products/list.php');
        const data = await response.json();

        if (data.success && data.data.products) {
            availableProducts = data.data.products.map(p => ({
                id: p.Product_ID,
                name: p.Name,
                category: p.Category || 'General',
                available: parseInt(p.Quantity) || 0,
                price: parseFloat(p.Price) || 0
            }));
            console.log(`Loaded ${availableProducts.length} products from database`);
        }
    } catch (error) {
        console.error('Error loading products:', error);
    }
}

// ===========================================
// Load All Orders
// ===========================================
async function loadAllOrders() {
    const tableBody = document.getElementById("ordersTableBody");
    if (!tableBody) return;

    // Show loading
    tableBody.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-5 text-muted">
                <i class="fas fa-spinner fa-spin fa-2x mb-3 d-block"></i>
                Loading orders...
            </td>
        </tr>
    `;

    try {
        const response = await fetch('/QuickMart code/backend/api/orders/list.php');
        const data = await response.json();

        if (data.success && data.data.orders) {
            allOrdersCache = data.data.orders;
            renderOrdersTable(allOrdersCache);
            updateOrderStats(allOrdersCache);
        } else {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3 d-block opacity-50"></i>
                        No orders yet. Click "New Order" to create one!
                    </td>
                </tr>
            `;
            updateOrderStats([]);
        }
    } catch (error) {
        console.error('Error loading orders:', error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5 text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-3 d-block"></i>
                    Error loading orders. Please refresh the page.
                </td>
            </tr>
        `;
    }
}

// ===========================================
// Render Orders Table
// ===========================================
function renderOrdersTable(orders) {
    const tableBody = document.getElementById("ordersTableBody");
    if (!tableBody) return;

    if (orders.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5 text-muted">
                    <i class="fas fa-filter fa-2x mb-3 d-block opacity-50"></i>
                    No orders match your filter.
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = orders.map(order => `
        <tr>
            <td><strong class="text-primary">${order.Order_ID}</strong></td>
            <td>
                <small class="text-muted">
                    <i class="fas fa-user-tie me-1"></i>${order.Staff_Name || 'Unknown'}
                </small>
            </td>
            <td>${order.Party_Name || '-'}</td>
            <td class="fw-bold text-success">$${parseFloat(order.Total_Amount || 0).toFixed(2)}</td>
            <td>
                <span class="badge ${order.Order_Type === 'Sell' ? 'bg-success' : 'bg-info'}">
                    <i class="fas ${order.Order_Type === 'Sell' ? 'fa-arrow-down' : 'fa-arrow-up'} me-1"></i>
                    ${order.Order_Type}
                </span>
            </td>
            <td class="text-muted">${formatOrderDate(order.Order_Date)}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="viewOrderDetails('${order.Order_ID}')">
                    <i class="fas fa-eye"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// ===========================================
// Update Order Stats
// ===========================================
function updateOrderStats(orders) {
    const totalOrders = orders.length;
    const sellOrders = orders.filter(o => o.Order_Type === 'Sell').length;
    const purchaseOrders = orders.filter(o => o.Order_Type === 'Purchase').length;
    const totalRevenue = orders
        .filter(o => o.Order_Type === 'Sell')
        .reduce((sum, o) => sum + parseFloat(o.Total_Amount || 0), 0);

    document.getElementById("totalOrdersCount").textContent = totalOrders;
    document.getElementById("sellOrdersCount").textContent = sellOrders;
    document.getElementById("purchaseOrdersCount").textContent = purchaseOrders;
    document.getElementById("totalRevenue").textContent = totalRevenue.toFixed(2);
}

// ===========================================
// Filter Orders
// ===========================================
function filterOrders(type) {
    // Update button states
    document.querySelectorAll('#filterAllBtn, #filterSellBtn, #filterPurchaseBtn').forEach(btn => {
        btn.classList.remove('active');
    });

    let filtered = allOrdersCache;

    if (type === 'all') {
        document.getElementById('filterAllBtn')?.classList.add('active');
    } else if (type === 'Sell') {
        document.getElementById('filterSellBtn')?.classList.add('active');
        filtered = allOrdersCache.filter(o => o.Order_Type === 'Sell');
    } else if (type === 'Purchase') {
        document.getElementById('filterPurchaseBtn')?.classList.add('active');
        filtered = allOrdersCache.filter(o => o.Order_Type === 'Purchase');
    }

    renderOrdersTable(filtered);
}

// ===========================================
// Search Orders
// ===========================================
function searchOrders(term) {
    const searchTerm = term.toLowerCase().trim();

    if (!searchTerm) {
        renderOrdersTable(allOrdersCache);
        return;
    }

    const filtered = allOrdersCache.filter(order =>
        order.Order_ID.toLowerCase().includes(searchTerm) ||
        (order.Party_Name && order.Party_Name.toLowerCase().includes(searchTerm)) ||
        (order.Staff_Name && order.Staff_Name.toLowerCase().includes(searchTerm))
    );

    renderOrdersTable(filtered);
}

// ===========================================
// View Order Details
// ===========================================
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`/QuickMart code/backend/api/orders/get.php?id=${orderId}`);
        const data = await response.json();

        if (data.success && data.data) {
            const order = data.data.order;
            const details = data.data.details;

            // Populate modal
            document.getElementById("modalOrderId").textContent = order.Order_ID;
            document.getElementById("modalCustomerName").textContent = order.Party_Name || 'N/A';
            document.getElementById("modalStaffName").textContent = `Staff: ${order.Staff_Name || order.Staff_ID}`;
            document.getElementById("modalOrderDate").textContent = formatOrderDate(order.Order_Date);

            const typeEl = document.getElementById("modalOrderType");
            typeEl.textContent = order.Order_Type;
            typeEl.className = `badge ${order.Order_Type === 'Sell' ? 'bg-success' : 'bg-info'}`;

            // Populate items
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

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById("orderDetailsModal"));
            modal.show();
        }
    } catch (error) {
        console.error('Error loading order details:', error);
        showToast('Error loading order details', 'error');
    }
}

// ===========================================
// Event Listeners Setup
// ===========================================
function setupEventListeners() {
    // Filter buttons
    document.getElementById("filterAllBtn")?.addEventListener("click", () => filterOrders('all'));
    document.getElementById("filterSellBtn")?.addEventListener("click", () => filterOrders('Sell'));
    document.getElementById("filterPurchaseBtn")?.addEventListener("click", () => filterOrders('Purchase'));

    // Search
    document.getElementById("searchOrders")?.addEventListener("input", debounce(function () {
        searchOrders(this.value);
    }, 300));

    // Order type change
    document.querySelectorAll('input[name="orderType"]').forEach(radio => {
        radio.addEventListener('change', updateOrderTypeLabel);
    });

    // Product search
    const searchInput = document.getElementById("productSearch");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(handleProductSearch, 300));
        searchInput.addEventListener("focus", handleProductSearch);
    }

    // Click outside to close suggestions
    document.addEventListener("click", function (e) {
        const suggestions = document.getElementById("productSuggestions");
        const searchInput = document.getElementById("productSearch");
        if (suggestions && !suggestions.contains(e.target) && e.target !== searchInput) {
            suggestions.classList.remove("show");
        }
    });

    // Add Item button - Opens product selection modal
    document.getElementById("addItemBtn")?.addEventListener("click", function () {
        const modalEl = document.getElementById("selectProductModal");
        if (modalEl) {
            new bootstrap.Modal(modalEl).show();
        }
    });

    // Product Selection Modal
    const selectProductModal = document.getElementById("selectProductModal");
    if (selectProductModal) {
        selectProductModal.addEventListener('show.bs.modal', loadModalProducts);
    }

    // Modal product search
    document.getElementById("modalProductSearch")?.addEventListener("input", debounce(filterModalProducts, 300));

    // Confirm add products
    document.getElementById("confirmAddProductsBtn")?.addEventListener("click", addSelectedProducts);

    // Discount and shipping
    document.getElementById("discountInput")?.addEventListener("input", calculateTotals);
    document.getElementById("shippingInput")?.addEventListener("input", calculateTotals);

    // Create order button - Shows review modal first
    document.getElementById("createOrderBtn")?.addEventListener("click", showOrderReview);

    // Confirm order button in review modal - Actually submits the order
    document.getElementById("confirmOrderBtn")?.addEventListener("click", submitOrderToServer);

    // Back to edit button in review modal
    document.getElementById("backToEditBtn")?.addEventListener("click", function () {
        bootstrap.Modal.getInstance(document.getElementById("orderReviewModal"))?.hide();
        setTimeout(() => {
            new bootstrap.Modal(document.getElementById("createOrderModal")).show();
        }, 300);
    });

    // Save draft
    document.getElementById("saveDraftBtn")?.addEventListener("click", saveDraft);

    // New order button in success modal
    document.getElementById("newOrderBtn")?.addEventListener("click", function () {
        bootstrap.Modal.getInstance(document.getElementById("successModal"))?.hide();
        resetOrderForm();
        setTimeout(() => {
            new bootstrap.Modal(document.getElementById("createOrderModal")).show();
        }, 300);
    });

    // Logout
    document.getElementById("logoutBtn")?.addEventListener("click", handleLogout);
}

// ===========================================
// Order Type Label Update
// ===========================================
function updateOrderTypeLabel() {
    const isSellOrder = document.getElementById("sellOrder")?.checked;
    const customerLabel = document.getElementById("customerLabel");
    if (customerLabel) {
        customerLabel.textContent = isSellOrder ? "Customer" : "Supplier";
    }
}

// ===========================================
// Product Search
// ===========================================
function handleProductSearch() {
    const searchInput = document.getElementById("productSearch");
    const suggestions = document.getElementById("productSuggestions");
    const searchTerm = searchInput.value.toLowerCase().trim();

    if (searchTerm.length < 1) {
        suggestions.classList.remove("show");
        return;
    }

    const filtered = availableProducts.filter(product =>
        product.name.toLowerCase().includes(searchTerm) ||
        product.category.toLowerCase().includes(searchTerm)
    );

    if (filtered.length === 0) {
        suggestions.innerHTML = `
            <div class="suggestion-item text-muted">
                <span>No products found matching "${searchTerm}"</span>
            </div>
        `;
    } else {
        suggestions.innerHTML = filtered.map(product => `
            <div class="suggestion-item" onclick="addProductToOrder('${product.id}')">
                <div>
                    <span class="product-name">${product.name}</span>
                    <div class="product-meta">${product.category} • ${product.available} available</div>
                </div>
                <span class="product-price">$${product.price.toFixed(2)}</span>
            </div>
        `).join('');
    }

    suggestions.classList.add("show");
}

// ===========================================
// Add Product to Order
// ===========================================
function addProductToOrder(productId) {
    const product = availableProducts.find(p => p.id === productId);
    if (!product) return;

    const existingIndex = orderItems.findIndex(item => item.id === productId);
    if (existingIndex !== -1) {
        orderItems[existingIndex].quantity++;
        showToast(`${product.name} quantity increased!`, "info");
    } else {
        orderItems.push({
            ...product,
            quantity: 1
        });
        showToast(`${product.name} added to order!`, "success");
    }

    renderOrderTable();
    document.getElementById("productSearch").value = "";
    document.getElementById("productSuggestions").classList.remove("show");
}

// ===========================================
// Render Order Table
// ===========================================
function renderOrderTable() {
    const tableBody = document.getElementById("orderTableBody");
    const emptyMessage = document.getElementById("emptyMessage");
    const isSellOrder = document.getElementById("sellOrder")?.checked;

    if (orderItems.length === 0) {
        tableBody.innerHTML = "";
        emptyMessage.style.display = "block";
    } else {
        emptyMessage.style.display = "none";

        tableBody.innerHTML = orderItems.map((item, index) => {
            // For Sell orders: max is available stock. For Purchase orders: no max limit
            const maxAttr = isSellOrder ? `max="${item.available}"` : '';
            const availableLabel = isSellOrder ? `${item.available} units` : 'N/A (Purchase)';

            return `
            <tr>
                <td class="product-cell">${item.name}</td>
                <td class="available-cell">${availableLabel}</td>
                <td class="price-cell">$<span class="item-price">${item.price.toFixed(2)}</span></td>
                <td>
                    <input type="number" class="form-control form-control-sm qty-input" 
                           style="direction: ltr; text-align: center;"
                           value="${item.quantity}" min="1" ${maxAttr}
                           onchange="updateQuantity(${index}, this.value)">
                </td>
                <td class="total-cell">$<span class="row-total">${(item.price * item.quantity).toFixed(2)}</span></td>
                <td>
                    <button class="remove-btn" onclick="removeFromOrder(${index})">[Remove]</button>
                </td>
            </tr>
        `}).join('');
    }

    calculateTotals();
}

// ===========================================
// Update Quantity
// ===========================================
function updateQuantity(index, newQty) {
    const item = orderItems[index];
    if (item) {
        let qty = parseInt(newQty) || 1;
        const isSellOrder = document.getElementById("sellOrder")?.checked;

        if (isSellOrder && qty > item.available) {
            qty = item.available;
            showToast(`Maximum available: ${item.available}`, "error");
        }

        if (qty < 1) qty = 1;
        item.quantity = qty;
        renderOrderTable();
    }
}

// ===========================================
// Remove from Order
// ===========================================
function removeFromOrder(index) {
    const removedItem = orderItems.splice(index, 1)[0];
    showToast(`${removedItem.name} removed`, "info");
    renderOrderTable();
}

// ===========================================
// Calculate Totals
// ===========================================
function calculateTotals() {
    let subtotal = orderItems.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const discount = parseFloat(document.getElementById("discountInput")?.value) || 0;
    const shipping = parseFloat(document.getElementById("shippingInput")?.value) || 0;
    const tax = subtotal * TAX_RATE;
    const grandTotal = subtotal + tax + shipping - discount;

    document.getElementById("subtotalDisplay").textContent = subtotal.toFixed(2);
    document.getElementById("taxDisplay").textContent = tax.toFixed(2);
    document.getElementById("discountDisplay").textContent = discount.toFixed(2);
    document.getElementById("shippingDisplay").textContent = shipping.toFixed(2);
    document.getElementById("grandTotalDisplay").textContent = grandTotal.toFixed(2);
}

// ===========================================
// Step 1: Show Order Review Modal
// ===========================================
function showOrderReview() {
    // Validate order items
    if (orderItems.length === 0) {
        showToast("Please add at least one product.", "error");
        return;
    }

    const customerName = document.getElementById("customerName").value.trim();
    if (!customerName) {
        showToast("Please enter customer/supplier name.", "error");
        document.getElementById("customerName").focus();
        return;
    }

    const staffId = sessionStorage.getItem("staffId") || sessionStorage.getItem("userStaffId");
    if (!staffId) {
        showToast("Session expired. Please login again.", "error");
        setTimeout(() => {
            window.location.href = "/QuickMart code/assets/login-signup/login.html";
        }, 2000);
        return;
    }

    // Populate review modal
    populateReviewModal();

    // Close create order modal and show review modal
    bootstrap.Modal.getInstance(document.getElementById("createOrderModal"))?.hide();
    setTimeout(() => {
        new bootstrap.Modal(document.getElementById("orderReviewModal")).show();
    }, 300);
}

// ===========================================
// Populate Review Modal
// ===========================================
function populateReviewModal() {
    const orderType = document.querySelector('input[name="orderType"]:checked').value === 'sell' ? 'Sell' : 'Purchase';
    const customerName = document.getElementById("customerName").value.trim();
    const customerEmail = document.getElementById("customerEmail").value.trim();
    const customerPhone = document.getElementById("customerPhone").value.trim();
    const staffName = sessionStorage.getItem("userName") || "Staff";

    // Order Info
    const typeEl = document.getElementById("reviewOrderType");
    typeEl.textContent = orderType;
    typeEl.className = `badge ${orderType === 'Sell' ? 'bg-success' : 'bg-info'}`;
    document.getElementById("reviewStaffName").textContent = staffName;

    // Party Info
    document.getElementById("reviewPartyTitle").textContent = orderType === 'Sell' ? 'Customer' : 'Supplier';
    document.getElementById("reviewPartyName").textContent = customerName || '-';
    document.getElementById("reviewPartyEmail").textContent = customerEmail || '-';
    document.getElementById("reviewPartyPhone").textContent = customerPhone || '-';

    // Items Table
    document.getElementById("reviewItemCount").textContent = orderItems.length;
    const itemsBody = document.getElementById("reviewItemsBody");
    if (orderItems.length > 0) {
        itemsBody.innerHTML = orderItems.map(item => `
            <tr>
                <td>${item.name}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">$${item.price.toFixed(2)}</td>
                <td class="text-end fw-bold">$${(item.price * item.quantity).toFixed(2)}</td>
            </tr>
        `).join('');
    }

    // Financial Summary
    const subtotal = parseFloat(document.getElementById("subtotalDisplay").textContent);
    const tax = parseFloat(document.getElementById("taxDisplay").textContent);
    const discount = parseFloat(document.getElementById("discountInput").value) || 0;
    const shipping = parseFloat(document.getElementById("shippingInput").value) || 0;
    const grandTotal = parseFloat(document.getElementById("grandTotalDisplay").textContent);

    document.getElementById("reviewSubtotal").textContent = subtotal.toFixed(2);
    document.getElementById("reviewTax").textContent = tax.toFixed(2);
    document.getElementById("reviewDiscount").textContent = discount.toFixed(2);
    document.getElementById("reviewShipping").textContent = shipping.toFixed(2);
    document.getElementById("reviewGrandTotal").textContent = grandTotal.toFixed(2);

    // Payment & Delivery
    const paymentMethods = {
        'cod': 'Cash on Delivery',
        'bank': 'Bank Transfer',
        'credit': 'Credit (Net 30)',
        'card': 'Credit Card'
    };
    const paymentMethod = document.getElementById("paymentTerms").value;
    document.getElementById("reviewPaymentMethod").textContent = paymentMethods[paymentMethod] || 'Cash on Delivery';

    const deliveryDate = document.getElementById("deliveryDate").value;
    if (deliveryDate) {
        document.getElementById("reviewDeliveryDate").textContent = new Date(deliveryDate).toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    } else {
        document.getElementById("reviewDeliveryDate").textContent = '-';
    }
}

// ===========================================
// Step 2: Submit Order to Server (After Confirmation)
// ===========================================
async function submitOrderToServer() {
    const orderType = document.querySelector('input[name="orderType"]:checked').value === 'sell' ? 'Sell' : 'Purchase';
    const customerName = document.getElementById("customerName").value.trim();
    const staffId = sessionStorage.getItem("staffId") || sessionStorage.getItem("userStaffId");

    const btn = document.getElementById("confirmOrderBtn");
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';

    try {
        const response = await fetch('/QuickMart code/backend/api/orders/create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                staff_id: staffId,
                order_type: orderType,
                party_name: customerName,
                items: orderItems.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    price: item.price
                }))
            })
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Failed to create order');
        }

        // Store order data for invoice modal
        const orderData = {
            orderId: data.data.order_id,
            orderType: orderType,
            customerName: customerName,
            customerEmail: document.getElementById("customerEmail").value.trim(),
            customerPhone: document.getElementById("customerPhone").value.trim(),
            items: [...orderItems],
            subtotal: parseFloat(document.getElementById("subtotalDisplay").textContent),
            tax: parseFloat(document.getElementById("taxDisplay").textContent),
            discount: parseFloat(document.getElementById("discountInput").value) || 0,
            shipping: parseFloat(document.getElementById("shippingInput").value) || 0,
            grandTotal: parseFloat(document.getElementById("grandTotalDisplay").textContent),
            paymentMethod: document.getElementById("paymentTerms").value,
            deliveryDate: document.getElementById("deliveryDate").value,
            staffName: sessionStorage.getItem("userName") || "Staff"
        };

        // Close review modal
        bootstrap.Modal.getInstance(document.getElementById("orderReviewModal"))?.hide();

        // Populate invoice confirmation modal
        populateInvoiceConfirmation(orderData);

        setTimeout(() => {
            new bootstrap.Modal(document.getElementById("successModal")).show();
        }, 300);

        // Reset and reload
        resetOrderForm();
        loadAllOrders();

    } catch (error) {
        console.error("Order Error:", error);
        showToast(error.message || "Failed to create order.", "error");
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> CONFIRM ORDER';
    }
}

// ===========================================
// Populate Invoice Confirmation Modal
// ===========================================
function populateInvoiceConfirmation(orderData) {
    // Order Number
    document.getElementById("orderNumber").textContent = `#${orderData.orderId}`;

    // Order Info
    const typeEl = document.getElementById("confirmOrderType");
    typeEl.textContent = orderData.orderType;
    typeEl.className = `badge ${orderData.orderType === 'Sell' ? 'bg-success' : 'bg-info'}`;

    document.getElementById("confirmOrderDate").textContent = new Date().toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    document.getElementById("confirmStaffName").textContent = orderData.staffName;

    // Party Info
    document.getElementById("confirmPartyTitle").textContent = orderData.orderType === 'Sell' ? 'Customer' : 'Supplier';
    document.getElementById("confirmPartyName").textContent = orderData.customerName || '-';
    document.getElementById("confirmPartyEmail").textContent = orderData.customerEmail || '-';
    document.getElementById("confirmPartyPhone").textContent = orderData.customerPhone || '-';

    // Items Table
    const itemsBody = document.getElementById("confirmItemsBody");
    if (orderData.items && orderData.items.length > 0) {
        itemsBody.innerHTML = orderData.items.map(item => `
            <tr>
                <td>${item.name}</td>
                <td class="text-center">${item.quantity}</td>
                <td class="text-end">$${item.price.toFixed(2)}</td>
                <td class="text-end fw-bold">$${(item.price * item.quantity).toFixed(2)}</td>
            </tr>
        `).join('');
    } else {
        itemsBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No items</td></tr>';
    }

    // Financial Summary
    document.getElementById("confirmSubtotal").textContent = orderData.subtotal.toFixed(2);
    document.getElementById("confirmTax").textContent = orderData.tax.toFixed(2);
    document.getElementById("confirmDiscount").textContent = orderData.discount.toFixed(2);
    document.getElementById("confirmShipping").textContent = orderData.shipping.toFixed(2);
    document.getElementById("confirmGrandTotal").textContent = orderData.grandTotal.toFixed(2);

    // Payment & Delivery
    const paymentMethods = {
        'cod': 'Cash on Delivery',
        'bank': 'Bank Transfer',
        'credit': 'Credit (Net 30)',
        'card': 'Credit Card'
    };
    document.getElementById("confirmPaymentMethod").textContent = paymentMethods[orderData.paymentMethod] || 'Cash on Delivery';

    if (orderData.deliveryDate) {
        document.getElementById("confirmDeliveryDate").textContent = new Date(orderData.deliveryDate).toLocaleDateString('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    } else {
        document.getElementById("confirmDeliveryDate").textContent = '-';
    }
}

// ===========================================
// Reset Order Form
// ===========================================
function resetOrderForm() {
    orderItems = [];
    selectedProducts = {};
    renderOrderTable();
    document.getElementById("customerName").value = "";
    document.getElementById("customerEmail").value = "";
    document.getElementById("customerAddress").value = "";
    document.getElementById("customerPhone").value = "";
    document.getElementById("discountInput").value = "0";
    document.getElementById("shippingInput").value = "0";
    document.getElementById("orderNotes").value = "";
    document.getElementById("sellOrder").checked = true;
    updateOrderTypeLabel();
    setDefaultDate();
}

// ===========================================
// Save Draft
// ===========================================
function saveDraft() {
    const draftData = {
        orderType: document.querySelector('input[name="orderType"]:checked').value,
        customer: {
            name: document.getElementById("customerName").value,
            email: document.getElementById("customerEmail").value,
            address: document.getElementById("customerAddress").value,
            phone: document.getElementById("customerPhone").value
        },
        items: orderItems,
        savedAt: new Date().toISOString()
    };

    localStorage.setItem("orderDraft", JSON.stringify(draftData));
    showToast("Draft saved successfully!", "success");
}

// ===========================================
// Modal Product Functions
// ===========================================
function loadModalProducts() {
    const tbody = document.getElementById("modalProductsBody");

    if (availableProducts.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="text-center py-4 text-muted">
                    <i class="fas fa-box-open me-2"></i>No products available
                </td>
            </tr>
        `;
        return;
    }

    renderModalProducts(availableProducts);
    selectedProducts = {};
    updateSelectedCount();
    document.getElementById("modalProductSearch").value = "";
}

function renderModalProducts(products) {
    const tbody = document.getElementById("modalProductsBody");
    const orderType = document.querySelector('input[name="orderType"]:checked')?.value;

    tbody.innerHTML = products.map(product => {
        const isSelected = selectedProducts[product.id] !== undefined;
        const isOutOfStock = product.available <= 0;
        const disableForSell = orderType === 'sell' && isOutOfStock;

        return `
            <tr class="${isSelected ? 'table-success' : ''} ${disableForSell ? 'text-muted' : ''}">
                <td class="text-center">
                    <input type="checkbox" 
                           class="form-check-input product-checkbox" 
                           data-id="${product.id}"
                           data-name="${product.name}"
                           data-price="${product.price}"
                           data-available="${product.available}"
                           ${isSelected ? 'checked' : ''}
                           ${disableForSell ? 'disabled' : ''}
                           onchange="toggleProductSelection(this)">
                </td>
                <td>
                    <strong>${product.name}</strong>
                    <small class="d-block text-muted">${product.id}</small>
                </td>
                <td><span class="badge bg-secondary">${product.category}</span></td>
                <td class="text-center">
                    <span class="${product.available <= 20 ? 'text-danger fw-bold' : 'text-success'}">
                        ${product.available}
                    </span>
                </td>
                <td class="text-end fw-bold">$${product.price.toFixed(2)}</td>
            </tr>
        `;
    }).join('');
}

function filterModalProducts() {
    const searchTerm = document.getElementById("modalProductSearch").value.toLowerCase();
    const filtered = availableProducts.filter(p =>
        p.name.toLowerCase().includes(searchTerm) ||
        p.id.toLowerCase().includes(searchTerm) ||
        p.category.toLowerCase().includes(searchTerm)
    );
    renderModalProducts(filtered);
}

function toggleProductSelection(checkbox) {
    const id = checkbox.dataset.id;

    if (checkbox.checked) {
        selectedProducts[id] = {
            id: id,
            name: checkbox.dataset.name,
            price: parseFloat(checkbox.dataset.price),
            available: parseInt(checkbox.dataset.available),
            quantity: 1
        };
    } else {
        delete selectedProducts[id];
    }

    checkbox.closest('tr').classList.toggle('table-success', checkbox.checked);
    updateSelectedCount();
    updateSelectedPreview();
}

function updateSelectedCount() {
    const count = Object.keys(selectedProducts).length;
    document.getElementById("selectedCount").textContent = `${count} product${count !== 1 ? 's' : ''} selected`;
    document.getElementById("confirmAddProductsBtn").disabled = count === 0;
}

function updateSelectedPreview() {
    const preview = document.getElementById("selectedProductsPreview");
    const list = document.getElementById("selectedProductsList");
    const products = Object.values(selectedProducts);
    const isSellOrder = document.getElementById("sellOrder")?.checked;

    if (products.length === 0) {
        preview.style.display = "none";
        return;
    }

    preview.style.display = "block";
    list.innerHTML = products.map(p => {
        // For Sell orders: limit by available. For Purchase: no limit
        const maxAttr = isSellOrder ? `max="${p.available}"` : '';

        return `
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
            <div>
                <strong>${p.name}</strong>
                <small class="text-muted ms-2">$${p.price.toFixed(2)}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Qty:</label>
                <input type="number" 
                       class="form-control form-control-sm" 
                       style="width: 70px; text-align: center;" 
                       min="1" ${maxAttr}
                       value="${p.quantity}"
                       onchange="updateProductQuantity('${p.id}', this.value)">
            </div>
        </div>
    `}).join('');
}

function updateProductQuantity(productId, quantity) {
    if (selectedProducts[productId]) {
        const isSellOrder = document.getElementById("sellOrder")?.checked;
        const maxQty = selectedProducts[productId].available;

        // For Sell orders: limit quantity. For Purchase: no limit
        if (isSellOrder) {
            quantity = Math.max(1, Math.min(parseInt(quantity) || 1, maxQty));
        } else {
            quantity = Math.max(1, parseInt(quantity) || 1);
        }

        selectedProducts[productId].quantity = quantity;
    }
}

function addSelectedProducts() {
    const products = Object.values(selectedProducts);

    if (products.length === 0) {
        showToast("Please select at least one product", "error");
        return;
    }

    products.forEach(product => {
        const existingIndex = orderItems.findIndex(item => item.id === product.id);

        if (existingIndex >= 0) {
            orderItems[existingIndex].quantity += product.quantity;
        } else {
            orderItems.push({
                id: product.id,
                name: product.name,
                category: 'Product',
                available: product.available,
                price: product.price,
                quantity: product.quantity
            });
        }
    });

    renderOrderTable();
    bootstrap.Modal.getInstance(document.getElementById("selectProductModal"))?.hide();
    selectedProducts = {};
    showToast(`Added ${products.length} product(s) to order`, "success");
}

// ===========================================
// Helper Functions
// ===========================================
function formatOrderDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
