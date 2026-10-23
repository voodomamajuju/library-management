@extends('layouts.member')

@section('title', 'My Dashboard | Member Portal')
@section('page', 'My Dashboard')

@section('content')
    {{-- Loading state --}}
    <div id="loadingArea" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2 mb-0">Loading your dashboard...</p>
    </div>

    {{-- Error state --}}
    <div id="errorArea" class="alert alert-danger d-none" role="alert">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span id="errorMessage">
                <i class="bi bi-exclamation-triangle me-1"></i>Unable to load your dashboard.
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger" id="retryBtn">
                <i class="bi bi-arrow-clockwise me-1"></i>Retry
            </button>
        </div>
    </div>

    {{-- Dashboard --}}
    <div id="dashboardArea" class="d-none">

        <div class="mb-4">
            <h4 class="mb-0">Welcome, <span id="welcomeName">Member</span></h4>
            <p class="text-muted mb-0 small">Here is an overview of your library activity.</p>
        </div>

        {{-- ===================== Summary cards ===================== --}}
        <div class="row g-3 mb-4" id="summary">
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-primary-subtle text-primary me-3">
                            <i class="bi bi-journal-bookmark fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Currently Borrowed</p>
                            <h3 class="mb-0" id="statCurrent">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-danger-subtle text-danger me-3">
                            <i class="bi bi-exclamation-triangle fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Overdue</p>
                            <h3 class="mb-0" id="statOverdue">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <div class="stat-icon bg-success-subtle text-success me-3">
                            <i class="bi bi-check2-circle fs-3"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-1">Books Returned</p>
                            <h3 class="mb-0" id="statReturned">0</h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stat-icon bg-info-subtle text-info me-3">
                                <i class="bi bi-speedometer2 fs-3"></i>
                            </div>
                            <div>
                                <p class="text-muted small mb-1">Borrowing Capacity</p>
                                <h3 class="mb-0" id="statCapacity">0 / 3</h3>
                            </div>
                        </div>
                        <div class="progress" style="height: 6px;" role="progressbar" aria-label="Borrowing capacity">
                            <div class="progress-bar" id="capacityBar" style="width: 0%;"></div>
                        </div>
                        <p class="text-muted small mb-0 mt-2" id="statCapacityNote"></p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===================== Currently borrowed ===================== --}}
        <div class="card shadow-sm mb-4" id="currentBorrowings">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-journal-bookmark me-1"></i>Currently Borrowed Books</span>
                <span class="badge text-bg-primary" id="currentCountBadge">0</span>
            </div>
            <div class="card-body">
                <div id="currentEmpty" class="text-center text-muted py-4 d-none">
                    <i class="bi bi-emoji-smile fs-3 d-block mb-2"></i>
                    You have no books borrowed right now.
                </div>
                <div class="table-responsive d-none" id="currentTableWrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Borrowed Date</th>
                                <th>Due Date</th>
                                <th class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody id="currentBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================== Borrowing history ===================== --}}
        <div class="card shadow-sm mb-4" id="history">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-clock-history me-1"></i>Borrowing History</span>
                <span class="badge text-bg-secondary" id="historyCountBadge">0</span>
            </div>
            <div class="card-body">
                <div id="historyEmpty" class="text-center text-muted py-4 d-none">
                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                    No borrowing history yet.
                </div>
                <div class="table-responsive d-none" id="historyTableWrap">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Book Title</th>
                                <th>Author</th>
                                <th>Borrowed Date</th>
                                <th>Due Date</th>
                                <th>Returned Date</th>
                                <th class="text-end">Status</th>
                            </tr>
                        </thead>
                        <tbody id="historyBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- ===================== Profile ===================== --}}
        <div class="card shadow-sm mb-4" id="profile">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-lines-fill me-1"></i>My Profile
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Full Name</p>
                        <p class="fw-semibold mb-0" id="profileName">&mdash;</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Email</p>
                        <p class="fw-semibold mb-0" id="profileEmail">&mdash;</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Phone</p>
                        <p class="fw-semibold mb-0" id="profilePhone">&mdash;</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Membership Date</p>
                        <p class="fw-semibold mb-0" id="profileMembershipDate">&mdash;</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Membership Status</p>
                        <p class="mb-0" id="profileStatus">&mdash;</p>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted small mb-1">Address</p>
                        <p class="fw-semibold mb-0" id="profileAddress">&mdash;</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        (function () {
            'use strict';

            const TOKEN_KEY = 'library_member_token';
            const USER_KEY = 'library_member_user';
            const token = localStorage.getItem(TOKEN_KEY);

            const loadingArea = document.getElementById('loadingArea');
            const errorArea = document.getElementById('errorArea');
            const errorMessage = document.getElementById('errorMessage');
            const dashboardArea = document.getElementById('dashboardArea');

            function clearMemberAuth() {
                localStorage.removeItem(TOKEN_KEY);
                localStorage.removeItem(USER_KEY);
            }

            function redirectToLogin() {
                clearMemberAuth();
                window.location.href = '{{ route("member.login") }}';
            }

            function esc(value) {
                const div = document.createElement('div');
                div.textContent = (value === null || value === undefined) ? '' : String(value);
                return div.innerHTML;
            }

            function fmtDate(value) {
                if (!value) {
                    return '\u2014';
                }
                const date = new Date(value);
                if (isNaN(date.getTime())) {
                    return '\u2014';
                }
                return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
            }

            function bookTitle(item) {
                return (item.book && item.book.title) ? item.book.title : 'Book no longer available';
            }

            function bookAuthor(item) {
                return (item.book && item.book.author) ? item.book.author : '\u2014';
            }

            function statusBadge(item) {
                if (item.returned_at) {
                    return '<span class="badge text-bg-secondary">Returned</span>';
                }
                return item.is_overdue
                    ? '<span class="badge text-bg-danger">Overdue</span>'
                    : '<span class="badge text-bg-success">Active</span>';
            }

            function renderSummary(summary) {
                const used = summary.currently_borrowed || 0;
                const limit = summary.borrowing_limit || 0;

                document.getElementById('statCurrent').textContent = used;
                document.getElementById('statOverdue').textContent = summary.overdue || 0;
                document.getElementById('statReturned').textContent = summary.returned || 0;
                document.getElementById('statCapacity').textContent = used + ' / ' + limit;

                const bar = document.getElementById('capacityBar');
                const pct = limit > 0 ? Math.min(100, Math.round((used / limit) * 100)) : 0;
                bar.style.width = pct + '%';
                bar.className = 'progress-bar' + (used >= limit ? ' bg-danger' : '');

                document.getElementById('statCapacityNote').textContent = used >= limit
                    ? 'You have reached the maximum of ' + limit + ' active borrowings.'
                    : (limit - used) + ' more book(s) can be borrowed.';
            }

            function renderCurrent(items) {
                const wrap = document.getElementById('currentTableWrap');
                const empty = document.getElementById('currentEmpty');
                document.getElementById('currentCountBadge').textContent = items.length;

                if (!items.length) {
                    wrap.classList.add('d-none');
                    empty.classList.remove('d-none');
                    return;
                }

                empty.classList.add('d-none');
                wrap.classList.remove('d-none');
                document.getElementById('currentBody').innerHTML = items.map(function (item) {
                    return '<tr>' +
                        '<td class="fw-semibold">' + esc(bookTitle(item)) + '</td>' +
                        '<td>' + esc(bookAuthor(item)) + '</td>' +
                        '<td>' + esc(fmtDate(item.borrowed_at)) + '</td>' +
                        '<td>' + esc(fmtDate(item.due_at)) + '</td>' +
                        '<td class="text-end">' + statusBadge(item) + '</td>' +
                        '</tr>';
                }).join('');
            }

            function renderHistory(items) {
                const wrap = document.getElementById('historyTableWrap');
                const empty = document.getElementById('historyEmpty');
                document.getElementById('historyCountBadge').textContent = items.length;

                if (!items.length) {
                    wrap.classList.add('d-none');
                    empty.classList.remove('d-none');
                    return;
                }

                empty.classList.add('d-none');
                wrap.classList.remove('d-none');
                document.getElementById('historyBody').innerHTML = items.map(function (item) {
                    return '<tr>' +
                        '<td class="fw-semibold">' + esc(bookTitle(item)) + '</td>' +
                        '<td>' + esc(bookAuthor(item)) + '</td>' +
                        '<td>' + esc(fmtDate(item.borrowed_at)) + '</td>' +
                        '<td>' + esc(fmtDate(item.due_at)) + '</td>' +
                        '<td>' + esc(fmtDate(item.returned_at)) + '</td>' +
                        '<td class="text-end">' + statusBadge(item) + '</td>' +
                        '</tr>';
                }).join('');
            }

            function renderProfile(member) {
                document.getElementById('welcomeName').textContent = member.full_name || 'Member';
                document.getElementById('profileName').textContent = member.full_name || '\u2014';
                document.getElementById('profileEmail').textContent = member.email || '\u2014';
                document.getElementById('profilePhone').textContent = member.phone || '\u2014';
                document.getElementById('profileMembershipDate').textContent = fmtDate(member.membership_date);
                document.getElementById('profileAddress').textContent = member.address || '\u2014';

                const badgeClass = member.status === 'Active' ? 'text-bg-success' : 'text-bg-secondary';
                document.getElementById('profileStatus').innerHTML =
                    '<span class="badge ' + badgeClass + '">' + esc(member.status || '\u2014') + '</span>';
            }

            function showLoading() {
                loadingArea.classList.remove('d-none');
                errorArea.classList.add('d-none');
                dashboardArea.classList.add('d-none');
            }

            function showError(message) {
                loadingArea.classList.add('d-none');
                dashboardArea.classList.add('d-none');
                errorMessage.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>' + esc(message);
                errorArea.classList.remove('d-none');
            }

            async function loadDashboard() {
                if (!token) {
                    redirectToLogin();
                    return;
                }

                showLoading();

                try {
                    const response = await fetch('/api/member/dashboard', {
                        method: 'GET',
                        headers: {
                            'Authorization': 'Bearer ' + token,
                            'Accept': 'application/json'
                        }
                    });

                    if (response.status === 401 || response.status === 403) {
                        redirectToLogin();
                        return;
                    }

                    if (!response.ok) {
                        showError('Unable to load your dashboard. Please try again.');
                        return;
                    }

                    const payload = await response.json().catch(function () { return {}; });
                    const data = payload.data;

                    if (!data || !data.member || !data.summary) {
                        showError('Unable to load your dashboard. Please try again.');
                        return;
                    }

                    renderSummary(data.summary);
                    renderCurrent(data.current_borrowings || []);
                    renderHistory(data.history || []);
                    renderProfile(data.member);

                    loadingArea.classList.add('d-none');
                    errorArea.classList.add('d-none');
                    dashboardArea.classList.remove('d-none');
                } catch (err) {
                    showError('Unable to reach the server. Please check your connection and try again.');
                }
            }

            document.getElementById('retryBtn').addEventListener('click', loadDashboard);

            loadDashboard();
        })();
    </script>
@endsection
