@extends('layouts.app')

@section('title', 'Add Book')
@section('page', 'Books')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Add Book</h4>
        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Books
        </a>
    </div>

    <div id="alertArea" class="alert alert-danger d-none" role="alert"></div>
    <div id="alertSuccess" class="alert alert-success d-none" role="alert">
        <i class="bi bi-check-circle me-1"></i>Book created successfully! Redirecting...
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <i class="bi bi-journal-plus me-2 text-primary"></i>Book Information
        </div>
        <div class="card-body">
            <form id="createBookForm" novalidate>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" maxlength="255" required>
                        <div class="invalid-feedback" id="titleError"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="author" class="form-label">Author <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="author" name="author" maxlength="255" required>
                        <div class="invalid-feedback" id="authorError"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="isbn" class="form-label">ISBN <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="isbn" name="isbn" maxlength="255" required>
                        <div class="invalid-feedback" id="isbnError"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" id="category" name="category" required>
                            <option value="">Select a category...</option>
                            <option value="Fiction">Fiction</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Self Help">Self Help</option>
                            <option value="Science">Science</option>
                            <option value="History">History</option>
                            <option value="Biography">Biography</option>
                        </select>
                        <div class="invalid-feedback" id="categoryError"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="publisher" class="form-label">Publisher <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="publisher" name="publisher" maxlength="255" required>
                        <div class="invalid-feedback" id="publisherError"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="published_year" class="form-label">Published Year <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="published_year" name="published_year"
                               min="1" max="{{ date('Y') }}" required>
                        <div class="invalid-feedback" id="published_yearError"></div>
                    </div>

                    <div class="col-md-6">
                        <label for="total_copies" class="form-label">Total Copies <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="total_copies" name="total_copies"
                               min="1" step="1" required>
                        <div class="invalid-feedback" id="total_copiesError"></div>
                    </div>
                    <div class="col-md-6">
                        <label for="available_copies" class="form-label">Available Copies <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="available_copies" name="available_copies"
                               min="0" step="1" required>
                        <div class="invalid-feedback" id="available_copiesError"></div>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer bg-white d-flex justify-content-end gap-2">
            <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="button" class="btn btn-primary" id="submitBtn" form="createBookForm">
                <span class="spinner-border spinner-border-sm d-none me-1" id="spinner"></span>
                <i class="bi bi-plus-lg me-1"></i>Add Book
            </button>
        </div>
    </div>
<script>
        (function () {
            'use strict';
            const TOKEN_KEY = 'library_auth_token';
            const token = localStorage.getItem(TOKEN_KEY);

            if (!token) {
                window.location.href = '{{ route("login") }}';
                return;
            }

            const form = document.getElementById('createBookForm');
            const submitBtn = document.getElementById('submitBtn');
            const spinner = document.getElementById('spinner');
            const alertArea = document.getElementById('alertArea');
            const alertSuccess = document.getElementById('alertSuccess');

            const FIELD_IDS = ['title', 'author', 'isbn', 'category', 'publisher',
                'published_year', 'total_copies', 'available_copies'];

            function fieldInput(name) { return document.getElementById(name); }
            function fieldError(name) { return document.getElementById(name + 'Error'); }

            function showAlert(message) {
                alertArea.textContent = message;
                alertArea.classList.remove('d-none');
            }
            function hideAlert() {
                alertArea.classList.add('d-none');
                alertSuccess.classList.add('d-none');
            }

            function clearErrors() {
                FIELD_IDS.forEach(function (name) {
                    fieldInput(name).classList.remove('is-invalid');
                    fieldError(name).textContent = '';
                });
            }

            function applyErrors(errors) {
                FIELD_IDS.forEach(function (name) {
                    if (errors[name]) {
                        fieldInput(name).classList.add('is-invalid');
                        fieldError(name).textContent = errors[name][0];
                    }
                });
            }

            submitBtn.addEventListener('click', async function () {
                if (!form.checkValidity()) {
                    form.classList.add('was-validated');
                    return;
                }

                hideAlert();
                clearErrors();

                // Disable + spinner
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');

                const payload = {
                    title: fieldInput('title').value.trim(),
                    author: fieldInput('author').value.trim(),
                    isbn: fieldInput('isbn').value.trim(),
                    category: fieldInput('category').value,
                    publisher: fieldInput('publisher').value.trim(),
                    published_year: parseInt(fieldInput('published_year').value, 10),
                    total_copies: parseInt(fieldInput('total_copies').value, 10),
                    available_copies: parseInt(fieldInput('available_copies').value, 10)
                };

                try {
                    const response = await fetch('/api/books', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'Authorization': 'Bearer ' + token
                        },
                        body: JSON.stringify(payload)
                    });

                    const data = await response.json().catch(() => ({}));

                    if (response.status === 201) {
                        alertSuccess.classList.remove('d-none');
                        setTimeout(function () {
                            window.location.href = '{{ route("books.index") }}';
                        }, 700);
                        return;
                    }

                    if (response.status === 401) {
                        localStorage.removeItem(TOKEN_KEY);
                        localStorage.removeItem('library_auth_user');
                        window.location.href = '{{ route("login") }}';
                        return;
                    }

                    if (response.status === 422) {
                        applyErrors(data.errors || {});
                        return;
                    }

                    showAlert(data.message || 'Something went wrong. Please try again.');
                } catch (err) {
                    showAlert('Unable to reach the server. Please try again.');
                } finally {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                }
            });
        })();
    </script>
@endsection