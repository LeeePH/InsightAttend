<!-- App favicon -->
<link rel="shortcut icon" href="{{ URL::asset('assets/images/') }}">
<meta name="viewport" content="width=device-width, initial-scale=1">      
@yield('css')

 <!-- App css -->
<link href="{{ URL::asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/css/metismenu.min.css') }}" rel="stylesheet" type="text/css">
<link href="{{ URL::asset('assets/css/icons.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('assets/css/style.css') }}" rel="stylesheet" type="text/css" />

{{-- <link href="{{ URL::asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css"> --}}
<link href="{{ asset('plugins/sweetalert.min.css') }}" rel="stylesheet">
<!-- Table css -->
<link href="{{ URL::asset('plugins/RWD-Table-Patterns/dist/css/rwd-table.min.css') }}" rel="stylesheet" type="text/css" media="screen">
<!-- DataTables -->
<link href="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ URL::asset('plugins/datatables/buttons.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
<!-- Responsive datatable examples -->
<link href="{{ URL::asset('plugins/datatables/responsive.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />

<style>
    :root {
        --brand-primary: #8B4513;
        --brand-primary-dark: #6f330d;
        --brand-primary-soft: #f3e4d7;
        --brand-bg: #f8f1eb;
        --brand-surface: #fffaf6;
        --brand-border: #e2cdbd;
        --brand-text: #3e2412;
        --brand-muted: #7b5a45;
    }

    body,
    .content-page .content,
    .page-content-wrapper,
    .container-fluid {
        background-color: var(--brand-bg) !important;
        color: var(--brand-text);
    }

    .topbar,
    .topbar .topbar-left {
        background-color: var(--brand-primary) !important;
    }

    .navbar-custom {
        background-color: var(--brand-primary-soft) !important;
        border-bottom: 1px solid var(--brand-border);
    }

    .navbar-custom .nav-link,
    .navbar-custom .mdi {
        color: var(--brand-text) !important;
    }

    .left.side-menu,
    #sidebar-menu > ul > li > a.mm-active,
    .enlarged #wrapper .left.side-menu #sidebar-menu > ul > li > a:hover,
    .enlarged #wrapper .left.side-menu #sidebar-menu ul > li:hover > a {
        background-color: var(--brand-primary) !important;
    }

    #sidebar-menu > ul > li > a,
    #sidebar-menu ul li a i,
    #sidebar-menu .menu-title {
        color: #f9ede4 !important;
    }

    #sidebar-menu ul.submenu li a,
    #sidebar-menu ul.submenu li a span,
    #sidebar-menu ul.submenu li a i {
        color: #f9ede4 !important;
    }

    #sidebar-menu ul.submenu li a:hover,
    #sidebar-menu ul.submenu li a:focus,
    #sidebar-menu ul.submenu li a.mm.active,
    #sidebar-menu ul.submenu li a.mm-active {
        color: #ffffff !important;
    }

    #sidebar-menu > ul > li > a:hover,
    #sidebar-menu > ul > li > a:focus,
    #sidebar-menu > ul > li > a:active {
        color: #ffffff !important;
        background-color: var(--brand-primary-dark) !important;
    }

    .card {
        border: 1px solid var(--brand-border) !important;
        background: var(--brand-surface);
    }

    .page-title,
    .header-title,
    h1, h2, h3, h4, h5, h6,
    .page-title-box .page-title {
        color: var(--brand-text);
    }

    .text-muted,
    .page-title-box .breadcrumb .active {
        color: var(--brand-muted) !important;
    }

    .page-title-box .breadcrumb {
        display: none !important;
    }

    .btn-primary,
    .btn-success,
    .theme-btn {
        background-color: var(--brand-primary) !important;
        border-color: var(--brand-primary) !important;
        color: #fff !important;
    }

    .btn-primary:hover,
    .btn-primary:focus,
    .btn-success:hover,
    .btn-success:focus,
    .theme-btn:hover,
    .theme-btn:focus {
        background-color: var(--brand-primary-dark) !important;
        border-color: var(--brand-primary-dark) !important;
        color: #fff !important;
    }

    .badge-primary,
    .badge-success,
    .theme-badge {
        background-color: var(--brand-primary) !important;
        border-color: var(--brand-primary) !important;
        color: #fff !important;
    }

    .table thead th {
        background: var(--brand-primary-soft) !important;
        color: var(--brand-text);
        border-color: var(--brand-border) !important;
    }

    .table td,
    .table th {
        border-color: var(--brand-border) !important;
    }

    a,
    .page-title-box .breadcrumb a {
        color: var(--brand-primary);
    }
</style>
