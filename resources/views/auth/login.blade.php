<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login | Library Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: .75rem;
        }
        .login-header {
            background: #212529;
            border-radius: .75rem .75rem 0 0;
            color: #fff;
        }
        .login-header i {
            font-size: 2rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card shadow">
                    <div class="card-body login-header text-center p-4">
                        <i class="bi bi-book-half mb-2"></i>
                        <h4 class="fw-semibold mb-0">Library Management System</h4>
                    </div>

                    <div class="card-body p-4">
                        {{-- Alert areas --}}
                        <div id="alertSuccess" class="alert alert-success d-none" role="alert">
                            <i class="bi bi-check-circle me-1"></i> Login successful. Redirecting...
                        </div>
                        <div id="alertDanger" class="alert alert-danger d-none" role="alert"></div>
                        <div id="alertGeneric" class="alert alert-warning d-none" role="alert">
                            <i class="bi bi-exclamation-triangle me-1"></i> Something went wrong. Please try again.
                        </div>

                        <form id="loginForm" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email"
                                           placeholder="you@example.com" required autofocus autocomplete="email">
                                </div>
                                <div class="invalid-feedback" id="emailError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Enter your password" required autocomplete="current-password">
                                </div>
                                <div class="invalid-feedback" id="passwordError"></div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100" id="loginBtn">
                                <span class="spinner-border spinner-border-sm d-none me-1" id="loginSpinner"></span>
                                <i class="bi bi-box-arrow-in-right me-1"></i>Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script>
        (function () {
            'use strict';

            const TOKEN_KEY = 'library_auth_token';
            const USER_KEY = 'library_auth_user';

            // If a token already exists, assume the user is logged in.
            // The backend remains the source of truth for validity.
            if (localStorage.getItem(TOKEN_KEY)) {
                window.location.href = '/';
                return;
            }

            const form = document.getElementById('loginForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const emailError = document.getElementById('emailError');
            const passwordError = document.getElementById('passwordError');
            const alertSuccess = document.getElementById('alertSuccess');
            const alertDanger = document.getElementById('alertDanger');
            const alertGeneric = document.getElementById('alertGeneric');
            const loginBtn = document.getElementById('loginBtn');
            const loginSpinner = document.getElementById('loginSpinner');

            function hideAlerts() {
                alertSuccess.classList.add('d-none');
                alertDanger.classList.add('d-none');
                alertGeneric.classList.add('d-none');
            }

            function showAlert(el, message) {
                el.textContent = message || '';
                el.classList.remove('d-none');
            }

            function resetErrors() {
                emailInput.classList.remove('is-invalid');
                passwordInput.classList.remove('is-invalid');
                emailError.textContent = '';
                passwordError.textContent = '';
            }

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (form.checkValidity() === false) {
                    form.classList.add('was-validated');
                    return;
                }

                hideAlerts();
                resetErrors();

                // Disable button + show spinner
                loginBtn.disabled = true;
                loginSpinner.classList.remove('d-none');

                const payload = {
                    email: emailInput.value.trim(),
                    password: passwordInput.value
                };

                try {
                    const response = await fetch('/api/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.status === 200) {
                        if (!data.token) {
                            throw new Error('No token returned');
                        }
                        localStorage.setItem(TOKEN_KEY, data.token);
                        if (data.user) {
                            localStorage.setItem(USER_KEY, JSON.stringify(data.user));
                        }
                        showAlert(alertSuccess, 'Login successful!');
                        setTimeout(function () {
                            window.location.href = '/';
                        }, 400);
                        return;
                    }

                    if (response.status === 401) {
                        showAlert(alertDanger, 'Invalid email or password.');
                        return;
                    }

                    if (response.status === 422) {
                        const errors = data.errors || {};
                        if (errors.email) {
                            emailError.textContent = errors.email[0];
                            emailInput.classList.add('is-invalid');
                        }
                        if (errors.password) {
                            passwordError.textContent = errors.password[0];
                            passwordInput.classList.add('is-invalid');
                        }
                        if (Object.keys(errors).length === 0) {
                            showAlert(alertDanger, 'Please check your details and try again.');
                        }
                        return;
                    }

                    showAlert(alertGeneric, 'Unexpected error. Please try again.');
                } catch (err) {
                    showAlert(alertGeneric, 'Unable to reach the server. Please try again.');
                } finally {
                    loginBtn.disabled = false;
                    loginSpinner.classList.add('d-none');
                }
            });
        })();
    </script>
</body>
</html>