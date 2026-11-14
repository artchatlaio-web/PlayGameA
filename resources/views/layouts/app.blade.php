<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'PlayGame Platform' }}</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="{{ route('download.index') }}" class="logo">
                <span>PlayGame</span>
                <span class="logo-badge">HUB</span>
            </a>

            <ul class="nav-links">
                <li><a href="{{ route('download.index') }}" class="nav-item {{ request()->routeIs('download.*') ? 'active' : '' }}">{{ __('Downloads') }}</a></li>
                <li><a href="{{ route('games.index') }}" class="nav-item {{ request()->routeIs('games.*') ? 'active' : '' }}">{{ __('Play Online') }}</a></li>
                @auth
                    @if(auth()->user()->role === 'admin')
                        <li><a href="{{ route('admin.games') }}" class="nav-item {{ request()->routeIs('admin.*') ? 'active' : '' }}">{{ __('Admin') }}</a></li>
                    @endif
                @endauth
            </ul>

            <div class="nav-right">
                <div class="lang-selector">
                    <a href="{{ route('lang.switch', 'ru') }}" class="lang-btn {{ app()->getLocale() == 'ru' ? 'active' : '' }}">RU</a>
                    <a href="{{ route('lang.switch', 'en') }}" class="lang-btn {{ app()->getLocale() == 'en' ? 'active' : '' }}">EN</a>
                    <a href="{{ route('lang.switch', 'es') }}" class="lang-btn {{ app()->getLocale() == 'es' ? 'active' : '' }}">ES</a>
                </div>

                @auth
                    <span style="font-size:0.85rem; color:#94a3b8; font-weight:600;">{{ auth()->user()->name }}</span>
                    <form action="{{ route('auth', ['action' => 'logout']) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">{{ __('Logout') }}</button>
                    </form>
                @else
                    <a href="{{ route('auth', ['action' => 'login']) }}" class="btn btn-outline btn-sm">{{ __('Login') }}</a>
                    <a href="{{ route('auth', ['action' => 'register']) }}" class="btn btn-primary btn-sm">{{ __('Register') }}</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('warning'))
            <div class="alert alert-warning" style="background:rgba(245,158,11,0.15);border-color:rgba(245,158,11,0.3);color:#fcd34d;">{{ session('warning') }}</div>
        @endif
        @if(session('info'))
            <div class="alert" style="background:rgba(59,130,246,0.15);border-color:rgba(59,130,246,0.3);color:#93c5fd;">{{ session('info') }}</div>
        @endif
        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $err)
                    <div>{{ $err }}</div>
                @endforeach
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="footer-line">Created by: Author</div>
        <div class="footer-line footer-protected">Website Protected</div>
    </footer>

    <script src="/js/app.js"></script>
    <script src="/js/games.js"></script>
    @yield('scripts')
</body>
</html>
