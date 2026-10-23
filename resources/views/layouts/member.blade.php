<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Member Portal | Library Management System')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body {
            background-color: #f4f6f9;
        }

        /* ---------- Member Sidebar ---------- */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #212529;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 1030;
            overflow-y: auto;
        }
        .sidebar .nav-link {
            color: #cfd4da;
            border-radius: .375rem;
            padding: .6rem .9rem;
            font-size: .95rem;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, .08);
        }
        .sidebar .nav-link.active {
            color: #fff;
            background: #0d6efd;
        }
        .sidebar-brand {
            color: #fff;
            text-decoration: none;
        }
        .sidebar-brand:hover {
            color: #fff;
        }

        /* ---------- Main content ---------- */
        #page-content-wrapper {
            margin-left: 260px;
        }

        /* ---------- Stat cards ---------- */
        .stat-card {
            border: none;
            border-radius: .75rem;
        }
        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: .75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ---------- Offcanvas (mobile) sidebar ---------- */
        .offcanvas-sidebar {
            background: #212529;
            color: #fff;
        }
        .offcanvas-sidebar .nav-link {
            color: #cfd4da;
            border-radius: .375rem;
        }
        .offcanvas-sidebar .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, .08);
        }
        .offcanvas-sidebar .nav-link.active {
            color: #fff;
            background: #0d6efd;
        }

        /* ---------- Responsive ---------- */
        @media (max-width: 991.98px) {
            .sidebar {
                display: none;
            }

            #page-content-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <div class="d-flex">

        {{-- ===================== Desktop Sidebar ===================== --}}
        <aside class="sidebar d-none d-lg-flex flex-column p-3">
            <a href="{{ route('member.dashboard') }}" class="sidebar-brand d-flex align-items-center mb-3">
                <i class="bi bi-person-circle me-2 fs-3"></i>
                <span class="fs-5 fw-semibold">Member Portal</span>
            </a>
            <hr class="text-secondary">

            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="{{ route('member.dashboard') }}#summary" class="nav-link">
                        <i class="bi bi-speedometer2 me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('member.dashboard') }}#currentBorrowings" class="nav-link">
                        <i class="bi bi-journal-bookmark me-2"></i>My Borrowings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('member.dashboard') }}#history" class="nav-link">
                        <i class="bi bi-clock-history me-2"></i>History
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('member.dashboard') }}#profile" class="nav-link">
                        <i class="bi bi-person-lines-fill me-2"></i>My Profile
                    </a>
                </li>
            </ul>

            <hr class="text-secondary">
            <div class="text-secondary small mb-2">
                <i class="bi bi-person me-1"></i><span class="js-member-name">Member</span>
            </div>
            <a href="#" class="nav-link text-danger js-member-logout" title="Logout">
                <i class="bi bi-box-arrow-right me-2"></i>Logout
            </a>
        </aside>

        {{-- ============ Mobile Offcanvas Sidebar ============ --}}
        <div class="offcanvas offcanvas-start offcanvas-sidebar" tabindex="-1" id="memberSidebarOffcanvas">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title">
                    <i class="bi bi-person-circle me-2"></i>Member Portal
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column">
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('member.dashboard') }}#summary" class="nav-link">
                            <i class="bi bi-speedometer2 me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('member.dashboard') }}#currentBorrowings" class="nav-link">
                            <i class="bi bi-journal-bookmark me-2"></i>My Borrowings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('member.dashboard') }}#history" class="nav-link">
                            <i class="bi bi-clock-history me-2"></i>History
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('member.dashboard') }}#profile" class="nav-link">
                            <i class="bi bi-person-lines-fill me-2"></i>My Profile
                        </a>
                    </li>
                </ul>
                <a href="#" class="nav-link text-danger js-member-logout" title="Logout">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </a>
            </div>
        </div>

        {{-- ===================== Page Content ===================== --}}
        <div id="page-content-wrapper" class="flex-grow-1">

            {{-- Top Navbar --}}
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3 py-2 sticky-top">
                <button class="btn btn-outline-secondary d-lg-none me-2" data-bs-toggle="offcanvas" data-bs-target="#memberSidebarOffcanvas" aria-label="Toggle sidebar">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <span class="navbar-brand mb-0 h5">@yield('page', 'Member Dashboard')</span>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <span class="badge text-bg-primary"><i class="bi bi-person-circle me-1"></i>Member</span>
                </div>
            </nav>

            {{-- Main Content --}}
            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function () {
            'use strict';

            const TOKEN_KEY = 'library_member_token';
            const USER_KEY = 'library_member_user';
            let loggingOut = false;

            // Show the stored member name in the sidebar (member keys only).
            try {
                const stored = JSON.parse(localStorage.getItem(USER_KEY) || 'null');
                if (stored && stored.full_name) {
                    document.querySelectorAll('.js-member-name').forEach(function (el) {
                        el.textContent = stored.full_name;
                    });
                }
            } catch (err) {
                // Ignore malformed stored data.
            }

            function clearMemberAuth() {
                localStorage.removeItem(TOKEN_KEY);
                localStorage.removeItem(USER_KEY);
            }

            document.querySelectorAll('.js-member-logout').forEach(function (link) {
                link.addEventListener('click', async function (event) {
                    event.preventDefault();
                    if (loggingOut) {
                        return;
                    }

                    const token = localStorage.getItem(TOKEN_KEY);
                    if (!token) {
                        clearMemberAuth();
                        window.location.href = '{{ route("member.login") }}';
                        return;
                    }

                    loggingOut = true;
                    document.querySelectorAll('.js-member-logout').forEach(function (item) {
                        item.setAttribute('aria-disabled', 'true');
                        item.classList.add('disabled');
                    });

                    try {
                        const response = await fetch('/api/member/logout', {
                            method: 'POST',
                            headers: {
                                'Authorization': 'Bearer ' + token,
                                'Accept': 'application/json'
                            }
                        });

                        if (response.ok || response.status === 401 || response.status === 403) {
                            clearMemberAuth();
                            window.location.href = '{{ route("member.login") }}';
                            return;
                        }
                    } catch (err) {
                        // Keep the session if logout cannot reach the server.
                    }

                    loggingOut = false;
                    document.querySelectorAll('.js-member-logout').forEach(function (item) {
                        item.removeAttribute('aria-disabled');
                        item.classList.remove('disabled');
                    });
                });
            });
        })();
    </script>
    @yield('scripts')
</body>
</html>
