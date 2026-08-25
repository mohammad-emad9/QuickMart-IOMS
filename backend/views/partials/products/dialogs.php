    <div class="modal fade" id="productModal" data-admin-only data-bs-keyboard="true" tabindex="-1"
        aria-labelledby="modalTitle" aria-describedby="productModalDescription" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header products-modal-header">
                    <div>
                        <p class="products-modal-kicker">Catalog record</p>
                        <h2 class="modal-title" id="modalTitle">
                            <svg class="products-modal-title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            <span>Add product</span>
                        </h2>
                    </div>
                    <button type="button" class="products-modal-close" data-bs-dismiss="modal" aria-label="Close product form">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <form id="productForm">
                    <div class="modal-body">
                        <p class="products-modal-description" id="productModalDescription">
                            Enter the fields supported by the inventory service. Prices, quantities, thresholds, and status remain server-authoritative.
                        </p>
                        <div class="products-form-alert" id="productFormError" role="alert" hidden></div>
                        <input type="hidden" id="productId">

                        <div class="products-form-grid">
                            <div class="filter-field products-form-field-wide">
                                <label for="productName">Product name</label>
                                <input type="text" class="form-control" id="productName" placeholder="Enter product name"
                                    autocomplete="off" maxlength="255" required>
                            </div>

                            <div class="filter-field">
                                <label for="productCategory">Category</label>
                                <select id="productCategory" class="form-select" required>
                                    <option value="">Select category</option>
                                </select>
                            </div>

                            <div class="filter-field">
                                <label for="productPrice">Price</label>
                                <input type="number" class="form-control" id="productPrice" placeholder="0.00"
                                    step="0.01" min="0" inputmode="decimal" required>
                            </div>

                            <div class="filter-field">
                                <label for="productQuantity">Quantity in stock</label>
                                <input type="number" class="form-control" id="productQuantity" placeholder="0" min="0"
                                    step="1" inputmode="numeric" required>
                            </div>

                            <div class="filter-field">
                                <label for="lowStockThreshold">Low-stock threshold</label>
                                <input type="number" class="form-control" id="lowStockThreshold" placeholder="20" min="0"
                                    step="1" inputmode="numeric" aria-describedby="thresholdHelp">
                                <span class="products-field-help" id="thresholdHelp">A low-stock status is set by the backend when quantity reaches this threshold.</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer products-modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="saveProductBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 4h12l2 2v14H5zM8 4v6h8V4M8 20v-6h8v6" />
                            </svg>
                            <span>Save product</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" data-admin-only data-bs-keyboard="true" tabindex="-1"
        aria-labelledby="deleteModalTitle" aria-describedby="deleteModalDescription" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header products-modal-header products-modal-header-danger">
                    <div>
                        <p class="products-modal-kicker">Destructive action</p>
                        <h2 class="modal-title" id="deleteModalTitle">
                            <svg class="products-modal-title-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3 21 20H3L12 3Z" />
                                <path d="M12 9v5M12 17h.01" />
                            </svg>
                            <span>Delete product?</span>
                        </h2>
                    </div>
                    <button type="button" class="products-modal-close" data-bs-dismiss="modal" aria-label="Close delete confirmation">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body products-delete-body">
                    <div class="products-delete-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 7h14M10 11v6M14 11v6M7 7l1 13h8l1-13M9 7V4h6v3" />
                        </svg>
                    </div>
                    <p id="deleteModalDescription">This removes the selected record from the catalog. Confirm only if you are sure.</p>
                    <strong class="products-delete-name" id="deleteProductName"></strong>
                    <div class="products-form-alert" id="deleteModalError" role="alert" hidden></div>
                </div>
                <div class="modal-footer products-modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 7h14M10 11v6M14 11v6M7 7l1 13h8l1-13M9 7V4h6v3" />
                        </svg>
                        <span>Delete record</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalTitle" aria-describedby="logoutModalDescription"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content products-logout-modal">
                <div class="modal-body products-logout-body">
                    <span class="products-logout-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" />
                        </svg>
                    </span>
                    <h2 class="products-logout-title" id="logoutModalTitle">Log out?</h2>
                    <p class="products-logout-copy" id="logoutModalDescription">Your current session will be closed.</p>
                    <div class="products-logout-actions">
                        <button type="button" class="btn btn-outline-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmLogoutBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" />
                            </svg>
                            <span>Log out</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
