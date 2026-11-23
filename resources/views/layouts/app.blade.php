@php
    $navItems = [
        ['route' => 'home', 'label' => 'Beranda', 'index' => '01'],
        ['route' => 'about', 'label' => 'Tentang', 'index' => '02'],
        ['route' => 'faq', 'label' => 'FAQ', 'index' => '03'],
        ['route' => 'contact', 'label' => 'Kontak', 'index' => '04'],
    ];
    $isAdminArea = request()->is('admin/*') || request()->routeIs('admin.*');
    $currentUser = $isAdminArea ? auth('admin')->user() : auth('web')->user();
    $logoutRoute = $isAdminArea ? 'admin.logout' : 'logout';
    $roleLabel = null;
    $dashboardRoute = null;
    if ($currentUser) {
        $roleLabel = $currentUser->hasRole('admin') ? 'Administrator' : ($currentUser->hasRole('officer') ? 'Petugas' : 'Pelapor');
        $dashboardRoute = $currentUser->hasRole('admin') ? 'admin.dashboard' : ($currentUser->hasRole('officer') ? 'officer.dashboard' : 'reporter.dashboard');
    }
    $initial = $currentUser ? \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($currentUser->name, 0, 1)) : '';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#070c0b">
    <meta name="description" content="MANUSACARE — sistem pelaporan dan pemeliharaan fasilitas sekolah untuk lingkungan belajar yang lebih aman dan nyaman.">
    <title>@yield('title', 'Beranda') · MANUSACARE</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}">
    <link rel="stylesheet" href="{{ asset('css/ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/page-transitions.css') }}">
    <script defer src="{{ asset('js/app.js') }}"></script>
    <script type="module" src="{{ asset('js/animation-engine.js') }}"></script>
</head>
<body>
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

    <div class="nav-sentinel" aria-hidden="true"></div>

    <header class="site-nav" data-site-nav>
        <a class="brand" href="{{ route('home') }}" aria-label="MANUSACARE, beranda">
            <span class="brand-mark" aria-hidden="true">C</span>
            <span class="brand-copy">
                <span class="brand-name">MANUSA<span class="brand-accent">CARE</span></span>
                <small>BUILDING A BETTER SCHOOL TOGETHER</small>
            </span>
        </a>

        <button class="mobile-menu-toggle" type="button" data-menu-toggle aria-expanded="false" aria-controls="primary-navigation" aria-label="Buka navigasi">
            <x-icon name="menu" class="icon-menu" />
            <x-icon name="close" class="icon-close" />
        </button>

        <div class="nav-panel" id="primary-navigation" data-nav-panel>
            <nav aria-label="Navigasi utama">
                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}" @if(request()->routeIs($item['route'])) aria-current="page" @endif>
                        <span class="nav-index" aria-hidden="true">{{ $item['index'] }}</span>{{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="nav-actions">
                <div class="sound-control" data-sound-control>
                    <button class="sound-toggle" type="button" data-sound-toggle aria-pressed="false" aria-label="Aktifkan efek suara" title="Efek suara nonaktif">
                        <x-icon name="volume" class="icon-volume-on" />
                        <x-icon name="volume-off" class="icon-volume-off" />
                    </button>
                    <label class="sound-volume" data-sound-volume-wrap>
                        <span class="sr-only">Volume efek suara</span>
                        <input type="range" min="0" max="100" value="35" step="1" data-sound-volume aria-label="Volume efek suara">
                    </label>
                </div>

                @if($currentUser)
                    <div class="nav-account" title="{{ $currentUser->name }}">
                        <span class="avatar" aria-hidden="true">{{ $initial }}</span>
                        <span class="nav-account-copy">
                            <small>{{ $roleLabel }}</small>
                            <b>{{ $currentUser->name }}</b>
                        </span>
                    </div>
                    <form method="POST" action="{{ route($logoutRoute) }}">
                        @csrf
                        <button class="button button-primary" type="submit">Keluar <x-icon name="logout" /></button>
                    </form>
                @else
                    <a class="button button-ghost" href="{{ route('login') }}">Masuk</a>
                    <a class="button button-primary" href="{{ route('register') }}">Daftar</a>
                @endif
            </div>
        </div>
    </header>

    <div class="nav-scrim" data-nav-scrim aria-hidden="true"></div>

    <main class="page-shell" id="main-content" tabindex="-1">
        @if(session('success') || session('warning') || $errors->any())
            <div class="notice-stack">
                @if(session('success'))
                    <div class="notice" role="status" data-notice data-flash-type="success" data-autodismiss="7000">
                        <x-icon name="check-circle" />
                        <div class="notice-copy">{{ session('success') }}</div>
                        <button class="notice-close" type="button" data-notice-close aria-label="Tutup pemberitahuan"><x-icon name="close" /></button>
                    </div>
                @endif
                @if(session('warning'))
                    <div class="notice notice-warning" role="status" data-notice data-flash-type="warning" data-autodismiss="9000">
                        <x-icon name="alert" />
                        <div class="notice-copy">{{ session('warning') }}</div>
                        <button class="notice-close" type="button" data-notice-close aria-label="Tutup pemberitahuan"><x-icon name="close" /></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="notice notice-error" role="alert" data-notice data-flash-type="error">
                        <x-icon name="alert" />
                        <div class="notice-copy">
                            <strong>Periksa kembali data berikut:</strong>
                            <ul class="notice-list">
                                @foreach($errors->unique() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button class="notice-close" type="button" data-notice-close aria-label="Tutup pemberitahuan"><x-icon name="close" /></button>
                    </div>
                @endif
            </div>
        @endif

        @yield('content')
    </main>

    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <a class="brand" href="{{ route('home') }}" aria-label="MANUSACARE, beranda">
                    <span class="brand-mark" aria-hidden="true">C</span>
                    <span class="brand-copy">
                        <span class="brand-name">MANUSA<span class="brand-accent">CARE</span></span>
                        <small>BUILDING A BETTER SCHOOL TOGETHER</small>
                    </span>
                </a>
                <p>Sistem pelaporan dan pemeliharaan fasilitas sekolah. Satu laporan yang jelas membantu sekolah menentukan prioritas perbaikan.</p>
            </div>
            <div class="footer-col">
                <h3>Navigasi</h3>
                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}">{{ $item['label'] }}</a>
                @endforeach
            </div>
            <div class="footer-col">
                <h3>Akses</h3>
                @if($currentUser)
                    <a href="{{ route($dashboardRoute) }}">Dashboard</a>
                    <form method="POST" action="{{ route($logoutRoute) }}">
                        @csrf
                        <button class="footer-link" type="submit">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Masuk</a>
                    <a href="{{ route('register') }}">Daftar akun</a>
                    <a href="{{ route('admin.login') }}">Login admin</a>
                @endif
                <a href="{{ route('faq') }}">Bantuan</a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© {{ date('Y') }} MANUSACARE</span>
            <span>Building a Better School Together.</span>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
