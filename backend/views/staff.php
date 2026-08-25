<?php
// Authentication Guard - Staff management is restricted to administrators.
require_once __DIR__ . '/../core/auth_check.php';
// auth_check.php performs the authenticated-session check on include.
// Keep the role gate here without running the shared authentication check twice.
if (!isAdmin()) {
    renderForbiddenPage();
}
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Staff management">
    <title>QuickMart IOMS - Staff Management</title>
    <link rel="icon" type="image/png" href="../../assets/icons/icon-512.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/staff/staff.css?v=ui10">
    <link rel="stylesheet" href="../../assets/common.css?v=ui12">
</head>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content staff-page">
        <div class="staff-content">
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
            </div>
        </div>
    </main>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/common.js"></script>
    <script src="../../assets/staff/staff.js"></script>
</body>

</html>
