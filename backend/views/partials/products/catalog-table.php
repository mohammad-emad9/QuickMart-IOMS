            <section class="products-panel" aria-labelledby="productsListTitle">
                <div class="products-panel-heading">
                    <div>
                        <p class="products-section-kicker">Inventory register</p>
                        <h2 class="products-section-title" id="productsListTitle">Product catalog</h2>
                    </div>
                    <span class="products-panel-note" id="tableStateLabel">Ready to load</span>
                </div>

                <div class="products-table-wrap">
                    <table class="products-table" aria-describedby="showingInfo">
                        <thead>
                            <tr>
                                <th class="products-selection-column" scope="col">
                                    <label class="checkbox-hit-area" for="selectAllProducts" data-admin-only>
                                        <input type="checkbox" class="form-check-input" id="selectAllProducts"
                                            data-admin-only aria-label="Select all visible products" title="Select all visible products">
                                    </label>
                                </th>
                                <th scope="col">ID</th>
                                <th scope="col">Product name</th>
                                <th scope="col">Category</th>
                                <th scope="col" class="products-quantity-column">Quantity</th>
                                <th scope="col" class="products-price-column">Price</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="products-actions-column" data-admin-only>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody"></tbody>
                    </table>
                </div>

                <div class="products-panel-footer">
                    <p id="showingInfo" aria-live="polite">Showing 0 products</p>
                    <div class="products-panel-actions">
                        <button class="btn btn-outline-primary" id="exportBtn" type="button">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M12 3v12M7 10l5 5 5-5M4 20h16" />
                            </svg>
                            <span>Export CSV</span>
                        </button>
                        <button class="btn btn-outline-danger" id="deleteSelectedBtn" type="button" data-admin-only disabled>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 7h14M10 11v6M14 11v6M7 7l1 13h8l1-13M9 7V4h6v3" />
                            </svg>
                            <span>Delete selected</span>
                        </button>
                    </div>
                </div>
            </section>
