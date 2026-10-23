@extends('layouts.app')

@section('title', 'Dashboard')
@section('page', 'Dashboard')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Dashboard Overview</h4>
    </div>

    <div id="dashboardAlert" class="alert alert-danger d-none" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i>
        Dashboard data could not be loaded. Please try again.
    </div>

    <div id="dashboardLoading" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2 mb-0">Loading dashboard...</p>
    </div>

    <div id="dashboardContent" class="d-none">
        {{-- ===== Statistic Cards ===== --}}
        <div class="row g-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-primary bg-opacity-10 me-3">
                        <i class="bi bi-journal-bookmark fs-4 text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Total Books</div>
                        <div class="fs-3 fw-bold" id="statTotalBooks">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 me-3">
                        <i class="bi bi-people fs-4 text-success"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Total Members</div>
                        <div class="fs-3 fw-bold" id="statTotalMembers">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 me-3">
                        <i class="bi bi-check2-circle fs-4 text-info"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Available Books</div>
                        <div class="fs-3 fw-bold" id="statAvailableBooks">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 me-3">
                        <i class="bi bi-arrow-left-right fs-4 text-warning"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Currently Borrowed</div>
                        <div class="fs-3 fw-bold" id="statCurrentlyBorrowed">0</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 me-3">
                        <i class="bi bi-exclamation-circle fs-4 text-danger"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase">Overdue Books</div>
                        <div class="fs-3 fw-bold" id="statOverdueBooks">0</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <span><i class="bi bi-trophy me-2 text-warning"></i>Top Borrowed Books</span>
                    </div>
                    <div class="card-body p-0">
                        <div id="topBooksEmpty" class="text-center text-muted py-4 d-none">
                            No borrowing history yet.
                        </div>
                        <div class="table-responsive" id="topBooksTableWrap">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Author</th>
                                        <th class="text-end">Borrow Count</th>
                                    </tr>
                                </thead>
                                <tbody id="topBooksBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            const TOKEN_KEY = 'library_auth_token';
            const USER_KEY = 'library_auth_user';
            const token = localStorage.getItem(TOKEN_KEY);

            if (!token) {
                window.location.href = '{{ route('login') }}';
                return;
            }

            const loadingEl = document.getElementById('dashboardLoading');
            const contentEl = document.getElementById('dashboardContent');
            const alertEl = document.getElementById('dashboardAlert');

            function clearAuthAndRedirect() {
                localStorage.removeItem(TOKEN_KEY);
                localStorage.removeItem(USER_KEY);
                window.location.href = '{{ route('login') }}';
            }

            function showError() {
                alertEl.classList.remove('d-none');
                contentEl.classList.add('d-none');
            }

            function escapeHtml(value) {
                return String(value)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            async function loadDashboard() {
                try {
                    const response = await fetch('/api/dashboard', {
                        method: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.status === 401) {
                        clearAuthAndRedirect();
                        return;
                    }

                    if (!response.ok) {
                        showError();
                        return;
                    }

                    const payload = await response.json();
                    const data = (payload && payload.data) || {};

                    document.getElementById('statTotalBooks').textContent = data.total_books ?? 0;
                    document.getElementById('statTotalMembers').textContent = data.total_members ?? 0;
                    document.getElementById('statCurrentlyBorrowed').textContent = data.books_currently_borrowed ?? 0;
                    document.getElementById('statAvailableBooks').textContent = data.available_books ?? 0;
                    document.getElementById('statOverdueBooks').textContent = data.overdue_books ?? 0;

                    const books = Array.isArray(data.top_borrowed_books) ? data.top_borrowed_books : [];
                    const body = document.getElementById('topBooksBody');
                    const emptyEl = document.getElementById('topBooksEmpty');
                    const tableWrap = document.getElementById('topBooksTableWrap');

                    if (books.length === 0) {
                        emptyEl.classList.remove('d-none');
                        tableWrap.classList.add('d-none');
                        body.innerHTML = '';
                    } else {
                        emptyEl.classList.add('d-none');
                        tableWrap.classList.remove('d-none');
                        body.innerHTML = books.map(function (book) {
                            const title = book.title == null ? '' : String(book.title);
                            const author = book.author == null ? '' : String(book.author);
                            const count = book.borrow_count == null ? 0 : book.borrow_count;
                            return '<tr>' +
                                '<td>' + escapeHtml(title) + '</td>' +
                                '<td>' + escapeHtml(author) + '</td>' +
                                '<td class="text-end">' + escapeHtml(count) + '</td>' +
                                '</tr>';
                        }).join('');
                    }

                    contentEl.classList.remove('d-none');
                } catch (err) {
                    showError();
                } finally {
                    loadingEl.classList.add('d-none');
                }
            }

            loadDashboard();
        })();
    </script>
@endsection