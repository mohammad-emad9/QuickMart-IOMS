            <section class="reports-summary" id="summaryCards" aria-labelledby="summaryHeading">
                <div class="section-heading reports-summary-heading">
                    <div>
                        <p class="section-kicker">At a glance</p>
                        <h2 id="summaryHeading">Operating picture</h2>
                    </div>
                    <p class="section-note">Backend-confirmed figures</p>
                </div>

                <div class="summary-grid">
                    <article class="metric-card metric-card--sales">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Total sales</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 3v18m4-14.5C15.2 5.7 13.8 5 12 5c-2.2 0-4 1.1-4 2.7 0 4.2 8 2.1 8 6.3 0 1.7-1.7 3-4 3-1.8 0-3.3-.7-4-1.8" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value money-value" id="totalSales" dir="ltr">—</p>
                        <p class="metric-card__detail"><span class="number-value" id="sellOrderCount" dir="ltr">—</span> sell orders</p>
                    </article>

                    <article class="metric-card metric-card--purchases">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Total purchases</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 5h2l2 11h10l2-8H7m3 13h.01M17 21h.01" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value money-value" id="totalPurchases" dir="ltr">—</p>
                        <p class="metric-card__detail"><span class="number-value" id="purchaseOrderCount" dir="ltr">—</span> purchase orders</p>
                    </article>

                    <article class="metric-card metric-card--revenue">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Net revenue</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 17 9 12l3 3 7-8M15 7h4v4" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value money-value" id="netRevenue" dir="ltr">—</p>
                        <p class="metric-card__detail">Sales less purchases</p>
                    </article>

                    <article class="metric-card metric-card--products">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Total products</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Zm-8 4.5 8 4.5 8-4.5M12 12v9" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value number-value" id="totalProducts" dir="ltr">—</p>
                        <p class="metric-card__detail">Products in the catalog</p>
                    </article>

                    <article class="metric-card metric-card--attention">
                        <div class="metric-card__topline">
                            <span class="metric-card__label">Low-stock count</span>
                            <span class="metric-card__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 4v8m0 4h.01M5 20h14a2 2 0 0 0 1.73-3L13.73 5a2 2 0 0 0-3.46 0L3.27 17A2 2 0 0 0 5 20Z" />
                                </svg>
                            </span>
                        </div>
                        <p class="metric-card__value number-value" id="lowStockCount" dir="ltr">—</p>
                        <p class="metric-card__detail">Low or out of stock</p>
                    </article>
                </div>
            </section>
