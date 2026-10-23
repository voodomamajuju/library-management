@extends('layouts.app')

@section('title', 'Borrowing')
@section('page', 'Borrowing')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Borrowing Management</h4>
        <button type="button" class="btn btn-primary" id="openBorrowModal">
            <i class="bi bi-plus-lg me-1"></i>Borrow Book
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div id="alertArea" class="alert d-none" role="alert"></div>
            <div id="emptyArea" class="alert alert-info d-none" role="alert">
                <i class="bi bi-info-circle me-1"></i>No borrowings found.
            </div>
            <div id="loadingArea" class="text-center py-4 d-none">
                <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
                <p class="text-muted mt-2 mb-0">Loading borrowings...</p>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Book</th>
                            <th>Member</th>
                            <th>Borrowed</th>
                            <th>Due</th>
                            <th>Returned</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody id="borrowingsTableBody"></tbody>
                </table>
            </div>

            <nav id="pagination" aria-label="Borrowing pages" class="pt-3"></nav>
        </div>
    </div>

    <div class="modal fade" id="borrowModal" tabindex="-1" aria-labelledby="borrowModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="borrowForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="borrowModalLabel">Borrow Book</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div id="borrowFormAlert" class="alert alert-danger d-none" role="alert"></div>
                        <div class="mb-3">
                            <label for="borrowBook" class="form-label">Book</label>
                            <select class="form-select" id="borrowBook" required>
                                <option value="">Select a book</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="borrowMember" class="form-label">Member</label>
                            <select class="form-select" id="borrowMember" required>
                                <option value="">Select a member</option>
                            </select>
                        </div>
                        <div class="mb-0">
                            <label for="borrowedAt" class="form-label">Borrowed date <span class="text-muted">(optional)</span></label>
                            <input type="date" class="form-control" id="borrowedAt">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBorrow">Borrow</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel">Return Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Return <strong id="returnBookTitle"></strong> for <strong id="returnMemberName"></strong>?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmReturn">Return</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
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

            const tbody = document.getElementById('borrowingsTableBody');
            const paginationEl = document.getElementById('pagination');
            const alertArea = document.getElementById('alertArea');
            const loadingArea = document.getElementById('loadingArea');
            const emptyArea = document.getElementById('emptyArea');
            const borrowModalEl = document.getElementById('borrowModal');
            const borrowModal = new bootstrap.Modal(borrowModalEl);
            const borrowForm = document.getElementById('borrowForm');
            const borrowFormAlert = document.getElementById('borrowFormAlert');
            const bookSelect = document.getElementById('borrowBook');
            const memberSelect = document.getElementById('borrowMember');
            const borrowedAtInput = document.getElementById('borrowedAt');
            const submitBorrowBtn = document.getElementById('submitBorrow');
            const returnModalEl = document.getElementById('returnModal');
            const returnModal = new bootstrap.Modal(returnModalEl);
            const confirmReturnBtn = document.getElementById('confirmReturn');
            let currentPage = 1;
            let pendingReturn = null;

            function showAlert(message, type) {
                alertArea.textContent = message;
                alertArea.classList.remove('d-none', 'alert-danger', 'alert-success');
                alertArea.classList.add(type === 'success' ? 'alert-success' : 'alert-danger');
            }

            function hideAlert() {
                alertArea.classList.add('d-none');
            }

            function showFormAlert(message) {
                borrowFormAlert.textContent = message;
                borrowFormAlert.classList.remove('d-none');
            }

            function hideFormAlert() {
                borrowFormAlert.classList.add('d-none');
                borrowFormAlert.textContent = '';
            }

            function redirectToLogin() {
                localStorage.removeItem(TOKEN_KEY);
                localStorage.removeItem(USER_KEY);
                window.location.href = '{{ route("login") }}';
            }

            async function api(url, options) {
                const response = await fetch(url, Object.assign({
                    headers: {
                        'Authorization': 'Bearer ' + token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                }, options || {}));

                if (response.status === 401) {
                    redirectToLogin();
                    return null;
                }

                return response;
            }

            function esc(value) {
                const div = document.createElement('div');
                div.textContent = (value === null || value === undefined) ? '' : String(value);
                return div.innerHTML;
            }

            function formatDate(value) {
                if (!value) {
                    return '—';
                }
                const date = new Date(value);
                if (Number.isNaN(date.getTime())) {
                    return esc(value);
                }
                return date.toLocaleDateString();
            }

            function errorMessage(data, fallback) {
                if (!data) {
                    return fallback;
                }
                if (data.errors) {
                    const messages = [];
                    Object.keys(data.errors).forEach(function (key) {
                        const value = data.errors[key];
                        if (Array.isArray(value)) {
                            messages.push(value.join(' '));
                        } else if (value) {
                            messages.push(String(value));
                        }
                    });
                    if (messages.length) {
                        return messages.join(' ');
                    }
                }
                return data.message || fallback;
            }

            function renderRows(borrowings) {
                tbody.innerHTML = borrowings.map(function (item) {
                    const returned = item.returned_at !== null && item.returned_at !== undefined && item.returned_at !== '';
                    const status = returned
                        ? '<span class="badge text-bg-secondary">Returned</span>'
                        : '<span class="badge text-bg-success">Borrowed</span>';
                    const action = returned
                        ? ''
                        : '<button type="button" class="btn btn-sm btn-outline-primary js-return" title="Return" data-id="' + esc(item.id) + '" data-book="' + esc(item.book ? item.book.title : 'this book') + '" data-member="' + esc(item.member ? item.member.full_name : 'this member') + '">Return</button>';

                    return '<tr>' +
                        '<td>' + esc(item.book ? item.book.title : 'Unavailable') + '</td>' +
                        '<td>' + esc(item.member ? item.member.full_name : 'Unavailable') + '</td>' +
                        '<td>' + formatDate(item.borrowed_at) + '</td>' +
                        '<td>' + formatDate(item.due_at) + '</td>' +
                        '<td>' + formatDate(item.returned_at) + '</td>' +
                        '<td>' + status + '</td>' +
                        '<td class="text-end">' + action + '</td>' +
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
                        loadBorrowings();
                    });
                });
            }

            async function loadBorrowings() {
                hideAlert();
                emptyArea.classList.add('d-none');
                loadingArea.classList.remove('d-none');
                tbody.innerHTML = '';

                try {
                    const response = await api('/api/borrowings?page=' + currentPage);
                    if (!response) {
                        return;
                    }
                    if (!response.ok) {
                        showAlert('Something went wrong while loading borrowings.');
                        return;
                    }

                    const payload = await response.json();
                    const borrowings = (payload && payload.data) || [];
                    const meta = (payload && payload.meta) || null;

                    if (borrowings.length === 0) {
                        emptyArea.classList.remove('d-none');
                        paginationEl.innerHTML = '';
                    } else {
                        renderRows(borrowings);
                        renderPagination(meta);
                    }
                } catch (err) {
                    showAlert('Unable to load borrowings. Please try again.');
                } finally {
                    loadingArea.classList.add('d-none');
                }
            }

            async function loadAllPages(url) {
                const items = [];
                let page = 1;
                let lastPage = 1;

                do {
                    const response = await api(url + (url.indexOf('?') === -1 ? '?' : '&') + 'page=' + page);
                    if (!response) {
                        return null;
                    }
                    if (!response.ok) {
                        throw new Error('load failed');
                    }
                    const payload = await response.json();
                    items.push.apply(items, (payload && payload.data) || []);
                    lastPage = (payload && payload.meta && payload.meta.last_page) || 1;
                    page += 1;
                } while (page <= lastPage);

                return items;
            }

            function fillSelect(select, placeholder, items) {
                select.innerHTML = '<option value="">' + placeholder + '</option>' + items.map(function (item) {
                    return '<option value="' + esc(item.id) + '">' + esc(item.label) + '</option>';
                }).join('');
            }

            async function loadFormOptions() {
                hideFormAlert();
                bookSelect.disabled = true;
                memberSelect.disabled = true;
                fillSelect(bookSelect, 'Loading books...', []);
                fillSelect(memberSelect, 'Loading members...', []);

                try {
                    const results = await Promise.all([
                        loadAllPages('/api/books'),
                        loadAllPages('/api/members')
                    ]);
                    if (!results[0] || !results[1]) {
                        return;
                    }

                    const books = results[0]
                        .filter(function (book) { return Number(book.available_copies) > 0; })
                        .map(function (book) {
                            return { id: book.id, label: book.title + ' (' + book.available_copies + ' available)' };
                        });
                    const members = results[1]
                        .filter(function (member) { return member.status === 'Active'; })
                        .map(function (member) {
                            return { id: member.id, label: member.full_name };
                        });

                    fillSelect(bookSelect, books.length ? 'Select a book' : 'No books with available copies', books);
                    fillSelect(memberSelect, members.length ? 'Select a member' : 'No active members', members);
                } catch (err) {
                    fillSelect(bookSelect, 'Unable to load books', []);
                    fillSelect(memberSelect, 'Unable to load members', []);
                    showFormAlert('Unable to load books or members. Please try again.');
                } finally {
                    bookSelect.disabled = false;
                    memberSelect.disabled = false;
                }
            }

            document.getElementById('openBorrowModal').addEventListener('click', function () {
                borrowForm.reset();
                hideFormAlert();
                submitBorrowBtn.disabled = false;
                borrowModal.show();
                loadFormOptions();
            });

            borrowForm.addEventListener('submit', async function (event) {
                event.preventDefault();
                hideFormAlert();

                if (!bookSelect.value || !memberSelect.value) {
                    showFormAlert('Please select a book and a member.');
                    return;
                }

                const payload = {
                    book_id: Number(bookSelect.value),
                    member_id: Number(memberSelect.value)
                };
                if (borrowedAtInput.value) {
                    payload.borrowed_at = borrowedAtInput.value;
                }

                submitBorrowBtn.disabled = true;
                try {
                    const response = await api('/api/borrowings', {
                        method: 'POST',
                        body: JSON.stringify(payload)
                    });
                    if (!response) {
                        return;
                    }

                    const data = await response.json().catch(function () { return {}; });
                    if (response.status === 422 || !response.ok) {
                        showFormAlert(errorMessage(data, 'The book could not be borrowed. Please try again.'));
                        return;
                    }

                    borrowModal.hide();
                    borrowForm.reset();
                    showAlert(data.message || 'Book borrowed successfully.', 'success');
                    currentPage = 1;
                    loadBorrowings();
                } catch (err) {
                    showFormAlert('The book could not be borrowed. Please try again.');
                } finally {
                    submitBorrowBtn.disabled = false;
                }
            });

            tbody.addEventListener('click', function (event) {
                const button = event.target.closest('.js-return');
                if (!button) {
                    return;
                }
                pendingReturn = {
                    id: button.getAttribute('data-id'),
                    book: button.getAttribute('data-book') || 'this book',
                    member: button.getAttribute('data-member') || 'this member'
                };
                document.getElementById('returnBookTitle').textContent = pendingReturn.book;
                document.getElementById('returnMemberName').textContent = pendingReturn.member;
                confirmReturnBtn.disabled = false;
                returnModal.show();
            });

            returnModalEl.addEventListener('hidden.bs.modal', function () {
                if (!confirmReturnBtn.disabled) {
                    pendingReturn = null;
                }
            });

            confirmReturnBtn.addEventListener('click', async function () {
                if (!pendingReturn) {
                    return;
                }

                const borrowing = pendingReturn;
                confirmReturnBtn.disabled = true;

                try {
                    const response = await api('/api/borrowings/' + encodeURIComponent(borrowing.id) + '/return', {
                        method: 'POST'
                    });
                    if (!response) {
                        return;
                    }

                    const data = await response.json().catch(function () { return {}; });
                    if (response.status === 422) {
                        showAlert(data.message || 'This borrowing cannot be returned.');
                        return;
                    }
                    if (!response.ok) {
                        showAlert(data.message || 'The book could not be returned. Please try again.');
                        return;
                    }

                    showAlert(data.message || 'Book returned successfully.', 'success');
                    loadBorrowings();
                } catch (err) {
                    showAlert('The book could not be returned. Please try again.');
                } finally {
                    confirmReturnBtn.disabled = false;
                    pendingReturn = null;
                    returnModal.hide();
                }
            });

            loadBorrowings();
        })();
    </script>
@endsection