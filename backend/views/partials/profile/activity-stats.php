                    <section class="profile-panel profile-stats-panel" aria-labelledby="profileStatsTitle">
                        <div class="profile-panel__heading">
                            <div>
                                <p class="profile-eyebrow">Operational snapshot</p>
                                <h2 id="profileStatsTitle">Your order activity</h2>
                            </div>
                            <span class="profile-panel__hint">Based on your existing order records.</span>
                        </div>
                        <div id="statsState" class="profile-state profile-state--info d-none" role="status" aria-live="polite"
                            aria-atomic="true">
                            <span id="statsStateMessage" class="profile-state__message">Loading order activity…</span>
                            <button type="button" class="profile-button profile-button--tertiary d-none" id="statsRetryBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 11a8 8 0 0 0-14.9-4M4 5v4h4M4 13a8 8 0 0 0 14.9 4M20 19v-4h-4" />
                                </svg>
                                <span>Retry</span>
                            </button>
                        </div>
                        <div class="profile-stat-grid">
                            <article class="profile-stat-card">
                                <span class="profile-stat-card__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 5h14v14H5zM8 9h8M8 13h6M8 17h4" />
                                    </svg>
                                </span>
                                <span class="profile-stat-card__copy">
                                    <strong id="totalOrders" class="profile-ltr" dir="ltr">—</strong>
                                    <span>Total orders</span>
                                </span>
                            </article>
                            <article class="profile-stat-card profile-stat-card--sell">
                                <span class="profile-stat-card__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 12h13m-5-5 5 5-5 5" />
                                    </svg>
                                </span>
                                <span class="profile-stat-card__copy">
                                    <strong id="sellOrders" class="profile-ltr" dir="ltr">—</strong>
                                    <span>Sell orders</span>
                                </span>
                            </article>
                            <article class="profile-stat-card profile-stat-card--purchase">
                                <span class="profile-stat-card__icon" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 12H6m5-5-5 5 5 5" />
                                    </svg>
                                </span>
                                <span class="profile-stat-card__copy">
                                    <strong id="purchaseOrders" class="profile-ltr" dir="ltr">—</strong>
                                    <span>Purchase orders</span>
                                </span>
                            </article>
                        </div>
                    </section>
