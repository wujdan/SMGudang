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

        .filter-form {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-form .form-control {
            height: 34px;
            padding: 0 10px;
            font-size: 13px;
        }

        .filter-search {
            flex: 1;
            min-width: 180px;
        }

        .filter-select {
            width: 150px;
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

        /* ── RESPONSIVE TABLE ── */
        /* hide less-critical columns on small screens */
        @media (max-width: 900px) {

            .col-min,
            .col-satuan {
                display: none;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-wrap: wrap;
            }

            .filter-select {
                width: 130px;
            }

            .filter-search {
                min-width: 140px;
            }

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

        @media (max-width: 540px) {
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .filter-select,
            .filter-search {
                width: 100%;
            }

            .filter-actions {
                display: flex;
                gap: 8px;
            }

            .filter-actions .btn {
                flex: 1;
                justify-content: center;
            }

            .col-status {
                display: none;
            }
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
                <input type="text" name="search" class="form-control filter-search" placeholder="Cari nama / kode..."
                    value="{{ request('search') }}" autocomplete="off">

                <select name="kategori" class="form-control filter-select">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>

                <select name="status" class="form-control filter-select">
                    <option value="">Semua Status</option>
                    <option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="habis" {{ request('status') == 'habis' ? 'selected' : '' }}>Habis</option>
                </select>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-magnifying-glass"></i> Cari
                    </button>
                    @if (request()->anyFilled(['search', 'kategori', 'status']))
                        <a href="{{ route('barang.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-xmark"></i> Reset
                        </a>
                    @endif
                </div>
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
                        <th style="width:110px;">Kategori</th>
                        <th class="col-satuan" style="width:80px;">Satuan</th>
                        <th style="width:80px;">Stok</th>
                        <th class="col-status" style="width:95px;">Status</th>
                        <th style="width:100px; text-align:center;">Aksi</th>
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
                                    <form method="POST" action="{{ route('barang.destroy', $b) }}"
                                        onsubmit="return confirm('Hapus barang {{ addslashes($b->nama_barang) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-xs btn-danger" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                    <p>
                                        @if (request()->anyFilled(['search', 'kategori', 'status']))
                                            Tidak ada barang yang cocok dengan filter.
                                            <a href="{{ route('barang.index') }}" style="color: var(--primary);">Reset
                                                filter</a>
                                        @else
                                            Belum ada data barang.
                                            <a href="{{ route('barang.create') }}" style="color: var(--primary);">Tambah
                                                sekarang</a>
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

@endsection
