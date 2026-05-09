<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Laporan PDF')</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 20px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #222;
        }

        h2 {
            text-align: center;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-size: 16px;
        }

        .subtitle {
            text-align: center;
            font-size: 12px;
            margin-top: -5px;
            margin-bottom: 15px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px 6px;
            text-align: center;
            text-transform: uppercase;
            font-size: 10px;
            font-weight: bold;
        }

        td {
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        td.text-left {
            text-align: left;
        }

        td.text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .badge-status {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-habis {
            color: red;
        }

        .status-menipis {
            color: orange;
        }

        .status-aman {
            color: green;
        }

        .footer {
            margin-top: 15px;
            width: 100%;
        }

        .info-bar {
            font-size: 10px;
            margin-bottom: 12px;
            padding: 8px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        .info-bar div {
            margin-bottom: 3px;
        }

        .signature {
            margin-top: 30px;
            width: 100%;
        }

        .no-border,
        .no-border td {
            border: none !important;
        }

        @stack('styles')
    </style>
</head>

<body>

    {{-- TITLE --}}
    <h2>@yield('heading', 'Laporan')</h2>

    {{-- SUBTITLE --}}
    <div class="subtitle">
        @yield('subtitle', 'GudangKu - Sistem Manajemen Gudang')
    </div>

    {{-- INFO BAR --}}
    <div class="info-bar">
        @yield('info-bar')

        @if (!View::hasSection('info-bar'))
            <div>
                <strong>Tanggal Cetak:</strong>
                {{ date('d-m-Y H:i:s') }}
            </div>
        @endif
    </div>

    {{-- CONTENT --}}
    @yield('content')

    {{-- FOOTER --}}
    <div class="signature">
        <table class="no-border" style="width: 100%;">
            <tr>
                <td style="width: 50%; text-align:left;">
                    <div style="font-size: 9px; color: #666;">
                        Dicetak pada:
                        {{ date('d/m/Y H:i:s') }}
                        <br>

                        Sistem:
                        @yield('system-name', 'GudangKu v1.0')
                    </div>
                </td>

                <td style="width: 50%; text-align: center;">
                    <div style="margin-bottom: 50px;">
                        @yield('signature-title', 'Mengetahui,')
                    </div>

                    <div style="margin-top: 5px;">
                        <div
                            style="
                            border-top: 1px solid black;
                            width: 150px;
                            margin: 0 auto;
                        ">
                        </div>

                        <div style="margin-top: 5px;">
                            @yield('signature-name', '(Admin Gudang)')
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
