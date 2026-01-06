/**
 * QuickMart IOMS - Order Details
 * Handles order display, stock impact visualization, and actions
 */

// ===========================================
// Sample Order Data (In real app, fetched from API)
// ===========================================
const sampleOrder = {
    id: "245",
    date: "2025-10-15 10:30 AM",
    staff: "Ali (STF001)",
    type: "Sell", // "Sell" or "Purchase"
    status: "Completed", // "Completed", "Pending", "Confirmed", "Cancelled"
    customer: {
        name: "John Doe",
        email: "john@email.com",
        phone: "+1 234 567 8900"
    },
    payment: {
        method: "Cash",
        invoiceNo: "INV-2025-245"
    },
    items: [
        { id: "002", name: "RTX 3060 12GB", qty: 2, unitPrice: 320.00, currentStock: 3 },
        { id: "007", name: "Thermal Paste 5g", qty: 5, unitPrice: 8.00, currentStock: 12 },
        { id: "008", name: "HDMI 2.1 Cable", qty: 3, unitPrice: 15.00, currentStock: 40 },
        { id: "011", name: "USB-C Hub", qty: 1, unitPrice: 45.00, currentStock: 8 }
    ],
    notes: "Customer requested invoice for tax purposes.",
    subtotal: 770.00,
    tax: 38.50,
    discount: 0.00,
    shipping: 0.00,
    grandTotal: 808.50
};

// Current order data
let currentOrder = null;

// ===========================================
// DOM Ready
// ===========================================
document.addEventListener("DOMContentLoaded", function () {
    initializePage();
    loadOrderDetails();
    setupEventListeners();
    console.log("QuickMart IOMS - Order Details page initialized");
});

// ===========================================
// Initialize Page
// ===========================================
function initializePage() {
    loadUserInfo();
}

// ===========================================
// Load Order Details
// ===========================================
function loadOrderDetails() {
    // Get order ID from URL parameters (e.g., order-details.html?id=245)
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('id') || "245";

    // Fetch order data from API
    currentOrder = { ...sampleOrder, id: orderId };

    // Update page title
    document.title = `QuickMart IOMS - Order #${currentOrder.id}`;

    // Populate header info
    document.getElementById("orderID").textContent = currentOrder.id;
    document.getElementById("orderDate").textContent = currentOrder.date;
    document.getElementById("staffName").textContent = currentOrder.staff;

    // Order type badge
    const orderTypeEl = document.getElementById("orderType");
    orderTypeEl.textContent = currentOrder.type.toUpperCase() + " ORDER";
    orderTypeEl.classList.add(currentOrder.type === "Sell" ? "order-type-sell" : "order-type-purchase");

    // Status badge
    const orderStatusEl = document.getElementById("orderStatus");
    orderStatusEl.innerHTML = getStatusHTML(currentOrder.status);
    updateStatusClass(orderStatusEl, currentOrder.status);

    // Customer info
    document.getElementById("customerName").textContent = currentOrder.customer.name;
    document.getElementById("customerContact").textContent = currentOrder.customer.email;
    document.getElementById("customerPhone").textContent = currentOrder.customer.phone;

    // Payment info
    document.getElementById("paymentMethod").textContent = currentOrder.payment.method;
    document.getElementById("invoiceNo").textContent = currentOrder.payment.invoiceNo;

    // Order items
    renderOrderItems();

    // Notes
    document.getElementById("orderNotes").textContent = currentOrder.notes || "No notes added.";

    // Financial summary
    document.getElementById("subtotalAmount").textContent = currentOrder.subtotal.toFixed(2);
    document.getElementById("taxAmount").textContent = currentOrder.tax.toFixed(2);
    document.getElementById("discountAmount").textContent = currentOrder.discount.toFixed(2);
    document.getElementById("shippingAmount").textContent = currentOrder.shipping.toFixed(2);
    document.getElementById("grandTotalAmount").textContent = "$" + currentOrder.grandTotal.toFixed(2);

    // Delete modal
    document.getElementById("deleteOrderId").textContent = currentOrder.id;
}

// ===========================================
// Render Order Items with Stock Impact
// ===========================================
function renderOrderItems() {
    const tableBody = document.getElementById("orderItemsBody");

    tableBody.innerHTML = currentOrder.items.map(item => {
        const total = item.qty * item.unitPrice;
        const stockImpact = getStockImpactHTML(item.currentStock, item.qty, currentOrder.type);

        return `
            <tr>
                <td class="product-name">${item.name}</td>
                <td class="text-center qty-cell">${item.qty}</td>
                <td class="text-end price-cell">$${item.unitPrice.toFixed(2)}</td>
                <td class="text-end total-cell">$${total.toFixed(2)}</td>
                <td class="text-center stock-after-col">${stockImpact}</td>
            </tr>
        `;
    }).join('');
}

// ===========================================
// Stock Impact Logic
// ===========================================
function getStockImpactHTML(currentStock, qty, orderType) {
    if (orderType === "Sell") {
        // Sell order: Stock decreases
        return `
            <span class="stock-current">${currentStock} units</span>
            <span class="stock-impact-neg">(-${qty})</span>
        `;
    } else {
        // Purchase order: Stock increases
        return `
            <span class="stock-current">${currentStock} units</span>
            <span class="stock-impact-pos">(+${qty})</span>
        `;
    }
}

// ===========================================
// Status Helpers
// ===========================================
function getStatusHTML(status) {
    const icons = {
        "Completed": "fa-check-circle",
        "Pending": "fa-clock",
        "Confirmed": "fa-thumbs-up",
        "Cancelled": "fa-times-circle"
    };
    const icon = icons[status] || "fa-info-circle";
    return `<i class="fas ${icon} me-1"></i>${status}`;
}

function updateStatusClass(element, status) {
    // Remove existing status classes
    element.classList.remove("status-completed", "status-pending", "status-confirmed", "status-cancelled");

    // Add appropriate class
    const classMap = {
        "Completed": "status-completed",
        "Pending": "status-pending",
        "Confirmed": "status-confirmed",
        "Cancelled": "status-cancelled"
    };
    element.classList.add(classMap[status] || "status-pending");
}

// ===========================================
// Event Listeners
// ===========================================
function setupEventListeners() {
    // Edit order button
    const editBtn = document.getElementById("editOrderBtn");
    if (editBtn) {
        editBtn.addEventListener("click", editOrder);
    }

    // Duplicate order button
    const duplicateBtn = document.getElementById("duplicateOrderBtn");
    if (duplicateBtn) {
        duplicateBtn.addEventListener("click", duplicateOrder);
    }

    // Delete order button
    const deleteBtn = document.getElementById("deleteOrderBtn");
    if (deleteBtn) {
        deleteBtn.addEventListener("click", showDeleteModal);
    }

    // Confirm delete button
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener("click", deleteOrder);
    }

    // Logout button
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", handleLogout);
    }
}

// ===========================================
// Action Handlers
// ===========================================
function editOrder() {
    showToast("Edit functionality would open the order for editing.", "info");
    // In real app: window.location.href = `create-order.html?edit=${currentOrder.id}`;
}

function duplicateOrder() {
    showToast(`Order #${currentOrder.id} duplicated! A new draft has been created.`, "success");
    // In real app: Create new order with same items, redirect to create-order page
}

function showDeleteModal() {
    const modal = new bootstrap.Modal(document.getElementById("deleteModal"));
    modal.show();
}

function deleteOrder() {
    // Close modal
    const modalEl = document.getElementById("deleteModal");
    const modal = bootstrap.Modal.getInstance(modalEl);
    modal.hide();

    // Show success message and redirect
    showToast(`Order #${currentOrder.id} has been deleted.`, "success");

    // In real app: Send DELETE request to API, then redirect
    setTimeout(() => {
        window.location.href = "/QuickMart code/backend/views/dashboard.php";
    }, 1500);
}

// End of file
