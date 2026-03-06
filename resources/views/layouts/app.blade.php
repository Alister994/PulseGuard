<!DOCTYPE html>
<<<<<<< HEAD
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('pulseguard.name', 'PulseGuard') }} – Uptime & SSL Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {} },
            darkMode: 'class'
        }
    </script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <style>
        body { font-family: 'DM Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-900 antialiased min-h-screen">
    <nav class="bg-slate-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-xl font-bold tracking-tight">
                        {{ config('pulseguard.name', 'PulseGuard') }}
                    </a>
                    <span class="ml-3 text-slate-400 text-sm">Uptime & SSL Monitor</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('message'))
            <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3">
                {{ session('message') }}
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="max-w-7xl mx-auto px-4 py-4 text-slate-500 text-sm border-t border-slate-200 mt-8">
        PulseGuard – Laravel Uptime & SSL Monitor
    </footer>
=======
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#206bc4">
    <title>@yield('title', 'Dashboard') – {{ config('app.name') }}</title>
    @php $av = '?v=' . config('app.asset_version'); @endphp
    <link id="favicon-ref" rel="icon" type="image/png" href="{{ asset('images/logo.png') }}{{ $av }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo.png') }}{{ $av }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <style>
        .navbar-brand .d-flex { overflow: visible; min-width: 0; align-items: center; gap: 0.5rem; }
        .brand-logo-icon { height: 32px; width: 32px; object-fit: cover; object-position: top center; flex-shrink: 0; border-radius: 6px; }
        .brand-name { font-weight: 600; font-size: 1.05rem; letter-spacing: -0.02em; white-space: nowrap; }
        [data-bs-theme="dark"] .brand-name { color: rgba(255,255,255,0.95) !important; }
        .navbar-light .brand-name { color: #1e293b !important; }
        .sidebar-brand-wrap { padding: 0.25rem 0; }
        .sidebar-brand-wrap .brand-logo-icon { box-shadow: 0 1px 2px rgba(0,0,0,0.08); }
        [data-bs-theme="dark"] .sidebar-brand-wrap .brand-logo-icon { box-shadow: 0 1px 3px rgba(0,0,0,0.3); }
    </style>
</head>
<body class="layout-fluid antialiased">
    <div class="page">
        <!-- Sidebar -->
        <aside class="navbar navbar-vertical navbar-expand-lg navbar-dark" data-bs-theme="dark">
            <div class="container-fluid">
                <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="navbar-collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3 sidebar-brand-wrap">
                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
                        <img src="{{ asset('images/logo.png') }}{{ $av }}" alt="" class="brand-logo-icon" width="32" height="32">
                        <span class="brand-name">{{ config('app.name') }}</span>
                    </a>
                </h1>
                <div class="navbar-nav flex-row d-lg-none">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">{{ auth()->user()->name ?? auth()->user()->username }}</a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item">Log out</button></form>
                        </div>
                    </div>
                </div>
                <div class="navbar-collapse collapse" id="sidebarMenu">
                    <ul class="navbar-nav pt-lg-3">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <span class="nav-link-icon"><i class="ti ti-smart-home"></i></span>
                                <span class="nav-link-title">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                                <span class="nav-link-icon"><i class="ti ti-bell"></i></span>
                                <span class="nav-link-title">Notifications</span>
                                @php $unreadCount = auth()->user()->unreadNotifications()->count(); @endphp
                                @if($unreadCount > 0)<span class="badge badge-pill bg-red text-white ms-auto">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>@endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}" href="{{ route('employees.index') }}">
                                <span class="nav-link-icon"><i class="ti ti-users"></i></span>
                                <span class="nav-link-title">Employees</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('payroll.*') ? 'active' : '' }}" href="{{ route('payroll.index') }}">
                                <span class="nav-link-icon"><i class="ti ti-currency-rupee"></i></span>
                                <span class="nav-link-title">Payroll</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                <span class="nav-link-icon"><i class="ti ti-report"></i></span>
                                <span class="nav-link-title">Reports</span>
                            </a>
                        </li>
                        @if(auth()->user()->isSuperAdmin() && \Illuminate\Support\Facades\Route::has('attendance-logs.index'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('attendance-logs.*') ? 'active' : '' }}" href="{{ route('attendance-logs.index') }}">
                                <span class="nav-link-icon"><i class="ti ti-list"></i></span>
                                <span class="nav-link-title">Attendance logs</span>
                            </a>
                        </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#navbar-setup" data-bs-toggle="dropdown">
                                <span class="nav-link-icon"><i class="ti ti-settings"></i></span>
                                <span class="nav-link-title">Setup</span>
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item {{ request()->routeIs('locations.*') ? 'active' : '' }}" href="{{ route('locations.index') }}">Locations</a>
                                <a class="dropdown-item {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">Departments</a>
                                <a class="dropdown-item {{ request()->routeIs('shifts.*') ? 'active' : '' }}" href="{{ route('shifts.index') }}">Shifts</a>
                                <a class="dropdown-item {{ request()->routeIs('devices.*') ? 'active' : '' }}" href="{{ route('devices.index') }}">Devices</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>
        <!-- Top navbar -->
        <header class="navbar navbar-expand-md navbar-light d-none d-lg-flex d-print-none">
            <div class="container-fluid">
                <div class="navbar-nav flex-row flex-grow-1">
                    <nav aria-label="Breadcrumb" class="pt-2">
                        <ol class="breadcrumb breadcrumb-arrows mb-0" aria-label="breadcrumbs">
                            @hasSection('breadcrumb')
                                @yield('breadcrumb')
                            @else
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            @endif
                        </ol>
                    </nav>
                </div>
                <div class="navbar-nav flex-row">
                    <a href="#" class="nav-link px-2 theme-toggle" id="theme-toggle-desktop" aria-label="Toggle theme" title="Toggle light/dark">
                        <i class="ti ti-bulb ti-lg"></i>
                    </a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm bg-primary-lt text-primary">{{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username ?? 'A', 0, 1)) }}</span>
                            <span class="d-none d-xl-block ps-2">{{ auth()->user()->name ?? auth()->user()->username }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item">Log out</button></form>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <header class="navbar navbar-expand-md navbar-light d-lg-none d-print-none">
            <div class="container-fluid">
                <ul class="navbar-nav flex-row">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="navbar-brand navbar-brand-autodark d-flex align-items-center text-decoration-none">
                            <img src="{{ asset('images/logo.png') }}{{ $av }}" alt="" class="brand-logo-icon" width="32" height="32">
                            <span class="brand-name">{{ config('app.name') }}</span>
                        </a>
                    </li>
                </ul>
                <div class="navbar-nav flex-row ms-auto">
                    <a href="#" class="nav-link px-3 theme-toggle" id="theme-toggle-mobile" aria-label="Toggle theme">
                        <i class="ti ti-bulb ti-lg"></i>
                    </a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm bg-primary-lt text-primary">{{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username ?? 'A', 0, 1)) }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item">Log out</button></form>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="page-wrapper">
            <div class="page-body">
                <div class="container-fluid">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible" role="alert"><div class="d-flex"><div>{{ session('success') }}</div></div><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible" role="alert"><div class="d-flex"><div>{{ session('error') }}</div></div><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
    <script>
        (function() {
            var av = '{{ $av }}';
            var faviconUrl = '{{ asset('images/logo.png') }}' + av;
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
            function setFavicon(isDark) {
                var ref = document.getElementById('favicon-ref');
                if (ref) ref.href = faviconUrl;
            }
            function updateIcons() {
                var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
                document.querySelectorAll('.theme-toggle i').forEach(function(icon) {
                    icon.className = isDark ? 'ti ti-moon ti-lg' : 'ti ti-bulb ti-lg';
                });
                setFavicon(isDark);
            }
            document.querySelectorAll('.theme-toggle').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var current = document.documentElement.getAttribute('data-bs-theme');
                    var next = current === 'dark' ? 'light' : 'dark';
                    document.documentElement.setAttribute('data-bs-theme', next);
                    localStorage.setItem('theme', next);
                    updateIcons();
                });
            });
            updateIcons();
        })();
    </script>
>>>>>>> 8f657c0a93cd52da770ffd6b01d7ceee028dcaf8
</body>
</html>
