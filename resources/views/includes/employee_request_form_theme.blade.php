{{-- Employee-facing request forms (leave / resignation). Expects $formUi (merged array) and $pageWrapperClass (e.g. leave-page). --}}
@php
    $ui = isset($formUi) && is_array($formUi) ? $formUi : [];
    $hex = function ($v, $fallback) {
        return is_string($v) && preg_match('/^#[0-9A-Fa-f]{6}$/', $v) ? $v : $fallback;
    };
    $accent = $hex($ui['accent'] ?? null, '#8B4513');
    $accentSoft = $hex($ui['accent_soft'] ?? null, '#f3e4d7');
    $bg = $hex($ui['bg'] ?? null, '#f8f1eb');
    $text = $hex($ui['text'] ?? null, '#3e2412');
    $muted = $hex($ui['muted'] ?? null, '#7b5a45');
    $border = $hex($ui['border'] ?? null, '#e2cdbd');
    $radius = isset($ui['card_radius']) ? max(0, min(40, (int) $ui['card_radius'])) : 14;
    $wrap = isset($pageWrapperClass) && is_string($pageWrapperClass) && preg_match('/^[a-z0-9-]+$/', $pageWrapperClass)
        ? $pageWrapperClass
        : 'leave-page';
@endphp
<style>
    :root {
        --theme-bg: {{ $bg }};
        --theme-card: #ffffff;
        --theme-text: {{ $text }};
        --theme-muted: {{ $muted }};
        --theme-border: {{ $border }};
        --theme-accent: {{ $accent }};
        --theme-accent-soft: {{ $accentSoft }};
        --theme-card-radius: {{ $radius }}px;
    }

    body {
        background-color: var(--theme-bg);
    }

    .{{ $wrap }} .card {
        border: 1px solid var(--theme-border);
        box-shadow: 0 10px 24px rgba(19, 28, 43, 0.06);
        border-radius: var(--theme-card-radius);
    }

    .{{ $wrap }} {
        margin-left: -8px;
        margin-right: -8px;
    }

    .{{ $wrap }} .card-body {
        padding: 20px 18px;
    }

    .{{ $wrap }} .leave-header,
    .{{ $wrap }} .page-header-title {
        color: var(--theme-text);
        margin-bottom: 6px;
    }

    .{{ $wrap }} .leave-subtitle,
    .{{ $wrap }} .page-subtitle {
        color: var(--theme-muted);
        margin-bottom: 0;
    }

    .{{ $wrap }} .employee-panel {
        background: var(--theme-accent-soft);
        border: 1px solid #d2dbea;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 20px;
    }

    .{{ $wrap }} .employee-panel h6 {
        margin-bottom: 10px;
        color: var(--theme-text);
        font-weight: 700;
    }

    .{{ $wrap }} .employee-panel p {
        margin-bottom: 6px;
        color: var(--theme-muted);
    }

    .{{ $wrap }} .employee-panel strong {
        color: var(--theme-text);
    }

    .{{ $wrap }} .notice-box {
        background: #f5f8fc;
        border: 1px solid #cfd7e6;
        border-radius: 12px;
        padding: 12px 14px;
        color: var(--theme-muted);
        margin-bottom: 18px;
    }

    .{{ $wrap }} .notice-box strong {
        color: var(--theme-text);
    }

    .{{ $wrap }} label {
        color: var(--theme-text);
        font-weight: 600;
    }

    .{{ $wrap }} .form-control {
        border-radius: 10px;
        border: 1px solid #cfd7e6;
        color: var(--theme-text);
        background: #ffffff;
    }

    .{{ $wrap }} .form-control:focus {
        border-color: #9fb0cc;
        box-shadow: 0 0 0 0.15rem rgba(46, 63, 92, 0.12);
    }

    .{{ $wrap }} .days-display {
        background: #f5f8fc;
        border: 1px solid #cfd7e6;
        border-radius: 12px;
        padding: 14px;
        text-align: center;
        margin: 18px 0 4px;
    }

    .{{ $wrap }} .days-display h3 {
        margin: 3px 0 0;
        color: var(--theme-accent);
        font-size: 30px;
        font-weight: 700;
    }

    .{{ $wrap }} .days-display small {
        color: var(--theme-muted);
        font-weight: 600;
    }

    .{{ $wrap }} .theme-btn {
        border-radius: 999px;
        border: 1px solid #c5cede;
        background: #f4f7fc;
        color: var(--theme-text);
        font-weight: 600;
        padding: 10px 20px;
        transition: all 0.2s ease;
    }

    .{{ $wrap }} .theme-btn:hover {
        background: #e8edf6;
        color: var(--theme-text);
        text-decoration: none;
    }

    .{{ $wrap }} .table {
        color: var(--theme-text);
    }

    .{{ $wrap }} .table thead th {
        background: var(--theme-accent-soft);
        color: var(--theme-text);
        border-color: var(--theme-border);
        font-weight: 600;
    }

    .{{ $wrap }} .table td {
        border-color: var(--theme-border);
    }

    .{{ $wrap }} .theme-badge {
        background: #eef2f8;
        color: var(--theme-text);
        border: 1px solid #cfd7e6;
        font-weight: 600;
        padding: 5px 10px;
        border-radius: 999px;
        display: inline-block;
    }
</style>
