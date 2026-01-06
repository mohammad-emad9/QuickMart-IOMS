/**
 * QuickMart IOMS - Create Order
 * Handles order creation, product search, calculations, and form submission
 */

// ===========================================
// Available Products (Loaded from Database)
// ===========================================
let availableProducts = [];

// Order items array
let orderItems = [];
let rowCounter = 0;

// Tax rate
const TAX_RATE = 0.05;

// ===========================================
// DOM Ready
// ===========================================
document.addEventListener("DOMContentLoaded", function () {
    initializePage();
    setupEventListeners();
    setDefaultDate();
    loadProductsFromDatabase();
    console.log("QuickMart IOMS - Create Order page initialized");
});

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
// Initialize Page
// ===========================================
function initializePage() {
    // Load user info
    loadUserInfo();

    // Update order type label on change
    updateOrderTypeLabel();
}

function setDefaultDate() {
    const dateInput = document.getElementById("deliveryDate");
    if (dateInput) {
        const today = new Date();
        today.setDate(today.getDate() + 3); // Default to 3 days from now
        dateInput.value = today.toISOString().split('T')[0];
    }
}

// ===========================================
// Event Listeners Setup
// ===========================================
function setupEventListeners() {
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

    // Product Selection Modal handlers
    const selectProductModal = document.getElementById("selectProductModal");
    if (selectProductModal) {
        selectProductModal.addEventListener('show.bs.modal', loadModalProducts);
    }

    // Modal product search
    const modalSearch = document.getElementById("modalProductSearch");
    if (modalSearch) {
        modalSearch.addEventListener("input", debounce(filterModalProducts, 300));
    }

    // Confirm add products button
    const confirmAddBtn = document.getElementById("confirmAddProductsBtn");
    if (confirmAddBtn) {
        confirmAddBtn.addEventListener("click", addSelectedProducts);
    }

    // Discount and shipping inputs
    const discountInput = document.getElementById("discountInput");
    const shippingInput = document.getElementById("shippingInput");

    if (discountInput) {
        discountInput.addEventListener("input", calculateTotals);
    }
    if (shippingInput) {
        shippingInput.addEventListener("input", calculateTotals);
    }

    // Create order button - Now shows preview first
    const createOrderBtn = document.getElementById("createOrderBtn");
    if (createOrderBtn) {
        createOrderBtn.addEventListener("click", showInvoicePreview);
    }

    // Confirm order button (inside preview modal)
    const confirmOrderBtn = document.getElementById("confirmOrderBtn");
    if (confirmOrderBtn) {
        confirmOrderBtn.addEventListener("click", processFinalOrder);
    }

    // Save draft button
    const saveDraftBtn = document.getElementById("saveDraftBtn");
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener("click", saveDraft);
    }

    // Logout button
    const logoutBtn = document.getElementById("logoutBtn");
    if (logoutBtn) {
        logoutBtn.addEventListener("click", handleLogout);
    }
}

// ===========================================
// Order Type Label Update
// ===========================================
function updateOrderTypeLabel() {
    const isSellOrder = document.getElementById("sellOrder").checked;
    const customerLabel = document.getElementById("customerLabel");

    if (customerLabel) {
        customerLabel.textContent = isSellOrder ? "Customer" : "Supplier";
    }
}

// ===========================================
// Product Search Functionality
// ===========================================
function handleProductSearch() {
    const searchInput = document.getElementById("productSearch");
    const suggestions = document.getElementById("productSuggestions");
    const searchTerm = searchInput.value.toLowerCase().trim();

    if (searchTerm.length < 1) {
        suggestions.classList.remove("show");
        return;
    }

    // Filter products
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

    // Check if product already in order
    const existingIndex = orderItems.findIndex(item => item.id === productId);
    if (existingIndex !== -1) {
        // Increment quantity
        const qtyInput = document.querySelector(`#row-${orderItems[existingIndex].rowId} .qty-input`);
        if (qtyInput) {
            qtyInput.value = parseInt(qtyInput.value) + 1;
            calculateTotals();
        }
        showToast(`${product.name} quantity increased!`, "info");
    } else {
        // Add new item
        rowCounter++;
        const newItem = {
            ...product,
            rowId: rowCounter,
            quantity: 1
        };
        orderItems.push(newItem);
        renderOrderTable();
        showToast(`${product.name} added to order!`, "success");
    }

    // Clear search
    document.getElementById("productSearch").value = "";
    document.getElementById("productSuggestions").classList.remove("show");
}

// Product selection is now handled via modal

// ===========================================
// Render Order Table
// ===========================================
function renderOrderTable() {
    const tableBody = document.getElementById("orderTableBody");
    const emptyMessage = document.getElementById("emptyMessage");
    const addProductLink = document.getElementById("addProductLink");

    if (orderItems.length === 0) {
        tableBody.innerHTML = "";
        emptyMessage.style.display = "block";
        addProductLink.style.display = "none";
    } else {
        emptyMessage.style.display = "none";
        addProductLink.style.display = "block";

        tableBody.innerHTML = orderItems.map(item => `
            <tr id="row-${item.rowId}">
                <td class="product-cell">${item.name}</td>
                <td class="available-cell">${item.available} units</td>
                <td class="price-cell">$<span class="item-price">${item.price.toFixed(2)}</span></td>
                <td>
                    <input type="number" class="form-control form-control-sm qty-input" 
                           style="direction: ltr; text-align: center;"
                           dir="ltr"
                           value="${item.quantity}" min="1" max="${item.available}" 
                           onchange="updateQuantity(${item.rowId}, this.value)">
                </td>
                <td class="total-cell">$<span class="row-total">${(item.price * item.quantity).toFixed(2)}</span></td>
                <td>
                    <button class="remove-btn" onclick="removeFromOrder(${item.rowId})">[Remove]</button>
                </td>
            </tr>
        `).join('');
    }

    calculateTotals();
}

// ===========================================
// Update Quantity
// ===========================================
function updateQuantity(rowId, newQty) {
    const item = orderItems.find(i => i.rowId === rowId);
    if (item) {
        let qty = parseInt(newQty) || 1;

        // Get current order type
        const isSellOrder = document.getElementById("sellOrder")?.checked;

        // For Sell orders, enforce max available quantity
        if (isSellOrder && qty > item.available) {
            qty = item.available;
            showToast(`Maximum available quantity for ${item.name} is ${item.available}`, "error");

            // Update the input field to show corrected value
            const row = document.getElementById(`row-${rowId}`);
            if (row) {
                const qtyInput = row.querySelector('.qty-input');
                if (qtyInput) qtyInput.value = qty;
            }
        }

        // Ensure minimum quantity is 1
        if (qty < 1) qty = 1;

        item.quantity = qty;

        // Update row total
        const row = document.getElementById(`row-${rowId}`);
        if (row) {
            const rowTotal = row.querySelector('.row-total');
            if (rowTotal) {
                rowTotal.textContent = (item.price * item.quantity).toFixed(2);
            }
        }

        calculateTotals();
    }
}

// ===========================================
// Remove from Order
// ===========================================
function removeFromOrder(rowId) {
    const itemIndex = orderItems.findIndex(i => i.rowId === rowId);
    if (itemIndex !== -1) {
        const removedItem = orderItems.splice(itemIndex, 1)[0];
        showToast(`${removedItem.name} removed from order`, "info");
        renderOrderTable();
    }
}

// ===========================================
// Calculate Totals
// ===========================================
function calculateTotals() {
    let subtotal = 0;

    // Calculate subtotal from all items
    orderItems.forEach(item => {
        subtotal += item.price * item.quantity;
    });

    // Get discount and shipping
    const discount = parseFloat(document.getElementById("discountInput")?.value) || 0;
    const shipping = parseFloat(document.getElementById("shippingInput")?.value) || 0;

    // Calculate tax
    const tax = subtotal * TAX_RATE;

    // Calculate grand total
    const grandTotal = subtotal + tax + shipping - discount;

    // Update display
    document.getElementById("subtotalDisplay").textContent = subtotal.toFixed(2);
    document.getElementById("taxDisplay").textContent = tax.toFixed(2);
    document.getElementById("discountDisplay").textContent = discount.toFixed(2);
    document.getElementById("shippingDisplay").textContent = shipping.toFixed(2);
    document.getElementById("grandTotalDisplay").textContent = grandTotal.toFixed(2);
}

// ===========================================
// Show Invoice Preview (before confirming)
// ===========================================
function showInvoicePreview() {
    // Validate: Are there items?
    if (orderItems.length === 0) {
        showToast("Please add at least one product to the order.", "error");
        return;
    }

    // Validate: Is customer name filled?
    const customerName = document.getElementById("customerName").value.trim();
    if (!customerName) {
        showToast("Please enter customer/supplier name.", "error");
        document.getElementById("customerName").focus();
        return;
    }

    // Populate preview modal data
    document.getElementById("prevCustomerName").textContent = customerName;
    document.getElementById("prevDate").textContent = document.getElementById("deliveryDate").value || new Date().toISOString().split('T')[0];

    // Populate items table
    const tbody = document.getElementById("prevItemsBody");
    tbody.innerHTML = orderItems.map(item => `
        <tr>
            <td>${item.name}</td>
            <td class="text-center">x${item.quantity}</td>
            <td class="text-end">$${(item.price * item.quantity).toFixed(2)}</td>
        </tr>
    `).join('');

    // Populate financial summary
    document.getElementById("prevSubtotal").textContent = document.getElementById("subtotalDisplay").textContent;
    document.getElementById("prevTax").textContent = document.getElementById("taxDisplay").textContent;
    document.getElementById("prevGrandTotal").textContent = document.getElementById("grandTotalDisplay").textContent;

    // Show the preview modal
    const previewModal = new bootstrap.Modal(document.getElementById("invoicePreviewModal"));
    previewModal.show();
}

// ===========================================
// Process Final Order (after confirmation)
// ===========================================
async function processFinalOrder() {
    // Hide preview modal first
    const previewModalEl = document.getElementById("invoicePreviewModal");
    const modalInstance = bootstrap.Modal.getInstance(previewModalEl);
    modalInstance.hide();

    // Get order type (Sell or Purchase)
    const orderTypeValue = document.querySelector('input[name="orderType"]:checked').value;
    const orderType = orderTypeValue === 'sell' ? 'Sell' : 'Purchase';

    // Get staff ID from session
    const staffId = sessionStorage.getItem("staffId") || sessionStorage.getItem("userStaffId");

    if (!staffId) {
        showToast("Session expired. Please login again.", "error");
        setTimeout(() => {
            window.location.href = "/QuickMart code/Frontend/login-signup/login.html";
        }, 2000);
        return;
    }

    // Prepare API request data
    const apiData = {
        staff_id: staffId,
        order_type: orderType,
        party_name: document.getElementById("customerName").value.trim(),
        items: orderItems.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            price: item.price
        }))
    };

    // Show loading state
    const confirmBtn = document.getElementById("confirmOrderBtn");
    if (confirmBtn) {
        confirmBtn.disabled = true;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    }

    try {
        // Call Backend API
        const response = await fetch('/QuickMart code/backend/api/orders/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(apiData)
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Failed to create order');
        }

        // Order created successfully
        const orderId = data.data.order_id;

        // Show success modal
        setTimeout(() => {
            document.getElementById("orderNumber").textContent = `#${orderId}`;
            const successModal = new bootstrap.Modal(document.getElementById("successModal"));
            successModal.show();
        }, 400);

        // Clear the order items
        orderItems = [];
        renderOrderTable();

    } catch (error) {
        console.error("Order Error:", error);
        showToast(error.message || "Failed to create order. Please try again.", "error");
    } finally {
        // Reset button state
        if (confirmBtn) {
            confirmBtn.disabled = false;
            confirmBtn.innerHTML = '<i class="fas fa-check me-2"></i>Confirm Order';
        }
    }
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
        notes: document.getElementById("orderNotes").value,
        savedAt: new Date().toISOString()
    };

    localStorage.setItem("orderDraft", JSON.stringify(draftData));
    showToast("Draft saved successfully!", "success");
}

// ===========================================
// Product Selection Modal Functions
// ===========================================
let selectedProducts = {};

// Load products into modal
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

    // Reset selections
    selectedProducts = {};
    updateSelectedCount();
    document.getElementById("modalProductSearch").value = "";
}

// Render products in modal table
function renderModalProducts(products) {
    const tbody = document.getElementById("modalProductsBody");
    const orderType = document.querySelector('input[name="orderType"]:checked')?.value;

    tbody.innerHTML = products.map(product => {
        const isSelected = selectedProducts[product.id] !== undefined;
        const isLowStock = product.available <= 20;
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
                    <span class="${isLowStock ? 'text-danger fw-bold' : 'text-success'}">
                        ${product.available}
                    </span>
                </td>
                <td class="text-end fw-bold">$${product.price.toFixed(2)}</td>
            </tr>
        `;
    }).join('');
}

// Filter products in modal
function filterModalProducts() {
    const searchTerm = document.getElementById("modalProductSearch").value.toLowerCase();

    const filtered = availableProducts.filter(p =>
        p.name.toLowerCase().includes(searchTerm) ||
        p.id.toLowerCase().includes(searchTerm) ||
        p.category.toLowerCase().includes(searchTerm)
    );

    renderModalProducts(filtered);
}

// Toggle product selection
function toggleProductSelection(checkbox) {
    const id = checkbox.dataset.id;
    const name = checkbox.dataset.name;
    const price = parseFloat(checkbox.dataset.price);
    const available = parseInt(checkbox.dataset.available);

    if (checkbox.checked) {
        selectedProducts[id] = {
            id: id,
            name: name,
            price: price,
            available: available,
            quantity: 1
        };
    } else {
        delete selectedProducts[id];
    }

    // Update row highlight
    checkbox.closest('tr').classList.toggle('table-success', checkbox.checked);

    updateSelectedCount();
    updateSelectedPreview();
}

// Update selected count display
function updateSelectedCount() {
    const count = Object.keys(selectedProducts).length;
    document.getElementById("selectedCount").textContent = `${count} product${count !== 1 ? 's' : ''} selected`;
    document.getElementById("confirmAddProductsBtn").disabled = count === 0;
}

// Update selected products preview
function updateSelectedPreview() {
    const preview = document.getElementById("selectedProductsPreview");
    const list = document.getElementById("selectedProductsList");
    const products = Object.values(selectedProducts);

    if (products.length === 0) {
        preview.style.display = "none";
        return;
    }

    preview.style.display = "block";
    list.innerHTML = products.map(p => `
        <div class="d-flex justify-content-between align-items-center mb-2 p-2 bg-white rounded">
            <div>
                <strong>${p.name}</strong>
                <small class="text-muted ms-2">$${p.price.toFixed(2)}</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <label class="small text-muted mb-0">Qty:</label>
                <input type="number" 
                       class="form-control form-control-sm" 
                       style="width: 70px; direction: ltr; text-align: center;" 
                       dir="ltr"
                       min="1" 
                       max="${p.available}"
                       value="${p.quantity}"
                       onchange="updateProductQuantity('${p.id}', this.value)">
            </div>
        </div>
    `).join('');
}

// Update quantity for selected product
function updateProductQuantity(productId, quantity) {
    if (selectedProducts[productId]) {
        const maxQty = selectedProducts[productId].available;
        quantity = Math.max(1, Math.min(parseInt(quantity) || 1, maxQty));
        selectedProducts[productId].quantity = quantity;
    }
}

// Add selected products to order
function addSelectedProducts() {
    const products = Object.values(selectedProducts);

    if (products.length === 0) {
        showToast("Please select at least one product", "error");
        return;
    }

    // Add each selected product to order
    products.forEach(product => {
        // Check if product already in order
        const existingIndex = orderItems.findIndex(item => item.id === product.id);

        if (existingIndex >= 0) {
            // Update quantity
            orderItems[existingIndex].quantity += product.quantity;
        } else {
            // Add new item
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

    // Render updated table
    renderOrderTable();
    calculateTotals();

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById("selectProductModal"));
    modal.hide();

    // Reset selections
    selectedProducts = {};

    showToast(`Added ${products.length} product(s) to order`, "success");
}

// ===========================================
// Recent Orders Section
// ===========================================
let allOrdersCache = [];
let currentFilter = 'all';

// Load recent orders on page load
document.addEventListener("DOMContentLoaded", function () {
    loadRecentOrders();
    setupOrderFilters();
});

// Setup filter buttons
function setupOrderFilters() {
    const filterAllBtn = document.getElementById("filterAllBtn");
    const filterSellBtn = document.getElementById("filterSellBtn");
    const filterPurchaseBtn = document.getElementById("filterPurchaseBtn");

    if (filterAllBtn) {
        filterAllBtn.addEventListener("click", () => filterOrders('all'));
    }
    if (filterSellBtn) {
        filterSellBtn.addEventListener("click", () => filterOrders('Sell'));
    }
    if (filterPurchaseBtn) {
        filterPurchaseBtn.addEventListener("click", () => filterOrders('Purchase'));
    }
}

// Load Recent Orders from Database
async function loadRecentOrders() {
    const ordersTable = document.getElementById("recentOrdersTable");
    if (!ordersTable) return;

    // Show loading state
    ordersTable.innerHTML = `
        <tr>
            <td colspan="7" class="text-center py-4 text-muted">
                <i class="fas fa-spinner fa-spin me-2"></i>Loading orders...
            </td>
        </tr>
    `;

    try {
        const response = await fetch('/QuickMart code/backend/api/orders/list.php');
        const data = await response.json();

        if (data.success && data.data.orders && data.data.orders.length > 0) {
            allOrdersCache = data.data.orders;
            renderOrdersTable(allOrdersCache);
        } else {
            ordersTable.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="fas fa-inbox me-2"></i>No orders yet. Create your first order above!
                    </td>
                </tr>
            `;
        }
    } catch (error) {
        console.error('Error loading recent orders:', error);
        ordersTable.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Error loading orders
                </td>
            </tr>
        `;
    }
}

// Render orders table
function renderOrdersTable(orders) {
    const ordersTable = document.getElementById("recentOrdersTable");
    if (!ordersTable) return;

    if (orders.length === 0) {
        ordersTable.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-4 text-muted">
                    <i class="fas fa-inbox me-2"></i>No orders found for this filter.
                </td>
            </tr>
        `;
        return;
    }

    ordersTable.innerHTML = orders.map(order => `
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

// Filter orders
function filterOrders(type) {
    currentFilter = type;

    // Update active button
    document.querySelectorAll('#filterAllBtn, #filterSellBtn, #filterPurchaseBtn').forEach(btn => {
        btn.classList.remove('active');
    });

    if (type === 'all') {
        document.getElementById('filterAllBtn')?.classList.add('active');
        renderOrdersTable(allOrdersCache);
    } else if (type === 'Sell') {
        document.getElementById('filterSellBtn')?.classList.add('active');
        const filtered = allOrdersCache.filter(o => o.Order_Type === 'Sell');
        renderOrdersTable(filtered);
    } else if (type === 'Purchase') {
        document.getElementById('filterPurchaseBtn')?.classList.add('active');
        const filtered = allOrdersCache.filter(o => o.Order_Type === 'Purchase');
        renderOrdersTable(filtered);
    }
}

// Format date helper
function formatOrderDate(dateStr) {
    if (!dateStr) return '-';
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
}

// View Order Details
async function viewOrderDetails(orderId) {
    try {
        const response = await fetch(`/QuickMart code/backend/api/orders/get.php?id=${orderId}`);
        const data = await response.json();

        if (data.success && data.data) {
            const order = data.data.order;
            const details = data.data.details;

            // Create and show a simple alert with order details
            let itemsList = details.map(item =>
                `• ${item.Product_Name || item.Product_ID}: ${item.Ordered_Qty} x $${parseFloat(item.Sold_Price).toFixed(2)} = $${parseFloat(item.Line_Total).toFixed(2)}`
            ).join('\n');

            const orderInfo = `
Order ID: ${order.Order_ID}
Type: ${order.Order_Type}
Customer/Supplier: ${order.Party_Name}
Staff: ${order.Staff_Name || order.Staff_ID}
Date: ${formatOrderDate(order.Order_Date)}

Items:
${itemsList}

Total: $${parseFloat(data.data.total_amount).toFixed(2)}
            `;

            alert(orderInfo);
        }
    } catch (error) {
        console.error('Error loading order details:', error);
        showToast('Error loading order details', 'error');
    }
}

