/**
 * Public sign-in behavior and authentication feedback.
 */

(function () {
    "use strict";

    const iconPaths = Object.freeze({
        loading: ["M12 3a9 9 0 1 0 9 9", "M12 3v3"],
        login: ["M13 5h6v14h-6M4 12h11M11 8l4 4-4 4"],
        reset: ["M4 12a8 8 0 1 0 2.3-5.7M4 5v5h5"],
        eye: ["M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z", "M12 9.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5Z"],
        eyeOff: ["m3 3 18 18", "M10.6 10.6a2 2 0 0 0 2.8 2.8", "M9.9 5.2A9.9 9.9 0 0 1 12 5c6 0 9.5 7 9.5 7a17 17 0 0 1-3.1 3.7", "M6.1 6.1C3.8 8 2.5 12 2.5 12s3.5 7 9.5 7a9 9 0 0 0 2.1-.2"],
        check: ["M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z", "m8 12 2.5 2.5L16 9"],
        error: ["M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z", "m9 9 6 6", "m15 9-6 6"],
        info: ["M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20Z", "M12 10v6", "M12 7.5h.01"]
    });

    const state = {
        submitting: false,
        redirecting: false
    };

    function getElement(id) {
        return document.getElementById(id);
    }

    function createIcon(name) {
        const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
        svg.setAttribute("viewBox", "0 0 24 24");
        svg.setAttribute("fill", "none");
        svg.setAttribute("stroke", "currentColor");
        svg.setAttribute("stroke-width", "1.8");
        svg.setAttribute("stroke-linecap", "round");
        svg.setAttribute("stroke-linejoin", "round");
        svg.setAttribute("aria-hidden", "true");

        (iconPaths[name] || iconPaths.info).forEach((pathData) => {
            const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
            path.setAttribute("d", pathData);
            svg.appendChild(path);
        });

        if (name === "loading") {
            svg.classList.add("login-loading-icon");
        }

        return svg;
    }

    function setButtonContents(button, iconName, label) {
        if (!button) {
            return;
        }

        button.replaceChildren(createIcon(iconName), document.createTextNode(label));
    }

    function setLoginMessage(message, type = "error") {
        const element = getElement("loginMessage");

        if (!element) {
            return;
        }

        element.className = "login-message";
        if (["success", "info"].includes(type)) {
            element.classList.add(`login-message--${type}`);
        }
        element.replaceChildren(
            createIcon(type === "success" ? "check" : type === "info" ? "info" : "error"),
            document.createTextNode(String(message || ""))
        );
        element.classList.remove("d-none");
    }

    function hideLoginMessage() {
        const element = getElement("loginMessage");

        if (!element) {
            return;
        }

        element.className = "login-message d-none";
        element.replaceChildren();
    }

    function notify(message, type = "error") {
        if (typeof showToast === "function") {
            showToast(String(message || ""), type);
        }
    }

    function validateInput(input) {
        if (!input) {
            return false;
        }

        const valid = input.value.trim().length > 0;
        input.classList.toggle("is-invalid", !valid);
        input.setAttribute("aria-invalid", String(!valid));
        return valid;
    }

    function clearInputError(input) {
        if (!input || !input.value.trim()) {
            return;
        }

        input.classList.remove("is-invalid");
        input.setAttribute("aria-invalid", "false");
    }

    function setFormBusy(form, busy) {
        if (!form) {
            return;
        }

        form.querySelectorAll("input, button").forEach((control) => {
            control.disabled = Boolean(busy);
        });
    }

    function setPasswordVisibility(passwordInput, toggleButton, visible) {
        if (!passwordInput || !toggleButton) {
            return;
        }

        passwordInput.type = visible ? "text" : "password";
        toggleButton.setAttribute("aria-pressed", String(visible));
        toggleButton.setAttribute("aria-label", visible ? "Hide password" : "Show password");
        toggleButton.replaceChildren(createIcon(visible ? "eyeOff" : "eye"));
    }

    function responseMessage(response, data) {
        if (data && typeof data.message === "string" && data.message.trim() !== "") {
            return data.message;
        }

        switch (response.status) {
            case 401:
                return "Invalid email, Staff ID, or password.";
            case 422:
                return "Please check the sign-in details and try again.";
            case 429:
                return "Too many sign-in attempts. Please wait and try again.";
            default:
                return "Unable to sign in right now. Please try again.";
        }
    }

    async function readApiResponse(response) {
        try {
            return await response.json();
        } catch (error) {
            return null;
        }
    }

    function storeSessionData(data) {
        if (!data || typeof data !== "object") {
            return;
        }

        if (typeof data.staff_id === "string") {
            sessionStorage.setItem("staffId", data.staff_id);
        }
        if (typeof data.full_name === "string") {
            sessionStorage.setItem("userName", data.full_name);
        }
        if (typeof data.email === "string") {
            sessionStorage.setItem("userEmail", data.email);
        }
        if (typeof data.role === "string") {
            sessionStorage.setItem("userRole", data.role);
        }
    }

    function clearClientAuthState() {
        ["isLoggedIn", "staffId", "userName", "userEmail", "userRole"].forEach((key) => {
            sessionStorage.removeItem(key);
        });
    }

    function restoreIdleState(loginForm, loginButton, resetButton) {
        state.submitting = false;
        setFormBusy(loginForm, false);
        setButtonContents(loginButton, "login", "Sign in");
        if (loginButton) {
            loginButton.classList.remove("loading");
            loginButton.removeAttribute("aria-busy");
        }
        if (resetButton) {
            resetButton.disabled = false;
        }
    }

    function resetFormState(loginForm, emailInput, passwordInput, togglePasswordButton) {
        if (!loginForm) {
            return;
        }

        loginForm.classList.remove("was-validated");
        [emailInput, passwordInput].forEach((input) => {
            if (input) {
                input.classList.remove("is-invalid");
                input.setAttribute("aria-invalid", "false");
            }
        });
        setPasswordVisibility(passwordInput, togglePasswordButton, false);
        hideLoginMessage();
    }

    function openForgotPasswordModal() {
        const modalElement = getElement("forgotPasswordModal");

        if (!modalElement) {
            return;
        }

        if (typeof bootstrap !== "undefined" && bootstrap.Modal) {
            bootstrap.Modal.getOrCreateInstance(modalElement).show();
        }
    }

    function requestCloseForgotPasswordModal() {
        const modalElement = getElement("forgotPasswordModal");

        if (!modalElement) {
            return;
        }

        // Let the opening transition finish before delegating to Bootstrap's
        // normal dismiss behavior, so Escape is reliable even immediately
        // after the dialog receives focus.
        window.setTimeout(() => {
            if (!modalElement.classList.contains("show")) {
                return;
            }

            const modalInstance = typeof bootstrap !== "undefined" && bootstrap.Modal
                ? bootstrap.Modal.getInstance(modalElement)
                : null;
            modalInstance?.hide();

            // The fallback also handles a close request that arrives while
            // Bootstrap is still opening the dialog transition.
            window.setTimeout(() => {
                if (!modalElement.classList.contains("show")) {
                    return;
                }

                modalElement.classList.remove("show");
                modalElement.style.display = "none";
                modalElement.setAttribute("aria-hidden", "true");
                modalElement.removeAttribute("aria-modal");
                modalElement.removeAttribute("role");
                document.body.classList.remove("modal-open");
                document.body.style.removeProperty("padding-right");
                document.querySelectorAll(".modal-backdrop").forEach((backdrop) => backdrop.remove());
                modalInstance?.dispose();
                getElement("forgotPasswordLink")?.focus();
            }, 150);
        }, 200);
    }

    async function handleSubmit(event) {
        event.preventDefault();

        if (state.submitting) {
            return;
        }

        const loginForm = event.currentTarget;
        const emailInput = getElement("emailInput");
        const passwordInput = getElement("passwordInput");
        const loginButton = loginForm.querySelector('button[type="submit"]');
        const resetButton = loginForm.querySelector('button[type="reset"]');

        loginForm.classList.add("was-validated");
        const emailValid = validateInput(emailInput);
        const passwordValid = validateInput(passwordInput);

        if (!emailValid || !passwordValid) {
            const firstInvalid = !emailValid ? emailInput : passwordInput;
            setLoginMessage("Enter your staff email or ID and password.");
            notify("Please complete the required sign-in fields.");
            firstInvalid?.focus();
            return;
        }

        const credentials = {
            email: emailInput.value.trim(),
            password: passwordInput.value
        };

        // Remove the password from the DOM as soon as it has been copied to
        // the request payload. It is never written to logs or sessionStorage.
        passwordInput.value = "";
        state.submitting = true;
        hideLoginMessage();
        setFormBusy(loginForm, true);
        setButtonContents(loginButton, "loading", "Signing in...");
        if (loginButton) {
            loginButton.classList.add("loading");
            loginButton.setAttribute("aria-busy", "true");
        }
        if (resetButton) {
            resetButton.disabled = true;
        }

        try {
            const response = await apiFetch("auth/login.php", {
                skipCsrf: true,
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(credentials)
            });
            const data = await readApiResponse(response);

            if (!response.ok || !data || data.success !== true) {
                if (response.status === 401) {
                    clearClientAuthState();
                }
                const apiError = new Error(responseMessage(response, data));
                apiError.isApiError = true;
                throw apiError;
            }

            storeSessionData(data.data);
            sessionStorage.setItem("isLoggedIn", "true");
            state.redirecting = true;
            setLoginMessage("Sign-in successful. Redirecting to your workspace...", "success");
            notify("Sign-in successful. Redirecting to your workspace...", "success");

            window.setTimeout(() => {
                window.location.href = viewPath("dashboard.php");
            }, 1500);
        } catch (error) {
            const message = error && error.isApiError
                ? error.message
                : "Unable to reach the sign-in service. Please try again.";
            setLoginMessage(message);
            notify(message);
        } finally {
            if (!state.redirecting) {
                restoreIdleState(loginForm, loginButton, resetButton);
            }
        }
    }

    document.addEventListener("DOMContentLoaded", function () {
        const loginForm = getElement("loginForm");
        const emailInput = getElement("emailInput");
        const passwordInput = getElement("passwordInput");
        const togglePasswordButton = getElement("togglePassword");
        const forgotPasswordLink = getElement("forgotPasswordLink");

        if (!loginForm || !emailInput || !passwordInput) {
            return;
        }

        emailInput.addEventListener("blur", () => validateInput(emailInput));
        passwordInput.addEventListener("blur", () => validateInput(passwordInput));
        emailInput.addEventListener("input", () => clearInputError(emailInput));
        passwordInput.addEventListener("input", () => clearInputError(passwordInput));

        togglePasswordButton?.addEventListener("click", () => {
            setPasswordVisibility(passwordInput, togglePasswordButton, passwordInput.type === "password");
            passwordInput.focus();
        });

        loginForm.addEventListener("submit", handleSubmit);
        loginForm.addEventListener("reset", () => {
            window.setTimeout(() => resetFormState(loginForm, emailInput, passwordInput, togglePasswordButton), 0);
        });

        forgotPasswordLink?.addEventListener("click", (event) => {
            event.preventDefault();
            openForgotPasswordModal();
        });

        const forgotPasswordModal = getElement("forgotPasswordModal");
        forgotPasswordModal?.addEventListener("keydown", (event) => {
            if (event.key === "Escape" && forgotPasswordModal.classList.contains("show")
                && typeof bootstrap !== "undefined" && bootstrap.Modal) {
                requestCloseForgotPasswordModal();
            }
        });

        document.addEventListener("keydown", (event) => {
            const modalElement = getElement("forgotPasswordModal");

            if (event.key === "Escape") {
                if (modalElement?.classList.contains("show")) {
                    requestCloseForgotPasswordModal();
                    return;
                }

                if (!state.submitting) {
                    loginForm.reset();
                }
            }

            if (event.key === "Enter" && event.target === emailInput) {
                event.preventDefault();
                passwordInput.focus();
            }
        });

        emailInput.focus();
    });
}());
