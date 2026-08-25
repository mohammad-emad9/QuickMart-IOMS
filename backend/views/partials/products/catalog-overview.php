            <section class="products-hero" aria-labelledby="productsPageTitle">
                <div class="products-hero-copy">
                    <p class="products-eyebrow">
                        <span class="products-eyebrow-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" />
                                <path d="m4.5 7.5 7.5 4 7.5-4M12 12v8.5" />
                            </svg>
                        </span>
                        Inventory workspace
                    </p>
                    <h1 class="page-title" id="productsPageTitle">Products &amp; Inventory</h1>
                    <p class="products-subtitle">
                        Keep the catalog accurate, spot stock pressure early, and maintain a reliable operating record.
                    </p>
                </div>
                <div class="products-hero-meta" aria-label="Catalog size">
                    <span class="products-meta-label">Catalog size</span>
                    <strong class="products-meta-value" id="productCount" aria-live="polite">Loading...</strong>
                    <span class="products-meta-note">Live inventory register</span>
                </div>
            </section>

            <section class="inventory-summary" aria-labelledby="inventorySummaryTitle">
                <div class="products-section-heading">
                    <div>
                        <p class="products-section-kicker">Inventory pulse</p>
                        <h2 class="products-section-title" id="inventorySummaryTitle">At-a-glance stock health</h2>
                    </div>
                    <span class="products-section-note">Backend-authoritative values</span>
                </div>

                <div class="inventory-summary-grid">
                    <article class="inventory-summary-card inventory-summary-card-total">
                        <div class="inventory-summary-topline">
                            <span class="inventory-summary-label">Total products</span>
                            <span class="inventory-summary-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" />
                                    <path d="m4.5 7.5 7.5 4 7.5-4M12 12v8.5" />
                                </svg>
                            </span>
                        </div>
                        <strong class="inventory-summary-value" id="summaryProductCount" aria-live="polite">—</strong>
                        <span class="inventory-summary-foot">Active catalog records</span>
                    </article>

                    <article class="inventory-summary-card inventory-summary-card-units">
                        <div class="inventory-summary-topline">
                            <span class="inventory-summary-label">Units in stock</span>
                            <span class="inventory-summary-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 8h16v12H4zM7 8V5h10v3M8 12h8M8 16h5" />
                                </svg>
                            </span>
                        </div>
                        <strong class="inventory-summary-value numeric-value" id="inventoryUnitCount"
                            aria-live="polite">—</strong>
                        <span class="inventory-summary-foot">Across all product records</span>
                    </article>

                    <article class="inventory-summary-card inventory-summary-card-low">
                        <div class="inventory-summary-topline">
                            <span class="inventory-summary-label">Low stock</span>
                            <span class="inventory-summary-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3 21 20H3L12 3Z" />
                                    <path d="M12 9v5M12 17h.01" />
                                </svg>
                            </span>
                        </div>
                        <strong class="inventory-summary-value numeric-value" id="lowStockCount" aria-live="polite">—</strong>
                        <span class="inventory-summary-foot">Needs replenishment review</span>
                    </article>

                    <article class="inventory-summary-card inventory-summary-card-out">
                        <div class="inventory-summary-topline">
                            <span class="inventory-summary-label">Out of stock</span>
                            <span class="inventory-summary-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 7.5 12 3l8 4.5v9L12 21l-8-4.5v-9Z" />
                                    <path d="m9 9 6 6M15 9l-6 6" />
                                </svg>
                            </span>
                        </div>
                        <strong class="inventory-summary-value numeric-value" id="outOfStockCount"
                            aria-live="polite">—</strong>
                        <span class="inventory-summary-foot">Requires immediate attention</span>
                    </article>
                </div>
            </section>
