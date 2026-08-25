            <div class="products-notice products-notice-readonly" id="accessNotice" role="status" aria-live="polite" hidden>
                <span class="products-notice-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3 20 6v5c0 5-3.5 8.5-8 10-4.5-1.5-8-5-8-10V6l8-3Z" />
                        <path d="M12 10v5M12 7.5h.01" />
                    </svg>
                </span>
                <span class="products-notice-copy">
                    <strong>Read-only access</strong>
                    <span id="accessNoticeText">Your role can review inventory but cannot change product records.</span>
                </span>
            </div>

            <section class="products-toolbar" aria-labelledby="productsFiltersTitle">
                <div class="products-section-heading products-toolbar-heading">
                    <div>
                        <p class="products-section-kicker">Catalog controls</p>
                        <h2 class="products-section-title" id="productsFiltersTitle">Find the right record</h2>
                    </div>
                    <span class="products-section-note">Search, refine, then export the current register</span>
                </div>

                <div class="products-filter-grid">
                    <div class="filter-field filter-field-search">
                        <label for="searchInput">Search products</label>
                        <div class="products-search-control">
                            <svg class="products-control-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="10.8" cy="10.8" r="6.8" />
                                <path d="m16 16 5 5" />
                            </svg>
                            <input type="search" class="form-control" id="searchInput"
                                placeholder="Search by product name or ID" autocomplete="off">
                        </div>
                    </div>

                    <div class="filter-field">
                        <label for="categoryFilter">Category</label>
                        <select id="categoryFilter" class="form-select">
                            <option value="All">All categories</option>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="stockFilter">Stock status</label>
                        <select id="stockFilter" class="form-select">
                            <option value="All">All stock statuses</option>
                            <option value="normal">Normal only</option>
                            <option value="low">Low or out of stock</option>
                            <option value="out-of-stock">Out of stock only</option>
                        </select>
                    </div>

                    <div class="filter-field">
                        <label for="sortFilter">Sort by</label>
                        <select id="sortFilter" class="form-select">
                            <option value="name">Name: A–Z</option>
                            <option value="name-desc">Name: Z–A</option>
                            <option value="price-asc">Price: low to high</option>
                            <option value="price-desc">Price: high to low</option>
                            <option value="quantity-asc">Stock: low to high</option>
                            <option value="quantity-desc">Stock: high to low</option>
                        </select>
                    </div>

                    <div class="filter-field filter-field-action" data-admin-only>
                        <span class="filter-field-label" aria-hidden="true">Record action</span>
                        <button class="btn btn-primary products-primary-action" id="addProductBtn" type="button" data-admin-only>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" aria-hidden="true">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            <span>Add product</span>
                        </button>
                    </div>
                </div>
            </section>
