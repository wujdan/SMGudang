@extends('layouts.app')
@section('title', 'Data Barang')

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
            color: var(--text);
            line-height: 1.2;
        }

        .page-header p {
            font-size: 12.5px;
            color: var(--muted);
            margin-top: 3px;
        }

        .page-header .actions {
            flex-shrink: 0;
        }

        /* ── FILTER BAR ── */
        .filter-bar {
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            background: #fafbfc;
            border-radius: var(--radius) var(--radius) 0 0;
        }

        /* Filter Form */
        .filter-form {
            display: flex;
            gap: 10px;
            margin-bottom: 0;
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

        .status-select {
            flex: 1;
            min-width: 0;
            max-width: 180px;
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

        /* ── TABLE WRAP ── */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap table {
            width: 100%;
            min-width: 520px;
            border-collapse: collapse;
        }

        /* ── ITEM PHOTO ── */
        .item-photo {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            object-fit: cover;
            border: 1px solid var(--border);
            flex-shrink: 0;
        }

        .item-photo-placeholder {
            width: 32px;
            height: 32px;
            border-radius: 7px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #cbd5e1;
            font-size: 13px;
            flex-shrink: 0;
            border: 1px solid var(--border);
        }

        .item-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .item-name {
            word-break: break-word;
        }

        /* ── STOK NUMBER ── */
        .stok-val {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 800;
            font-size: 15px;
            line-height: 1;
        }

        .stok-pinjam {
            font-size: 10.5px;
            color: var(--warning);
            margin-top: 2px;
        }

        /* ── ACTION BUTTONS ── */
        .action-group {
            display: flex;
            gap: 4px;
            align-items: center;
        }

        /* ── CARD FOOTER ── */
        .card-footer {
            padding: 12px 18px;
            border-top: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .total-label {
            font-size: 12px;
            color: var(--muted);
            white-space: nowrap;
        }

        /* ── TABLET (≤900px) ── */
        @media (max-width: 900px) {

            .col-min,
            .col-satuan {
                display: none;
            }
        }

        /* ── SMALL TABLET (≤768px) ── */
        @media (max-width: 768px) {

            .col-no,
            .col-kode {
                display: none;
            }

            .item-photo,
            .item-photo-placeholder {
                width: 28px;
                height: 28px;
            }
        }

        /* ── MOBILE (≤640px) ── */
        @media (max-width: 640px) {

            /* Page header */
            .page-header {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
            }

            .page-header h2 {
                font-size: 16px;
            }

            .page-header p {
                font-size: 11px;
            }

            .page-header .actions .btn {
                padding: 6px 14px;
                font-size: 13px;
            }

            /* Filter: search di atas, select & tombol di bawah */
            .filter-form {
                flex-wrap: wrap;
                gap: 8px;
            }

            .search-input {
                flex: 1 1 100%;
            }

            .status-select {
                flex: 1;
                max-width: none;
            }

            .filter-form .btn {
                flex-shrink: 0;
            }

            /* Sembunyikan kolom */
            .col-status {
                display: none;
            }

            .col-penginput {
                display: none;
            }

            .card-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }

        /* ── VERY SMALL (≤400px) ── */
        @media (max-width: 400px) {

            .item-photo,
            .item-photo-placeholder {
                display: none;
            }

            .col-harga {
                display: none;
            }
        }

        /* ── Nama barang & kode lebih lega ── */
        .table-wrap table th:nth-child(2),
        .table-wrap table td:nth-child(2) {
            min-width: 110px;
        }

        .table-wrap table th:nth-child(3),
        .table-wrap table td:nth-child(3) {
            min-width: 180px;
        }

        .item-name {
            font-weight: 600;
            font-size: 13px;
            line-height: 1.4;
            white-space: normal;
            word-break: break-word;
        }

        .item-sub {
            font-size: 11px;
            color: var(--muted);
            margin-top: 2px;
            white-space: normal;
        }

        /* ── Kode barang tidak terpotong ── */
        .kode {
            white-space: nowrap;
            display: inline-block;
        }
    </style>
@endpush

@section('content')

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div>
            <h2>Data Barang</h2>
            <p>Kelola semua barang: Consumable, Material, dan Tools</p>
        </div>
        <div class="actions">
            <a href="{{ route('barang.create') }}" class="btn btn-dark">
                <i class="fa-solid fa-plus"></i> Tambah Barang
            </a>
        </div>
    </div>

    <div class="card">

        {{-- FILTER --}}
        <div class="filter-bar">
            <form method="GET" class="filter-form">
                <input type="text" name="search" class="form-control search-input" placeholder="Cari nama / kode..."
                    value="{{ request('search') }}" autocomplete="off">

                <select name="kategori" class="form-control status-select">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>

                <select name="status" class="form-control status-select">
                    <option value="">Semua Status</option>
                    <option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="habis" {{ request('status') == 'habis' ? 'selected' : '' }}>Habis</option>
                </select>

                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-magnifying-glass"></i> Cari
                </button>
                @if (request()->anyFilled(['search', 'kategori', 'status']))
                    <a href="{{ route('barang.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </div>

        {{-- TABLE --}}
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th class="col-no" style="width:40px;">#</th>
                        <th class="col-kode" style="width:110px;">Kode</th>
                        <th>Nama Barang</th>
                        <th style="width:95px;">Kategori</th>
                        <th class="col-satuan" style="width:70px;">Satuan</th>
                        <th style="width:60px;">Stok</th>
                        <th class="col-status" style="width:80px;">Status</th>
                        <th class="col-harga" style="width:120px;">Harga</th>
                        <th class="col-penginput" style="width:110px;">Diinput Oleh</th>
                        <th style="width:90px; text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barang as $i => $b)
                        <tr>
                            {{-- No --}}
                            <td class="col-no" style="color: var(--muted); font-size: 12px;">
                                {{ $barang->firstItem() + $i }}
                            </td>

                            {{-- Kode --}}
                            <td class="col-kode">
                                <code class="kode">{{ $b->kode_barang }}</code>
                            </td>

                            {{-- Nama --}}
                            <td>
                                <div class="item-cell">
                                    @if ($b->foto)
                                        <img src="{{ asset('storage/' . $b->foto) }}" class="item-photo"
                                            alt="{{ $b->nama_barang }}">
                                    @else
                                        <div class="item-photo-placeholder">
                                            <i class="fa-solid fa-box"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="item-name">{{ $b->nama_barang }}</div>
                                        @if ($b->keterangan)
                                            <div class="item-sub">{{ Str::limit($b->keterangan, 45) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Kategori --}}
                            <td>
                                @php
                                    $badgeKat = match ($b->kategori) {
                                        'cons' => ['badge-warning', 'Consumable'],
                                        'material' => ['badge-info', 'Material'],
                                        'tools' => ['badge-success', 'Tools'],
                                        default => ['badge-secondary', strtoupper($b->kategori)],
                                    };
                                @endphp
                                <span class="badge {{ $badgeKat[0] }}">{{ $badgeKat[1] }}</span>
                            </td>

                            {{-- Satuan --}}
                            <td class="col-satuan item-sub">{{ $b->satuan }}</td>

                            {{-- Stok --}}
                            <td>
                                <div class="stok-val"
                                    style="color: {{ $b->stok == 0 ? 'var(--danger)' : ($b->isStokMenipis() ? 'var(--warning)' : 'var(--success)') }};">
                                    {{ $b->stok }}
                                </div>
                                @if ($b->isTools() && $b->stok_dipinjam > 0)
                                    <div class="stok-pinjam">{{ $b->stok_dipinjam }} dipinjam</div>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="col-status">
                                @if ($b->stok == 0)
                                    <span class="badge badge-danger">HABIS</span>
                                @elseif($b->isStokMenipis())
                                    <span class="badge badge-warning">MENIPIS</span>
                                @else
                                    <span class="badge badge-success">AMAN</span>
                                @endif
                            </td>

                            {{-- Harga --}}
                            <td class="col-harga">
                                @if ($b->kategori === 'tools')
                                    <span style="color: var(--muted); font-size: 12px; font-style: italic;">
                                        Tidak digunakan
                                    </span>
                                @else
                                    <span class="harga-item">
                                        Rp {{ number_format($b->prices, 0, ',', '.') }}
                                    </span>
                                @endif
                            </td>
                            <td class="col-penginput" style="font-size:13px;">
                                {{ $b->created_by_name ?? '—' }}
                            </td>
                            {{-- Aksi --}}
                            <td>
                                <div class="action-group" style="justify-content: center;">
                                    <a href="{{ route('barang.show', $b) }}" class="btn btn-xs btn-secondary"
                                        title="Detail">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('barang.edit', $b) }}" class="btn btn-xs btn-warning" title="Edit">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <button type="button" class="btn btn-xs btn-danger" title="Hapus"
                                        onclick="confirmDelete({{ $b->id }}, '{{ addslashes($b->nama_barang) }}')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                    <p>
                                        @if (request()->anyFilled(['search', 'kategori', 'status']))
                                            Tidak ada barang yang cocok dengan filter.
                                            <a href="{{ route('barang.index') }}" style="color: var(--primary);">Reset
                                                filter</a>
                                        @else
                                            Belum ada data barang.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- FOOTER --}}
        <div class="card-footer">
            <span class="total-label">
                <strong>{{ $barang->total() }}</strong> total barang
                @if (request()->anyFilled(['search', 'kategori', 'status']))
                    <span style="color: var(--primary);">(terfilter)</span>
                @endif
            </span>
            {{ $barang->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>

    </div>

    {{-- MODAL HAPUS --}}
    <div id="deleteModal"
        style="display:none; position:fixed; inset:0; z-index:999; background:rgba(0,0,0,0.4); align-items:center; justify-content:center;">
        <div
            style="background:white; border-radius:12px; padding:24px; width:calc(100% - 32px); max-width:420px; box-shadow:0 8px 32px rgba(0,0,0,0.15); box-sizing:border-box;">
            <div
                style="font-weight:700; font-size:15px; margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid var(--border);">
                Hapus Barang
            </div>
            <p style="margin-bottom:6px;">Yakin ingin menghapus barang <strong id="deleteNama"></strong>?</p>
            <p style="font-size:12px; color:var(--muted); margin-bottom:20px;">Tindakan ini tidak bisa dibatalkan.</p>
            <div
                style="display:flex; justify-content:flex-end; gap:8px; padding-top:12px; border-top:1px solid var(--border);">
                <button type="button" class="btn btn-secondary" onclick="closeDelete()">Batal</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function confirmDelete(id, nama) {
                document.getElementById('deleteNama').textContent = nama;
                document.getElementById('deleteForm').action = '/barang/' + id;
                document.getElementById('deleteModal').style.display = 'flex';
            }

            function closeDelete() {
                document.getElementById('deleteModal').style.display = 'none';
            }

            document.getElementById('deleteModal').addEventListener('click', function(e) {
                if (e.target === this) closeDelete();
            });
        </script>
    @endpush
@endsection
