@extends('layouts.app')
@section('title', 'Statistik & Grafik')

@push('styles')
    <style>
        /* ── PAGE HEADER ── */
        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
        }

        .page-header h2 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.3px;
            line-height: 1.2;
        }

        .page-header p {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 3px;
        }

        /* ── PERIODE PILLS ── */
        .periode-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .periode-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-right: 4px;
        }

        /* ── CHART CONTAINERS ── */
        .chart-container {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 6;
            min-height: 180px;
            max-height: 280px;
        }

        .chart-container canvas {
            position: absolute;
            inset: 0;
            width: 100% !important;
            height: 100% !important;
        }

        /* ── DISTRIBUSI SECTION ── */
        .distribusi-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .distribusi-donut {
            position: relative;
            flex-shrink: 0;
            width: 150px;
            height: 150px;
        }

        .distribusi-donut canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .distribusi-list {
            flex: 1;
            min-width: 0;
        }

        .distribusi-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 0;
            border-bottom: 1px solid #f1f5f9;
            gap: 10px;
        }

        .distribusi-row:last-child {
            border-bottom: none;
        }

        .distribusi-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .distribusi-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .distribusi-label {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--text-soft, #334155);
        }

        .distribusi-right {
            text-align: right;
        }

        .distribusi-count {
            font-size: 15px;
            font-weight: 800;
            color: var(--text);
            line-height: 1;
        }

        .distribusi-stok {
            font-size: 10.5px;
            color: var(--muted);
            margin-top: 1px;
        }

        /* ── TOP BARANG BAR CHART ── */
        .top-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 7px 0;
            border-bottom: 1px solid #f8fafc;
        }

        .top-item:last-child {
            border-bottom: none;
        }

        .top-rank {
            font-size: 11px;
            font-weight: 800;
            color: var(--muted);
            width: 18px;
            text-align: center;
            flex-shrink: 0;
        }

        .top-rank.gold {
            color: #f59e0b;
        }

        .top-rank.silver {
            color: #94a3b8;
        }

        .top-rank.bronze {
            color: #b45309;
        }

        .top-bar-wrap {
            flex: 1;
            min-width: 0;
        }

        .top-name {
            font-size: 13px;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 5px;
        }

        .top-bar-track {
            height: 5px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
        }

        .top-bar-fill {
            height: 100%;
            border-radius: 10px;
            background: var(--danger);
            transition: width .6s cubic-bezier(.4, 0, .2, 1);
        }

        .top-val {
            font-size: 13px;
            font-weight: 800;
            color: var(--danger);
            flex-shrink: 0;
            min-width: 28px;
            text-align: right;
        }

        .top-sat {
            font-size: 11px;
            color: var(--muted);
            flex-shrink: 0;
        }

        /* ── STOK MENIPIS TABLE ── */
        .deficit-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            font-size: 12px;
            font-weight: 700;
            color: var(--danger);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 1100px) {
            .grid-2 {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .chart-container {
                aspect-ratio: 16/8;
                max-height: 220px;
            }

            .distribusi-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }

            .distribusi-donut {
                width: 120px;
                height: 120px;
            }

            .top-sat {
                display: none;
            }
        }

        @media (max-width: 540px) {
            .chart-container {
                aspect-ratio: 4/3;
                max-height: 200px;
            }
        }
    </style>
@endpush

@section('content')

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div>
            <h2>Statistik & Grafik</h2>
            <p>Ringkasan aktivitas dan performa gudang</p>
        </div>
        <div class="periode-bar" style="margin-bottom: 0;">
            <span class="periode-label">Periode</span>
            @foreach ([7 => '7 Hari', 30 => '30 Hari', 90 => '3 Bulan'] as $val => $lbl)
                <a href="{{ route('laporan.statistik', ['periode' => $val]) }}"
                    class="btn btn-sm {{ $periode == $val ? 'btn-primary' : 'btn-secondary' }}">
                    {{ $lbl }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- ═══ CHARTS ROW 1 ═══ --}}
    <div class="grid-2" style="margin-bottom: 16px;">

        {{-- Trend Masuk vs Keluar --}}
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-chart-column" style="color: var(--primary-mid);"></i>
                    Trend Masuk vs Keluar
                </h3>
                <div style="display:flex; gap:10px;">
                    <span
                        style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:#15803d;">
                        <span
                            style="width:8px; height:8px; border-radius:50%; background:#22c55e; display:inline-block;"></span>
                        Masuk
                    </span>
                    <span
                        style="display:flex; align-items:center; gap:5px; font-size:11px; font-weight:700; color:var(--danger);">
                        <span
                            style="width:8px; height:8px; border-radius:50%; background:var(--danger); display:inline-block;"></span>
                        Keluar
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartTrend"></canvas>
                </div>
            </div>
        </div>

        {{-- Distribusi Kategori --}}
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-chart-pie" style="color: var(--accent);"></i>
                    Distribusi per Kategori
                </h3>
            </div>
            <div class="card-body">
                <div class="distribusi-section">
                    <div class="distribusi-donut">
                        <canvas id="chartDistribusi"></canvas>
                    </div>
                    <div class="distribusi-list">
                        @php $distColors = ['cons' => '#f59e0b', 'material' => '#38bdf8', 'tools' => '#22c55e']; @endphp
                        @foreach ($distribusiKategori as $dk)
                            <div class="distribusi-row">
                                <div class="distribusi-left">
                                    <div class="distribusi-dot"
                                        style="background: {{ $distColors[$dk->kategori] ?? '#94a3b8' }};"></div>
                                    <span class="distribusi-label">{{ $dk->kategori }}</span>
                                </div>
                                <div class="distribusi-right">
                                    <div class="distribusi-count">{{ $dk->count }} item</div>
                                    <div class="distribusi-stok">Stok: {{ number_format($dk->total_stok) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ CHARTS ROW 2 ═══ --}}
    <div class="grid-2" style="margin-bottom: 16px;">

        {{-- Top 10 Barang Keluar --}}
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-arrow-trend-up" style="color: var(--danger);"></i>
                    Top 10 Barang Paling Sering Keluar
                </h3>
            </div>
            @if ($topKeluar->isEmpty())
                <div class="empty-state">
                    <i class="fa-solid fa-chart-bar"></i>
                    <p>Belum ada data pada periode ini</p>
                </div>
            @else
                <div class="card-body" style="padding-top: 10px; padding-bottom: 10px;">
                    @foreach ($topKeluar as $i => $tk)
                        <div class="top-item">
                            <span
                                class="top-rank {{ $i == 0 ? 'gold' : ($i == 1 ? 'silver' : ($i == 2 ? 'bronze' : '')) }}">
                                {{ $i + 1 }}
                            </span>
                            <div class="top-bar-wrap">
                                <div class="top-name">{{ $tk->barang->nama_barang }}</div>
                                <div class="top-bar-track">
                                    <div class="top-bar-fill"
                                        style="width: {{ min(($tk->total_keluar / $topKeluar->first()->total_keluar) * 100, 100) }}%;">
                                    </div>
                                </div>
                            </div>
                            <span class="top-val">{{ $tk->total_keluar }}</span>
                            <span class="top-sat">{{ $tk->barang->satuan }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tools Dipinjam --}}
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-screwdriver-wrench" style="color: var(--accent);"></i>
                    Tools Sedang Dipinjam
                </h3>
                @if ($toolsDipinjam->isNotEmpty())
                    <span class="badge badge-warning">{{ $toolsDipinjam->count() }} aktif</span>
                @endif
            </div>
            @if ($toolsDipinjam->isEmpty())
                <div class="empty-state">
                    <i class="fa-solid fa-circle-check" style="color: var(--success); opacity:1;"></i>
                    <p>Semua tools sudah kembali!</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tools</th>
                                <th>Pekerjaan</th>
                                <th>PIC</th>
                                <th>Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($toolsDipinjam as $t)
                                <tr>
                                    <td>
                                        <div class="item-name">{{ $t->barang->nama_barang }}</div>
                                        <div class="item-sub">×{{ $t->jumlah }} {{ $t->barang->satuan }}</div>
                                    </td>
                                    <td class="item-sub">{{ $t->pekerjaan->nama_pekerjaan }}</td>
                                    <td style="font-size: 12px;">{{ $t->pekerjaan->nama_peminjam }}</td>
                                    <td
                                        style="font-size: 12px; {{ $t->isTerlambat() ? 'color:var(--danger); font-weight:700;' : 'color:var(--muted);' }}">
                                        {{ $t->tgl_kembali_rencana?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $t->isTerlambat() ? 'badge-danger' : 'badge-warning' }}">
                                            {{ $t->isTerlambat() ? 'TERLAMBAT' : 'AKTIF' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- ═══ STOK MENIPIS ═══ --}}
    @if ($stokMenipis->isNotEmpty())
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-triangle-exclamation" style="color: var(--warning);"></i>
                    Daftar Stok Menipis
                </h3>
                <div style="display:flex; align-items:center; gap:10px;">
                    <span class="badge badge-warning">{{ $stokMenipis->count() }} item</span>
                    <a href="{{ route('laporan.stok', ['status' => 'menipis']) }}" class="btn btn-xs btn-secondary">
                        <i class="fa-solid fa-arrow-right"></i> Laporan Lengkap
                    </a>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Kategori</th>
                            <th style="width:70px;">Stok</th>
                            <th style="width:60px;">Min</th>
                            <th style="width:80px;">Defisit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stokMenipis as $b)
                            <tr>
                                <td>
                                    <div class="item-name">{{ $b->nama_barang }}</div>
                                    <div class="item-sub">{{ $b->kode_barang }}</div>
                                </td>
                                <td>
                                    @php
                                        $badgeKat = match ($b->kategori) {
                                            'cons' => 'badge-warning',
                                            'material' => 'badge-info',
                                            'tools' => 'badge-success',
                                            default => 'badge-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $badgeKat }}">{{ strtoupper($b->kategori) }}</span>
                                </td>
                                <td>
                                    <span class="stok-num"
                                        style="color: {{ $b->stok == 0 ? 'var(--danger)' : 'var(--warning)' }};">
                                        {{ $b->stok }}
                                    </span>
                                </td>
                                <td class="item-sub">{{ $b->stok_minimum }}</td>
                                <td>
                                    <span class="deficit-badge">
                                        <i class="fa-solid fa-arrow-down" style="font-size:9px;"></i>
                                        {{ $b->stok_minimum - $b->stok }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        // ── Chart Trend (Bar) ──
        new Chart(document.getElementById('chartTrend'), {
            type: 'bar',
            data: {
                labels: @json($labels),
                datasets: [{
                        label: 'Masuk',
                        data: @json($dataMasuk),
                        backgroundColor: 'rgba(34,197,94,.75)',
                        hoverBackgroundColor: 'rgba(34,197,94,1)',
                        borderRadius: 5,
                        borderSkipped: false,
                    },
                    {
                        label: 'Keluar',
                        data: @json($dataKeluar),
                        backgroundColor: 'rgba(220,38,38,.75)',
                        hoverBackgroundColor: 'rgba(220,38,38,1)',
                        borderRadius: 5,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: {
                            family: "'Plus Jakarta Sans'",
                            size: 12,
                            weight: '700'
                        },
                        bodyFont: {
                            family: "'Plus Jakarta Sans'",
                            size: 11
                        },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} item`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: {
                                family: "'Plus Jakarta Sans'",
                                size: 11
                            },
                            color: '#94a3b8',
                            padding: 6,
                        },
                        grid: {
                            color: '#f1f5f9'
                        },
                        border: {
                            display: false
                        },
                    },
                    x: {
                        ticks: {
                            font: {
                                family: "'Plus Jakarta Sans'",
                                size: 11
                            },
                            color: '#94a3b8',
                            maxRotation: 0,
                        },
                        grid: {
                            display: false
                        },
                        border: {
                            display: false
                        },
                    }
                },
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                barPercentage: 0.6,
                categoryPercentage: 0.7,
            }
        });

        // ── Chart Distribusi (Donut) ──
        const distData = @json($distribusiKategori);
        const distColors = {
            cons: '#f59e0b',
            material: '#38bdf8',
            tools: '#22c55e'
        };

        new Chart(document.getElementById('chartDistribusi'), {
            type: 'doughnut',
            data: {
                labels: distData.map(d => d.kategori.toUpperCase()),
                datasets: [{
                    data: distData.map(d => d.count),
                    backgroundColor: distData.map(d => distColors[d.kategori] ?? '#94a3b8'),
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: {
                            family: "'Plus Jakarta Sans'",
                            size: 12,
                            weight: '700'
                        },
                        bodyFont: {
                            family: "'Plus Jakarta Sans'",
                            size: 11
                        },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: ctx => ` ${ctx.label}: ${ctx.parsed} item`
                        }
                    }
                },
                cutout: '68%',
            }
        });
    </script>
@endpush
