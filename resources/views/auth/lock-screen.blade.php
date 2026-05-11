@extends('layouts.master-blank')

@section('content')
<style>
    body {
        min-height: 100vh;
        margin: 0;
        background:
            linear-gradient(135deg, rgba(45, 25, 15, 0.92), rgba(88, 47, 20, 0.9)),
            url('{{ asset('images.jpg') }}') center center / cover no-repeat fixed;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: "DM Sans", system-ui, sans-serif;
    }

    .lock-card {
        width: min(100%, 420px);
        padding: 2rem;
        border-radius: 22px;
        background: rgba(255, 255, 255, 0.09);
        border: 1px solid rgba(255, 255, 255, 0.14);
        box-shadow: 0 24px 80px rgba(0, 0, 0, 0.35);
        backdrop-filter: blur(14px);
        color: #fff;
    }

    .lock-avatar {
        width: 88px;
        height: 88px;
        margin: 0 auto 1rem;
        border-radius: 22px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
    }

    .lock-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .lock-card .form-control {
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.16);
        background: rgba(7, 11, 18, 0.35);
        color: #fff;
        padding: 14px 16px;
    }

    .lock-card .btn {
        border-radius: 12px;
        padding: 12px 16px;
        font-weight: 700;
    }
</style>

<div class="lock-card">
    <div class="text-center">
        <div class="lock-avatar">
            @if (!empty($lockData['avatar']))
                <img src="{{ $lockData['avatar'] }}" alt="{{ $lockData['name'] }}">
            @else
                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($lockData['name'] ?? 'U', 0, 1)) }}
            @endif
        </div>
        <h3 class="mb-2">{{ $lockData['name'] ?? 'Locked User' }}</h3>
        <p class="text-light mb-4">Enter your password to unlock the session.</p>
    </div>

    <form method="POST" action="{{ route('lock.screen.unlock') }}">
        @csrf
        <div class="form-group">
            <label for="password" class="text-light">Password</label>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required autofocus>
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-light btn-block">Unlock</button>
        <a href="{{ route('login') }}" class="btn btn-outline-light btn-block mt-2">Use another account</a>
    </form>
</div>
@endsection
