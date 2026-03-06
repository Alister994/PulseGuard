<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login – {{ config('app.name') }}</title>
    @php $av = '?v=' . config('app.asset_version'); @endphp
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}{{ $av }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/logo.png') }}{{ $av }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
</head>
<body class="d-flex flex-column border-0">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="{{ url('/') }}" class="navbar-brand navbar-brand-autodark"><img src="{{ asset('images/logo.png') }}{{ $av }}" alt="BioAttent" height="40"></a>
            </div>
            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Admin login</h2>
                    <p class="text-secondary text-center mb-4">Sign in to manage attendance and payroll</p>
                    @if ($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Username or email</label>
                            <input type="text" name="login" value="{{ old('login') }}" class="form-control" required autofocus autocomplete="username" placeholder="Username or email">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required autocomplete="current-password" placeholder="Password">
                        </div>
                        <div class="mb-3">
                            <label class="form-check"><input type="checkbox" name="remember" class="form-check-input"><span class="form-check-label">Remember me</span></label>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100">Log in</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
</body>
</html>
