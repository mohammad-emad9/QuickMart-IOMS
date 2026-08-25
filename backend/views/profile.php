<?php
// Authentication Guard - Profile is available to every authenticated role.
require_once __DIR__ . '/../core/auth_check.php';
?>
<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="QuickMart IOMS - Profile and account settings">
    <title>QuickMart IOMS - Profile</title>
    <link rel="icon" type="image/png" href="../../assets/icons/icon-512.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/profile/profile.css?v=ui10">
    <link rel="stylesheet" href="../../assets/common.css?v=ui12">
</head>

<body>
    <?php require __DIR__ . '/partials/shell-nav.php'; ?>

    <main class="main-content profile-page">
        <div class="profile-content">
            <section id="profileState" class="profile-state profile-state--info" role="status" aria-live="polite"
                aria-atomic="true">
                <span class="profile-state__icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a9 9 0 1 0 9 9" />
                        <path d="M12 3v3" />
                    </svg>
                </span>
                <span id="profileStateMessage" class="profile-state__message">Loading profile…</span>
                <button type="button" class="profile-button profile-button--tertiary d-none" id="profileRetryBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20 11a8 8 0 0 0-14.9-4M4 5v4h4M4 13a8 8 0 0 0 14.9 4M20 19v-4h-4" />
                    </svg>
                    <span>Retry</span>
                </button>
            </section>

            <header class="profile-hero">
                <div class="profile-hero__copy">
                    <div class="profile-eyebrow">
                        <span class="profile-eyebrow__mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21a8 8 0 0 0-16 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                            </svg>
                        </span>
                        <span>Account workspace</span>
                    </div>
                    <h1>Profile and account settings</h1>
                    <p>Keep your operational identity current and manage your sign-in security from one focused workspace.</p>
                </div>
                <div class="profile-hero__note">
                    <span class="profile-hero__note-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 5 6v5c0 4.5 2.9 8 7 10 4.1-2 7-5.5 7-10V6l-7-3Z" />
                            <path d="m9.5 12 1.7 1.7 3.5-3.5" />
                        </svg>
                    </span>
                    <span>Role access is controlled by the QuickMart administration team.</span>
                </div>
            </header>

            <div class="profile-layout">
                <aside class="profile-identity-card" aria-labelledby="profileIdentityTitle">
                    <div class="profile-identity-card__top">
                        <div class="profile-avatar" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21a8 8 0 0 0-16 0M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z" />
                            </svg>
                        </div>
                        <div class="profile-identity-card__heading">
                            <p class="profile-eyebrow">Your identity</p>
                            <h2 id="profileIdentityTitle">Account profile</h2>
                        </div>
                    </div>
                    <div class="profile-identity-card__name-block">
                        <h3 id="profileName">—</h3>
                        <span class="profile-role-badge" id="profileRole">—</span>
                    </div>
                    <div class="profile-info" aria-label="Account information">
                        <div class="profile-info__row">
                            <span class="profile-info__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 6h16v12H4zM4 7l8 6 8-6" />
                                </svg>
                            </span>
                            <span class="profile-info__copy">
                                <span class="profile-info__label">Email</span>
                                <span id="profileEmail" class="profile-info__value profile-ltr" dir="ltr">—</span>
                            </span>
                        </div>
                        <div class="profile-info__row">
                            <span class="profile-info__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M7 4h3l1.2 4-2 1.6a14 14 0 0 0 5.2 5.2L16 13l4 1.2v3c0 1-.8 1.8-2 1.8C10.8 19 5 13.2 5 6c0-1 .8-2 2-2Z" />
                                </svg>
                            </span>
                            <span class="profile-info__copy">
                                <span class="profile-info__label">Phone</span>
                                <span id="profilePhone" class="profile-info__value profile-ltr" dir="ltr">—</span>
                            </span>
                        </div>
                        <div class="profile-info__row">
                            <span class="profile-info__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M4 5h16v14H4zM8 9h8M8 13h5" />
                                </svg>
                            </span>
                            <span class="profile-info__copy">
                                <span class="profile-info__label">Staff ID</span>
                                <span id="profileId" class="profile-info__value profile-ltr" dir="ltr">—</span>
                            </span>
                        </div>
                    </div>
                    <p class="profile-identity-card__note">Profile details are loaded from the authenticated staff record.</p>
                </aside>

                <div class="profile-workspace">
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

                    <section class="profile-panel" aria-labelledby="editProfileTitle">
                        <div class="profile-panel__heading">
                            <div>
                                <p class="profile-eyebrow">Personal details</p>
                                <h2 id="editProfileTitle">Edit profile</h2>
                            </div>
                            <span class="profile-panel__hint">Only your own profile fields can be changed here.</span>
                        </div>
                        <form id="editProfileForm" class="profile-form" autocomplete="off" novalidate>
                            <div class="profile-form__grid">
                                <div class="profile-field">
                                    <label for="editName">Full name</label>
                                    <input type="text" id="editName" required aria-required="true"
                                        aria-describedby="editNameHint" autocomplete="off">
                                    <span class="profile-field__hint" id="editNameHint">Use the name shown to colleagues in the operations ledger.</span>
                                </div>
                                <div class="profile-field">
                                    <label for="editEmail">Email</label>
                                    <input type="email" id="editEmail" required aria-required="true" autocomplete="off">
                                </div>
                                <div class="profile-field">
                                    <label for="editPhone">Phone <span>(optional)</span></label>
                                    <input type="tel" id="editPhone" inputmode="tel" autocomplete="off" dir="ltr">
                                </div>
                                <div class="profile-field">
                                    <label for="editRole">Role</label>
                                    <input type="text" id="editRole" readonly disabled aria-readonly="true"
                                        aria-describedby="editRoleHint">
                                    <span class="profile-field__hint" id="editRoleHint">Role access is managed by an administrator.</span>
                                </div>
                            </div>
                            <div id="profileFormMessage" class="profile-form-message d-none" role="alert" aria-live="polite"></div>
                            <div class="profile-form__actions">
                                <button type="submit" class="profile-button profile-button--primary">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M5 4h12l2 2v14H5zM8 4v6h8V4M8 20v-6h8v6" />
                                    </svg>
                                    <span>Save profile</span>
                                </button>
                            </div>
                        </form>
                    </section>

                    <section class="profile-panel profile-security-panel" aria-labelledby="changePasswordTitle">
                        <div class="profile-panel__heading">
                            <div>
                                <p class="profile-eyebrow">Sign-in security</p>
                                <h2 id="changePasswordTitle">Change password</h2>
                            </div>
                            <span class="profile-panel__hint">Password fields are used only for this secure request.</span>
                        </div>
                        <div class="profile-security-note">
                            <span class="profile-security-note__icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M7 11V8a5 5 0 0 1 10 0v3M5 11h14v9H5zM12 15v2" />
                                </svg>
                            </span>
                            <span>Enter your current password to confirm the change. Your password is never displayed or saved in browser storage.</span>
                        </div>
                        <form id="changePasswordForm" class="profile-form" autocomplete="off" novalidate>
                            <div class="profile-form__grid profile-form__grid--password">
                                <div class="profile-field">
                                    <label for="currentPassword">Current password</label>
                                    <input type="password" id="currentPassword" required aria-required="true"
                                        autocomplete="current-password">
                                </div>
                                <div class="profile-field">
                                    <label for="newPassword">New password</label>
                                    <input type="password" id="newPassword" required aria-required="true" minlength="6"
                                        maxlength="255" autocomplete="new-password" aria-describedby="newPasswordHint">
                                    <span class="profile-field__hint" id="newPasswordHint">Use the existing password policy.</span>
                                </div>
                                <div class="profile-field">
                                    <label for="confirmPassword">Confirm password</label>
                                    <input type="password" id="confirmPassword" required aria-required="true"
                                        minlength="6" maxlength="255" autocomplete="new-password">
                                </div>
                            </div>
                            <div id="passwordMessage" class="profile-form-message d-none" role="alert" aria-live="polite"></div>
                            <div class="profile-form__actions">
                                <button type="submit" class="profile-button profile-button--security">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                                        stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M7 11V8a5 5 0 0 1 10 0v3M5 11h14v9H5zM12 15v2" />
                                    </svg>
                                    <span>Update password</span>
                                </button>
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <div class="toast-container position-fixed bottom-0 end-0 p-3"></div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/common.js"></script>
    <script src="../../assets/profile/profile.js"></script>
</body>

</html>
