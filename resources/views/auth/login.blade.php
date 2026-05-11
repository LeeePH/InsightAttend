@extends('layouts.master-blank')

@section('css')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400..700;1,9..40,400..700&display=swap" rel="stylesheet">
@endsection

@section('content')
<style>
    :root {
        --login-bg-deep: #2d190f;
        --login-surface: rgba(255, 255, 255, 0.07);
        --login-border: rgba(255, 255, 255, 0.12);
        --login-text: #f1f5f9;
        --login-muted: rgba(241, 245, 249, 0.65);
        --login-accent: #8B4513;
        --login-accent-soft: rgba(139, 69, 19, 0.22);
        --login-radius: 20px;
        --login-shadow: 0 24px 80px rgba(0, 0, 0, 0.45);
        --login-font: "DM Sans", system-ui, -apple-system, sans-serif;
    }

    html, body {
        min-height: 100%;
    }

    body {
        margin: 0;
        font-family: var(--login-font);
        background: var(--login-bg-deep) !important;
        color: var(--login-text);
        -webkit-font-smoothing: antialiased;
    }

    body::before {
        content: "";
        position: fixed;
        inset: 0;
        background:
            radial-gradient(ellipse 120% 80% at 10% -20%, rgba(139, 69, 19, 0.32), transparent 50%),
            radial-gradient(ellipse 90% 70% at 100% 0%, rgba(111, 51, 13, 0.2), transparent 45%),
            radial-gradient(ellipse 70% 50% at 50% 100%, rgba(185, 106, 52, 0.15), transparent 50%),
            url('{{ asset('images.jpg') }}') center center / cover no-repeat fixed;
        z-index: -2;
    }

    body::after {
        content: "";
        position: fixed;
        inset: 0;
        background: linear-gradient(165deg, rgba(45, 25, 15, 0.82) 0%, rgba(45, 25, 15, 0.72) 40%, rgba(45, 25, 15, 0.88) 100%);
        z-index: -1;
    }

    .login-page {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: clamp(20px, 5vw, 48px) clamp(16px, 4vw, 32px);
        box-sizing: border-box;
    }

    .login-back {
        position: fixed;
        top: clamp(16px, 3vw, 28px);
        left: clamp(16px, 3vw, 28px);
        z-index: 10;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--login-muted) !important;
        text-decoration: none;
        padding: 10px 16px;
        border-radius: 999px;
        border: 1px solid transparent;
        transition: color 0.2s ease, background 0.2s ease, border-color 0.2s ease;
    }

    .login-back:hover {
        color: var(--login-text) !important;
        background: var(--login-surface);
        border-color: var(--login-border);
        text-decoration: none;
    }

    .login-panel {
        width: min(100%, 440px);
    }

    .login-brand {
        text-align: center;
        margin-bottom: clamp(20px, 4vw, 28px);
    }

    .login-brand img {
        width: clamp(72px, 18vw, 96px);
        height: clamp(72px, 18vw, 96px);
        border-radius: 22px;
        object-fit: cover;
        border: 1px solid var(--login-border);
        box-shadow: var(--login-shadow);
        margin-bottom: 16px;
    }

    .login-brand .eyebrow {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--login-muted);
        margin: 0 0 6px;
    }

    .login-brand .org-name {
        font-size: clamp(1.25rem, 3.5vw, 1.5rem);
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
        margin: 0;
        color: #fff;
    }

    .login-card {
        padding: clamp(28px, 5vw, 40px) clamp(22px, 4vw, 32px);
        border-radius: var(--login-radius);
        background: var(--login-surface);
        border: 1px solid var(--login-border);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        box-shadow: var(--login-shadow);
    }

    .login-card .form-title {
        margin: 0 0 8px;
        font-size: 1.5rem;
        font-weight: 700;
        letter-spacing: -0.02em;
        color: #fff;
    }

    .login-card .login-lead {
        margin: 0 0 28px;
        font-size: 0.9rem;
        color: var(--login-muted);
        line-height: 1.5;
    }

    .login-card .form-group {
        margin-bottom: 20px;
    }

    .login-card label.col-form-label {
        font-size: 0.8rem;
        font-weight: 600;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: var(--login-muted);
        margin-bottom: 8px;
        padding: 0;
    }

    .login-card .form-control {
        height: auto;
        padding: 14px 16px;
        border-radius: 12px;
        border: 1px solid var(--login-border);
        background: rgba(7, 11, 18, 0.45);
        color: var(--login-text);
        font-size: 1rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }

    .login-card .form-control::placeholder {
        color: rgba(241, 245, 249, 0.45);
    }

    .login-card .form-control:focus {
        border-color: rgba(139, 69, 19, 0.6);
        box-shadow: 0 0 0 3px var(--login-accent-soft);
        background: rgba(7, 11, 18, 0.55);
        color: #fff;
    }

    .password-field {
        position: relative;
    }

    .password-field .form-control {
        padding-right: 52px;
    }

    .password-toggle {
        position: absolute;
        top: 50%;
        right: 14px;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: rgba(241, 245, 249, 0.7);
        padding: 0;
        cursor: pointer;
    }

    .password-toggle:focus {
        outline: none;
        color: #fff;
    }

    .login-card .invalid-feedback {
        margin-top: 8px;
        font-size: 0.85rem;
        color: #fecaca;
    }

    .login-card .form-check {
        padding-left: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .login-card .form-check-input {
        position: static;
        margin: 0;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 6px;
        border: 1px solid var(--login-border);
        background: rgba(7, 11, 18, 0.5);
        cursor: pointer;
    }

    .login-card .form-check-input:checked {
        background-color: var(--login-accent);
        border-color: var(--login-accent);
    }

    .login-card .form-check-label {
        font-size: 0.9rem;
        color: var(--login-muted);
        cursor: pointer;
        padding: 0;
    }

    .login-submit {
        width: 100%;
        border: none;
        border-radius: 12px;
        background: linear-gradient(135deg, #8B4513 0%, #a85a24 100%);
        color: #fff5ec !important;
        font-weight: 700;
        font-size: 1rem;
        padding: 14px 22px;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
        box-shadow: 0 12px 32px rgba(139, 69, 19, 0.4);
    }

    .login-submit:hover,
    .login-submit:focus {
        filter: brightness(1.06);
        transform: translateY(-1px);
        box-shadow: 0 16px 40px rgba(139, 69, 19, 0.48);
        outline: none;
    }
</style>

<a href="{{ url('/') }}" class="login-back" aria-label="Back to home">
    <i class="fa fa-arrow-left" aria-hidden="true"></i>
    Back to home
</a>

<div class="login-page">
    <div class="login-panel">
        <div class="login-brand">
            <img src="{{ asset('assets/images/logo-sm.jpg') }}" alt="">
            <p class="eyebrow">InsightAttend</p>
            <h1 class="org-name">Colegio de Sta. Teresa De Avila</h1>
        </div>

        <div class="login-card">
            <h2 class="form-title">Sign in</h2>
            <p class="login-lead">Use your staff credentials to access the dashboard.</p>

            <form class="form-horizontal m-t-10" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="col-form-label d-block">{{ __('Email Address') }}</label>
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                        name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                        placeholder="you@example.com">

                    @error('email')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="col-form-label d-block">{{ __('Password') }}</label>
                    <div class="password-field">
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="current-password"
                            placeholder="Password">
                        <button type="button" class="password-toggle" id="togglePassword" aria-label="Show password">
                            <i class="fa fa-eye" aria-hidden="true"></i>
                        </button>
                    </div>

                    @error('password')
                        <span class="invalid-feedback d-block" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="form-group login-remember">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember"
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
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var password = document.getElementById('password');
        var toggle = document.getElementById('togglePassword');
        if (!password || !toggle) return;

        toggle.addEventListener('click', function () {
            var visible = password.type === 'text';
            password.type = visible ? 'password' : 'text';
            toggle.setAttribute('aria-label', visible ? 'Show password' : 'Hide password');
            toggle.innerHTML = visible
                ? '<i class="fa fa-eye" aria-hidden="true"></i>'
                : '<i class="fa fa-eye-slash" aria-hidden="true"></i>';
        });
    });
</script>
@endsection
