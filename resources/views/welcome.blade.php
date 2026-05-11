@extends('layouts.welcome')

@section('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<style>
    :root {
        --welcome-bg-deep: #f8f1eb;
        --welcome-surface: rgba(255, 255, 255, 0.64);
        --welcome-surface-hover: rgba(255, 255, 255, 0.88);
        --welcome-border: rgba(139, 69, 19, 0.18);
        --welcome-text: #3e2412;
        --welcome-muted: rgba(92, 57, 31, 0.68);
        --welcome-accent: #8b4513;
        --welcome-accent-soft: rgba(139, 69, 19, 0.14);
        --welcome-in: #a0522d;
        --welcome-in-soft: rgba(160, 82, 45, 0.16);
        --welcome-radius: 20px;
        --welcome-shadow: 0 24px 80px rgba(78, 49, 26, 0.18);
        --welcome-font: "DM Sans", system-ui, -apple-system, sans-serif;
        --welcome-mono: "JetBrains Mono", ui-monospace, monospace;
        --gallery-height: clamp(250px, 34vh, 310px);
        --gallery-mobile-height: 520px;
    }

    html, body {
        height: 100%;
        overflow: hidden;
    }

    body {
        margin: 0;
        font-family: var(--welcome-font);
        color: var(--welcome-text);
        -webkit-font-smoothing: antialiased;

        /* Static gradient background — no carousel */
        background:
            radial-gradient(ellipse 120% 90% at 10% -10%, rgba(139, 69, 19, 0.14) 0%, transparent 54%),
            radial-gradient(ellipse 95% 72% at 92% 108%, rgba(111, 78, 55, 0.16) 0%, transparent 58%),
            linear-gradient(155deg, #fbf5ef 0%, #f4e7da 35%, #efe0d2 65%, #ead7c6 100%) !important;
    }

    .welcome-page {
        height: 100vh;
        height: 100dvh;
        display: flex;
        flex-direction: column;
        position: relative;
        width: 100%;
        max-width: none;
        margin: 0;
        box-sizing: border-box;
        padding: clamp(8px, 2vh, 18px) clamp(16px, 4vw, 48px) clamp(10px, 2vh, 18px);
    }

    /* ── Header (above main content so nav links stay clickable) ── */
    .welcome-header {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: 0;
        width: 100%;
        min-height: 0;
        position: relative;
        z-index: 200;
    }

    .welcome-nav {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        position: relative;
        z-index: 201;
    }

    .welcome-nav a {
        color: var(--welcome-text) !important;
        text-decoration: none;
        font-size: 0.875rem;
        font-weight: 600;
        padding: 10px 18px;
        border-radius: 999px;
        border: 1px solid transparent;
        background: transparent;
        transition: background 0.2s ease, border-color 0.2s ease, transform 0.2s ease;
        position: relative;
        z-index: 202;
        cursor: pointer;
        pointer-events: auto;
    }

    .welcome-nav a:hover {
        background: var(--welcome-surface);
        border-color: var(--welcome-border);
        transform: translateY(-1px);
    }

    .welcome-nav a.primary {
        background: var(--welcome-accent-soft);
        border-color: rgba(26, 77, 150, 0.45);
        color: #ddeaff !important;
    }

    .welcome-nav a.primary:hover {
        background: rgba(26, 77, 150, 0.38);
    }

    /* ── Main split layout ── */
    .welcome-main {
        flex: 1;
        display: flex;
        flex-direction: row;
        align-items: stretch;
        gap: clamp(32px, 5vw, 64px);
        width: 100%;
        max-width: 1280px;
        margin: 0 auto;
        min-height: 0;
        position: relative;
        z-index: 1;
    }

    /* ══════════════════════════════════════
       LEFT SIDE — Gallery + branding
    ══════════════════════════════════════ */
    .welcome-left {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: clamp(14px, 2.2vh, 22px);
        position: relative;
        padding: 0;
    }

    /* School branding above the gallery */
    .welcome-brand {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: clamp(8px, 1.4vh, 12px);
        text-decoration: none;
        color: inherit;
    }

    .welcome-brand img {
        width: clamp(68px, 8vh, 88px);
        height: clamp(68px, 8vh, 88px);
        border-radius: 20px;
        object-fit: cover;
        border: 2px solid rgba(139,69,19,0.16);
        box-shadow: 0 14px 34px rgba(78, 49, 26, 0.16);
    }

    .welcome-brand-text {
        text-align: center;
    }

    .welcome-brand-text .eyebrow {
        font-size: clamp(0.65rem, 1vw, 0.8rem);
        font-weight: 600;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(123, 90, 69, 0.9);
        margin: 0 0 6px;
    }

    .welcome-brand-text .name {
        font-size: clamp(1.25rem, 2.5vw, 1.9rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        margin: 0;
        line-height: 1.2;
        color: #4c2d17;
        text-shadow: none;
    }

    /* Collage-style gallery */
    .school-gallery {
        width: 100%;
        max-width: 700px;
        height: var(--gallery-height);
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        grid-template-rows: repeat(4, minmax(0, 1fr));
        grid-auto-rows: minmax(0, 1fr);
        gap: 6px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow:
            0 0 0 1px rgba(139,69,19,0.08),
            0 20px 56px rgba(78, 49, 26, 0.16),
            0 6px 20px rgba(139, 69, 19, 0.12);
        position: relative;
    }

    /* Subtle gradient sheen over entire grid */
    .school-gallery::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(145deg, rgba(139, 69, 19, 0.12) 0%, transparent 55%);
        pointer-events: none;
        z-index: 3;
        border-radius: 16px;
    }

    .gallery-cell {
        position: relative;
        overflow: hidden;
        background: rgba(95, 62, 38, 0.12);
    }

    .gallery-cell::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(76, 45, 23, 0.18) 0%, rgba(139, 69, 19, 0.08) 100%);
        z-index: 1;
    }

    .gallery-cell img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        position: relative;
        z-index: 2;
        transition: transform 0.55s ease, filter 0.55s ease;
        filter: brightness(0.92) saturate(0.95);
    }

    .gallery-cell:hover img {
        transform: scale(1.07);
        filter: brightness(1.02) saturate(1.1);
    }

    .gallery-cell:nth-child(1)  { grid-column: 1 / 3; grid-row: 1 / 3; }
    .gallery-cell:nth-child(2)  { grid-column: 3 / 5; grid-row: 1 / 2; }
    .gallery-cell:nth-child(3)  { grid-column: 5 / 7; grid-row: 1 / 3; }
    .gallery-cell:nth-child(4)  { grid-column: 3 / 4; grid-row: 2 / 3; }
    .gallery-cell:nth-child(5)  { grid-column: 4 / 5; grid-row: 2 / 3; }
    .gallery-cell:nth-child(6)  { grid-column: 1 / 2; grid-row: 3 / 4; }
    .gallery-cell:nth-child(7)  { grid-column: 2 / 4; grid-row: 3 / 5; }
    .gallery-cell:nth-child(8)  { grid-column: 4 / 6; grid-row: 3 / 4; }
    .gallery-cell:nth-child(9)  { grid-column: 6 / 7; grid-row: 3 / 4; }
    .gallery-cell:nth-child(10) { grid-column: 1 / 2; grid-row: 4 / 5; }
    .gallery-cell:nth-child(11) { grid-column: 4 / 5; grid-row: 4 / 5; }
    .gallery-cell:nth-child(12) { grid-column: 5 / 7; grid-row: 4 / 5; }

    /* Tagline below gallery */
    .gallery-tagline {
        font-size: 0.78rem;
        color: var(--welcome-muted);
        text-align: center;
        letter-spacing: 0.04em;
        margin-top: 2px;
    }

    /* ══════════════════════════════════════
       RIGHT SIDE — Login form
    ══════════════════════════════════════ */
    .welcome-right {
        flex: 0 0 420px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        z-index: 1;
    }

    .welcome-right .login-panel {
        width: 100%;
    }

    .welcome-right .login-card {
        padding: clamp(28px, 5vw, 40px) clamp(28px, 5vw, 36px);
        border-radius: var(--welcome-radius);
        background: rgba(255, 255, 255, 0.82);
        border: 1px solid rgba(139, 69, 19, 0.12);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        box-shadow: var(--welcome-shadow);
    }

    .welcome-right .login-card .form-title {
        margin: 0 0 8px;
        font-size: 1.55rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #3e2412;
    }

    .welcome-right .login-card .login-lead {
        margin: 0 0 32px;
        font-size: 0.88rem;
        color: var(--welcome-muted);
        line-height: 1.55;
    }

    .welcome-right .form-group {
        margin-bottom: 20px;
    }

    .welcome-right label.col-form-label {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(180, 205, 255, 0.6);
        margin-bottom: 8px;
        padding: 0;
        display: block;
    }

    .welcome-right .form-control {
        height: auto;
        padding: 13px 16px;
        border-radius: 12px;
        border: 1px solid rgba(139, 69, 19, 0.14);
        background: rgba(255, 255, 255, 0.88);
        color: var(--welcome-text);
        font-size: 0.975rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        width: 100%;
        box-sizing: border-box;
    }

    .welcome-right .form-control::placeholder {
        color: rgba(123, 90, 69, 0.55);
    }

    .welcome-right .form-control:focus {
        outline: none;
        border-color: rgba(139, 69, 19, 0.45);
        box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.14);
        background: #fff;
        color: var(--welcome-text);
    }

    .welcome-right .form-control.is-invalid {
        border-color: rgba(248, 113, 113, 0.65);
    }

    .welcome-right .password-field-wrap {
        position: relative;
    }

    .welcome-right .password-field-wrap .form-control {
        padding-right: 48px;
    }

    .welcome-right .password-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 8px;
        background: rgba(139, 69, 19, 0.08);
        color: rgba(92, 57, 31, 0.85);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s ease, color 0.2s ease;
    }

    .welcome-right .password-toggle:hover,
    .welcome-right .password-toggle:focus {
        background: rgba(139, 69, 19, 0.15);
        color: var(--welcome-text);
        outline: none;
    }

    .welcome-right .invalid-feedback {
        margin-top: 8px;
        font-size: 0.83rem;
        color: #fecaca;
        display: block;
    }

    .welcome-right .login-remember {
        margin-bottom: 24px;
    }

    .welcome-right .form-check {
        padding-left: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .welcome-right .form-check-input {
        position: static;
        margin: 0;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 6px;
        border: 1px solid rgba(139,69,19,0.16);
        background: rgba(255, 255, 255, 0.8);
        cursor: pointer;
        flex-shrink: 0;
    }

    .welcome-right .form-check-input:checked {
        background-color: var(--welcome-accent);
        border-color: var(--welcome-accent);
    }

    .welcome-right .form-check-label {
        font-size: 0.88rem;
        color: var(--welcome-muted);
        cursor: pointer;
        padding: 0;
    }

    .welcome-right .login-submit {
        width: 100%;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #8b4513 0%, #a0522d 100%);
        color: #fff7f1 !important;
        font-weight: 700;
        font-size: 0.975rem;
        padding: 14px 22px;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        box-shadow: 0 10px 30px rgba(139, 69, 19, 0.24);
        letter-spacing: 0.01em;
    }

    .welcome-right .login-submit:hover,
    .welcome-right .login-submit:focus {
        filter: brightness(1.08);
        transform: translateY(-1px);
        box-shadow: 0 14px 38px rgba(139, 69, 19, 0.3);
        outline: none;
    }

    .welcome-right .login-submit:active {
        transform: translateY(0);
    }

    /* ── Footer ── */
    .welcome-footer {
        position: absolute;
        left: 16px;
        right: 16px;
        bottom: clamp(8px, 1.8vh, 16px);
        margin-top: 0;
        padding-top: 0;
        text-align: center;
        font-size: 0.78rem;
        color: rgba(92, 57, 31, 0.52);
    }

    .welcome-footer kbd {
        font-family: var(--welcome-mono);
        font-size: 0.7rem;
        padding: 2px 7px;
        border-radius: 5px;
        background: rgba(255,255,255,0.55);
        border: 1px solid rgba(139,69,19,0.12);
        color: rgba(92, 57, 31, 0.75);
    }

    /* ── Responsive ── */
    @media (max-width: 900px) {
        html, body { overflow: auto; }

        .welcome-page {
            height: auto;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        .welcome-main {
            flex-direction: column;
            align-items: center;
        }

        .welcome-left {
            width: 100%;
            max-width: 620px;
        }

        .school-gallery {
            height: min(var(--gallery-mobile-height), 82vw);
        }

        .welcome-right {
            flex: 1;
            flex-basis: auto;
            width: 100%;
            max-width: 420px;
        }
    }
</style>
@endsection

@section('content')
<div class="welcome-page">
    <header class="welcome-header">
        @if (Route::has('login'))
        <nav class="welcome-nav top-right links" aria-label="Account">
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ url('/admin') }}" class="primary">Dashboard</a>
                @endif
                @if(auth()->user()->hasRole('employee'))
                    <a href="{{ url('/employee/dashboard') }}">Dashboard</a>
                @endif
                <a href="{{ route('logout') }}"
                   title="Signed in as {{ auth()->user()->name }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endauth
        </nav>
        @endif
    </header>

    <main class="welcome-main">

        <!-- ══ LEFT — School branding + gallery ══ -->
        <div class="welcome-left">

            <!-- School logo + name -->
            <a href="{{ url('/') }}" class="welcome-brand" aria-label="Home">
                <img src="{{ asset('assets/images/logo-sm.jpg') }}" alt="School logo">
                <div class="welcome-brand-text">
                    <p class="eyebrow">Colegio de Sta. Teresa De Avila</p>
                    <p class="name">Attendance Management</p>
                </div>
            </a>

            @php
                $availableGalleryImages = [];

                for ($imageNumber = 1; $imageNumber <= 12; $imageNumber++) {
                    $imageName = "school-{$imageNumber}.jpg";

                    if (file_exists(public_path("assets/images/{$imageName}"))) {
                        $availableGalleryImages[] = $imageName;
                    }
                }

                if (empty($availableGalleryImages)) {
                    $availableGalleryImages[] = 'logo-sm.jpg';
                }

                $schoolGalleryImages = [];

                for ($tileIndex = 0; $tileIndex < 12; $tileIndex++) {
                    $schoolGalleryImages[] = $availableGalleryImages[$tileIndex % count($availableGalleryImages)];
                }
            @endphp

            <!-- Collage, filled with 12 visible tiles from the available school photos -->
            <div class="school-gallery" aria-label="School photo gallery">
                @foreach ($schoolGalleryImages as $tileIndex => $imageName)
                    <div class="gallery-cell">
                        <img src="{{ asset("assets/images/{$imageName}") }}" alt="Campus photo {{ $tileIndex + 1 }}" loading="lazy">
                    </div>
                @endforeach
            </div>

            <p class="gallery-tagline">S.Y. 2026–2027</p>
        </div>

        <!-- ══ RIGHT — Sign in form ══ -->
        <div class="welcome-right">
            <div class="login-panel">
                <div class="login-card">
                    <h2 class="form-title">Sign in</h2>
                    <p class="login-lead">Use your staff credentials to access the dashboard.</p>

                    <form class="form-horizontal m-t-10" method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="form-group">
                            <label for="email" class="col-form-label">{{ __('Email Address') }}</label>
                            <input id="email" type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   name="email" value="{{ old('email') }}"
                                   required autocomplete="email" autofocus
                                   placeholder="you@example.com">
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password" class="col-form-label">{{ __('Password') }}</label>
                            <div class="password-field-wrap">
                                <input id="password" type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       name="password" required autocomplete="current-password"
                                       placeholder="••••••••">
                                <button type="button" class="password-toggle" id="welcome-password-toggle" aria-label="Show password" title="Show password">
                                    <i class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group login-remember">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="remember" id="remember"
                                       {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">
                                    {{ __('Remember Me') }}
                                </label>
                            </div>
                        </div>

                        <button class="btn login-submit" type="submit">Log in</button>
                    </form>
                </div>
            </div>
        </div>

    </main>

    <footer class="welcome-footer">
        Secure attendance portal · Times shown in <kbd>Asia/Manila</kbd>
    </footer>
</div>
@endsection

@section('script')
<script>
(function () {
    var input = document.getElementById('password');
    var btn = document.getElementById('welcome-password-toggle');
    if (!input || !btn) return;

    var icon = btn.querySelector('i');
    btn.addEventListener('click', function () {
        var showing = input.type === 'text';
        input.type = showing ? 'password' : 'text';
        btn.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
        btn.setAttribute('title', showing ? 'Show password' : 'Hide password');
        if (icon) {
            icon.className = showing ? 'fa fa-eye' : 'fa fa-eye-slash';
        }
    });
})();
</script>
@endsection
