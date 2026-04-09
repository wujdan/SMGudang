<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Gudang') — GudangKu</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* ═══════════════════════════════════════
           VARIABLES
        ═══════════════════════════════════════ */
        :root {
            --primary: #1e40af;
            --primary-dark: #1e3a8a;
            --primary-light: #dbeafe;
            --primary-mid: #3b82f6;
            --accent: #f59e0b;
            --danger: #dc2626;
            --danger-light: #fee2e2;
            --success: #16a34a;
            --success-light: #dcfce7;
            --warning: #d97706;
            --warning-light: #fef3c7;
            --info: #0891b2;
            --info-light: #cffafe;

            --sidebar-w: 256px;
            --topbar-h: 60px;

            --bg: #f1f5f9;
            --card: #ffffff;
            --text: #1e293b;
            --text-soft: #334155;
            --muted: #64748b;
            --border: #e2e8f0;
            --border-soft: #f1f5f9;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, .06), 0 1px 2px rgba(0, 0, 0, .04);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, .08);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, .12);
        }

        /* ═══════════════════════════════════════
           RESET
        ═══════════════════════════════════════ */
        *,
        *::before,
        *::after {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.5;
        }

        /* ═══════════════════════════════════════
           SIDEBAR OVERLAY (mobile)
        ═══════════════════════════════════════ */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 99;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* ═══════════════════════════════════════
           SIDEBAR
        ═══════════════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--primary-dark);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 100;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform .28s cubic-bezier(.4, 0, .2, 1);
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .15) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .15);
            border-radius: 4px;
        }

        /* Brand */
        .sidebar-brand {
            padding: 18px 18px 14px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .sidebar-brand-icon {
            width: 36px;
            height: 36px;
            background: var(--accent);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #fff;
            flex-shrink: 0;
        }

        .sidebar-brand-text h1 {
            font-size: 15px;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
        }

        .sidebar-brand-text span {
            font-size: 10.5px;
            color: rgba(255, 255, 255, .45);
            font-weight: 500;
        }

        /* Section labels */
        .sidebar-section {
            padding: 14px 16px 4px;
            font-size: 9.5px;
            font-weight: 700;
            color: rgba(255, 255, 255, .3);
            letter-spacing: 1.2px;
            text-transform: uppercase;
            flex-shrink: 0;
        }

        /* Nav */
        .sidebar-nav {
            list-style: none;
            padding: 2px 10px 4px;
            flex-shrink: 0;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: var(--radius-sm);
            color: rgba(255, 255, 255, .65);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: background .18s, color .18s;
        }

        .sidebar-nav li a:hover {
            background: rgba(255, 255, 255, .09);
            color: #fff;
        }

        .sidebar-nav li a.active {
            background: rgba(255, 255, 255, .14);
            color: #fff;
            font-weight: 600;
        }

        .sidebar-nav li a i {
            width: 16px;
            text-align: center;
            font-size: 13px;
            opacity: .85;
            flex-shrink: 0;
        }

        .sidebar-nav li a.active i {
            opacity: 1;
        }

        .sidebar-badge {
            margin-left: auto;
            background: var(--danger);
            color: #fff;
            font-size: 9.5px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 10px;
        }

        /* Footer */
        .sidebar-footer {
            margin-top: auto;
            padding: 14px 16px;
            border-top: 1px solid rgba(255, 255, 255, .08);
            flex-shrink: 0;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
        }

        .sidebar-avatar {
            width: 34px;
            height: 34px;
            background: rgba(255, 255, 255, .15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .sidebar-user-info {
            min-width: 0;
        }

        .sidebar-user-info strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-info span {
            font-size: 11px;
            color: rgba(255, 255, 255, .45);
        }

        /* ═══════════════════════════════════════
           MAIN WRAP
        ═══════════════════════════════════════ */
        .main-wrap {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
        }

        /* ═══════════════════════════════════════
           TOPBAR
        ═══════════════════════════════════════ */
        .topbar {
            background: var(--card);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: var(--topbar-h);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            gap: 12px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .topbar-menu-btn {
            display: none;
            width: 36px;
            height: 36px;
            border: none;
            background: var(--bg);
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-size: 15px;
            color: var(--text);
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background .15s;
        }

        .topbar-menu-btn:hover {
            background: var(--border);
        }

        .topbar-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .topbar-date {
            font-size: 12px;
            color: var(--muted);
            white-space: nowrap;
        }

        /* ═══════════════════════════════════════
           CONTENT
        ═══════════════════════════════════════ */
        .content {
            padding: 24px;
            flex: 1;
        }

        /* ═══════════════════════════════════════
           CARDS
        ═══════════════════════════════════════ */
        .card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }

        .card-header {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .card-header h3 {
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .card-body {
            padding: 18px;
        }

        /* ═══════════════════════════════════════
           STAT CARDS
        ═══════════════════════════════════════ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--card);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 16px 16px 14px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            position: relative;
            overflow: hidden;
            transition: box-shadow .2s, transform .22s;
            animation: fadeUp .4s ease both;
        }

        .stat-card:nth-child(1) {
            animation-delay: .03s;
        }

        .stat-card:nth-child(2) {
            animation-delay: .09s;
        }

        .stat-card:nth-child(3) {
            animation-delay: .15s;
        }

        .stat-card:nth-child(4) {
            animation-delay: .21s;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        /* accent top bar */
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        .stat-card.blue::before {
            background: var(--primary-mid);
        }

        .stat-card.amber::before {
            background: var(--accent);
        }

        .stat-card.red::before {
            background: var(--danger);
        }

        .stat-card.green::before {
            background: var(--success);
        }

        .stat-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-card .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .stat-card.blue .stat-icon {
            background: var(--primary-light);
            color: var(--primary-mid);
        }

        .stat-card.amber .stat-icon {
            background: var(--warning-light);
            color: var(--warning);
        }

        .stat-card.red .stat-icon {
            background: var(--danger-light);
            color: var(--danger);
        }

        .stat-card.green .stat-icon {
            background: var(--success-light);
            color: var(--success);
        }

        .stat-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .stat-status i {
            font-size: 7px;
        }

        .stat-status.ok {
            background: var(--success-light);
            color: var(--success);
        }

        .stat-status.warn {
            background: var(--warning-light);
            color: #92400e;
        }

        .stat-status.danger {
            background: var(--danger-light);
            color: var(--danger);
        }

        .stat-status.neutral {
            background: var(--primary-light);
            color: var(--primary);
        }

        .stat-val {
            font-size: 1.9rem;
            font-weight: 800;
            line-height: 1;
            color: var(--text);
            letter-spacing: -0.03em;
        }

        .stat-card.red .stat-val.alert {
            color: var(--danger);
        }

        .stat-card.green .stat-val {
            color: var(--success);
        }

        .stat-lbl {
            font-size: 11px;
            color: var(--muted);
            font-weight: 500;
            margin-top: 3px;
        }

        /* ═══════════════════════════════════════
           TABLE
        ═══════════════════════════════════════ */
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        th {
            background: #f8fafc;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: var(--muted);
            padding: 9px 14px;
            text-align: left;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #f8fafc;
        }

        code.kode {
            background: var(--bg);
            border: 1px solid var(--border);
            padding: 2px 7px;
            border-radius: 5px;
            font-size: 11.5px;
            color: var(--primary);
            font-family: 'JetBrains Mono', 'Fira Code', monospace;
        }

        /* ═══════════════════════════════════════
           BADGES
        ═══════════════════════════════════════ */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 10.5px;
            font-weight: 600;
            white-space: nowrap;
        }

        .badge-success {
            background: var(--success-light);
            color: #15803d;
        }

        .badge-danger {
            background: var(--danger-light);
            color: var(--danger);
        }

        .badge-warning {
            background: var(--warning-light);
            color: #92400e;
        }

        .badge-info {
            background: var(--info-light);
            color: #0e7490;
        }

        .badge-secondary {
            background: #f1f5f9;
            color: #475569;
        }

        .badge-primary {
            background: var(--primary-light);
            color: #1d4ed8;
        }

        /* ═══════════════════════════════════════
           BUTTONS
        ═══════════════════════════════════════ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all .18s;
            white-space: nowrap;
            font-family: inherit;
        }

        .btn-sm {
            padding: 5px 11px;
            font-size: 12px;
        }

        .btn-xs {
            padding: 4px 9px;
            font-size: 11.5px;
            border-radius: 6px;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-success {
            background: var(--success);
            color: #fff;
        }

        .btn-success:hover {
            background: #15803d;
        }

        .btn-danger {
            background: var(--danger);
            color: #fff;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        .btn-warning {
            background: var(--warning);
            color: #fff;
        }

        .btn-warning:hover {
            background: #b45309;
        }

        .btn-secondary {
            background: #f1f5f9;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover {
            background: var(--border);
        }

        .btn-dark {
            background: var(--text);
            color: #fff;
        }

        .btn-dark:hover {
            background: #0f172a;
        }

        /* ═══════════════════════════════════════
           FORMS
        ═══════════════════════════════════════ */
        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1.5px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-family: inherit;
            background: #fff;
            color: var(--text);
            transition: border-color .18s, box-shadow .18s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-mid);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .12);
        }

        .form-control.is-invalid {
            border-color: var(--danger);
        }

        .invalid-feedback {
            font-size: 11.5px;
            color: var(--danger);
            margin-top: 4px;
        }

        select.form-control {
            cursor: pointer;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 80px;
        }

        /* ═══════════════════════════════════════
           ALERTS
        ═══════════════════════════════════════ */
        .alert {
            padding: 11px 15px;
            border-radius: var(--radius-sm);
            font-size: 13px;
            margin-bottom: 16px;
            display: flex;
            align-items: flex-start;
            gap: 9px;
        }

        .alert-success {
            background: var(--success-light);
            color: #15803d;
            border: 1px solid #86efac;
        }

        .alert-danger {
            background: var(--danger-light);
            color: var(--danger);
            border: 1px solid #fca5a5;
        }

        .alert-warning {
            background: var(--warning-light);
            color: #92400e;
            border: 1px solid #fcd34d;
        }

        .alert-info {
            background: var(--info-light);
            color: #0e7490;
            border: 1px solid #67e8f9;
        }

        /* ═══════════════════════════════════════
           PAGINATION
        ═══════════════════════════════════════ */
        .pagination {
            display: flex;
            gap: 4px;
            align-items: center;
            justify-content: flex-end;
            margin-top: 14px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 5px 11px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 500;
            border: 1px solid var(--border);
            color: var(--text);
            text-decoration: none;
        }

        .pagination a:hover {
            background: var(--primary-light);
            border-color: var(--primary-mid);
            color: var(--primary);
        }

        .pagination .active span {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }

        /* ═══════════════════════════════════════
           GRID LAYOUTS
        ═══════════════════════════════════════ */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 16px;
        }

        /* ═══════════════════════════════════════
           EMPTY STATE
        ═══════════════════════════════════════ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: var(--muted);
        }

        .empty-state i {
            font-size: 36px;
            margin-bottom: 10px;
            opacity: .35;
            display: block;
        }

        .empty-state p {
            font-size: 13px;
        }

        /* ═══════════════════════════════════════
           MODAL
        ═══════════════════════════════════════ */
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .45);
            z-index: 200;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .modal {
            background: var(--card);
            border-radius: 16px;
            width: 100%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            padding: 18px 22px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid var(--border);
        }

        .modal-header h4 {
            font-size: 15px;
            font-weight: 700;
        }

        .modal-body {
            padding: 18px 22px;
        }

        .modal-footer {
            padding: 14px 22px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 9px;
            justify-content: flex-end;
        }

        .btn-close {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 17px;
            color: var(--muted);
            transition: color .15s;
        }

        .btn-close:hover {
            color: var(--text);
        }

        /* ═══════════════════════════════════════
           DIVIDER
        ═══════════════════════════════════════ */
        .divider {
            border: none;
            border-top: 1px solid var(--border);
            margin: 14px 0;
        }

        /* ═══════════════════════════════════════
           RESPONSIVE — TABLET (≤1100px)
        ═══════════════════════════════════════ */
        @media (max-width: 1100px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .grid-3 {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* ═══════════════════════════════════════
           RESPONSIVE — MOBILE (≤768px)
        ═══════════════════════════════════════ */
        @media (max-width: 768px) {
            :root {
                --sidebar-w: 256px;
            }

            .sidebar {
                transform: translateX(-100%);
                box-shadow: var(--shadow-lg);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-wrap {
                margin-left: 0;
            }

            .topbar {
                padding: 0 16px;
            }

            .topbar-menu-btn {
                display: flex;
            }

            .topbar-date {
                display: none;
            }

            .content {
                padding: 16px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }

            .stat-card {
                padding: 13px 12px 11px;
                gap: 9px;
            }

            .stat-card .stat-icon {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }

            .stat-val {
                font-size: 1.55rem;
            }

            .stat-lbl {
                font-size: 10px;
            }

            .stat-status {
                font-size: 9px;
                padding: 2px 6px;
            }

            .grid-2,
            .grid-3 {
                grid-template-columns: 1fr;
            }

            .card-header {
                padding: 12px 14px;
                flex-wrap: wrap;
                gap: 8px;
            }

            .card-body {
                padding: 14px;
            }

            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 8px 10px;
            }
        }

        /* ═══════════════════════════════════════
           RESPONSIVE — SMALL MOBILE (≤400px)
        ═══════════════════════════════════════ */
        @media (max-width: 400px) {
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .stat-val {
                font-size: 1.35rem;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    <!-- Overlay mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    <!-- ═══════════ SIDEBAR ═══════════ -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-icon">
                <i class="fa-solid fa-warehouse"></i>
            </div>
            <div class="sidebar-brand-text">
                <h1>GudangKu</h1>
                <span>Manajemen Gudang</span>
            </div>
        </div>

        <div class="sidebar-section">Utama</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge-high"></i> Dashboard
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Master Data</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('barang.index') }}" class="{{ request()->routeIs('barang.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-boxes-stacked"></i> Data Barang
                </a>
            </li>
            <li>
                <a href="{{ route('pekerjaan.index') }}"
                    class="{{ request()->routeIs('pekerjaan.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-hard-hat"></i> Pekerjaan
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Transaksi</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('barang-masuk.index') }}"
                    class="{{ request()->routeIs('barang-masuk.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-truck-ramp-box"></i> Barang Masuk
                </a>
            </li>
            <li>
                <a href="{{ route('barang-keluar.index') }}"
                    class="{{ request()->routeIs('barang-keluar.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-right-from-bracket"></i> Barang Keluar
                </a>
            </li>
        </ul>

        <div class="sidebar-section">Laporan</div>
        <ul class="sidebar-nav">
            <li>
                <a href="{{ route('laporan.stok') }}"
                    class="{{ request()->routeIs('laporan.stok') ? 'active' : '' }}">
                    <i class="fa-solid fa-clipboard-list"></i> Stok Terkini
                </a>
            </li>
            <li>
                <a href="{{ route('laporan.masuk') }}"
                    class="{{ request()->routeIs('laporan.masuk') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-import"></i> Barang Masuk
                </a>
            </li>
            <li>
                <a href="{{ route('laporan.keluar') }}"
                    class="{{ request()->routeIs('laporan.keluar') ? 'active' : '' }}">
                    <i class="fa-solid fa-file-export"></i> Barang Keluar
                </a>
            </li>
            <li>
                <a href="{{ route('laporan.rekap') }}"
                    class="{{ request()->routeIs('laporan.rekap') ? 'active' : '' }}">
                    <i class="fa-solid fa-folder-open"></i> Rekap Pekerjaan
                </a>
            </li>
            <li>
                <a href="{{ route('laporan.statistik') }}"
                    class="{{ request()->routeIs('laporan.statistik') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i> Statistik
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>Admin Gudang</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-secondary"
                    style="width:100%; justify-content:center; font-size:12.5px;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- ═══════════ MAIN ═══════════ -->
    <div class="main-wrap">
        <div class="topbar">
            <div class="topbar-left">
                <button class="topbar-menu-btn" onclick="toggleSidebar()" aria-label="Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="topbar-title">@yield('title', 'Dashboard')</div>
            </div>
            <div class="topbar-right">
                <span class="topbar-date">
                    <i class="fa-regular fa-calendar" style="margin-right:4px; color:var(--muted);"></i>
                    {{ now()->isoFormat('dddd, D MMMM Y') }}
                </span>
            </div>
        </div>

        <div class="content">
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if (session('error') || $errors->any())
                <div class="alert alert-danger">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <div>
                        @if (session('error'))
                            {{ session('error') }}
                        @endif
                        @if ($errors->any())
                            <ul style="margin:0; padding-left:16px;">
                                @foreach ($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('show');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('show');
        }
    </script>
    @stack('scripts')
</body>

</html>
