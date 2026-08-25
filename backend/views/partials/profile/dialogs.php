    <div class="modal fade profile-modal" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content profile-modal__content">
                <div class="modal-body profile-logout">
                    <div class="profile-logout__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" />
                        </svg>
                    </div>
                    <h2 id="logoutModalTitle">Sign out?</h2>
                    <p>Your current session will end on this device.</p>
                    <div class="profile-logout__actions">
                        <button type="button" class="profile-button profile-button--secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="profile-button profile-button--danger" id="confirmLogoutBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" />
                            </svg>
                            <span>Sign out</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
