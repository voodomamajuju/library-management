@extends('layouts.app')

@section('title', 'Books')
@section('page', 'Books')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Books</h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                <i class="bi bi-upload me-1"></i>Bulk Upload
            </button>
            <a href="{{ route('books.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Book
            </a>
        </div>
    </div>

    {{-- TODO: Wire this form to the books API (filters/search) when consuming the API. --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form id="bookFilters" class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                        <input type="search" class="form-control" id="searchTitle" placeholder="Search by title or author...">
                    </div>
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="filterCategory">
                        <option value="">All Categories</option>
                        <option value="Fiction">Fiction</option>
                        <option value="Non-Fiction">Non-Fiction</option>
                        <option value="Self Help">Self Help</option>
                        <option value="Science">Science</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="number" class="form-control" id="filterYear" placeholder="Year">
                </div>
                <div class="col-md-2">
                    <select class="form-select" id="sortBy">
                        <option value="">Sort By</option>
                        <option value="title">Title</option>
                        <option value="published_year">Published Year</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <select class="form-select" id="sortOrder">
                        <option value="asc">Asc</option>
                        <option value="desc">Desc</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" type="submit">Search</button>
                    <button type="button" class="btn btn-outline-secondary" id="resetFilters">Reset</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div id="alertArea" class="alert d-none" role="alert"></div>
            <div id="emptyArea" class="alert alert-info d-none" role="alert">
                <i class="bi bi-info-circle me-1"></i>No books found.
            </div>
            <div id="loadingArea" class="text-center py-4 d-none">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                <p class="text-muted mt-2 mb-0">Loading books...</p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Publisher</th>
                            <th>Year</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Available</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="booksTableBody"></tbody>
                </table>
            </div>

            <nav id="pagination" aria-label="Book pages" class="pt-3"></nav>
        </div>
    </div>

    <div class="modal fade" id="deleteBookModal" tabindex="-1" aria-labelledby="deleteBookModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBookModalLabel">Delete Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete <strong id="deleteBookTitle"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBook">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Upload Modal -->
    <div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkUploadModalLabel">
                        <i class="bi bi-upload me-1"></i>Bulk Upload Books
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        Import multiple books at once by uploading a CSV file.
                        Every row is validated first &mdash; if any row is invalid, no books are imported.
                    </p>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="downloadCsvTemplate">
                            <i class="bi bi-download me-1"></i>Download CSV Template
                        </button>
                    </div>

                    <div class="mb-3">
                        <label for="bulkCsvFile" class="form-label">CSV file</label>
                        <div class="input-group">
                            <input class="form-control" type="file" id="bulkCsvFile" accept=".csv" aria-describedby="bulkCsvFileName">
                            <span class="input-group-text" id="bulkCsvFileName">No file chosen</span>
                        </div>
                        <div class="form-text">Only .csv files are accepted.</div>
                    </div>

                    <div class="mb-2">
                        <span class="d-block fw-semibold small mb-1">Expected columns:</span>
                        <code class="d-block small">title, author, isbn, category, publisher, published_year, total_copies, available_copies</code>
                    </div>

                    <div id="bulkErrors" class="alert alert-danger d-none mt-3 mb-0" style="max-height: 220px; overflow-y: auto;" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="bulkImportBtn">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="bulkImportSpinner" role="status"></span>
                        <span id="bulkImportLabel">Upload &amp; Import</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

<script>
        (function () {
            'use strict';
            const TOKEN_KEY = 'library_auth_token';
            const USER_KEY = 'library_auth_user';
            const token = localStorage.getItem(TOKEN_KEY);

            if (!token) {
                window.location.href = '{{ route("login") }}';
                return;
            }

            const tbody = document.getElementById('booksTableBody');
            const paginationEl = document.getElementById('pagination');
            const alertArea = document.getElementById('alertArea');
            const loadingArea = document.getElementById('loadingArea');
            const emptyArea = document.getElementById('emptyArea');

            const fields = {
                title: document.getElementById('searchTitle'),
                category: document.getElementById('filterCategory'),
                year: document.getElementById('filterYear'),
                sortBy: document.getElementById('sortBy'),
                sortOrder: document.getElementById('sortOrder'),
            };

            let currentPage = 1;

            function showAlert(message, type) {
                alertArea.textContent = message;
                alertArea.classList.remove('d-none', 'alert-danger', 'alert-success');
                alertArea.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
            }
            function hideAlert() {
                alertArea.classList.add('d-none');
            }

            function buildQuery(page) {
                const params = new URLSearchParams();
                if (page) params.set('page', page);
                if (fields.title.value.trim()) params.set('title', fields.title.value.trim());
                if (fields.category.value) params.set('category', fields.category.value);
                if (fields.year.value.trim()) params.set('published_year', fields.year.value.trim());
                if (fields.sortBy.value) {
                    params.set('sort_by', fields.sortBy.value);
                    params.set('sort_order', fields.sortOrder.value || 'asc');
                }
                return params.toString();
            }

            function esc(value) {
                const div = document.createElement('div');
                div.textContent = (value === null || value === undefined) ? '' : String(value);
                return div.innerHTML;
            }

            function renderRows(books) {
                tbody.innerHTML = books.map(function (book) {
                    const avail = Number(book.available_copies) || 0;
                    const availBadge = avail > 0
                        ? '<span class="badge text-bg-success">' + avail + '</span>'
                        : '<span class="badge text-bg-warning">0</span>';
                    return '<tr>' +
                        '<td>' + esc(book.title) + '</td>' +
                        '<td>' + esc(book.author) + '</td>' +
                        '<td>' + esc(book.isbn) + '</td>' +
                        '<td><span class="badge text-bg-secondary">' + esc(book.category) + '</span></td>' +
                        '<td>' + esc(book.publisher) + '</td>' +
                        '<td>' + esc(book.published_year) + '</td>' +
                        '<td class="text-center">' + (Number(book.total_copies) || 0) + '</td>' +
                        '<td class="text-center">' + availBadge + '</td>' +
                        '<td class="text-end">' +
                            '<a href="/books/' + book.id + '/edit" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a> ' +
                            '<button type="button" class="btn btn-sm btn-outline-danger js-delete-book" title="Delete" data-id="' + esc(book.id) + '" data-title="' + esc(book.title) + '"><i class="bi bi-trash"></i></button>' +
                        '</td>' +
                    '</tr>';
                }).join('');
            }

            function renderPagination(meta) {
                if (!meta || !meta.last_page || meta.last_page <= 1) {
                    paginationEl.innerHTML = '';
                    return;
                }
                let html = '<ul class="pagination pagination-sm justify-content-center mb-0">';
                for (let i = 1; i <= meta.last_page; i++) {
                    const active = (i === meta.current_page) ? ' active' : '';
                    html += '<li class="page-item' + active + '">' +
                        '<button type="button" class="page-link" data-page="' + i + '">' + i + '</button>' +
                        '</li>';
                }
                html += '</ul>';
                paginationEl.innerHTML = html;

                paginationEl.querySelectorAll('.page-link').forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        currentPage = parseInt(btn.getAttribute('data-page'), 10);
                        loadBooks();
                    });
                });
            }
async function loadBooks() {
                hideAlert();
                emptyArea.classList.add('d-none');
                loadingArea.classList.remove('d-none');
                tbody.innerHTML = '';

                const qs = buildQuery(currentPage);
                const url = '/api/books' + (qs ? '?' + qs : '');

                try {
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.status === 401) {
                        localStorage.removeItem(TOKEN_KEY);
                        localStorage.removeItem(USER_KEY);
                        window.location.href = '{{ route("login") }}';
                        return;
                    }
                    if (response.status === 404) {
                        showAlert('Books resource not found (404).');
                        return;
                    }
                    if (!response.ok) {
                        showAlert('Something went wrong while loading books.');
                        return;
                    }

                    const data = await response.json();
                    const books = (data && data.data) || [];
                    const meta = (data && data.meta) || null;

                    if (books.length === 0) {
                        emptyArea.classList.remove('d-none');
                        paginationEl.innerHTML = '';
                    } else {
                        renderRows(books);
                        renderPagination(meta);
                    }
                } catch (err) {
                    showAlert('Unable to load books. Please try again.');
                } finally {
                    loadingArea.classList.add('d-none');
                }
            }

            document.getElementById('bookFilters').addEventListener('submit', function (event) {
                event.preventDefault();
                currentPage = 1;
                loadBooks();
            });

            document.getElementById('resetFilters').addEventListener('click', function () {
                fields.title.value = '';
                fields.category.value = '';
                fields.year.value = '';
                fields.sortBy.value = '';
                fields.sortOrder.value = 'asc';
                currentPage = 1;
                loadBooks();
            });

            loadBooks();

            const deleteModalEl = document.getElementById('deleteBookModal');
            const deleteTitleEl = document.getElementById('deleteBookTitle');
            const confirmDeleteBtn = document.getElementById('confirmDeleteBook');
            let pendingDelete = null;
            let deleteBookModal = null;

            function getDeleteBookModal() {
                if (deleteBookModal) {
                    return deleteBookModal;
                }
                try {
                    if (window.bootstrap && window.bootstrap.Modal) {
                        deleteBookModal = new bootstrap.Modal(deleteModalEl);
                    }
                } catch (e) {
                    console.error('deleteBookModal init failed:', e);
                }
                return deleteBookModal;
            }

            tbody.addEventListener('click', function (event) {
                const button = event.target.closest('.js-delete-book');
                if (!button) {
                    return;
                }
                pendingDelete = {
                    id: button.getAttribute('data-id'),
                    title: button.getAttribute('data-title') || 'this book'
                };
                deleteTitleEl.textContent = pendingDelete.title;
                confirmDeleteBtn.disabled = false;
                const modal = getDeleteBookModal();
                if (modal) {
                    modal.show();
                }
            });

            deleteModalEl.addEventListener('hidden.bs.modal', function () {
                if (!confirmDeleteBtn.disabled) {
                    pendingDelete = null;
                }
            });

            confirmDeleteBtn.addEventListener('click', async function () {
                if (!pendingDelete) {
                    return;
                }

                const book = pendingDelete;
                confirmDeleteBtn.disabled = true;

                try {
                    const response = await fetch('/api/books/' + encodeURIComponent(book.id), {
                        method: 'DELETE',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.status === 401) {
                        localStorage.removeItem(TOKEN_KEY);
                        localStorage.removeItem(USER_KEY);
                        window.location.href = '{{ route("login") }}';
                        return;
                    }

                    const data = await response.json().catch(function () { return {}; });

                    if (response.status === 422) {
                        showAlert(data.message || 'This book cannot be deleted because it has borrowing history.');
                        return;
                    }

                    if (!response.ok) {
                        showAlert(data.message || 'The book could not be deleted. Please try again.');
                        return;
                    }

                    showAlert(data.message || 'Book deleted successfully.', 'success');
                    loadBooks();
                } catch (err) {
                    showAlert('The book could not be deleted. Please try again.');
                } finally {
                    confirmDeleteBtn.disabled = false;
                    pendingDelete = null;
                    const modal = getDeleteBookModal();
                    if (modal) {
                        modal.hide();
                    }
                }
            });

            // ---------------- Bulk Upload (CSV import) ----------------
            const bulkModalEl = document.getElementById('bulkUploadModal');
            const bulkImportBtn = document.getElementById('bulkImportBtn');
            const bulkImportLabel = document.getElementById('bulkImportLabel');
            const bulkImportSpinner = document.getElementById('bulkImportSpinner');
            const bulkCsvFile = document.getElementById('bulkCsvFile');
            const bulkCsvFileName = document.getElementById('bulkCsvFileName');
            const bulkErrors = document.getElementById('bulkErrors');
            let bulkUploading = false;

            function updateBulkFileLabel() {
                const selectedFile = bulkCsvFile.files && bulkCsvFile.files[0];
                bulkCsvFileName.textContent = selectedFile ? selectedFile.name : 'No file chosen';
            }

            function setBulkUploading(isUploading) {
                bulkUploading = isUploading;
                bulkImportBtn.disabled = isUploading;
                bulkImportSpinner.classList.toggle('d-none', !isUploading);
                bulkImportLabel.textContent = isUploading ? 'Importing...' : 'Upload & Import';
            }

            function resetBulkForm() {
                bulkCsvFile.value = '';
                bulkCsvFileName.textContent = 'No file chosen';
                bulkErrors.classList.add('d-none');
                bulkErrors.innerHTML = '';
                setBulkUploading(false);
            }

            function showBulkError(html) {
                bulkErrors.innerHTML = html;
                bulkErrors.classList.remove('d-none');
            }

            function escapeBulkError(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            bulkCsvFile.addEventListener('change', updateBulkFileLabel);

            document.getElementById('downloadCsvTemplate').addEventListener('click', function () {
                const header = 'title,author,isbn,category,publisher,published_year,total_copies,available_copies';
                const blob = new Blob([header + '\r\n'], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.href = url;
                link.download = 'books-template.csv';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            });

            bulkModalEl.addEventListener('hidden.bs.modal', function () {
                if (!bulkUploading) {
                    resetBulkForm();
                }
            });

            bulkImportBtn.addEventListener('click', async function () {
                if (bulkUploading) {
                    return;
                }

                bulkErrors.classList.add('d-none');
                bulkErrors.innerHTML = '';

                const file = bulkCsvFile.files && bulkCsvFile.files[0];
                if (!file) {
                    showBulkError('Please select a CSV file to upload.');
                    return;
                }

                if (!file.name.toLowerCase().endsWith('.csv')) {
                    showBulkError('Only .csv files are allowed.');
                    return;
                }

                const formData = new FormData();
                formData.append('file', file);

                setBulkUploading(true);

                try {
                    const response = await fetch('/api/books/import', {
                        method: 'POST',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    if (response.status === 401) {
                        localStorage.removeItem(TOKEN_KEY);
                        localStorage.removeItem(USER_KEY);
                        window.location.href = '{{ route("login") }}';
                        return;
                    }

                    const data = await response.json().catch(function () { return {}; });

                    if (response.status === 201) {
                        const importedRows = data.imported_rows || 0;
                        const totalRows = data.total_rows || importedRows;
                        try {
                            if (window.bootstrap && window.bootstrap.Modal) {
                                bootstrap.Modal.getOrCreateInstance(bulkModalEl).hide();
                            }
                        } catch (uiErr) {
                            console.error('bulk modal hide failed:', uiErr);
                        }
                        resetBulkForm();
                        showAlert(
                            (data.message || 'Books imported successfully.') +
                            ' Imported ' + importedRows + ' of ' + totalRows + ' rows.',
                            'success'
                        );
                        loadBooks();
                        return;
                    }

                    if (response.status === 422) {
                        let html = '<strong>' + escapeBulkError(data.message || 'The import failed. Please fix the errors and try again.') + '</strong>';
                        if (Array.isArray(data.errors) && data.errors.length > 0) {
                            html += '<ul class="mb-0 mt-2 ps-3">';
                            data.errors.forEach(function (error) {
                                const rowLabel = (error.row === null || error.row === undefined) ? 'File' : 'Row ' + error.row;
                                const fieldLabel = error.field ? error.field : 'general CSV/file error';
                                html += '<li>' + escapeBulkError(rowLabel + ' \u2014 ' + fieldLabel + ' \u2014 ' + (error.message || '')) + '</li>';
                            });
                            html += '</ul>';
                        }
                        showBulkError(html);
                        return;
                    }

                    showBulkError(escapeBulkError(data.message || 'The import could not be completed. Please try again.'));
                } catch (err) {
                    showBulkError('The import could not be completed. Please check your connection and try again.');
                } finally {
                    setBulkUploading(false);
                }
            });
        })();
    </script>
@endsection