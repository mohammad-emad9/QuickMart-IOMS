    <div class="modal fade staff-modal" id="createStaffModal" tabindex="-1" data-bs-keyboard="false"
        aria-labelledby="createStaffModalTitle" aria-describedby="createStaffModalDescription" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content staff-modal__content">
                <div class="modal-header staff-modal__header">
                    <div>
                        <p class="staff-eyebrow">Admin action</p>
                        <h2 class="modal-title" id="createStaffModalTitle">Add staff member</h2>
                        <p id="createStaffModalDescription" class="staff-modal__description">
                            Create an operational account and assign one of the existing QuickMart roles.
                        </p>
                    </div>
                    <button type="button" class="staff-icon-button" data-bs-dismiss="modal"
                        aria-label="Close add staff dialog">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body staff-modal__body">
                    <form id="createStaffForm" class="staff-form" autocomplete="off" novalidate>
                        <div class="staff-form__grid">
                            <div class="staff-field">
                                <label for="createFullName">Full name</label>
                                <input type="text" id="createFullName" autocomplete="name" required aria-required="true"
                                    maxlength="100" aria-describedby="createFullNameHint">
                                <span class="staff-field__hint" id="createFullNameHint">Use the name shown in the operations directory.</span>
                            </div>
                            <div class="staff-field">
                                <label for="createEmail">Email</label>
                                <input type="email" id="createEmail" autocomplete="email" required aria-required="true"
                                    maxlength="100">
                            </div>
                            <div class="staff-field">
                                <label for="createPhone">Phone number <span>(optional)</span></label>
                                <input type="tel" id="createPhone" autocomplete="tel" inputmode="tel" maxlength="20">
                            </div>
                            <div class="staff-field">
                                <label for="createRole">Role</label>
                                <select id="createRole" required aria-required="true">
                                    <option value="Staff">Staff</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                            <div class="staff-field">
                                <label for="createPassword">Initial password</label>
                                <input type="password" id="createPassword" autocomplete="new-password" minlength="6"
                                    maxlength="255" required aria-required="true" aria-describedby="createPasswordHint">
                                <span class="staff-field__hint" id="createPasswordHint">You are setting the initial password. Recommend that the new user changes it after first sign-in.</span>
                            </div>
                            <div class="staff-field">
                                <label for="createPasswordConfirm">Confirm initial password</label>
                                <input type="password" id="createPasswordConfirm" autocomplete="new-password" minlength="6"
                                    maxlength="255" required aria-required="true" aria-describedby="createPasswordConfirmHint">
                                <span class="staff-field__hint" id="createPasswordConfirmHint">The two password fields must match.</span>
                            </div>
                        </div>
                        <div id="createMessage" class="staff-inline-message d-none" role="alert" aria-live="polite"></div>
                        <div class="staff-form__actions">
                            <button type="button" class="staff-button staff-button--secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="staff-button staff-button--primary" id="createStaffSubmitBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M22 11h-6" />
                                </svg>
                                <span>Create account</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade staff-modal" id="editStaffModal" tabindex="-1" data-bs-keyboard="false" aria-labelledby="editStaffModalTitle"
        aria-describedby="editStaffModalDescription" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content staff-modal__content">
                <div class="modal-header staff-modal__header">
                    <div>
                        <p class="staff-eyebrow">Admin action</p>
                        <h2 class="modal-title" id="editStaffModalTitle">Edit staff record</h2>
                        <p id="editStaffModalDescription" class="staff-modal__description">Update the account details used for operational access.</p>
                    </div>
                    <button type="button" class="staff-icon-button" data-bs-dismiss="modal" aria-label="Close edit staff dialog">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body staff-modal__body">
                    <form id="editStaffForm" class="staff-form" autocomplete="off" novalidate>
                        <input type="hidden" id="editStaffId">
                        <div class="staff-form__grid">
                            <div class="staff-field">
                                <label for="editFullName">Full name</label>
                                <input type="text" id="editFullName" autocomplete="off" required aria-required="true"
                                    aria-describedby="editFullNameHint">
                                <span class="staff-field__hint" id="editFullNameHint">Use the name shown in the operations directory.</span>
                            </div>
                            <div class="staff-field">
                                <label for="editEmail">Email</label>
                                <input type="email" id="editEmail" autocomplete="off" required aria-required="true">
                            </div>
                            <div class="staff-field">
                                <label for="editPhone">Phone number <span>(optional)</span></label>
                                <input type="tel" id="editPhone" autocomplete="off" inputmode="tel">
                            </div>
                            <div class="staff-field">
                                <label for="editRole">Role</label>
                                <select id="editRole" required aria-required="true">
                                    <option value="Staff">Staff</option>
                                    <option value="Manager">Manager</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                            <div class="staff-field staff-field--wide">
                                <label for="editPassword">New password <span>(optional)</span></label>
                                <input type="password" id="editPassword" autocomplete="new-password" minlength="6"
                                    maxlength="255" aria-describedby="editPasswordHint">
                                <span class="staff-field__hint" id="editPasswordHint">Leave blank to keep the current password. It is never displayed or saved in the browser.</span>
                            </div>
                        </div>
                        <div id="editMessage" class="staff-inline-message d-none" role="alert" aria-live="polite"></div>
                        <div class="staff-form__actions">
                            <button type="button" class="staff-button staff-button--secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="staff-button staff-button--primary" id="saveEditBtn">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 4h12l2 2v14H5zM8 4v6h8V4M8 20v-6h8v6" />
                                </svg>
                                <span>Save changes</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade staff-modal" id="staffDetailsModal" tabindex="-1" data-bs-keyboard="false" aria-labelledby="staffDetailsTitle"
        aria-describedby="staffDetailsDescription" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content staff-modal__content">
                <div class="modal-header staff-modal__header">
                    <div>
                        <p class="staff-eyebrow">Read-only view</p>
                        <h2 class="modal-title" id="staffDetailsTitle">Staff details</h2>
                        <p id="staffDetailsDescription" class="staff-modal__description">Review the account record without exposing password data.</p>
                    </div>
                    <button type="button" class="staff-icon-button" data-bs-dismiss="modal" aria-label="Close staff details dialog">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body staff-modal__body">
                    <div id="staffDetailsState" class="staff-modal-state staff-modal-state--info" role="status" aria-live="polite">
                        <span id="staffDetailsStateMessage">Loading staff details…</span>
                        <button type="button" class="staff-button staff-button--tertiary staff-modal-state__retry d-none" id="staffDetailsRetryBtn">Retry</button>
                    </div>
                    <dl id="staffDetailsList" class="staff-details-list d-none">
                        <div class="staff-details-list__row">
                            <dt>Staff ID</dt>
                            <dd id="detailsStaffId" class="staff-ltr" dir="ltr">—</dd>
                        </div>
                        <div class="staff-details-list__row">
                            <dt>Full name</dt>
                            <dd id="detailsFullName">—</dd>
                        </div>
                        <div class="staff-details-list__row">
                            <dt>Email</dt>
                            <dd id="detailsEmail" class="staff-ltr" dir="ltr">—</dd>
                        </div>
                        <div class="staff-details-list__row">
                            <dt>Phone</dt>
                            <dd id="detailsPhone" class="staff-ltr" dir="ltr">—</dd>
                        </div>
                        <div class="staff-details-list__row">
                            <dt>Role</dt>
                            <dd id="detailsRole">—</dd>
                        </div>
                    </dl>
                </div>
                <div class="modal-footer staff-modal__footer">
                    <button type="button" class="staff-button staff-button--secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="staff-button staff-button--primary" id="staffDetailsEditBtn" disabled>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m4 16-.7 4.7L8 20l10.8-10.8a2.2 2.2 0 0 0-3.1-3.1L4 16Zm9.9-8.4 3.1 3.1" />
                        </svg>
                        <span>Edit record</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade staff-modal" id="deleteStaffModal" tabindex="-1" data-bs-keyboard="false" aria-labelledby="deleteStaffModalTitle"
        aria-describedby="deleteStaffModalDescription" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content staff-modal__content staff-modal__content--danger">
                <div class="modal-header staff-modal__header">
                    <div>
                        <p class="staff-eyebrow">Destructive action</p>
                        <h2 class="modal-title" id="deleteStaffModalTitle">Remove staff account?</h2>
                    </div>
                    <button type="button" class="staff-icon-button" data-bs-dismiss="modal" aria-label="Close delete staff dialog">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="m6 6 12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>
                <div class="modal-body staff-modal__body">
                    <div class="staff-danger-callout">
                        <span class="staff-danger-callout__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3 2.8 20h18.4L12 3Z" />
                                <path d="M12 9v5m0 3h.01" />
                            </svg>
                        </span>
                        <p id="deleteStaffModalDescription">This removes the account from the staff directory. Accounts referenced by orders cannot be removed.</p>
                    </div>
                    <p class="staff-delete-target-label">Selected account</p>
                    <p class="staff-delete-target" id="deleteStaffName">—</p>
                    <input type="hidden" id="deleteStaffId">
                    <div id="deleteMessage" class="staff-inline-message staff-inline-message--warning d-none" role="alert" aria-live="assertive"></div>
                </div>
                <div class="modal-footer staff-modal__footer">
                    <button type="button" class="staff-button staff-button--secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="staff-button staff-button--danger" id="confirmDeleteBtn">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 7h14m-9 4v5m4-5v5M9 7V4h6v3m-8 0 1 13h8l1-13" />
                        </svg>
                        <span>Remove account</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade staff-modal" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content staff-modal__content">
                <div class="modal-body staff-logout">
                    <div class="staff-logout__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m10 17 5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-5" />
                        </svg>
                    </div>
                    <h2 id="logoutModalTitle">Sign out?</h2>
                    <p>Your current session will end on this device.</p>
                    <div class="staff-logout__actions">
                        <button type="button" class="staff-button staff-button--secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="staff-button staff-button--danger" id="confirmLogoutBtn">
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
