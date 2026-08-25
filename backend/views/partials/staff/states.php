            <section id="accessDenied" class="staff-state-card staff-state-card--restricted d-none" role="alert"
                aria-labelledby="accessDeniedTitle" aria-live="polite">
                <div class="staff-state-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M7 11V8a5 5 0 0 1 10 0v3M5 11h14v9H5zM12 15v2" />
                    </svg>
                </div>
                <div class="staff-state-card__copy">
                    <p class="staff-eyebrow">Restricted workspace</p>
                    <h1 id="accessDeniedTitle">Staff management is unavailable</h1>
                    <p>This workspace is reserved for administrators. Your account can continue in the operational areas assigned to its role.</p>
                    <a class="staff-button staff-button--secondary" href="dashboard.php">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 5 8 12l7 7M9 12h11" />
                        </svg>
                        <span>Return to dashboard</span>
                    </a>
                </div>
            </section>

            <section id="staffErrorState" class="staff-state-card staff-state-card--error d-none" role="alert"
                aria-labelledby="staffErrorTitle" aria-live="assertive">
                <div class="staff-state-card__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20ZM9 9l6 6m0-6-6 6" />
                    </svg>
                </div>
                <div class="staff-state-card__copy">
                    <p class="staff-eyebrow">Directory unavailable</p>
                    <h1 id="staffErrorTitle">We could not load the staff directory</h1>
                    <p id="staffErrorMessage">Please retry when the staff service is available.</p>
                    <button type="button" class="staff-button staff-button--primary" id="staffRetryBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 11a8 8 0 0 0-14.9-4M4 5v4h4M4 13a8 8 0 0 0 14.9 4M20 19v-4h-4" />
                        </svg>
                        <span>Retry loading</span>
                    </button>
                </div>
            </section>

            <section id="loadingSpinner" class="staff-state-card staff-state-card--loading" role="status"
                aria-live="polite">
                <div class="staff-state-card__icon staff-state-card__icon--loading" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a9 9 0 1 0 9 9" />
                        <path d="M12 3v3" />
                    </svg>
                </div>
                <div class="staff-state-card__copy">
                    <p class="staff-eyebrow">Admin access check</p>
                    <h1>Loading staff directory</h1>
                    <p>Verifying your session and preparing the latest staff records.</p>
                </div>
            </section>
