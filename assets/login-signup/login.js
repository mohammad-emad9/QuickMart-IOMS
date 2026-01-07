/**
 * QuickMart IOMS - Login Page JavaScript
 * Handles form validation, submission, and UI interactions
 */

// Wait for DOM to be fully loaded
document.addEventListener("DOMContentLoaded", function () {

    // ===========================================
    // Element References
    // ===========================================
    const loginForm = document.getElementById("loginForm");
    const emailInput = document.getElementById("emailInput");
    const passwordInput = document.getElementById("passwordInput");
    const togglePasswordBtn = document.getElementById("togglePassword");
    const forgotPasswordLink = document.getElementById("forgotPasswordLink");

    // ===========================================
    // Password Visibility Toggle
    // ===========================================
    if (togglePasswordBtn) {
        togglePasswordBtn.addEventListener("click", function () {
            const icon = this.querySelector("i");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    }

    // ===========================================
    // Toast Notification System
    // ===========================================
    function showToast(message, type = "success") {
        // Remove existing toast if any
        const existingToast = document.querySelector(".toast-notification");
        if (existingToast) {
            existingToast.remove();
        }

        // Create new toast
        const toast = document.createElement("div");
        toast.className = `toast-notification ${type}`;
        toast.innerHTML = `
            <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
            ${message}
        `;

        document.body.appendChild(toast);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = "slideIn 0.3s ease-out reverse";
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // ===========================================
    // Input Validation
    // ===========================================
    function validateInput(input) {
        if (!input.value.trim()) {
            input.classList.add("is-invalid");
            return false;
        } else {
            input.classList.remove("is-invalid");
            return true;
        }
    }

    // Real-time validation on blur
    if (emailInput) {
        emailInput.addEventListener("blur", function () {
            validateInput(this);
        });
    }

    if (passwordInput) {
        passwordInput.addEventListener("blur", function () {
            validateInput(this);
        });
    }

    // Remove invalid state on input
    [emailInput, passwordInput].forEach(input => {
        if (input) {
            input.addEventListener("input", function () {
                if (this.value.trim()) {
                    this.classList.remove("is-invalid");
                }
            });
        }
    });

    // ===========================================
    // Form Submission Handler
    // ===========================================
    if (loginForm) {
        loginForm.addEventListener("submit", async function (event) {
            // Prevent default form submission
            event.preventDefault();

            // Validate all fields
            const isEmailValid = validateInput(emailInput);
            const isPasswordValid = validateInput(passwordInput);

            if (!isEmailValid || !isPasswordValid) {
                showToast("Please fill in all required fields.", "error");
                return;
            }

            // Get form values
            const credentials = {
                email: emailInput.value.trim(),
                password: passwordInput.value
            };

            // Get login button and add loading state
            const loginBtn = loginForm.querySelector(".btn-login");
            const originalBtnText = loginBtn.innerHTML;
            loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Signing In...';
            loginBtn.classList.add("loading");
            loginBtn.disabled = true;

            try {
                // Simulate API call delay (remove this in production)
                await simulateLogin(credentials);

                // Success - Show toast and redirect
                showToast("Login Successful! Redirecting to Dashboard...", "success");

                // Store session info
                sessionStorage.setItem("isLoggedIn", "true");
                sessionStorage.setItem("userEmail", credentials.email);

                // Redirect to dashboard after short delay
                setTimeout(() => {
                    window.location.href = "../../backend/views/dashboard.php";
                }, 1500);

            } catch (error) {
                // Error handling
                showToast(error.message || "Login failed. Please try again.", "error");

                // Reset button state
                loginBtn.innerHTML = originalBtnText;
                loginBtn.classList.remove("loading");
                loginBtn.disabled = false;
            }
        });
    }

    // ===========================================
    // Real Login Function - Calls Backend API
    // ===========================================
    async function simulateLogin(credentials) {
        const response = await fetch('/QuickMart code/backend/api/auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(credentials)
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Invalid email/staff ID or password.');
        }

        // Store user data in sessionStorage
        if (data.data) {
            sessionStorage.setItem('staffId', data.data.staff_id);
            sessionStorage.setItem('userName', data.data.full_name);
            sessionStorage.setItem('userEmail', data.data.email);
            sessionStorage.setItem('userRole', data.data.role);
        }

        return data;
    }

    // ===========================================
    // Forgot Password Handler - Opens Modal
    // ===========================================
    if (forgotPasswordLink) {
        forgotPasswordLink.addEventListener("click", function (event) {
            event.preventDefault();

            // Pre-fill email if already entered
            const resetEmail = document.getElementById("resetEmail");
            if (emailInput.value.trim()) {
                resetEmail.value = emailInput.value.trim();
            }

            // Show the modal
            const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            modal.show();
        });
    }

    // ===========================================
    // Forgot Password Form Submit
    // ===========================================
    const forgotPasswordForm = document.getElementById("forgotPasswordForm");
    if (forgotPasswordForm) {
        forgotPasswordForm.addEventListener("submit", async function (event) {
            event.preventDefault();

            const email = document.getElementById("resetEmail").value.trim();
            const newPassword = document.getElementById("newPassword").value;
            const confirmPassword = document.getElementById("confirmNewPassword").value;
            const resetMessage = document.getElementById("resetMessage");
            const resetBtn = document.getElementById("resetBtn");

            // Validate passwords match
            if (newPassword !== confirmPassword) {
                resetMessage.className = "alert alert-danger";
                resetMessage.textContent = "Passwords do not match!";
                resetMessage.classList.remove("d-none");
                return;
            }

            // Validate password length
            if (newPassword.length < 6) {
                resetMessage.className = "alert alert-danger";
                resetMessage.textContent = "Password must be at least 6 characters!";
                resetMessage.classList.remove("d-none");
                return;
            }

            // Show loading
            const originalText = resetBtn.innerHTML;
            resetBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Resetting...';
            resetBtn.disabled = true;

            try {
                const response = await fetch('/QuickMart code/backend/api/auth/forgot_password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, new_password: newPassword })
                });

                const data = await response.json();

                if (data.success) {
                    resetMessage.className = "alert alert-success";
                    resetMessage.textContent = data.message;
                    resetMessage.classList.remove("d-none");

                    // Close modal after 2 seconds
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
                        forgotPasswordForm.reset();
                        resetMessage.classList.add("d-none");
                        showToast("Password reset successful! Please login.", "success");
                    }, 2000);
                } else {
                    resetMessage.className = "alert alert-danger";
                    resetMessage.textContent = data.message;
                    resetMessage.classList.remove("d-none");
                }
            } catch (error) {
                resetMessage.className = "alert alert-danger";
                resetMessage.textContent = "An error occurred. Please try again.";
                resetMessage.classList.remove("d-none");
            } finally {
                resetBtn.innerHTML = originalText;
                resetBtn.disabled = false;
            }
        });
    }

    // ===========================================
    // Form Reset Handler
    // ===========================================
    if (loginForm) {
        loginForm.addEventListener("reset", function () {
            // Clear validation states
            [emailInput, passwordInput].forEach(input => {
                if (input) {
                    input.classList.remove("is-invalid");
                }
            });

            // Reset password visibility
            if (passwordInput) {
                passwordInput.type = "password";
            }
            if (togglePasswordBtn) {
                const icon = togglePasswordBtn.querySelector("i");
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        });
    }

    // ===========================================
    // Keyboard Shortcuts
    // ===========================================
    document.addEventListener("keydown", function (event) {
        // Enter key submits form (when not in a textarea)
        if (event.key === "Enter" && event.target.tagName !== "TEXTAREA") {
            if (document.activeElement === emailInput) {
                passwordInput.focus();
                event.preventDefault();
            }
        }

        // Escape key clears form
        if (event.key === "Escape") {
            loginForm.reset();
        }
    });

    // ===========================================
    // Auto-focus on Email Input
    // ===========================================
    if (emailInput) {
        emailInput.focus();
    }

    // ===========================================
    // Session Check (Optional - for redirect logic)
    // ===========================================
    function checkExistingSession() {
        const isLoggedIn = sessionStorage.getItem("isLoggedIn");
        if (isLoggedIn === "true") {
            // User is already logged in, redirect to dashboard
            window.location.href = "../../backend/views/dashboard.php";
        }
    }

    // Uncomment the line below to enable auto-redirect for logged-in users
    // checkExistingSession();

    console.log("QuickMart IOMS - Login page initialized");
});
