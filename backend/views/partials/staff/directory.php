            <div id="adminContent" class="d-none">
                <header class="staff-hero">
                    <div class="staff-hero__copy">
                        <div class="staff-eyebrow">
                            <span class="staff-eyebrow__mark" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM17 8a3 3 0 1 1 0 6M20 21v-2a4 4 0 0 0-2.5-3.7" />
                                </svg>
                            </span>
                            <span>Administrator workspace</span>
                        </div>
                        <h1 class="staff-title">Staff management</h1>
                        <p class="staff-subtitle">Keep access records accurate, focused, and ready for daily operations.</p>
                    </div>
                    <div class="staff-hero__actions">
                        <div class="staff-count-card" aria-label="Staff directory count">
                            <span class="staff-count-card__label">Directory</span>
                            <span class="staff-count-card__value" id="staffCount" dir="ltr">— staff members</span>
                        </div>
                        <button type="button" class="staff-button staff-button--secondary" id="refreshStaffBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 11a8 8 0 0 0-14.9-4M4 5v4h4M4 13a8 8 0 0 0 14.9 4M20 19v-4h-4" />
                            </svg>
                            <span>Refresh directory</span>
                        </button>
                        <button type="button" class="staff-button staff-button--primary" id="addStaffBtn"
                            aria-haspopup="dialog" aria-controls="createStaffModal">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M22 11h-6" />
                            </svg>
                            <span>Add staff member</span>
                        </button>
                    </div>
                </header>

                <section class="staff-context-strip" aria-label="Staff management guidance">
                    <span class="staff-context-strip__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 5 6v5c0 4.5 2.9 8 7 10 4.1-2 7-5.5 7-10V6l-7-3Z" />
                            <path d="m9.5 12 1.7 1.7 3.5-3.5" />
                        </svg>
                    </span>
                    <span>Administrator controls are limited to updating existing staff records and removing accounts that are not referenced by orders.</span>
                    <span class="staff-context-strip__note">Passwords are never shown here.</span>
                </section>

                <section class="staff-directory-panel" aria-labelledby="staffDirectoryTitle">
                    <div class="staff-section-heading">
                        <div>
                            <p class="staff-eyebrow">Access directory</p>
                            <h2 id="staffDirectoryTitle">People with operational access</h2>
                        </div>
                        <span class="staff-section-heading__hint">Select a record to view details or edit access.</span>
                    </div>
                    <div id="staffGrid" class="staff-grid" role="list" aria-label="Staff directory"></div>
                </section>
