/**
 * QuickMart IOMS - Products Page JavaScript
 * Handles product listing, filtering, CRUD operations, and UI interactions
 * Note: Session management and common utilities are in common.js
 */

// ===========================================
// Products Data (Loaded from Database)
// ===========================================
let products = [];

// Currently editing product ID
let editingProductId = null;
let deleteProductId = null;
let productsLoadInFlight = false;
let productMutationInFlight = false;
let deleteMutationInFlight = false;
let bulkDeleteInFlight = false;

// ===========================================
// DOM Ready
// ===========================================
document.addEventListener("DOMContentLoaded", function () {
    // Initialize page
    initializePage();

    // Load products from database
    loadProductsFromDatabase();

    // Setup event listeners
    setupEventListeners();
});

// ===========================================
// Load Products from Database
// ===========================================
async function parseApiResponse(response) {
    let data;

    try {
        data = await response.json();
    } catch (error) {
        throw new Error('The server returned an invalid response.');
    }

    if (!response.ok || !data || data.success !== true) {
        throw new Error(data && data.message ? data.message : `Request failed (${response.status}).`);
    }

    return data;
}

function renderTableMessage(title, message, options = {}) {
    const tableBody = document.getElementById("productsTableBody");
    if (!tableBody) return;

    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 8;
    cell.className = options.cellClass || 'text-center py-5 text-muted';

    if (options.loading) {
        const spinner = document.createElement('div');
        spinner.className = 'spinner-border text-primary';
        spinner.setAttribute('role', 'status');

        const hiddenText = document.createElement('span');
        hiddenText.className = 'visually-hidden';
        hiddenText.textContent = 'Loading...';
        spinner.appendChild(hiddenText);

        const loadingText = document.createElement('p');
        loadingText.className = 'mt-2 text-muted';
        loadingText.textContent = message;

        cell.append(spinner, loadingText);
    } else {
        const icon = document.createElement('i');
        icon.className = options.iconClass || 'fas fa-inbox fa-3x mb-3';

        const heading = document.createElement('h5');
        heading.textContent = title;

        const detail = document.createElement('p');
        detail.textContent = message;

        cell.append(icon, heading, detail);
    }

    row.appendChild(cell);
    tableBody.replaceChildren(row);
    setupCheckboxListeners();
}

async function loadProductsFromDatabase() {
    if (productsLoadInFlight) return;

    productsLoadInFlight = true;

    // Show loading state
    renderTableMessage('', 'Loading products...', { loading: true });

    try {
        const response = await apiFetch('products/list.php');
        const data = await parseApiResponse(response);
        const payload = data.data || {};
        const productRows = Array.isArray(payload.products) ? payload.products : [];

        products = productRows.map(product => ({
            id: String(product.Product_ID ?? ''),
            name: String(product.Name ?? ''),
            category: String(product.Category ?? 'General'),
            quantity: Number.isFinite(Number(product.Quantity)) ? Number(product.Quantity) : 0,
            price: Number.isFinite(Number(product.Price)) ? Number(product.Price) : 0,
            status: typeof product.Status === 'string' ? product.Status : '',
            threshold: product.Threshold === null || product.Threshold === undefined || product.Threshold === ''
                ? 20
                : (Number.isFinite(Number(product.Threshold)) ? Number(product.Threshold) : 20)
        }));

        // Populate category filter from the backend response.
        populateCategoryFilter(payload.categories);

        // Render products using the backend-provided status values.
        filterProducts();
    } catch (error) {
        products = [];
        renderTableMessage('Error loading products', error.message || 'Please check your database connection.', {
            cellClass: 'text-center py-5 text-danger',
            iconClass: 'fas fa-exclamation-triangle fa-3x mb-3'
        });
        showToast(error.message || 'Error loading products', 'error');
    } finally {
        productsLoadInFlight = false;
    }
}

// Populate category filter from database
function populateCategoryFilter(categories) {
    const categoryFilter = document.getElementById("categoryFilter");
    if (!categoryFilter) return;

    const options = document.createDocumentFragment();
    const allOption = document.createElement('option');
    allOption.value = 'All';
    allOption.textContent = 'All Categories';
    options.appendChild(allOption);

    if (Array.isArray(categories)) {
        categories.forEach(category => {
            const option = document.createElement('option');
            option.value = String(category ?? '');
            option.textContent = String(category ?? '');
            options.appendChild(option);
        });
    }

    categoryFilter.replaceChildren(options);
}

// ===========================================
// Initialize Page
// ===========================================
function initializePage() {
    // Session check (optional - uncomment to enable)
    // checkSession(); // from common.js

    // Load user info from common.js
    loadUserInfo();
}

// Note: checkSession() and loadUserInfo() are now in common.js

// ===========================================
// Event Listeners Setup
// ===========================================
function setupEventListeners() {
    // Search input
    const searchInput = document.getElementById("searchInput");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(filterProducts, 300));
    }

    // Category filter
    const categoryFilter = document.getElementById("categoryFilter");
    if (categoryFilter) {
        categoryFilter.addEventListener("change", filterProducts);
    }

    // Stock filter
    const stockFilter = document.getElementById("stockFilter");
    if (stockFilter) {
        stockFilter.addEventListener("change", filterProducts);
    }

    // Sort filter
    const sortFilter = document.getElementById("sortFilter");
    if (sortFilter) {
        sortFilter.addEventListener("change", filterProducts);
    }

    // Add product button
    const addProductBtn = document.getElementById("addProductBtn");
    if (addProductBtn) {
        addProductBtn.addEventListener("click", openAddModal);
    }

    // Product form submission
    const productForm = document.getElementById("productForm");
    if (productForm) {
        productForm.addEventListener("submit", handleProductSubmit);
    }

    // Confirm delete button
    const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener("click", confirmDelete);
    }

    // Export button
    const exportBtn = document.getElementById("exportBtn");
    if (exportBtn) {
        exportBtn.addEventListener("click", exportToCSV);
    }

    // Delete Selected button
    const deleteSelectedBtn = document.getElementById("deleteSelectedBtn");
    if (deleteSelectedBtn) {
        deleteSelectedBtn.addEventListener("click", deleteSelectedProducts);
    }

    // Select All checkbox
    const selectAllCheckbox = document.getElementById("selectAllProducts");
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener("change", toggleSelectAll);
    }

    // Logout button - uses handleLogout from common.js
    // Note: common.js auto-sets up logout handler on DOMContentLoaded
}

// ===========================================
// Load and Render Products
// ===========================================
function loadProducts() {
    filterProducts();
}

function filterProducts() {
    const searchTerm = document.getElementById("searchInput")?.value.toLowerCase() || "";
    const categoryValue = document.getElementById("categoryFilter")?.value || "All";
    const stockValue = document.getElementById("stockFilter")?.value || "All";
    const sortValue = document.getElementById("sortFilter")?.value || "name";

    // Filter products
    let filtered = products.filter(product => {
        // Search filter
        const matchesSearch = product.name.toLowerCase().includes(searchTerm) ||
            product.id.toLowerCase().includes(searchTerm);

        // Category filter
        const matchesCategory = categoryValue === "All" || product.category === categoryValue;

        // Stock filter
        let matchesStock = true;
        if (stockValue === "low") {
            matchesStock = product.status === 'Low Stock' || product.status === 'Out of Stock';
        } else if (stockValue === "normal") {
            matchesStock = product.status === 'Normal';
        }

        return matchesSearch && matchesCategory && matchesStock;
    });

    // Sort products
    filtered = sortProducts(filtered, sortValue);

    // Render the filtered products
    renderProducts(filtered);

    // Update product count
    updateProductCount(filtered.length);
}

function sortProducts(productList, sortBy) {
    return [...productList].sort((a, b) => {
        switch (sortBy) {
            case "name":
                return a.name.localeCompare(b.name);
            case "name-desc":
                return b.name.localeCompare(a.name);
            case "price-asc":
                return a.price - b.price;
            case "price-desc":
                return b.price - a.price;
            case "quantity-asc":
                return a.quantity - b.quantity;
            case "quantity-desc":
                return b.quantity - a.quantity;
            default:
                return 0;
        }
    });
}

function renderProducts(productList) {
    const tableBody = document.getElementById("productsTableBody");
    if (!tableBody) return;

    // This is UX-only; product mutation endpoints enforce Admin server-side.
    const canManageProducts = sessionStorage.getItem('userRole') === 'Admin';

    if (productList.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 8;

        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';

        const icon = document.createElement('i');
        icon.className = 'fas fa-box-open';

        const heading = document.createElement('h5');
        heading.textContent = 'No Products Found';

        const detail = document.createElement('p');
        detail.textContent = 'Try adjusting your search or filter criteria.';

        emptyState.append(icon, heading, detail);
        cell.appendChild(emptyState);
        row.appendChild(cell);
        tableBody.replaceChildren(row);
        setupCheckboxListeners();
        return;
    }

    const rows = document.createDocumentFragment();

    productList.forEach(product => {
        const row = document.createElement('tr');
        row.dataset.id = product.id;

        const selectionCell = document.createElement('td');
        selectionCell.className = 'ps-3';
        selectionCell.hidden = !canManageProducts;

        if (canManageProducts) {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'form-check-input product-checkbox';
            checkbox.dataset.id = product.id;
            selectionCell.appendChild(checkbox);
        }
        row.appendChild(selectionCell);

        const idCell = document.createElement('td');
        const idText = document.createElement('span');
        idText.className = 'product-id';
        idText.textContent = product.id;
        idCell.appendChild(idText);
        row.appendChild(idCell);

        const nameCell = document.createElement('td');
        const nameText = document.createElement('span');
        nameText.className = 'product-name';
        nameText.textContent = product.name;
        nameCell.appendChild(nameText);
        row.appendChild(nameCell);

        const categoryCell = document.createElement('td');
        const categoryBadge = document.createElement('span');
        categoryBadge.className = 'category-badge';
        const categoryIcon = document.createElement('i');
        categoryIcon.className = `fas ${getCategoryIcon(product.category)}`;
        categoryBadge.append(categoryIcon, document.createTextNode(` ${product.category}`));
        categoryCell.appendChild(categoryBadge);
        row.appendChild(categoryCell);

        const quantityCell = document.createElement('td');
        quantityCell.className = 'text-center';
        const quantityText = document.createElement('span');
        quantityText.className = `quantity-display ${product.status === 'Normal' ? 'quantity-normal' : 'quantity-low'}`;
        quantityText.textContent = String(product.quantity);
        quantityCell.appendChild(quantityText);
        row.appendChild(quantityCell);

        const priceCell = document.createElement('td');
        const priceText = document.createElement('span');
        priceText.className = 'price-display';
        priceText.textContent = `$${product.price.toFixed(2)}`;
        priceCell.appendChild(priceText);
        row.appendChild(priceCell);

        const statusCell = document.createElement('td');
        statusCell.appendChild(createStatusBadge(product.status));
        row.appendChild(statusCell);

        const actionCell = document.createElement('td');
        actionCell.hidden = !canManageProducts;
        if (canManageProducts) {
            const actionButtons = document.createElement('div');
            actionButtons.className = 'action-buttons';

            const editButton = document.createElement('button');
            editButton.type = 'button';
            editButton.className = 'action-btn action-btn-edit';
            editButton.title = 'Edit';
            editButton.setAttribute('aria-label', `Edit ${product.name}`);
            const editIcon = document.createElement('i');
            editIcon.className = 'fas fa-edit';
            editButton.appendChild(editIcon);
            editButton.addEventListener('click', () => editProduct(product.id));

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'action-btn action-btn-delete';
            deleteButton.title = 'Delete';
            deleteButton.setAttribute('aria-label', `Delete ${product.name}`);
            const deleteIcon = document.createElement('i');
            deleteIcon.className = 'fas fa-trash-alt';
            deleteButton.appendChild(deleteIcon);
            deleteButton.addEventListener('click', () => deleteProduct(product.id));

            actionButtons.append(editButton, deleteButton);
            actionCell.appendChild(actionButtons);
        }
        row.appendChild(actionCell);

        rows.appendChild(row);
    });

    tableBody.replaceChildren(rows);

    // Setup checkbox event listeners after rendering
    setupCheckboxListeners();
}

// ===========================================
// Helper Functions
// ===========================================
function getCategoryIcon(category) {
    const icons = {
        "GPU": "fa-microchip",
        "CPU": "fa-server",
        "RAM": "fa-memory",
        "Storage": "fa-hdd",
        "Power": "fa-bolt",
        "Cooling": "fa-fan",
        "Cables": "fa-plug"
    };
    return Object.prototype.hasOwnProperty.call(icons, category) ? icons[category] : "fa-tag";
}

function createStatusBadge(status) {
    const badge = document.createElement('span');
    const label = String(status || 'Unknown');
    const statusPresentation = {
        'Out of Stock': ['badge-out-of-stock', 'fa-times-circle'],
        'Low Stock': ['badge-low', 'fa-exclamation-triangle'],
        'Normal': ['badge-normal', 'fa-check-circle']
    };
    const presentation = statusPresentation[label] || ['badge-normal', 'fa-info-circle'];

    badge.className = `status-badge ${presentation[0]}`;
    const icon = document.createElement('i');
    icon.className = `fas ${presentation[1]}`;
    badge.append(icon, document.createTextNode(` ${label}`));

    return badge;
}

function updateProductCount(count) {
    const productCountEl = document.getElementById("productCount");
    const showingInfoEl = document.getElementById("showingInfo");

    if (productCountEl) {
        productCountEl.textContent = `${products.length} Total Products`;
    }

    if (showingInfoEl) {
        showingInfoEl.textContent = `Showing ${count} of ${products.length} products`;
    }
}

// ===========================================
// Modal Functions
// ===========================================
function openAddModal() {
    editingProductId = null;

    // Reset form
    document.getElementById("productForm").reset();
    document.getElementById("productId").value = "";
    // Leave lowStockThreshold empty - placeholder shows default hint, actual default (20) applied on submit
    document.getElementById("modalTitle").innerHTML = '<i class="fas fa-plus-circle me-2"></i>Add New Product';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById("productModal"));
    modal.show();
}

function editProduct(productId) {
    editingProductId = productId;
    const product = products.find(p => p.id === productId);

    if (!product) {
        showToast("Product not found", "error");
        return;
    }

    // Populate form
    document.getElementById("productId").value = product.id;
    document.getElementById("productName").value = product.name;
    document.getElementById("productCategory").value = product.category;
    document.getElementById("productPrice").value = product.price;
    document.getElementById("productQuantity").value = product.quantity;
    document.getElementById("lowStockThreshold").value = product.threshold;
    document.getElementById("modalTitle").innerHTML = '<i class="fas fa-edit me-2"></i>Edit Product';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById("productModal"));
    modal.show();
}

async function handleProductSubmit(event) {
    event.preventDefault();

    if (productMutationInFlight) return;

    productMutationInFlight = true;
    const isEditing = Boolean(editingProductId);
    const thresholdInput = document.getElementById("lowStockThreshold");
    const thresholdRawValue = thresholdInput.value.trim();

    const formData = {
        name: document.getElementById("productName").value.trim(),
        category: document.getElementById("productCategory").value,
        price: parseFloat(document.getElementById("productPrice").value),
        quantity: parseInt(document.getElementById("productQuantity").value),
        threshold: thresholdRawValue === '' ? 20 : Number(thresholdRawValue)
    };

    const submitBtn = document.querySelector('#productForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    submitBtn.disabled = true;

    try {
        let response;
        if (isEditing) {
            // Update existing product
            formData.product_id = editingProductId;
            response = await apiFetch('products/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        } else {
            // Add new product
            response = await apiFetch('products/create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        }

        const data = await parseApiResponse(response);
        showToast(data.message || (isEditing ? "Product updated!" : "Product added!"), "success");

        // Close modal
        const modalEl = document.getElementById("productModal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        // Reload products from database
        await loadProductsFromDatabase();
    } catch (error) {
        showToast(error.message || "Error saving product. Please try again.", "error");
    } finally {
        productMutationInFlight = false;
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

// ===========================================
// Delete Functions
// ===========================================
function deleteProduct(productId) {
    deleteProductId = productId;
    const product = products.find(p => p.id === productId);

    if (product) {
        document.getElementById("deleteProductName").textContent = product.name;
        const modal = new bootstrap.Modal(document.getElementById("deleteModal"));
        modal.show();
    }
}

async function confirmDelete() {
    if (!deleteProductId || deleteMutationInFlight) return;

    deleteMutationInFlight = true;

    const deleteBtn = document.getElementById("confirmDeleteBtn");
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
    deleteBtn.disabled = true;

    try {
        const response = await apiFetch('products/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: deleteProductId })
        });

        const data = await parseApiResponse(response);
        showToast(data.message || "Product deleted successfully!", "success");

        // Close modal
        const modalEl = document.getElementById("deleteModal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();

        // Reload products from database
        await loadProductsFromDatabase();
    } catch (error) {
        showToast(error.message || "Error deleting product. Please try again.", "error");
    } finally {
        deleteMutationInFlight = false;
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        deleteProductId = null;
    }
}

// ===========================================
// Export Function
// ===========================================
function exportToCSV() {
    const headers = ["ID", "Product Name", "Category", "Quantity", "Price", "Status"];
    const rows = products.map(p => [
        p.id,
        p.name,
        p.category,
        p.quantity,
        `$${p.price.toFixed(2)}`,
        p.status || "Normal"  // Use status from database
    ]);

    let csvContent = headers.join(",") + "\n";
    csvContent += rows.map(row => row.join(",")).join("\n");

    const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = `QuickMart_Products_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();

    showToast("Products exported to CSV!", "success");
}

// ===========================================
// Checkbox Selection Functions
// ===========================================
let selectedProducts = new Set();

function setupCheckboxListeners() {
    // Clear previous selections when products are re-rendered
    selectedProducts.clear();

    // Reset select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    if (selectAllCheckbox) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    }

    // Update the delete button to show correct count
    updateDeleteSelectedButton();

    // Listen to individual checkbox changes
    document.querySelectorAll('.product-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const productId = this.dataset.id;
            if (this.checked) {
                selectedProducts.add(productId);
            } else {
                selectedProducts.delete(productId);
            }
            updateDeleteSelectedButton();
            updateSelectAllCheckbox();
        });
    });
}

function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    const checkboxes = document.querySelectorAll('.product-checkbox');

    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
        const productId = checkbox.dataset.id;
        if (selectAllCheckbox.checked) {
            selectedProducts.add(productId);
        } else {
            selectedProducts.delete(productId);
        }
    });

    updateDeleteSelectedButton();
}

function updateSelectAllCheckbox() {
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;

    if (checkboxes.length === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedCount === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
    } else if (checkedCount === checkboxes.length) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
    } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
    }
}

function updateDeleteSelectedButton() {
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const count = selectedProducts.size;

    if (deleteSelectedBtn) {
        deleteSelectedBtn.disabled = count === 0;
        if (count > 0) {
            deleteSelectedBtn.innerHTML = `<i class="fas fa-trash-alt me-1"></i> Delete Selected (${count})`;
        } else {
            deleteSelectedBtn.innerHTML = `<i class="fas fa-trash-alt me-1"></i> Delete Selected`;
        }
    }
}

async function deleteSelectedProducts() {
    const count = selectedProducts.size;
    if (count === 0 || bulkDeleteInFlight) return;

    // Show confirmation
    if (!confirm(`Are you sure you want to delete ${count} selected product(s)? This action cannot be undone.`)) {
        return;
    }

    bulkDeleteInFlight = true;
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const originalText = deleteSelectedBtn.innerHTML;
    deleteSelectedBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';
    deleteSelectedBtn.disabled = true;

    let successCount = 0;
    let failCount = 0;
    const failureMessages = [];

    try {
        // Delete each selected product sequentially to keep requests bounded.
        for (const productId of selectedProducts) {
            try {
                const response = await apiFetch('products/delete.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ product_id: productId })
                });

                await parseApiResponse(response);
                successCount++;
            } catch (error) {
                failCount++;
                if (error.message) failureMessages.push(error.message);
            }
        }

        if (successCount > 0) {
            const failedText = failCount > 0 ? `, ${failCount} failed` : '';
            showToast(`Successfully deleted ${successCount} product(s)${failedText}`, 'success');
        } else {
            showToast(failureMessages[0] || 'Failed to delete products', 'error');
        }

        // Reload products after the batch completes.
        await loadProductsFromDatabase();
    } finally {
        selectedProducts.clear();
        const selectAllCheckbox = document.getElementById('selectAllProducts');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }

        bulkDeleteInFlight = false;
        deleteSelectedBtn.innerHTML = originalText;
        deleteSelectedBtn.disabled = false;
        updateDeleteSelectedButton();
    }
}

// Note: showToast(), handleLogout(), and debounce() are now in common.js
