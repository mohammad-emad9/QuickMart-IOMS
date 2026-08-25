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
