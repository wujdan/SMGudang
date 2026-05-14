@extends('layouts.app')
@section('title', 'Detail Pekerjaan: ' . $pekerjaan->kode_pekerjaan)

@push('styles')
    <style>
        /* ── Info header ── */
        .hpp-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 0;
            font-size: 13px;
            color: var(--muted);
            border-bottom: 1px dashed var(--border);
        }

        .hpp-row:last-child {
            border-bottom: none;
        }

        .hpp-row .label {
            font-weight: 500;
        }

        .hpp-row .value {
            font-weight: 700;
            color: var(--text);
            font-family: monospace;
            font-size: 13.5px;
        }

        .subtotal-bar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 16px;
            padding: 10px 16px;
            background: #f8fafc;
            border-top: 1.5px solid var(--border);
            font-size: 13px;
        }

        .subtotal-bar .label {
            color: var(--muted);
        }

        .subtotal-bar .value {
            font-weight: 800;
            color: var(--text);
            font-size: 14px;
        }

        .grand-total-card {
            background: var(--dark, #1e293b);
            color: #fff;
            border-radius: 12px;
            padding: 20px 24px;
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .grand-total-card .gt-label {
            font-size: 13px;
            opacity: .65;
            margin-bottom: 4px;
        }

        .grand-total-card .gt-value {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -.5px;
            line-height: 1;
        }

        .grand-total-card .gt-breakdown {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .grand-total-card .gt-item {
            text-align: center;
        }

        .grand-total-card .gt-item-label {
            font-size: 11px;
            opacity: .55;
            margin-bottom: 2px;
        }

        .grand-total-card .gt-item-val {
            font-size: 14px;
            font-weight: 700;
            opacity: .9;
        }

        .harga-cell {
            line-height: 1.3;
        }

        .harga-cell .satuan {
            font-weight: 600;
            font-size: 13px;
        }

        .harga-cell .total {
            font-size: 11px;
            color: var(--muted);
        }

        /* ── Foto thumbnail ── */
        .foto-thumb {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid var(--border);
            transition: transform .15s, box-shadow .15s;
        }

        .foto-thumb:hover {
            transform: scale(1.08);
            box-shadow: 0 4px 12px rgba(0, 0, 0, .15);
        }

        .no-foto {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-light);
            border: 1px dashed var(--border);
            border-radius: 6px;
            color: var(--muted);
            font-size: 14px;
        }

        /* ── Lightbox ── */
        .lightbox-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, .85);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .lightbox-backdrop.show {
            display: flex;
        }

        .lightbox-inner {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .lightbox-inner img {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .5);
            object-fit: contain;
        }

        .lightbox-caption {
            color: rgba(255, 255, 255, .75);
            font-size: 13px;
            text-align: center;
        }

        .lightbox-close {
            position: absolute;
            top: -14px;
            right: -14px;
            width: 32px;
            height: 32px;
            background: white;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: #333;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .25);
            transition: background .15s;
        }

        .lightbox-close:hover {
            background: #f1f1f1;
        }

        /* ════════════════════════════════════════
           MODAL TAMBAH BARANG — gaya baru
        ════════════════════════════════════════ */

        /* Item card di dalam modal */
        .modal-item-card {
            border: 1px solid var(--border);
            border-radius: 10px;
            background: var(--bg-light);
            padding: 12px;
            margin-bottom: 8px;
            transition: border-color .2s, box-shadow .2s;
        }

        .modal-item-card:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 59, 130, 246), .07);
        }

        /* Grid responsif dalam modal */
        .modal-item-grid {
            display: grid;
            gap: 8px;
            grid-template-columns: 1fr;
        }

        @media (min-width: 540px) {
            .modal-item-grid {
                grid-template-columns: 1fr 1fr;
            }

            .modal-item-grid .col-full {
                grid-column: 1 / -1;
            }
        }

        @media (min-width: 820px) {
            .modal-item-grid {
                grid-template-columns: 2fr 72px 130px 130px 90px 34px;
                align-items: end;
            }

            .modal-item-grid .col-full {
                grid-column: auto;
            }

            .modal-card-delete-bar {
                display: none !important;
            }

            .col-del-desktop {
                display: flex !important;
            }
        }

        .col-del-desktop {
            display: none;
        }

        /* Label field kecil */
        .modal-field-label {
            font-size: 10px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 3px;
        }

        .modal-item-grid .form-group {
            margin: 0;
        }

        .modal-item-grid .form-control {
            height: 34px;
            font-size: 12px;
            width: 100%;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
            border-bottom: none !important;
        }

        input[type=date].form-control {
            line-height: 34px;
            padding-top: 0;
            padding-bottom: 0;
        }

        /* Autocomplete */
        .autocomplete-wrapper {
            position: relative;
        }

        .suggestions {
            position: absolute;
            top: calc(100% + 3px);
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
            z-index: 200;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .12);
            max-height: 180px;
            overflow-y: auto;
        }

        .sug-item {
            padding: 8px 10px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
        }

        .sug-item:last-child {
            border-bottom: none;
        }

        .sug-item:hover {
            background: var(--bg-light);
        }

        .sug-item strong {
            display: block;
            font-size: 12px;
        }

        .sug-item small {
            color: var(--muted);
            font-size: 10px;
        }

        /* Upload foto */
        .upload-label-modal {
            position: relative;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 34px;
            border: 2px dashed var(--border);
            border-radius: 8px;
            cursor: pointer;
            font-size: 11px;
            color: var(--muted);
            background: var(--bg-light);
            overflow: hidden;
            transition: border-color .2s, background .2s;
            gap: 5px;
            box-sizing: border-box;
        }

        .upload-label-modal:hover {
            border-color: var(--primary);
            background: white;
        }

        .upload-label-modal input[type=file] {
            display: none;
        }

        .upload-label-modal .thumb {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 6px;
            display: none;
        }

        .upload-label-modal.has-photo {
            border-style: solid;
            border-color: var(--primary);
        }

        .upload-label-modal.has-photo .thumb {
            display: block;
        }

        .upload-label-modal.has-photo .cam-icon {
            display: none;
        }

        .upload-label-modal.has-photo::after {
            content: 'Ganti';
            position: absolute;
            top: 50%;
            right: 5px;
            transform: translateY(-50%);
            font-size: 10px;
            font-weight: 700;
            color: white;
            background: rgba(0, 0, 0, .5);
            border-radius: 4px;
            padding: 1px 5px;
            font-family: sans-serif;
            line-height: 16px;
        }

        /* Foto wajib — highlight merah */
        .upload-label-modal.required-empty {
            border-color: #dc3545;
            background: #fff5f5;
            animation: shake .3s ease;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-4px);
            }

            75% {
                transform: translateX(4px);
            }
        }

        /* Field disabled placeholder */
        .field-disabled-modal {
            height: 34px;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: var(--bg-light);
            display: flex;
            align-items: center;
            padding: 0 10px;
            font-size: 12px;
            color: var(--muted);
            opacity: .5;
        }

        /* Tombol hapus bar bawah (mobile/tablet) */
        .modal-card-delete-bar {
            display: flex;
            justify-content: flex-end;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--border);
        }

        /* Tombol tambah item */
        .btn-add-row-modal {
            width: 100%;
            border: 1.5px dashed var(--border);
            background: transparent;
            color: var(--muted);
            font-size: 12px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            margin-top: 6px;
            font-weight: 600;
            transition: border-color .2s, color .2s, background .2s;
        }

        .btn-add-row-modal:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--bg-light);
        }

        /* Toast */
        #modal-foto-toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: #dc3545;
            color: #fff;
            padding: 11px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            z-index: 99999;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .2);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform .3s ease, opacity .3s ease;
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
        }

        #modal-foto-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }
    </style>
@endpush

@section('content')

    <!-- HEADER -->
    <div
        style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:12px;">
        <div>
            <a href="{{ route('pekerjaan.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom:10px;">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <h2 style="font-size:20px; font-weight:800;">{{ $pekerjaan->nama_pekerjaan }}</h2>
            <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:6px; font-size:13px; color:var(--muted);">
                <span><i class="fa-solid fa-hashtag"></i> {{ $pekerjaan->kode_pekerjaan }}</span>
                <span><i class="fa-solid fa-user"></i> {{ $pekerjaan->nama_peminjam }}</span>
                @if ($pekerjaan->lokasi)
                    <span><i class="fa-solid fa-location-dot"></i> {{ $pekerjaan->lokasi }}</span>
                @endif
                <span><i class="fa-regular fa-calendar"></i> {{ $pekerjaan->tanggal_mulai->format('d/m/Y') }}</span>
            </div>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <span class="badge {{ $pekerjaan->status == 'aktif' ? 'badge-warning' : 'badge-success' }}"
                style="font-size:13px; padding:6px 14px;">
                {{ strtoupper($pekerjaan->status) }}
            </span>
            @if ($pekerjaan->status == 'aktif' && auth()->user()->isAdmin())
                <button onclick="document.getElementById('modal-tambah').classList.add('show')" class="btn btn-primary">
                    <i class="fa-solid fa-cart-plus"></i> Tambah Barang
                </button>
            @endif
        </div>
    </div>

    @php
        $tools = $pekerjaan->transaksi->filter(fn($t) => $t->barang->kategori === 'tools');
        $cons = $pekerjaan->transaksi->filter(fn($t) => $t->barang->kategori === 'cons');
        $material = $pekerjaan->transaksi->filter(fn($t) => $t->barang->kategori === 'material');

        $hppTools = $tools->sum('total_hpp');
        $hppCons = $cons->sum('total_hpp');
        $hppMaterial = $material->sum('total_hpp');
        $grandTotal = $hppTools + $hppCons + $hppMaterial;
    @endphp

    <!-- TOOLS TABLE -->
    @if ($tools->isNotEmpty())
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <h3><i class="fa-solid fa-screwdriver-wrench" style="color:var(--success); margin-right:8px;"></i>Tools</h3>
                <span class="badge badge-warning">{{ $tools->where('status_pinjam', 'dipinjam')->count() }} aktif
                    dipinjam</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Tools</th>
                            <th>Jumlah</th>
                            <th>Tgl Keluar</th>
                            <th>Rencana Kembali</th>
                            <th>Tgl Kembali</th>
                            <th>Foto</th>
                            <th>Diinput Oleh</th>
                            <th>Status</th>
                            @if (auth()->user()->isAdmin())
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tools as $t)
                            <tr>
                                <td style="font-weight:600;">{{ $t->barang->nama_barang }}</td>
                                <td>{{ $t->jumlah }} {{ $t->barang->satuan }}</td>
                                <td>{{ $t->tanggal_keluar->format('d/m/Y') }}</td>
                                <td>
                                    @if ($t->tgl_kembali_rencana)
                                        <span style="color:{{ $t->isTerlambat() ? 'var(--danger)' : 'inherit' }};">
                                            {{ $t->tgl_kembali_rencana->format('d/m/Y') }}
                                            @if ($t->isTerlambat())
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                            @endif
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $t->tgl_kembali_aktual ? $t->tgl_kembali_aktual->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if ($t->foto)
                                        <img src="{{ Storage::url($t->foto) }}" class="foto-thumb"
                                            onclick="openLightbox('{{ Storage::url($t->foto) }}', '{{ addslashes($t->barang->nama_barang) }}')"
                                            alt="Foto {{ $t->barang->nama_barang }}">
                                    @else
                                        <div class="no-foto" title="Belum ada foto"><i class="fa-regular fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:13px;">{{ $t->created_by_name ?? '—' }}</td>
                                <td><span class="badge {{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                                @if (auth()->user()->isAdmin())
                                    <td>
                                        @if ($t->status_pinjam === 'dipinjam')
                                            <button
                                                onclick="openReturnModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ $t->barang->satuan }}')"
                                                class="btn btn-sm btn-success">
                                                <i class="fa-solid fa-rotate-left"></i> Kembalikan
                                            </button>
                                        @else
                                            <span style="color:var(--success); font-size:12px;"><i
                                                    class="fa-solid fa-check"></i> Sudah Kembali</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="subtotal-bar">
                <span class="label"><i class="fa-solid fa-screwdriver-wrench" style="margin-right:4px;"></i>Subtotal
                    Tools</span>
                <span class="value" style="color:var(--muted); font-style:italic;">Tidak digunakan</span>
            </div>
        </div>
    @endif

    <!-- CONS TABLE -->
    @if ($cons->isNotEmpty())
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <h3><i class="fa-solid fa-fire" style="color:var(--warning); margin-right:8px;"></i>Consumables</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Tanggal Keluar</th>
                            <th>Total Harga</th>
                            <th>Foto</th>
                            <th>Diinput Oleh</th>
                            <th>Keterangan</th>
                            @if (auth()->user()->isAdmin())
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cons as $t)
                            <tr>
                                <td style="font-weight:600;">{{ $t->barang->nama_barang }}</td>
                                <td>{{ $t->jumlah }}</td>
                                <td>{{ $t->barang->satuan }}</td>
                                <td>{{ $t->tanggal_keluar->format('d/m/Y') }}</td>
                                <td>
                                    <div class="harga-cell">
                                        <div class="satuan">Rp {{ number_format($t->hpp_satuan, 0, ',', '.') }}</div>
                                        <div class="total">Total: Rp {{ number_format($t->total_hpp, 0, ',', '.') }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if ($t->foto)
                                        <img src="{{ Storage::url($t->foto) }}" class="foto-thumb"
                                            onclick="openLightbox('{{ Storage::url($t->foto) }}', '{{ addslashes($t->barang->nama_barang) }}')"
                                            alt="Foto {{ $t->barang->nama_barang }}">
                                    @else
                                        <div class="no-foto" title="Belum ada foto"><i class="fa-regular fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:13px;">{{ $t->created_by_name ?? '—' }}</td>
                                <td style="color:var(--muted);">{{ $t->keterangan ?? '-' }}</td>
                                @if (auth()->user()->isAdmin())
                                    <td style="display:flex; gap:6px;">
                                        @if ($pekerjaan->status == 'aktif')
                                            <button
                                                onclick="openEditModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ addslashes($t->keterangan ?? '') }}')"
                                                class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></button>
                                            <button
                                                onclick="openDeleteModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ $t->barang->satuan }}')"
                                                class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="subtotal-bar">
                <span class="label"><i class="fa-solid fa-fire" style="margin-right:4px;"></i> Subtotal Consumables</span>
                <span class="value">Rp {{ number_format($hppCons, 0, ',', '.') }}</span>
            </div>
        </div>
    @endif

    <!-- MATERIAL TABLE -->
    @if ($material->isNotEmpty())
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <h3><i class="fa-solid fa-cube" style="color:var(--info); margin-right:8px;"></i>Material</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Tanggal Keluar</th>
                            <th>Total Harga</th>
                            <th>Foto</th>
                            <th>Diinput Oleh</th>
                            <th>Keterangan</th>
                            @if (auth()->user()->isAdmin())
                                <th>Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($material as $t)
                            <tr>
                                <td style="font-weight:600;">{{ $t->barang->nama_barang }}</td>
                                <td>{{ $t->jumlah }}</td>
                                <td>{{ $t->barang->satuan }}</td>
                                <td>{{ $t->tanggal_keluar->format('d/m/Y') }}</td>
                                <td>
                                    <div class="harga-cell">
                                        <div class="satuan">Rp {{ number_format($t->hpp_satuan, 0, ',', '.') }}</div>
                                        <div class="total">Total: Rp {{ number_format($t->total_hpp, 0, ',', '.') }}</div>
                                    </div>
                                </td>
                                <td>
                                    @if ($t->foto)
                                        <img src="{{ Storage::url($t->foto) }}" class="foto-thumb"
                                            onclick="openLightbox('{{ Storage::url($t->foto) }}', '{{ addslashes($t->barang->nama_barang) }}')"
                                            alt="Foto {{ $t->barang->nama_barang }}">
                                    @else
                                        <div class="no-foto" title="Belum ada foto"><i class="fa-regular fa-image"></i>
                                        </div>
                                    @endif
                                </td>
                                <td style="font-size:13px;">{{ $t->created_by_name ?? '—' }}</td>
                                <td style="color:var(--muted);">{{ $t->keterangan ?? '-' }}</td>
                                @if (auth()->user()->isAdmin())
                                    <td style="display:flex; gap:6px;">
                                        @if ($pekerjaan->status == 'aktif')
                                            <button
                                                onclick="openEditModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ addslashes($t->keterangan ?? '') }}')"
                                                class="btn btn-sm btn-warning"><i class="fa fa-edit"></i></button>
                                            <button
                                                onclick="openDeleteModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ $t->barang->satuan }}')"
                                                class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="subtotal-bar">
                <span class="label"><i class="fa-solid fa-cube" style="margin-right:4px;"></i> Subtotal Material</span>
                <span class="value">Rp {{ number_format($hppMaterial, 0, ',', '.') }}</span>
            </div>
        </div>
    @endif

    @if ($pekerjaan->transaksi->isEmpty())
        <div class="card">
            <div class="empty-state" style="padding:60px;">
                <i class="fa-solid fa-cart-shopping"></i>
                <p>Belum ada barang yang dicatat untuk pekerjaan ini.<br>Klik "Tambah Barang" untuk mulai.</p>
            </div>
        </div>
    @endif

    <!-- GRAND TOTAL -->
    @if ($pekerjaan->transaksi->isNotEmpty())
        <div class="grand-total-card">
            <div>
                <div class="gt-label"><i class="fa-solid fa-receipt" style="margin-right:6px;"></i>Total HPP Pekerjaan
                </div>
                <div class="gt-value">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
            </div>
            <div class="gt-breakdown">
                @if ($tools->isNotEmpty())
                    <div class="gt-item">
                        <div class="gt-item-label"><i class="fa-solid fa-screwdriver-wrench"></i> Tools</div>
                        <div class="gt-item-val">Rp {{ number_format($hppTools, 0, ',', '.') }}</div>
                    </div>
                @endif
                @if ($cons->isNotEmpty())
                    <div class="gt-item">
                        <div class="gt-item-label"><i class="fa-solid fa-fire"></i> Consumables</div>
                        <div class="gt-item-val">Rp {{ number_format($hppCons, 0, ',', '.') }}</div>
                    </div>
                @endif
                @if ($material->isNotEmpty())
                    <div class="gt-item">
                        <div class="gt-item-label"><i class="fa-solid fa-cube"></i> Material</div>
                        <div class="gt-item-val">Rp {{ number_format($hppMaterial, 0, ',', '.') }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════
     MODAL TAMBAH BARANG — gaya baru
    ═══════════════════════════════════════ --}}
    <div class="modal-backdrop" id="modal-tambah">
        <div class="modal" style="max-width:880px;">
            <div class="modal-header">
                <h4><i class="fa-solid fa-cart-plus" style="color:var(--primary); margin-right:8px;"></i>Tambah Barang ke
                    Pekerjaan</h4>
                <button class="btn-close" onclick="document.getElementById('modal-tambah').classList.remove('show')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('pekerjaan.add-item', $pekerjaan) }}" id="form-cart"
                enctype="multipart/form-data" novalidate>
                @csrf
                <div class="modal-body">
                    <div id="cart-items"></div>
                    <button type="button" class="btn-add-row-modal" onclick="addModalItem()">
                        <i class="fa-solid fa-plus"></i> Tambah Item Lagi
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('modal-tambah').classList.remove('show')">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-konfirmasi">
                        <i class="fa-solid fa-check"></i> Konfirmasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL PENGEMBALIAN TOOLS --}}
    <div class="modal-backdrop" id="modal-return">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-rotate-left" style="color:var(--success); margin-right:8px;"></i>Konfirmasi
                    Pengembalian Tools</h4>
                <button class="btn-close" onclick="document.getElementById('modal-return').classList.remove('show')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" id="form-return">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info" id="return-info"></div>
                    <div class="form-group">
                        <label class="form-label">Tanggal Dikembalikan <span style="color:var(--danger);">*</span></label>
                        <input type="date" name="tgl_kembali_aktual" class="form-control"
                            value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan (Kondisi Tools, dll)</label>
                        <textarea name="keterangan_kembali" class="form-control" rows="2" placeholder="Kondisi saat kembali..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('modal-return').classList.remove('show')">Batal</button>
                    <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT ITEM --}}
    <div class="modal-backdrop" id="modal-edit">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-pen" style="color:var(--warning); margin-right:8px;"></i>Edit Item</h4>
                <button class="btn-close" onclick="document.getElementById('modal-edit').classList.remove('show')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" id="form-edit">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info" id="edit-info"></div>
                    <div class="form-group">
                        <label class="form-label">Jumlah <span style="color:var(--danger);">*</span></label>
                        <input type="number" name="jumlah" id="edit-jumlah" class="form-control" min="1"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <input type="text" name="keterangan" id="edit-keterangan" class="form-control"
                            placeholder="Opsional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('modal-edit').classList.remove('show')">Batal</button>
                    <button type="submit" class="btn btn-warning"><i class="fa-solid fa-check"></i> Simpan
                        Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL HAPUS ITEM --}}
    <div class="modal-backdrop" id="modal-delete">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-triangle-exclamation"
                        style="color:var(--danger); margin-right:8px;"></i>Konfirmasi Hapus</h4>
                <button class="btn-close" onclick="document.getElementById('modal-delete').classList.remove('show')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" id="form-delete">
                @csrf @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger" id="delete-info"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('modal-delete').classList.remove('show')">Batal</button>
                    <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Ya, Hapus
                        Item</button>
                </div>
            </form>
        </div>
    </div>

    {{-- LIGHTBOX FOTO --}}
    <div class="lightbox-backdrop" id="lightbox" onclick="closeLightbox(event)">
        <div class="lightbox-inner">
            <button class="lightbox-close" onclick="document.getElementById('lightbox').classList.remove('show')">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <img id="lightbox-img" src="" alt="">
            <div class="lightbox-caption" id="lightbox-caption"></div>
        </div>
    </div>

    {{-- Toast peringatan foto --}}
    <div id="modal-foto-toast">
        <i class="fa-solid fa-camera"></i>
        <span id="modal-foto-toast-msg">Gambar wajib terisi!</span>
    </div>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
    <script>
        const barangList = {!! json_encode(
            $barang->map(
                    fn($b) => [
                        'id' => $b->id,
                        'nama_barang' => $b->nama_barang,
                        'kategori' => $b->kategori,
                        'stok' => $b->stok,
                        'satuan' => $b->satuan,
                    ],
                )->toArray(),
        ) !!};

        let modalItemIndex = 0;

        /* ── Toast ── */
        let toastTimer;

        function showModalToast(msg) {
            const t = document.getElementById('modal-foto-toast');
            document.getElementById('modal-foto-toast-msg').textContent = msg;
            t.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
        }

        /* ── Buat satu card item ── */
        function createModalItemRow(index) {
            const today = new Date().toISOString().split('T')[0];

            const card = document.createElement('div');
            card.className = 'modal-item-card';
            card.dataset.index = index;

            card.innerHTML = `
            <div class="modal-item-grid">

                <!-- Nama barang -->
                <div class="form-group col-full">
                    <div class="modal-field-label">Nama Barang <span style="color:#dc3545;">*</span></div>
                    <div class="autocomplete-wrapper">
                        <input type="text" class="form-control barang-input"
                            placeholder="Ketik nama barang..." autocomplete="off">
                        <input type="hidden" name="items[${index}][barang_id]" class="barang-id" required>
                        <div class="suggestions" id="modal-sug-${index}"></div>
                    </div>
                </div>

                <!-- Jumlah -->
                <div class="form-group">
                    <div class="modal-field-label">Jumlah <span style="color:#dc3545;">*</span></div>
                    <input type="number" name="items[${index}][jumlah]"
                        class="form-control" placeholder="0" min="1" value="1" required>
                </div>

                <!-- Tgl Keluar -->
                <div class="form-group">
                    <div class="modal-field-label">Tgl Keluar <span style="color:#dc3545;">*</span></div>
                    <input type="date" name="items[${index}][tgl_keluar]"
                        class="form-control" value="${today}" required>
                </div>

                <!-- Rencana Kembali -->
                <div class="form-group">
                    <div class="modal-field-label">Rencana Kembali</div>
                    <div class="tools-only" style="display:none;">
                        <input type="date" name="items[${index}][tgl_kembali_rencana]"
                            class="form-control" min="${today}">
                    </div>
                    <div class="tools-placeholder">
                        <div class="field-disabled-modal">-</div>
                    </div>
                </div>

                <!-- Foto -->
                <div class="form-group">
                    <div class="modal-field-label">Foto <span style="color:#dc3545;">*</span></div>
                    <label class="upload-label-modal foto-label" title="Upload foto">
                        <span class="cam-icon" style="display:flex; align-items:center; gap:4px;">
                            <i class="fa-solid fa-camera" style="font-size:13px;"></i>
                            <span style="font-size:10px; font-weight:600;">Foto</span>
                        </span>
                        <img class="thumb" src="" alt="preview">
                        <input type="file" name="items[${index}][foto]"
                            class="foto-input" accept="image/*" required>
                    </label>
                </div>

                <!-- Hapus (desktop) -->
                <div class="form-group col-del-desktop" style="align-items:flex-end;">
                    <button type="button"
                        class="btn btn-danger btn-icon btn-sm remove-modal-item"
                        style="width:34px; height:34px; padding:0; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; visibility:hidden;"
                        title="Hapus baris">
                        <i class="fa-solid fa-trash" style="font-size:12px;"></i>
                    </button>
                </div>

            </div>

            <!-- Hapus bar (mobile/tablet) -->
            <div class="modal-card-delete-bar" style="display:none;">
                <button type="button" class="btn btn-danger btn-sm remove-modal-item"
                    style="height:30px; padding:0 12px; font-size:12px; display:flex; align-items:center; gap:5px;">
                    <i class="fa-solid fa-trash"></i> Hapus
                </button>
            </div>
        `;

            /* Autocomplete */
            const input = card.querySelector('.barang-input');
            const hidden = card.querySelector('.barang-id');
            const sugBox = card.querySelector('.suggestions');
            input.addEventListener('input', () => searchModalBarang(input, hidden, sugBox, card));

            /* Foto preview + HEIC */
            const fotoInput = card.querySelector('.foto-input');
            const uploadLabel = card.querySelector('.upload-label-modal');
            const thumbImg = card.querySelector('.thumb');

            fotoInput.addEventListener('change', async function() {
                const file = this.files && this.files[0];
                if (!file) {
                    thumbImg.src = '';
                    uploadLabel.classList.remove('has-photo', 'required-empty');
                    return;
                }

                const isHeic = file.type === 'image/heic' || file.type === 'image/heif' ||
                    file.name.toLowerCase().endsWith('.heic') ||
                    file.name.toLowerCase().endsWith('.heif');

                if (isHeic) {
                    uploadLabel.style.opacity = '0.5';
                    try {
                        const blob = await heic2any({
                            blob: file,
                            toType: 'image/jpeg',
                            quality: 0.85
                        });
                        const converted = new File(
                            [blob],
                            file.name.replace(/\.heic$/i, '.jpg').replace(/\.heif$/i, '.jpg'), {
                                type: 'image/jpeg'
                            }
                        );
                        const dt = new DataTransfer();
                        dt.items.add(converted);
                        this.files = dt.files;
                        thumbImg.src = URL.createObjectURL(converted);
                        uploadLabel.classList.add('has-photo');
                        uploadLabel.classList.remove('required-empty');
                    } catch (err) {
                        alert('Gagal memproses foto HEIC. Silakan convert ke JPG terlebih dahulu.');
                        this.value = '';
                    } finally {
                        uploadLabel.style.opacity = '';
                    }
                } else {
                    thumbImg.src = URL.createObjectURL(file);
                    uploadLabel.classList.add('has-photo');
                    uploadLabel.classList.remove('required-empty');
                }
            });

            return card;
        }

        /* ── Tambah item ── */
        function addModalItem() {
            document.getElementById('cart-items').appendChild(createModalItemRow(modalItemIndex));
            modalItemIndex++;
            refreshModalCards();
        }

        /* ── Refresh tombol hapus ── */
        function refreshModalCards() {
            const cards = document.querySelectorAll('#cart-items .modal-item-card');
            cards.forEach(c => {
                const bar = c.querySelector('.modal-card-delete-bar');
                if (bar) bar.style.display = cards.length > 1 ? 'flex' : 'none';
                const desktopBtn = c.querySelector('.col-del-desktop button');
                if (desktopBtn) desktopBtn.style.visibility = cards.length > 1 ? 'visible' : 'hidden';
            });
        }

        /* ── Autocomplete ── */
        function searchModalBarang(input, hidden, sugBox, card) {
            const keyword = input.value.toLowerCase().trim();
            sugBox.innerHTML = '';
            hidden.value = '';
            if (keyword.length < 1) return;

            const results = barangList.filter(b => b.nama_barang.toLowerCase().includes(keyword));
            if (results.length === 0) {
                sugBox.innerHTML = '<div class="sug-item"><small>Barang tidak ditemukan</small></div>';
                return;
            }

            results.forEach(b => {
                const item = document.createElement('div');
                item.className = 'sug-item';
                item.innerHTML =
                    `<strong>${b.nama_barang}</strong><small>${b.kategori.toUpperCase()} &middot; Stok: ${b.stok} ${b.satuan}</small>`;
                item.addEventListener('click', () => {
                    input.value = b.nama_barang;
                    hidden.value = b.id;
                    sugBox.innerHTML = '';
                    const isTools = b.kategori.toLowerCase() === 'tools';
                    card.querySelector('.tools-only').style.display = isTools ? 'block' : 'none';
                    card.querySelector('.tools-placeholder').style.display = isTools ? 'none' : 'block';
                });
                sugBox.appendChild(item);
            });
        }

        /* ── Hapus baris & tutup dropdown ── */
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-modal-item')) {
                const cards = document.querySelectorAll('#cart-items .modal-item-card');
                if (cards.length > 1) {
                    e.target.closest('.modal-item-card').remove();
                    refreshModalCards();
                }
                return;
            }
            if (!e.target.closest('.autocomplete-wrapper')) {
                document.querySelectorAll('.suggestions').forEach(el => el.innerHTML = '');
            }
        });

        /* ── Validasi foto sebelum submit ── */
        document.getElementById('form-cart').addEventListener('submit', function(e) {
            const cards = document.querySelectorAll('#cart-items .modal-item-card');
            let missing = [];

            cards.forEach((card, i) => {
                const label = card.querySelector('.foto-label');
                const input = card.querySelector('.foto-input');
                const hasFile = input && input.files && input.files.length > 0;
                if (!hasFile) {
                    label.classList.add('required-empty');
                    missing.push(i + 1);
                } else {
                    label.classList.remove('required-empty');
                }
            });

            if (missing.length > 0) {
                e.preventDefault();
                showModalToast('Gambar wajib terisi!');
                const firstBad = document.querySelectorAll('#cart-items .modal-item-card')[missing[0] - 1];
                if (firstBad) firstBad.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });

        /* ── Lightbox ── */
        function openLightbox(src, caption) {
            document.getElementById('lightbox-img').src = src;
            document.getElementById('lightbox-caption').textContent = caption;
            document.getElementById('lightbox').classList.add('show');
        }

        function closeLightbox(e) {
            if (e.target === document.getElementById('lightbox'))
                document.getElementById('lightbox').classList.remove('show');
        }

        /* ── Modal helpers ── */
        function openReturnModal(id, nama, jumlah, satuan) {
            document.getElementById('form-return').action = `/transaksi/${id}/return`;
            document.getElementById('return-info').innerHTML =
                `<i class="fa-solid fa-info-circle"></i> Mengembalikan: <strong>${nama}</strong> ${jumlah} ${satuan}<br>Stok otomatis bertambah.`;
            document.getElementById('modal-return').classList.add('show');
        }

        function openEditModal(id, nama, jumlah, keterangan) {
            document.getElementById('form-edit').action = `/transaksi/${id}`;
            document.getElementById('edit-info').innerHTML =
                `<i class="fa-solid fa-info-circle"></i> Edit item: <strong>${nama}</strong>`;
            document.getElementById('edit-jumlah').value = jumlah;
            document.getElementById('edit-keterangan').value = keterangan;
            document.getElementById('modal-edit').classList.add('show');
        }

        function openDeleteModal(id, nama, jumlah, satuan) {
            document.getElementById('form-delete').action = `/transaksi/${id}`;
            document.getElementById('delete-info').innerHTML =
                `<i class="fa-solid fa-triangle-exclamation"></i> Hapus: <strong>${nama}</strong> - ${jumlah} ${satuan}`;
            document.getElementById('modal-delete').classList.add('show');
        }

        /* ── Init ── */
        document.addEventListener('DOMContentLoaded', () => addModalItem());
    </script>
@endpush
