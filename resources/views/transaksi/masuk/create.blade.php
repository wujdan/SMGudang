@extends('layouts.app')
@section('title', 'Input Barang Masuk')

@push('styles')
    <style>
        /* ── Page meta ── */
        .page-meta {
            font-size: 13px;
            color: var(--muted);
            margin-top: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        /* ── Item card (mobile-first) ── */
        .item-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--bg-light);
            padding: 14px;
            margin-bottom: 10px;
            transition: border-color .2s, box-shadow .2s;
            position: relative;
        }

        .item-card:focus-within {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb, 59, 130, 246), .08);
        }

        /* ── Grid tata letak dalam card ── */
        .item-grid {
            display: grid;
            gap: 10px;
            grid-template-columns: 1fr;
        }

        @media (min-width: 600px) {
            .item-grid {
                grid-template-columns: 1fr 1fr;
            }

            .item-grid .col-full {
                grid-column: 1 / -1;
            }
        }

        @media (min-width: 960px) {

            .item-card .field-label {
                display: none;
            }

            .item-grid {
                grid-template-columns: 2fr 80px 140px 140px 130px 120px 36px;
                align-items: end;
            }

            .item-grid .col-full {
                grid-column: auto;
            }

            .card-delete-bar {
                display: none !important;
            }

            .col-del-desktop {
                display: flex !important;
            }
        }

        .col-del-desktop {
            display: none;
        }

        /* ── Label kecil di atas field ── */
        .field-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 4px;
        }

        .field-label .req {
            color: #dc3545;
        }

        .form-group {
            margin: 0;
        }

        .form-control {
            height: 38px;
            font-size: 13px;
            width: 100%;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 0 10px;
            background: #fff;
            color: var(--text);
            transition: border-color .15s, box-shadow .15s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .1);
        }

        input[type=date].form-control {
            line-height: 38px;
            padding-top: 0;
            padding-bottom: 0;
        }

        .input-disabled {
            background: #f1f5f9 !important;
            color: #94a3b8 !important;
            cursor: not-allowed !important;
        }

        /* ── Autocomplete ── */
        .autocomplete-wrapper {
            position: relative;
        }

        .suggestions {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            z-index: 10;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .12);
            max-height: 220px;
            overflow-y: auto;
        }

        .sug-item {
            padding: 10px 12px;
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
            font-size: 13px;
        }

        .sug-item small {
            color: var(--muted);
            font-size: 11px;
        }

        .sug-empty {
            color: var(--muted);
            cursor: default;
            font-size: 12px;
            padding: 10px 12px;
        }

        /* ── Tombol hapus row (mobile) ── */
        .card-delete-bar {
            display: flex;
            justify-content: flex-end;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid var(--border);
        }

        .btn-remove {
            height: 34px;
            padding: 0 14px;
            border-radius: 8px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
            background: #dc3545;
            border: none;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
            transition: background .15s;
        }

        .btn-remove:hover {
            background: #b91c1c;
        }

        /* ── Tombol hapus desktop ── */
        .col-del-desktop .btn-del-icon {
            width: 36px;
            height: 38px;
            padding: 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #dc3545;
            border: none;
            color: #fff;
            cursor: pointer;
            flex-shrink: 0;
            transition: background .15s;
        }

        .col-del-desktop .btn-del-icon:hover {
            background: #b91c1c;
        }

        /* ── Tombol tambah barang ── */
        .btn-add-row {
            margin-top: 10px;
            width: 100%;
            border: 1.5px dashed var(--border);
            background: transparent;
            color: var(--muted);
            font-size: 13px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            transition: border-color .2s, color .2s, background .2s;
            font-weight: 600;
        }

        .btn-add-row:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--bg-light);
        }

        /* ── Footer actions ── */
        .footer-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .footer-actions .btn {
            white-space: nowrap;
        }

        /* Desktop col headers — hidden di mobile */
        .col-headers {
            display: none;
        }

        @media (min-width: 960px) {
            .col-headers {
                display: grid;
                grid-template-columns: 2fr 80px 140px 140px 130px 120px 40px;
                gap: 8px;
                padding: 0 10px;
                margin-bottom: 4px;
            }

            .col-headers span {
                font-size: 11px;
                font-weight: 600;
                color: var(--muted);
                text-transform: uppercase;
                letter-spacing: .4px;
            }

            .btn-add-row {
                width: auto;
                display: inline-flex;
                height: 36px;
                padding: 0 16px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="card">

        {{-- Header --}}
        <div class="card-header">
            <div style="display:flex; align-items:center; gap:8px;">
                <a href="{{ route('barang-masuk.index') }}" class="btn btn-sm btn-secondary" title="Kembali">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h3 style="margin:0;">
                    <i class="fa-solid fa-truck-ramp-box" style="color:var(--success); margin-right:8px;"></i>
                    Input Barang Masuk
                </h3>
            </div>
            <div class="page-meta">
                <span>Isi form di bawah untuk menambahkan stok barang masuk</span>
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('barang-masuk.store') }}" id="form-masuk" novalidate>
            @csrf

            <div class="card-body">

                {{-- Error bag --}}
                @if ($errors->any())
                    <div class="alert alert-danger" style="margin-bottom:16px;">
                        <ul style="margin:0; padding-left:18px;">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Desktop: col headers --}}
                <div class="col-headers">
                    <span>Barang *</span>
                    <span>Jumlah</span>
                    <span>Harga Satuan</span>
                    <span>Tanggal Masuk</span>
                    <span>Supplier</span>
                    <span>Keterangan</span>
                    <span></span>
                </div>

                {{-- Items container --}}
                <div id="items-container"></div>

                {{-- Tombol tambah baris --}}
                <button type="button" id="add-item" class="btn-add-row">
                    <i class="fa-solid fa-plus"></i> Tambah Barang
                </button>

                {{-- Footer actions --}}
                <div class="footer-actions">
                    <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary">
                         Batal
                    </a>
                    <button type="submit" class="btn btn-primary" id="btn-simpan">
                        <i class="fa-solid fa-save"></i> Simpan
                    </button>
                </div>

            </div>
        </form>

    </div>
@endsection

@push('scripts')
    <script>
        let itemIndex = 0;
        const barangList = @json($barang);
        const today = '{{ date('Y-m-d') }}';

        /* ── Format & clean rupiah ── */
        function formatRupiah(n) {
            return new Intl.NumberFormat('id-ID').format(String(n).replace(/\D/g, ''));
        }

        function cleanRupiah(s) {
            return s.replace(/\./g, '');
        }

        /* ── Refresh tombol hapus ── */
        function refreshCards() {
            const cards = document.querySelectorAll('.item-card');
            cards.forEach((c) => {
                const bar = c.querySelector('.card-delete-bar');
                if (bar) bar.style.display = cards.length > 1 ? 'flex' : 'none';
                const desktopBtn = c.querySelector('.col-del-desktop .btn-del-icon');
                if (desktopBtn) desktopBtn.style.visibility = cards.length > 1 ? 'visible' : 'hidden';
            });
        }

        /* ── Buat satu card item ── */
        function createItemCard(index) {
            const card = document.createElement('div');
            card.className = 'item-card';
            card.dataset.index = index;

            card.innerHTML = `
                <div class="item-grid">

                    <!-- Kolom 1: Nama barang -->
                    <div class="form-group col-full">
                        <div class="field-label">Nama Barang <span class="req">*</span></div>
                        <div class="autocomplete-wrapper">
                            <input type="text"
                                class="form-control barang-input"
                                placeholder="Ketik nama barang..."
                                autocomplete="off">
                            <input type="hidden"
                                name="items[${index}][barang_id]"
                                class="barang-id"
                                required>
                            <div class="suggestions" id="suggestions-${index}"></div>
                        </div>
                    </div>

                    <!-- Kolom 2: Jumlah -->
                    <div class="form-group">
                        <div class="field-label">Jumlah <span class="req">*</span></div>
                        <input type="number"
                            name="items[${index}][jumlah]"
                            class="form-control"
                            min="1" value="1"
                            required>
                    </div>

                    <!-- Kolom 3: Harga Satuan -->
                    <div class="form-group">
                        <div class="field-label">Harga Satuan <span class="req">*</span></div>
                        <input type="text"
                            name="items[${index}][harga_satuan]"
                            class="form-control harga-input"
                            value="0"
                            autocomplete="off"
                            inputmode="numeric"
                            required>
                    </div>

                    <!-- Kolom 4: Tanggal Masuk -->
                    <div class="form-group">
                        <div class="field-label">Tanggal Masuk <span class="req">*</span></div>
                        <input type="date"
                            name="items[${index}][tanggal]"
                            class="form-control"
                            value="${today}"
                            required>
                    </div>

                    <!-- Kolom 5: Supplier -->
                    <div class="form-group">
                        <div class="field-label">Supplier</div>
                        <input type="text"
                            name="items[${index}][sumber]"
                            class="form-control"
                            placeholder="Nama supplier">
                    </div>

                    <!-- Kolom 6: Keterangan -->
                    <div class="form-group">
                        <div class="field-label">Keterangan</div>
                        <input type="text"
                            name="items[${index}][keterangan]"
                            class="form-control"
                            placeholder="Opsional">
                    </div>

                    <!-- Kolom hapus (desktop) -->
                    <div class="form-group col-del-desktop" style="align-items:flex-end; padding-bottom:0;">
                        <button type="button"
                            class="btn-del-icon remove-item"
                            style="visibility:hidden;"
                            title="Hapus baris">
                            <i class="fa-solid fa-trash" style="font-size:13px;"></i>
                        </button>
                    </div>

                </div>

                <!-- Bar hapus (mobile) -->
                <div class="card-delete-bar" style="display:none;">
                    <button type="button" class="btn-remove remove-item">
                        <i class="fa-solid fa-trash"></i> Hapus Barang Ini
                    </button>
                </div>
            `;

            // Event: autocomplete
            const input = card.querySelector('.barang-input');
            const hidden = card.querySelector('.barang-id');
            const sugBox = card.querySelector('.suggestions');
            const hargaInput = card.querySelector('.harga-input');

            input.addEventListener('input', function() {
                const keyword = this.value.toLowerCase().trim();
                sugBox.innerHTML = '';
                hidden.value = '';

                if (keyword.length < 2) return;

                const filtered = barangList.filter(b =>
                    b.nama_barang && (b.nama_barang.toLowerCase().includes(keyword) ||
                        (b.kategori && b.kategori.toLowerCase().includes(keyword)))
                );

                if (!filtered.length) {
                    sugBox.innerHTML = '<div class="sug-empty">Barang tidak ditemukan</div>';
                    return;
                }

                filtered.slice(0, 10).forEach(b => {
                    const el = document.createElement('div');
                    el.className = 'sug-item';
                    el.innerHTML = `
                        <strong>${b.nama_barang}</strong>
                        <small>${b.kategori ? b.kategori.toUpperCase() : '-'} &middot; Stok: ${b.stok} ${b.satuan}</small>
                    `;
                    el.addEventListener('click', () => {
                        input.value = b.nama_barang;
                        hidden.value = b.id;
                        sugBox.innerHTML = '';

                        const isTools = b.is_tools || (b.kategori && b.kategori
                            .toLowerCase() === 'tools');
                        if (isTools) {
                            hargaInput.value = '';
                            hargaInput.setAttribute('disabled', true);
                            hargaInput.removeAttribute('required');
                            hargaInput.placeholder = 'Tidak digunakan';
                            hargaInput.classList.add('input-disabled');
                        } else {
                            hargaInput.removeAttribute('disabled');
                            hargaInput.setAttribute('required', true);
                            hargaInput.placeholder = '';
                            hargaInput.classList.remove('input-disabled');
                            hargaInput.value = new Intl.NumberFormat('id-ID')
                                .format(Math.floor(b.prices || 0));
                        }
                    });
                    sugBox.appendChild(el);
                });
            });

            return card;
        }

        /* ── Tambah baris ── */
        function addItem() {
            document.getElementById('items-container').appendChild(createItemCard(itemIndex));
            itemIndex++;
            refreshCards();
        }

        document.getElementById('add-item').addEventListener('click', addItem);

        /* ── Format harga otomatis ── */
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('harga-input') && !e.target.disabled) {
                e.target.value = formatRupiah(e.target.value);
            }
        });

        /* ── Hapus & tutup dropdown ── */
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                const rows = document.querySelectorAll('.item-card');
                if (rows.length > 1) {
                    e.target.closest('.item-card').remove();
                    refreshCards();
                } else {
                    alert('Minimal harus ada satu item barang');
                }
                return;
            }
            if (!e.target.closest('.autocomplete-wrapper')) {
                document.querySelectorAll('.suggestions').forEach(el => el.innerHTML = '');
            }
        });

        /* ── Bersihkan sebelum submit ── */
        document.getElementById('form-masuk').addEventListener('submit', function() {
            document.querySelectorAll('.harga-input').forEach(el => {
                if (!el.disabled) {
                    el.value = cleanRupiah(el.value);
                }
            });
        });

        /* ── Init: 1 baris langsung tampil ── */
        document.addEventListener('DOMContentLoaded', addItem);
    </script>
@endpush
