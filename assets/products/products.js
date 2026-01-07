/**
 * QuickMart IOMS - Products Page JavaScript
 * Handles product listing, filtering, CRUD operations, and UI interactions
 * Note: Session management and common utilities are in common.js
 */

// ===========================================
// Products Data (Loaded from Database)
// ===========================================
let products = [];

// Low stock threshold
const LOW_STOCK_THRESHOLD = 20;

// Currently editing product ID
let editingProductId = null;
let deleteProductId = null;

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

    console.log("QuickMart IOMS - Products page initialized");
});

// ===========================================
// Load Products from Database
// ===========================================
async function loadProductsFromDatabase() {
    const tableBody = document.getElementById("productsTableBody");

    // Show loading state
    if (tableBody) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading products...</p>
                </td>
            </tr>
        `;
    }

    try {
        const response = await fetch('/QuickMart code/backend/api/products/list.php');
        const data = await response.json();

        if (data.success && data.data.products) {
            products = data.data.products.map(p => ({
                id: p.Product_ID,
                name: p.Name,
                category: p.Category || 'General',
                quantity: parseInt(p.Quantity) || 0,
                price: parseFloat(p.Price) || 0,
                status: p.Status,
                threshold: parseInt(p.Threshold) || 20
            }));

            // Populate category filter
            populateCategoryFilter(data.data.categories);

            // Render products
            filterProducts();

            console.log(`Loaded ${products.length} products from database`);
        } else {
            if (tableBody) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <h5>No products found</h5>
                            <p>Click "Add Product" to add your first product.</p>
                        </td>
                    </tr>
                `;
            }
        }
    } catch (error) {
        console.error('Error loading products:', error);
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center py-5 text-danger">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h5>Error loading products</h5>
                        <p>Please check your database connection.</p>
                    </td>
                </tr>
            `;
        }
    }
}

// Populate category filter from database
function populateCategoryFilter(categories) {
    const categoryFilter = document.getElementById("categoryFilter");
    if (categoryFilter && categories && categories.length > 0) {
        categoryFilter.innerHTML = '<option value="All">All Categories</option>';
        categories.forEach(cat => {
            categoryFilter.innerHTML += `<option value="${cat}">${cat}</option>`;
        });
    }
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
            matchesStock = product.quantity <= product.threshold;
        } else if (stockValue === "normal") {
            matchesStock = product.quantity > product.threshold;
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

    if (productList.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="8">
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h5>No Products Found</h5>
                        <p>Try adjusting your search or filter criteria.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }

    tableBody.innerHTML = productList.map(product => `
        <tr data-id="${product.id}">
            <td class="ps-3">
                <input type="checkbox" class="form-check-input product-checkbox" data-id="${product.id}">
            </td>
            <td>
                <span class="product-id">${product.id}</span>
            </td>
            <td>
                <span class="product-name">${product.name}</span>
            </td>
            <td>
                <span class="category-badge">
                    <i class="fas ${getCategoryIcon(product.category)}"></i>
                    ${product.category}
                </span>
            </td>
            <td class="text-center">
                <span class="quantity-display ${product.quantity <= product.threshold ? 'quantity-low' : 'quantity-normal'}">
                    ${product.quantity}
                </span>
            </td>
            <td>
                <span class="price-display">$${product.price.toFixed(2)}</span>
            </td>
            <td>
                ${getStatusBadge(product.status)}
            </td>
            <td>
                <div class="action-buttons">
                    <button class="action-btn action-btn-edit" onclick="editProduct('${product.id}')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="action-btn action-btn-delete" onclick="deleteProduct('${product.id}')" title="Delete">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');

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
    return icons[category] || "fa-tag";
}

function getStatusBadge(status) {
    if (status === 'Out of Stock') {
        return `<span class="status-badge badge-out-of-stock">
                    <i class="fas fa-times-circle"></i> Out of Stock
                </span>`;
    } else if (status === 'Low Stock') {
        return `<span class="status-badge badge-low">
                    <i class="fas fa-exclamation-triangle"></i> Low Stock
                </span>`;
    } else {
        return `<span class="status-badge badge-normal">
                    <i class="fas fa-check-circle"></i> Normal
                </span>`;
    }
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
    document.getElementById("lowStockThreshold").value = product.threshold || 20;
    document.getElementById("modalTitle").innerHTML = '<i class="fas fa-edit me-2"></i>Edit Product';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById("productModal"));
    modal.show();
}

async function handleProductSubmit(event) {
    event.preventDefault();

    const formData = {
        name: document.getElementById("productName").value.trim(),
        category: document.getElementById("productCategory").value,
        price: parseFloat(document.getElementById("productPrice").value),
        quantity: parseInt(document.getElementById("productQuantity").value),
        threshold: parseInt(document.getElementById("lowStockThreshold").value) || 20
    };

    // Debug: Log threshold being sent
    console.log('=== Threshold Debug ===');
    console.log('Input value:', document.getElementById("lowStockThreshold").value);
    console.log('Parsed value:', parseInt(document.getElementById("lowStockThreshold").value));
    console.log('Final threshold:', formData.threshold);
    console.log('Full formData:', JSON.stringify(formData));

    const submitBtn = document.querySelector('#productForm button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Saving...';
    submitBtn.disabled = true;

    try {
        let response;
        if (editingProductId) {
            // Update existing product
            formData.product_id = editingProductId;
            response = await fetch('/QuickMart code/backend/api/products/update.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        } else {
            // Add new product
            response = await fetch('/QuickMart code/backend/api/products/create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
        }

        const data = await response.json();

        if (data.success) {
            showToast(data.message || (editingProductId ? "Product updated!" : "Product added!"), "success");

            // Close modal
            const modalEl = document.getElementById("productModal");
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            // Reload products from database
            loadProductsFromDatabase();
        } else {
            showToast(data.message || "Error saving product", "error");
        }
    } catch (error) {
        console.error('Error saving product:', error);
        showToast("Error saving product. Please try again.", "error");
    } finally {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
}

function generateProductId() {
    const maxId = Math.max(...products.map(p => parseInt(p.id)), 0);
    return String(maxId + 1).padStart(3, '0');
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
    if (!deleteProductId) return;

    const deleteBtn = document.getElementById("confirmDeleteBtn");
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
    deleteBtn.disabled = true;

    try {
        const response = await fetch('/QuickMart code/backend/api/products/delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: deleteProductId })
        });

        const data = await response.json();

        if (data.success) {
            showToast("Product deleted successfully!", "success");

            // Close modal
            const modalEl = document.getElementById("deleteModal");
            const modal = bootstrap.Modal.getInstance(modalEl);
            modal.hide();

            // Reload products from database
            loadProductsFromDatabase();
        } else {
            showToast(data.message || "Error deleting product", "error");
        }
    } catch (error) {
        console.error('Error deleting product:', error);
        showToast("Error deleting product. Please try again.", "error");
    } finally {
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
    if (count === 0) return;

    // Show confirmation
    if (!confirm(`Are you sure you want to delete ${count} selected product(s)? This action cannot be undone.`)) {
        return;
    }

    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const originalText = deleteSelectedBtn.innerHTML;
    deleteSelectedBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Deleting...';
    deleteSelectedBtn.disabled = true;

    let successCount = 0;
    let failCount = 0;

    // Delete each selected product
    for (const productId of selectedProducts) {
        try {
            const response = await fetch('/QuickMart code/backend/api/products/delete.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            });

            const data = await response.json();
            if (data.success) {
                successCount++;
            } else {
                failCount++;
            }
        } catch (error) {
            console.error(`Error deleting product ${productId}:`, error);
            failCount++;
        }
    }

    // Clear selections
    selectedProducts.clear();
    const selectAllCheckbox = document.getElementById('selectAllProducts');
    if (selectAllCheckbox) selectAllCheckbox.checked = false;

    // Show result
    if (successCount > 0) {
        showToast(`Successfully deleted ${successCount} product(s)${failCount > 0 ? `, ${failCount} failed` : ''}`, 'success');
    } else {
        showToast('Failed to delete products', 'error');
    }

    // Reload products
    loadProductsFromDatabase();

    // Reset button
    deleteSelectedBtn.innerHTML = originalText;
    updateDeleteSelectedButton();
}

// Note: showToast(), handleLogout(), and debounce() are now in common.js
