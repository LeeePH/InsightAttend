@extends('layouts.welcome')

@section('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
@endsection

@section('content')

<style>
    :root {
        --welcome-bg-deep: #070b12;
        --welcome-surface: rgba(255, 255, 255, 0.06);
        --welcome-surface-hover: rgba(255, 255, 255, 0.1);
        --welcome-border: rgba(255, 255, 255, 0.12);
        --welcome-text: #f1f5f9;
        --welcome-muted: rgba(241, 245, 249, 0.65);
        --welcome-accent: #38bdf8;
        --welcome-accent-soft: rgba(56, 189, 248, 0.18);
        --welcome-in: #34d399;
        --welcome-in-soft: rgba(52, 211, 153, 0.15);
        --welcome-out: #f472b6;
        --welcome-out-soft: rgba(244, 114, 182, 0.15);
        --welcome-radius: 20px;
        --welcome-shadow: 0 24px 80px rgba(0, 0, 0, 0.45);
        --welcome-font: "DM Sans", system-ui, -apple-system, sans-serif;
        --welcome-mono: "JetBrains Mono", ui-monospace, monospace;
    }

    html, body {
        min-height: 100%;
    }

    body {
        margin: 0;
        font-family: var(--welcome-font);
        background: var(--welcome-bg-deep) !important;
        color: var(--welcome-text);
        -webkit-font-smoothing: antialiased;
    }

    body::before {
        content: "";
        position: fixed;
        inset: 0;
        background:
            radial-gradient(ellipse 120% 80% at 10% -20%, rgba(56, 189, 248, 0.22), transparent 50%),
            radial-gradient(ellipse 90% 70% at 100% 0%, rgba(244, 114, 182, 0.12), transparent 45%),
            radial-gradient(ellipse 70% 50% at 50% 100%, rgba(52, 211, 153, 0.08), transparent 50%),
            url('{{ asset('images.jpg') }}') center center / cover no-repeat fixed;
        z-index: -2;
    }

    body::after {
        content: "";
        position: fixed;
        inset: 0;
        background: linear-gradient(165deg, rgba(7, 11, 18, 0.82) 0%, rgba(7, 11, 18, 0.72) 40%, rgba(7, 11, 18, 0.88) 100%);
        z-index: -1;
    }

    .welcome-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        width: 100%;
        max-width: none;
        margin: 0;
        box-sizing: border-box;
        padding: clamp(16px, 4vw, 40px) clamp(16px, 4vw, 48px) clamp(16px, 4vw, 40px) clamp(8px, 1.5vw, 20px);
    }

    .welcome-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
        margin-bottom: clamp(28px, 6vw, 56px);
        width: 100%;
    }

    .welcome-brand {
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none;
        color: inherit;
        margin-left: 0;
        margin-right: auto;
        position: relative;
        bottom: 20px;
    }

    .welcome-brand img {
        width: clamp(56px, 12vw, 72px);
        height: clamp(56px, 12vw, 72px);
        border-radius: 18px;
        object-fit: cover;
        border: 1px solid var(--welcome-border);
        box-shadow: var(--welcome-shadow);
    }

    .welcome-brand-text {
        text-align: left;
    }

    .welcome-brand-text .eyebrow {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--welcome-muted);
        margin: 0 0 4px;
    }

    .welcome-brand-text .name {
        font-size: clamp(1.15rem, 2.5vw, 1.45rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        margin: 0;
        line-height: 1.2;
    }

    .welcome-nav {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
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
    }

    .welcome-nav a:hover {
        background: var(--welcome-surface);
        border-color: var(--welcome-border);
        transform: translateY(-1px);
    }

    .welcome-nav a.primary {
        background: var(--welcome-accent-soft);
        border-color: rgba(56, 189, 248, 0.35);
        color: #e0f2fe !important;
    }

    .welcome-nav a.primary:hover {
        background: rgba(56, 189, 248, 0.28);
    }

    .welcome-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        gap: clamp(20px, 4vw, 36px);
    }

    .welcome-clock-card {
        width: min(100%, 520px);
        padding: clamp(24px, 5vw, 36px) clamp(20px, 4vw, 32px);
        border-radius: var(--welcome-radius);
        background: var(--welcome-surface);
        border: 1px solid var(--welcome-border);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        box-shadow: var(--welcome-shadow);
    }

    .welcome-clock-label {
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--welcome-muted);
        margin-bottom: 12px;
    }

    .welcome-clock {
        font-family: var(--welcome-mono);
        font-size: clamp(2.5rem, 10vw, 4.25rem);
        font-weight: 600;
        letter-spacing: -0.02em;
        color: #fff;
        line-height: 1;
        text-shadow: 0 4px 32px rgba(0, 0, 0, 0.35);
    }

    .welcome-clock-meta {
        margin-top: 16px;
        font-size: 0.9rem;
        color: var(--welcome-muted);
    }

    .welcome-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: clamp(16px, 3vw, 24px);
        width: min(100%, 640px);
    }

    .welcome-action {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
        padding: clamp(22px, 4vw, 28px);
        border-radius: var(--welcome-radius);
        text-decoration: none;
        color: var(--welcome-text) !important;
        border: 1px solid var(--welcome-border);
        background: var(--welcome-surface);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        position: relative;
        overflow: hidden;
    }

    .welcome-action::before {
        content: "";
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.25s ease;
        pointer-events: none;
    }

    .welcome-action--in::before {
        background: radial-gradient(circle at 100% 0%, var(--welcome-in-soft), transparent 55%);
    }

    .welcome-action--out::before {
        background: radial-gradient(circle at 100% 0%, var(--welcome-out-soft), transparent 55%);
    }

    .welcome-action:hover {
        transform: translateY(-4px);
        border-color: rgba(255, 255, 255, 0.22);
        background: var(--welcome-surface-hover);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
    }

    .welcome-action:hover::before {
        opacity: 1;
    }

    .welcome-action--in:hover {
        border-color: rgba(52, 211, 153, 0.45);
    }

    .welcome-action--out:hover {
        border-color: rgba(244, 114, 182, 0.45);
    }

    .welcome-action-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 16px;
        position: relative;
        z-index: 1;
    }

    .welcome-action--in .welcome-action-icon {
        background: var(--welcome-in-soft);
        color: #6ee7b7;
    }

    .welcome-action--out .welcome-action-icon {
        background: var(--welcome-out-soft);
        color: #f9a8d4;
    }

    .welcome-action h2 {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0 0 8px;
        position: relative;
        z-index: 1;
        letter-spacing: -0.02em;
    }

    .welcome-action p {
        margin: 0;
        font-size: 0.9rem;
        color: var(--welcome-muted);
        line-height: 1.5;
        position: relative;
        z-index: 1;
    }

    .welcome-action-cta {
        margin-top: 18px;
        font-size: 0.8rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--welcome-accent);
        position: relative;
        z-index: 1;
    }

    .welcome-action--out .welcome-action-cta {
        color: #fbcfe8;
    }

    .welcome-footer {
        margin-top: auto;
        padding-top: clamp(32px, 6vw, 48px);
        text-align: center;
        font-size: 0.8rem;
        color: var(--welcome-muted);
    }

    .welcome-footer kbd {
        font-family: var(--welcome-mono);
        font-size: 0.72rem;
        padding: 2px 8px;
        border-radius: 6px;
        background: var(--welcome-surface);
        border: 1px solid var(--welcome-border);
    }
</style>

<div class="welcome-page">
    <header class="welcome-header">
        <a href="{{ url('/') }}" class="welcome-brand" aria-label="Home">
            <img src="{{ asset('assets/images/logo-sm.jpg') }}" alt="">
            <div class="welcome-brand-text">
                <p class="eyebrow">Colegio de Sta. Teresa De Avila</p>
                <p class="name">Attendance Management</p>
            </div>
        </a>

        @if (Route::has('login'))
        <nav class="welcome-nav top-right links" aria-label="Account">
            @auth
                @if(auth()->user()->hasRole('admin'))
                    <a href="{{ url('/admin') }}" class="primary">Admin</a>
                @endif
                @if(auth()->user()->hasRole('employee'))
                    <a href="{{ url('/employee/dashboard') }}">Dashboard</a>
                @endif
                <a href="{{ route('logout') }}" title="Signed in as {{ auth()->user()->name }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Sign out</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @else
                <a href="{{ route('login') }}" class="primary">Sign in</a>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}">Register</a>
                @endif
            @endauth
        </nav>
        @endif
    </header>

    <main class="welcome-main">
        <div class="welcome-clock-card" aria-live="polite">
            <div class="welcome-clock-label">Current time</div>
            <div class="welcome-clock" id="clock">—</div>
            <div class="welcome-clock-meta">Philippines standard time</div>
        </div>

        <div class="welcome-actions">
            <a href="{{ route('timein.index') }}" class="welcome-action welcome-action--in">
                <span class="welcome-action-icon" aria-hidden="true"><i class="fa fa-sign-in-alt"></i></span>
                <h2>Time in</h2>
                <p>Clock in when you start your shift or session.</p>
                <span class="welcome-action-cta">Start <i class="fa fa-arrow-right" style="font-size: 0.75rem;"></i></span>
            </a>
            <a href="{{ route('timeout.index') }}" class="welcome-action welcome-action--out">
                <span class="welcome-action-icon" aria-hidden="true"><i class="fa fa-sign-out-alt"></i></span>
                <h2>Time out</h2>
                <p>Clock out when your work period is complete.</p>
                <span class="welcome-action-cta">Finish <i class="fa fa-arrow-right" style="font-size: 0.75rem;"></i></span>
            </a>
        </div>
    </main>

    <footer class="welcome-footer">
        Secure attendance portal · Times shown in <kbd>Asia/Manila</kbd>
    </footer>
</div>
@endsection
