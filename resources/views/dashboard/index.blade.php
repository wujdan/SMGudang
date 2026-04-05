@extends('layouts.app')
@section('title', 'Dashboard')

@push('styles')
    <style>
        /* ── CHART AKTIVITAS ── */
        .chart-container {
            position: relative;
            width: 100%;
            aspect-ratio: 16 / 5;
            min-height: 160px;
            max-height: 260px;
        }

        .chart-container canvas {
            position: absolute;
            inset: 0;
            width: 100% !important;
            height: 100% !important;
        }

        .chart-summary {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .summary-chip.green {
            background: #dcfce7;
            color: #15803d;
        }

        .summary-chip.red {
            background: #fee2e2;
            color: var(--danger);
        }

        .summary-chip .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
        }

        .summary-chip.green .dot {
            background: #22c55e;
        }

        .summary-chip.red .dot {
            background: #ef4444;
        }

        .chart-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            padding: 10px 18px 14px;
            border-top: 1px solid var(--border);
        }

        .range-pill {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background: transparent;
            color: var(--muted);
            font-family: inherit;
            transition: background .15s, color .15s;
        }

        .range-pill:hover {
            background: var(--bg);
            color: var(--text);
        }

        .range-pill.active {
            background: var(--primary-light);
            color: var(--primary);
        }

        /* ── CHART KATEGORI ── */
        .kategori-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .chart-donut-wrap {
            position: relative;
            flex-shrink: 0;
            width: 150px;
            height: 150px;
        }

        .chart-donut-wrap canvas {
            width: 100% !important;
            height: 100% !important;
        }

        .legend-list {
            flex: 1;
            min-width: 0;
        }

        .legend-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid #f1f5f9;
            gap: 8px;
        }

        .legend-row:last-child {
            border-bottom: none;
        }

        .legend-left {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .legend-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .legend-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .legend-right {
            text-align: right;
        }

        .legend-num {
            font-size: 15px;
            font-weight: 800;
            line-height: 1;
            color: var(--text);
        }

        .legend-sub {
            font-size: 10.5px;
            color: var(--muted);
        }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .card-header {
                flex-wrap: wrap;
            }

            .chart-summary {
                width: 100%;
            }

            .chart-container {
                aspect-ratio: 16/7;
                max-height: 200px;
            }

            .kategori-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }

            .chart-donut-wrap {
                width: 120px;
                height: 120px;
            }
        }

        @media (max-width: 400px) {
            .chart-container {
                aspect-ratio: 4/3;
                max-height: 180px;
            }
        }
    </style>
@endpush

@section('content')

    {{-- ═══ STAT CARDS ═══ --}}
    <div class="stats-grid">

        <div class="stat-card blue">
            <div class="stat-top">
                <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <span class="stat-status neutral"><i class="fa-solid fa-layer-group"></i> Tercatat</span>
            </div>
            <div class="stat-bottom">
                <div class="stat-val">{{ number_format($totalBarang ?? 0) }}</div>
                <div class="stat-lbl">Total Item Barang</div>
            </div>
        </div>

        <div class="stat-card amber">
            <div class="stat-top">
                <div class="stat-icon"><i class="fa-solid fa-screwdriver-wrench"></i></div>
                <span class="stat-status warn"><i class="fa-solid fa-circle-dot"></i> Aktif</span>
            </div>
            <div class="stat-bottom">
                <div class="stat-val">{{ $toolsDipinjam }}</div>
                <div class="stat-lbl">Tools Dipinjam</div>
            </div>
        </div>

        <div class="stat-card red">
            <div class="stat-top">
                <div class="stat-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                @if ($stokMenipis > 0)
                    <span class="stat-status danger"><i class="fa-solid fa-circle-exclamation"></i> Restock</span>
                @else
                    <span class="stat-status ok"><i class="fa-solid fa-check"></i> Aman</span>
                @endif
            </div>
            <div class="stat-bottom">
                <div class="stat-val {{ $stokMenipis > 0 ? 'alert' : '' }}">{{ $stokMenipis }}</div>
                <div class="stat-lbl">Stok Menipis</div>
            </div>
        </div>

        <div class="stat-card {{ $toolsTerlambat > 0 ? 'red' : 'green' }}">
            <div class="stat-top">
                <div class="stat-icon"><i class="fa-solid fa-clock-rotate-left"></i></div>
                @if ($toolsTerlambat > 0)
                    <span class="stat-status danger"><i class="fa-solid fa-circle-exclamation"></i> Terlambat</span>
                @else
                    <span class="stat-status ok"><i class="fa-solid fa-check"></i> Aman</span>
                @endif
            </div>
            <div class="stat-bottom">
                <div class="stat-val {{ $toolsTerlambat > 0 ? 'alert' : '' }}">{{ $toolsTerlambat }}</div>
                <div class="stat-lbl">Tools Terlambat</div>
            </div>
        </div>

    </div>

    {{-- ═══ CHARTS ═══ --}}
    <div class="grid-2" style="margin-bottom: 16px;">

        {{-- Aktivitas --}}
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-chart-area" style="color: var(--primary-mid);"></i>
                    Aktivitas 7 Hari Terakhir
                </h3>
                <div class="chart-summary">
                    <span class="summary-chip green" id="chipMasuk">
                        <span class="dot"></span> Masuk: {{ array_sum($dataMasuk) }}
                    </span>
                    <span class="summary-chip red" id="chipKeluar">
                        <span class="dot"></span> Keluar: {{ array_sum($dataKeluar) }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="chartAktivitas"></canvas>
                </div>
            </div>
            <div class="chart-footer">
                <button class="range-pill active" onclick="setRange(this, 7)">7H</button>
                <button class="range-pill" onclick="setRange(this, 14)">14H</button>
                <button class="range-pill" onclick="setRange(this, 30)">30H</button>
            </div>
        </div>

        {{-- Stok per Kategori --}}
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-chart-pie" style="color: var(--accent);"></i>
                    Stok per Kategori
                </h3>
            </div>
            <div class="card-body">
                <div class="kategori-section">
                    <div class="chart-donut-wrap">
                        <canvas id="chartKategori"></canvas>
                    </div>
                    <div class="legend-list">
                        @php $donutColors = ['#f59e0b','#38bdf8','#22c55e','#a78bfa','#f87171']; @endphp
                        @foreach ($stokKategori as $sk)
                            <div class="legend-row">
                                <div class="legend-left">
                                    <div class="legend-dot" style="background: {{ $donutColors[$loop->index % 5] }};"></div>
                                    <span class="legend-label">{{ $sk->kategori }}</span>
                                </div>
                                <div class="legend-right">
                                    <div class="legend-num">{{ number_format($sk->total_stok) }}</div>
                                    <div class="legend-sub">{{ $sk->jumlah_item }} item</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ ALERTS: Stok Menipis + Tools Dipinjam ═══ --}}
    <div class="grid-2" style="margin-bottom: 16px;">

        {{-- Stok Menipis --}}
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-triangle-exclamation" style="color: var(--warning);"></i>
                    Stok Menipis
                </h3>
                <a href="{{ route('laporan.stok', ['status' => 'menipis']) }}" class="btn btn-xs btn-secondary">
                    <i class="fa-solid fa-arrow-right"></i> Semua
                </a>
            </div>
            @if ($barangMenipis->isEmpty())
                <div class="empty-state">
                    <i class="fa-solid fa-circle-check" style="color: var(--success); opacity: 1;"></i>
                    <p>Semua stok aman!</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th>Kat.</th>
                                <th>Stok</th>
                                <th>Min</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($barangMenipis as $b)
                                <tr>
                                    <td>
                                        <div class="item-name">{{ $b->nama_barang }}</div>
                                        <div class="item-sub">{{ $b->kode_barang }}</div>
                                    </td>
                                    <td>
                                        <span class="badge badge-secondary">{{ strtoupper($b->kategori) }}</span>
                                    </td>
                                    <td>
                                        <span class="stok-num"
                                            style="color: {{ $b->stok == 0 ? 'var(--danger)' : 'var(--warning)' }};">
                                            {{ $b->stok }}
                                        </span>
                                        <span style="font-size: 11px; color: var(--muted);"> {{ $b->satuan }}</span>
                                    </td>
                                    <td style="color: var(--muted); font-size: 12px;">{{ $b->stok_minimum }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Tools Dipinjam --}}
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-screwdriver-wrench" style="color: var(--accent);"></i>
                    Tools Dipinjam
                </h3>
                <a href="{{ route('laporan.keluar', ['status' => 'dipinjam']) }}" class="btn btn-xs btn-secondary">
                    <i class="fa-solid fa-arrow-right"></i> Semua
                </a>
            </div>
            @if ($toolsAktif->isEmpty())
                <div class="empty-state">
                    <i class="fa-solid fa-circle-check" style="color: var(--success); opacity: 1;"></i>
                    <p>Tidak ada tools yang dipinjam</p>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Tools</th>
                                <th>Peminjam</th>
                                <th>Kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($toolsAktif as $t)
                                <tr>
                                    <td>
                                        <div class="item-name">{{ $t->barang->nama_barang }}</div>
                                        <div class="item-sub">×{{ $t->jumlah }} {{ $t->barang->satuan }}</div>
                                    </td>
                                    <td style="font-size: 13px;">{{ $t->pekerjaan->nama_peminjam }}</td>
                                    <td
                                        style="font-size: 12px; {{ $t->isTerlambat() ? 'color:var(--danger); font-weight:700;' : 'color:var(--muted);' }}">
                                        {{ $t->tgl_kembali_rencana?->format('d/m/Y') ?? '-' }}
                                    </td>
                                    <td>
                                        <span class="badge {{ $t->isTerlambat() ? 'badge-danger' : 'badge-warning' }}">
                                            {{ $t->isTerlambat() ? 'TERLAMBAT' : 'DIPINJAM' }}
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

    {{-- ═══ PEKERJAAN AKTIF ═══ --}}
    <div class="card">
        <div class="card-header">
            <h3>
                <i class="fa-solid fa-helmet-safety" style="color: var(--primary-mid);"></i>
                Pekerjaan Aktif
            </h3>
            <a href="{{ route('pekerjaan.create') }}" class="btn btn-sm btn-dark">
                <i class="fa-solid fa-plus"></i> Pekerjaan Baru
            </a>
        </div>
        @if ($pekerjaanAktif->isEmpty())
            <div class="empty-state">
                <i class="fa-solid fa-helmet-safety"></i>
                <p>Belum ada pekerjaan aktif</p>
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Pekerjaan</th>
                            <th>PIC</th>
                            <th>Lokasi</th>
                            <th>Tgl Mulai</th>
                            <th>Item</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pekerjaanAktif as $p)
                            <tr>
                                <td><code class="kode">{{ $p->kode_pekerjaan }}</code></td>
                                <td><span class="item-name">{{ $p->nama_pekerjaan }}</span></td>
                                <td style="font-size: 13px;">{{ $p->nama_peminjam }}</td>
                                <td class="item-sub">{{ $p->lokasi ?? '-' }}</td>
                                <td class="item-sub">{{ $p->tanggal_mulai->format('d/m/Y') }}</td>
                                <td><span class="badge badge-info">{{ $p->transaksi_count }} item</span></td>
                                <td>
                                    <a href="{{ route('pekerjaan.show', $p) }}" class="btn btn-xs btn-primary">
                                        Detail <i class="fa-solid fa-arrow-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection

@push('scripts')
    <script>
        // ── Data dari controller ──
        const _labels7 = @json($labels);
        const _masuk7 = @json($dataMasuk);
        const _keluar7 = @json($dataKeluar);

        // ── Chart Aktivitas ──
        const chartAktivitas = new Chart(document.getElementById('chartAktivitas'), {
            type: 'line',
            data: {
                labels: _labels7,
                datasets: [{
                        label: 'Masuk',
                        data: _masuk7,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,.08)',
                        fill: true,
                        tension: .4,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#22c55e',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    },
                    {
                        label: 'Keluar',
                        data: _keluar7,
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239,68,68,.08)',
                        fill: true,
                        tension: .4,
                        borderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        pointBackgroundColor: '#ef4444',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // ← PENTING: ikuti ukuran .chart-container
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
            }
        });

        // ── Range filter — fetch data baru via AJAX ──
        function setRange(btn, days) {
            document.querySelectorAll('.range-pill').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            fetch(`/dashboard/aktivitas?days=${days}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(r => r.json())
                .then(data => {
                    chartAktivitas.data.labels = data.labels;
                    chartAktivitas.data.datasets[0].data = data.masuk;
                    chartAktivitas.data.datasets[1].data = data.keluar;
                    chartAktivitas.update('active');

                    const sumMasuk = data.masuk.reduce((a, b) => a + b, 0);
                    const sumKeluar = data.keluar.reduce((a, b) => a + b, 0);
                    document.getElementById('chipMasuk').innerHTML = `<span class="dot"></span> Masuk: ${sumMasuk}`;
                    document.getElementById('chipKeluar').innerHTML = `<span class="dot"></span> Keluar: ${sumKeluar}`;
                })
                .catch(() => chartAktivitas.update('active'));
        }

        // ── Chart Kategori Donut ──
        const kategoriData = @json($stokKategori);
        const donutColors = ['#f59e0b', '#38bdf8', '#22c55e', '#a78bfa', '#f87171'];

        new Chart(document.getElementById('chartKategori'), {
            type: 'doughnut',
            data: {
                labels: kategoriData.map(k => k.kategori),
                datasets: [{
                    data: kategoriData.map(k => k.jumlah_item),
                    backgroundColor: donutColors,
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false, // ← PENTING: ikuti ukuran .chart-donut-wrap
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
