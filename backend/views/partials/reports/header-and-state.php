            <header class="reports-hero">
                <div class="reports-hero-copy">
                    <div class="reports-eyebrow">
                        <span class="reports-eyebrow-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19V5h16v14H4Zm4-3v-4m4 4V8m4 8V6" />
                            </svg>
                        </span>
                        <span>Administrator workspace</span>
                    </div>
                    <h1 class="reports-title">Reports &amp; Analytics</h1>
                    <p class="reports-subtitle">A calm, evidence-led view of commercial activity and inventory health.</p>
                </div>

                <div class="reports-actions" aria-label="Report actions">
                    <button type="button" class="reports-button reports-button--secondary" id="refreshReportsBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 11a8 8 0 0 0-14.9-4M4 5v4h4M4 13a8 8 0 0 0 14.9 4M20 19v-4h-4" />
                        </svg>
                        <span>Refresh report</span>
                    </button>
                    <button type="button" class="reports-button reports-button--primary" id="exportReportBtn" disabled aria-disabled="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3v12m0 0 4-4m-4 4-4-4M5 19h14" />
                        </svg>
                        <span>Export CSV</span>
                    </button>
                </div>
            </header>

            <section id="reportsState" class="reports-state" role="status" aria-live="polite" hidden>
                <div class="reports-state__content">
                    <span class="reports-state__icon" data-report-state-icon aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 8v5m0 3h.01M10.3 3.8 2.8 17a2 2 0 0 0 1.75 3h14.9a2 2 0 0 0 1.75-3l-7.5-13.2a2 2 0 0 0-3.4 0Z" />
                        </svg>
                    </span>
                    <span id="reportsStateMessage"></span>
                </div>
                <button type="button" class="reports-button reports-button--compact reports-button--state d-none" id="reportsRetryBtn" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 11a8 8 0 0 0-14.9-4M4 5v4h4" />
                    </svg>
                    <span>Retry</span>
                </button>
            </section>
