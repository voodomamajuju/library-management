<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Member Registration | Library Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: .75rem;
        }
        .register-header {
            background: #0d6efd;
            border-radius: .75rem .75rem 0 0;
            color: #fff;
        }
        .register-header i { font-size: 2rem; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card register-card shadow">
                    <div class="card-body register-header text-center p-4">
                        <i class="bi bi-person-plus mb-2"></i>
                        <h4 class="fw-semibold mb-0">Member Registration</h4>
                        <small class="opacity-75">Create your library member account</small>
                    </div>

                    <div class="card-body p-4">
                        <div id="alertSuccess" class="alert alert-success d-none" role="alert"></div>
                        <div id="alertDanger" class="alert alert-danger d-none" role="alert"></div>
                        <div id="alertGeneric" class="alert alert-warning d-none" role="alert"></div>

                        <form id="memberRegisterForm" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                                </div>
                                <div class="invalid-feedback" id="emailError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
                                </div>
                                <div class="invalid-feedback" id="passwordError"></div>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8" autocomplete="new-password">
                                </div>
                                <div class="invalid-feedback" id="passwordConfirmationError"></div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" id="registerBtn">
                                    <span class="spinner-border spinner-border-sm me-1 d-none" id="registerSpinner"></span>
                                    Create Account
                                </button>
                            </div>
                        </form>

                        <p class="text-center text-muted small mt-3 mb-0">
                            Already have an account? <a href="{{ route('member.login') }}">Log in</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            'use strict';

            const form = document.getElementById('memberRegisterForm');
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const confirmationInput = document.getElementById('password_confirmation');
            const registerBtn = document.getElementById('registerBtn');
            const registerSpinner = document.getElementById('registerSpinner');
            const alerts = [
                document.getElementById('alertSuccess'),
                document.getElementById('alertDanger'),
                document.getElementById('alertGeneric')
            ];

            function showAlert(element, message) {
                element.textContent = message;
                element.classList.remove('d-none');
            }

            function hideAlerts() {
                alerts.forEach(function (element) { element.classList.add('d-none'); });
            }

            function resetErrors() {
                [emailInput, passwordInput, confirmationInput].forEach(function (input) {
                    input.classList.remove('is-invalid');
                });
                ['emailError', 'passwordError', 'passwordConfirmationError'].forEach(function (id) {
                    document.getElementById(id).textContent = '';
                });
            }

            function showValidationErrors(errors) {
                const fields = {
                    email: [emailInput, document.getElementById('emailError')],
                    password: [passwordInput, document.getElementById('passwordError')],
                    password_confirmation: [confirmationInput, document.getElementById('passwordConfirmationError')]
                };
                Object.keys(fields).forEach(function (field) {
                    if (errors[field]) {
                        fields[field][0].classList.add('is-invalid');
                        fields[field][1].textContent = errors[field][0];
                    }
                });
            }

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                hideAlerts();
                resetErrors();
                registerBtn.disabled = true;
                registerSpinner.classList.remove('d-none');

                try {
                    const response = await fetch('/api/member/register', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                        body: JSON.stringify({
                            email: emailInput.value.trim(),
                            password: passwordInput.value,
                            password_confirmation: confirmationInput.value
                        })
                    });
                    const data = await response.json().catch(function () { return {}; });

                    if (response.status === 201 || response.status === 200) {
                        showAlert(document.getElementById('alertSuccess'), 'Account created successfully. Redirecting to login...');
                        setTimeout(function () { window.location.href = '{{ route('member.login') }}'; }, 700);
                        return;
                    }
                    if (response.status === 422) {
                        if (data.errors) { showValidationErrors(data.errors); }
                        showAlert(document.getElementById('alertDanger'), data.message || 'Unable to create a member account with these details.');
                        return;
                    }
                    if (response.status === 403 || response.status === 409) {
                        showAlert(document.getElementById('alertDanger'), data.message || 'Unable to create this member account.');
                        return;
                    }
                    showAlert(document.getElementById('alertGeneric'), 'Unexpected error. Please try again.');
                } catch (error) {
                    showAlert(document.getElementById('alertGeneric'), 'Unable to reach the server. Please try again.');
                } finally {
                    registerBtn.disabled = false;
                    registerSpinner.classList.add('d-none');
                }
            });
        })();
    </script>
</body>
</html>