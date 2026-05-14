@extends('layouts.app')
@section('title', 'Barang Masuk')

@push('styles')
    <style>
        /* ── FILTER BAR ── */
        .filter-bar {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            background: #fafbfc;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        /* Filter Form - Default: Desktop sejajar horizontal */
        .filter-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-form .form-control {
            height: 34px;
            padding: 0 10px;
            font-size: 13px;
            box-sizing: border-box;
        }

        .search-input {
            flex: 2;
            min-width: 0;
        }

        .select-kategori,
        .date-input {
            flex: 1;
            min-width: 0;
            max-width: 150px;
        }

        /* Styling khusus date input */
        .date-input {
            color: var(--text);
            -webkit-appearance: none;
            appearance: none;
            min-height: 34px;
        }

        .date-input:not(:valid) {
            color: var(--muted);
        }

        .date-input:valid {
            color: var(--text);
        }

        .filter-form .btn {
            flex-shrink: 0;
            white-space: nowrap;
            height: 34px;
            padding: 0 14px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .btn-reset {
            flex-shrink: 0;
        }

        /* ── TABLE WRAP ── */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap table {
            width: 100%;
            min-width: 800px;
            border-collapse: collapse;
        }

        /* ── TABLET (≤768px) ── */
        @media (max-width: 768px) {
            .filter-form {
                flex-wrap: wrap;
                gap: 8px;
            }

            .search-input {
                flex: 1 1 100%;
            }

            .select-kategori {
                flex: 1 1 calc(50% - 4px);
                max-width: none;
            }

            .date-input {
                flex: 1 1 calc(50% - 4px);
                max-width: none;
            }

            .filter-form .btn {
                flex: 1 1 auto;
            }
        }

        /* ── HP (≤480px) ── */
        @media (max-width: 480px) {
            .filter-form {
                gap: 6px;
            }

            .search-input {
                flex: 1 1 100%;
            }

            .search-input-mobile {
                flex: 1 1 calc(50% - 3px);
                max-width: none;
            }

            .select-kategori {
                flex: 1 1 calc(50% - 3px);
                max-width: none;
            }

            .date-input {
                flex: 1 1 calc(50% - 3px);
                max-width: none;
            }

            /* Filter melebar, Reset kecil di kanan — satu baris */
            .btn-filter {
                flex: 1 1 auto;
                justify-content: center;
            }

            .filter-form .btn-reset {
                flex: 0 0 auto;
                padding: 0 10px;
                font-size: 11px;
                height: 28px;
                align-self: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-truck-ramp-box" style="color: var(--success); margin-right: 8px;"></i>Riwayat Barang
                Masuk</h3>
            <a href="{{ route('barang-masuk.create') }}" class="btn btn-success">
                <i class="fa-solid fa-plus"></i> Input Barang
            </a>
        </div>

        {{-- FILTER --}}
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                {{-- Search Desktop --}}
                <input type="text" name="search" class="form-control search-input search-desktop"
                    placeholder="Cari nama barang / no transaksi..." value="{{ request('search') }}" autocomplete="off">

                {{-- Search Mobile --}}
                <input type="text" name="search" class="form-control search-input-mobile" placeholder="Cari..."
                    value="{{ request('search') }}" autocomplete="off" style="display: none;">

                <select name="kategori" class="form-control select-kategori">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>

                <input type="date" name="dari" class="form-control date-input" value="{{ request('dari') }}"
                    title="Dari tanggal">
                <input type="date" name="sampai" class="form-control date-input" value="{{ request('sampai') }}"
                    title="Sampai tanggal">

                <button type="submit" class="btn btn-primary btn-filter">
                    <i class="fa-solid fa-search"></i> <span class="btn-text">Cari</span>
                </button>
                @if (request()->anyFilled(['search', 'kategori', 'dari', 'sampai']))
                    <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary btn-reset">Reset</a>
                @endif
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Stok Sebelum</th>
                        <th>Stok Sesudah</th>
                        <th>Sumber</th>
                        <th>Diinput Oleh</th>
                        <th>Total Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangMasuk as $bm)
                        <tr>
                            <td><code
                                    style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $bm->no_transaksi }}</code>
                            </td>
                            <td>{{ $bm->tanggal->format('d/m/Y') }}</td>
                            <td style="font-weight: 600;">{{ $bm->barang->nama_barang }}</td>
                            <td><span
                                    class="badge badge-{{ $bm->barang->kategori_badge }}">{{ strtoupper($bm->barang->kategori) }}</span>
                            </td>
                            <td><span style="color: var(--success); font-weight: 700;">+{{ $bm->jumlah }}</span>
                                {{ $bm->barang->satuan }}</td>
                            <td style="color: var(--muted);">{{ $bm->stok_sebelum }}</td>
                            <td style="font-weight: 600;">{{ $bm->stok_sesudah }}</td>
                            <td style="color: var(--muted);">{{ $bm->sumber ?? '-' }}</td>
                            <td style="font-size:13px;">{{ $bm->created_by_name ?? '—' }}</td>
                            <td>
                                @if ($bm->barang->kategori === 'tools')
                                    <span style="font-size: 12px; color: var(--muted); font-style: italic;">
                                        Tidak digunakan
                                    </span>
                                @else
                                    <div style="font-weight: 600;">
                                        Rp {{ number_format($bm->harga_satuan, 0, ',', '.') }}
                                        <div style="font-size: 11px; color: var(--muted);">
                                            Total: Rp {{ number_format($bm->jumlah * $bm->harga_satuan, 0, ',', '.') }}
                                        </div>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('barang-masuk.destroy', $bm) }}"
                                    onsubmit="return confirm('Hapus & rollback stok?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus & rollback stok">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state"><i class="fa-solid fa-truck-ramp-box"></i>
                                    <p>Belum ada data barang masuk</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body" style="padding-top: 12px;">
            {{ $barangMasuk->links('vendor.pagination.custom') }}
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Tampilkan/sembunyikan search input berdasarkan ukuran layar
        function handleSearchVisibility() {
            const searchDesktop = document.querySelector('.search-desktop');
            const searchMobile = document.querySelector('.search-input-mobile');

            if (window.innerWidth <= 480) {
                searchDesktop.style.display = 'none';
                searchMobile.style.display = 'block';
            } else {
                searchDesktop.style.display = 'block';
                searchMobile.style.display = 'none';
            }
        }

        // Sinkronkan nilai search
        function syncSearchValues() {
            const searchDesktop = document.querySelector('.search-desktop');
            const searchMobile = document.querySelector('.search-input-mobile');

            searchDesktop.addEventListener('input', function() {
                searchMobile.value = this.value;
            });

            searchMobile.addEventListener('input', function() {
                searchDesktop.value = this.value;
            });
        }

        window.addEventListener('load', function() {
            handleSearchVisibility();
            syncSearchValues();
        });
        window.addEventListener('resize', handleSearchVisibility);
    </script>
@endpush
