            <section class="create-order-shell" id="createOrderModal" aria-labelledby="createOrderTitle">
                <header class="page-intro">
                    <div class="page-intro-copy">
                        <p class="qm-eyebrow">Order intake</p>
                        <h1 id="createOrderTitle">Create an order</h1>
                        <p class="page-intro-lead">Build a clear order draft, review the backend pricing, then submit it once.</p>
                    </div>
                    <div class="workflow-status-panel" aria-label="Order workspace status">
                        <span class="status-panel-label">Draft workspace</span>
                        <span class="status-panel-value" id="workflowStatus" role="status" aria-live="polite">Ready for products</span>
                    </div>
                </header>

                <div class="workflow-alert" id="workflowAlert" role="alert" aria-live="assertive" hidden></div>

                <div class="workflow-grid">
                    <form class="order-form" id="orderForm" novalidate>
                        <section class="flow-section" aria-labelledby="orderTypeHeading">
                            <div class="section-heading-row">
                                <div>
                                    <p class="section-kicker">01 / Direction</p>
                                    <h2 id="orderTypeHeading">Choose the order type</h2>
                                </div>
                                <span class="section-index" aria-hidden="true">01</span>
                            </div>

                            <fieldset class="order-type-fieldset">
                                <legend class="visually-hidden">Order type</legend>
                                <div class="order-type-grid">
                                    <div class="order-type-option">
                                        <input class="order-type-input" type="radio" name="orderType" id="sellOrder" value="sell" checked>
                                        <label class="order-type-card" for="sellOrder">
                                            <span class="order-type-icon order-type-icon-sell" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 4v16M7 9l5-5 5 5M5 20h14" />
                                                </svg>
                                            </span>
                                            <span class="order-type-copy">
                                                <span class="order-type-title">Sell order</span>
                                                <span class="order-type-description">Release stock to a customer</span>
                                            </span>
                                            <span class="selection-mark" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m5 12 4 4L19 6" />
                                                </svg>
                                            </span>
                                        </label>
                                    </div>

                                    <div class="order-type-option">
                                        <input class="order-type-input" type="radio" name="orderType" id="purchaseOrder" value="purchase">
                                        <label class="order-type-card" for="purchaseOrder">
                                            <span class="order-type-icon order-type-icon-purchase" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 20V4m5 11-5 5-5-5M5 4h14" />
                                                </svg>
                                            </span>
                                            <span class="order-type-copy">
                                                <span class="order-type-title">Purchase order</span>
                                                <span class="order-type-description">Receive stock from a supplier</span>
                                            </span>
                                            <span class="selection-mark" aria-hidden="true">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m5 12 4 4L19 6" />
                                                </svg>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </section>

                        <section class="flow-section" aria-labelledby="partyHeading">
                            <div class="section-heading-row">
                                <div>
                                    <p class="section-kicker">02 / Party</p>
                                    <h2 id="partyHeading">Add the <span id="customerLabel">Customer</span> information</h2>
                                </div>
                                <span class="section-index" aria-hidden="true">02</span>
                            </div>
                            <div class="field-block">
                                <label class="field-label" for="customerName"><span id="customerLabelText">Customer or supplier</span> name <span class="required-mark" aria-hidden="true">*</span></label>
                                <input class="form-control field-control" type="text" id="customerName" name="party_name" autocomplete="organization" maxlength="120"
                                    placeholder="Enter a name for this order" aria-describedby="customerNameHelp customerNameError" required>
                                <div class="field-help" id="customerNameHelp">Use the customer or supplier name that should appear on the order.</div>
                                <div class="field-error" id="customerNameError" role="alert" hidden></div>
                            </div>
                        </section>

                        <section class="flow-section" aria-labelledby="productsHeading">
                            <div class="section-heading-row">
                                <div>
                                    <p class="section-kicker">03 / Products</p>
                                    <h2 id="productsHeading">Select products</h2>
                                </div>
                                <span class="section-index" aria-hidden="true">03</span>
                            </div>

                            <div class="product-search-block">
                                <label class="field-label" for="productSearch">Search the product catalog</label>
                                <div class="product-search-row">
                                    <div class="product-search-control">
                                        <span class="input-leading-icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="11" cy="11" r="6.5" />
                                                <path d="m16 16 4.5 4.5" />
                                            </svg>
                                        </span>
                                        <input class="form-control field-control" type="search" id="productSearch" autocomplete="off"
                                            placeholder="Search by name or category" role="combobox" aria-autocomplete="list"
                                            aria-controls="productSuggestions" aria-expanded="false" aria-describedby="productAvailabilityState">
                                    </div>
                                    <button class="btn btn-secondary action-button" type="button" id="addItemBtn" data-bs-toggle="modal" data-bs-target="#selectProductModal">
                                        <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M12 5v14M5 12h14" />
                                        </svg>
                                        <span>Add from catalog</span>
                                    </button>
                                </div>
                                <div class="product-availability-state" id="productAvailabilityState" role="status" aria-live="polite">Loading product availability…</div>
                                <div class="product-suggestions" id="productSuggestions" role="listbox" aria-label="Product suggestions" hidden></div>
                            </div>

                            <div class="catalog-note">
                                <span class="note-icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="9" />
                                        <path d="M12 10v6M12 7h.01" />
                                    </svg>
                                </span>
                                <span>Sell quantities are limited by the latest available stock. Purchase quantities are checked again by the backend when submitted.</span>
                            </div>
                        </section>

                        <section class="flow-section cart-section" aria-labelledby="cartHeading">
                            <div class="section-heading-row section-heading-row-tight">
                                <div>
                                    <p class="section-kicker">04 / Cart</p>
                                    <h2 id="cartHeading">Review selected products</h2>
                                </div>
                                <span class="cart-count" id="cartItemCount" aria-live="polite">0 items</span>
                            </div>

                            <div class="order-table-frame">
                                <table class="table order-table" id="orderTable" aria-describedby="orderItemsHelp">
                                    <caption class="visually-hidden">Products selected for this order</caption>
                                    <thead>
                                <tr>
                                            <th scope="col">Product</th>
                                            <th scope="col">Available</th>
                                            <th scope="col">Unit price</th>
                                            <th scope="col">Quantity</th>
                                            <th scope="col">Line total after confirmation</th>
                                            <th scope="col"><span class="visually-hidden">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody id="orderTableBody"></tbody>
                                </table>
                                <div class="empty-order-message" id="emptyMessage" role="status">
                                    <span class="empty-state-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 6h16v13H4zM8 6V4h8v2M8 11h8M8 15h5" />
                                        </svg>
                                    </span>
                                    <strong>Your cart is ready for its first product</strong>
                                    <span>Search the catalog above or open the full product list to begin.</span>
                                </div>
                            </div>
                            <div class="field-error cart-error" id="orderItemsError" role="alert" hidden></div>
                            <button class="add-product-link" type="button" id="addProductLink">
                                <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                <span>Add another product</span>
                            </button>
                            <p class="field-help" id="orderItemsHelp">Prices shown here come from the product API. The backend confirms stored prices, stock, and the final total when you submit.</p>
                        </section>

                        <section class="order-summary-panel" aria-labelledby="summaryHeading">
                            <div>
                                <p class="section-kicker">05 / Ready to review</p>
                                <h2 id="summaryHeading">Order summary</h2>
                                <p class="summary-note">The total stays pending until the backend confirms stored prices, stock, and the final amount.</p>
                            </div>
                            <div class="summary-total-block">
                                <span class="summary-total-label">Backend total pending</span>
                                <span class="summary-total-value currency-value" id="grandTotalDisplay">—</span>
                            </div>
                        </section>

                        <div class="form-actions">
                            <button class="btn btn-quiet action-button" type="button" id="cancelOrderBtn">
                                <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 6l12 12M18 6 6 18" />
                                </svg>
                                <span>Cancel</span>
                            </button>
                            <button class="btn btn-primary action-button action-button-primary" type="submit" id="createOrderBtn">
                                <svg class="button-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m5 12 4 4L19 6" />
                                </svg>
                                <span id="createOrderBtnLabel">Review order</span>
                            </button>
                        </div>
                    </form>

                    <aside class="workflow-aside" aria-label="Order workflow guide">
                        <div class="aside-card aside-card-primary">
                            <p class="aside-eyebrow">Quick guide</p>
                            <h2>Keep the handoff clear</h2>
                            <p>Complete each checkpoint before confirming. The final response from QuickMart remains the source of truth.</p>
                            <ol class="workflow-steps">
                                <li class="workflow-step is-active">
                                    <span class="step-number">01</span>
                                    <span><strong>Choose a direction</strong><small>Sell stock or receive stock.</small></span>
                                </li>
                                <li class="workflow-step">
                                    <span class="step-number">02</span>
                                    <span><strong>Add a party</strong><small>Record who the order is for.</small></span>
                                </li>
                                <li class="workflow-step">
                                    <span class="step-number">03</span>
                                    <span><strong>Review and confirm</strong><small>Submit only the quantities.</small></span>
                                </li>
                            </ol>
                        </div>
                        <div class="aside-card aside-card-note">
                            <span class="aside-note-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M5 4h14v16H5zM8 8h8M8 12h8M8 16h5" />
                                </svg>
                            </span>
                            <div><strong>Stored values stay authoritative</strong><span>Unit prices, stock, order ID, and final totals are confirmed by the API.</span></div>
                        </div>
                    </aside>
                </div>
            </section>
