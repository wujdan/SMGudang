@extends('layouts.app')

@section('title', 'Tambah Barang Keluar - ' . $pekerjaan->nama_pekerjaan)

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

        .page-meta .sep {
            display: inline-block;
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: var(--muted);
            opacity: .4;
            vertical-align: middle;
            margin: 0 2px;
            font-size: 0;
        }

        /* ── Item card (mobile-first) ── */
        .item-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            background: var(--bg-light);
            padding: 14px;
            margin-bottom: 10px;
            transition: border-color .2s, box-shadow .2s;
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
            .item-grid {
                grid-template-columns: 2fr 80px 140px 140px 100px 36px;
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
        }

        /* iOS date input fix */
        input[type=date].form-control {
            line-height: 38px;
            padding-top: 0;
            padding-bottom: 0;
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

        /* ── Upload foto ── */
        .upload-label {
            position: relative;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 38px;
            border: 2px dashed var(--border);
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            color: var(--muted);
            background: var(--bg-light);
            overflow: hidden;
            transition: border-color .2s, background .2s;
            gap: 6px;
            box-sizing: border-box;
        }

        .upload-label:hover {
            border-color: var(--primary);
            background: white;
        }

        .upload-label input[type=file] {
            display: none;
        }

        .upload-label .thumb-preview {
            position: absolute;
            inset: 0;
            object-fit: cover;
            width: 100%;
            height: 100%;
            border-radius: 8px;
            display: none;
        }

        .upload-label.has-photo {
            border-style: solid;
            border-color: var(--primary);
        }

        .upload-label.has-photo .thumb-preview {
            display: block;
        }

        .upload-label.has-photo .upload-icon {
            display: none;
        }

        .upload-label.has-photo::after {
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

        /* Foto wajib — highlight merah jika belum diisi */
        .upload-label.required-empty {
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
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        /* ── Toast peringatan ── */
        #foto-toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(80px);
            background: #dc3545;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            z-index: 9999;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .2);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform .3s ease, opacity .3s ease;
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
        }

        #foto-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* ── Tombol hapus row ── */
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

        /* ── Disabled date placeholder ── */
        .field-disabled {
            height: 38px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--bg-light);
            display: flex;
            align-items: center;
            padding: 0 12px;
            font-size: 13px;
            color: var(--muted);
            opacity: .5;
        }
    </style>
@endpush

@section('content')
    <div class="card">

        {{-- Header --}}
        <div class="card-header">
            <div style="display:flex; align-items:center; gap:8px;">
                <a href="{{ route('barang-keluar.index') }}" class="btn btn-sm btn-secondary" title="Kembali">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h3>
                    <i class="fa-solid fa-truck-ramp-box" style="color:var(--primary); margin-right:8px;"></i>
                    Tambah Barang Keluar
                </h3>
            </div>
            <div class="page-meta">
                <strong>{{ $pekerjaan->nama_pekerjaan }}</strong>
                <span class="sep"></span>
                <span>{{ $pekerjaan->nama_peminjam }}</span>
                @if ($pekerjaan->lokasi)
                    <span class="sep"></span>
                    <span>{{ $pekerjaan->lokasi }}</span>
                @endif
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('barang-keluar.store', $pekerjaan) }}" enctype="multipart/form-data"
            id="main-form" novalidate>
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

                {{-- Items container --}}
                <div id="items-container"></div>

                {{-- Tombol tambah baris --}}
                <button type="button" id="add-item" class="btn-add-row">
                    <i class="fa-solid fa-plus"></i> Tambah Barang
                </button>

            </div>

            {{-- Footer --}}
            <div class="card-footer" style="display:flex; gap:8px; justify-content:flex-end; padding:12px 16px;">
                <a href="{{ route('barang-keluar.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary" id="btn-simpan">
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
            </div>
        </form>

    </div>

    {{-- Toast peringatan foto --}}
    <div id="foto-toast">
        <i class="fa-solid fa-camera-slash"></i>
        <span id="foto-toast-msg">Harap upload foto terlebih dahulu!</span>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.4/dist/heic2any.min.js"></script>
    <script>
        let itemIndex = 0;
        const barangList = @json($barang);

        /* ── Toast ───────────────────────────────────────────────── */
        let toastTimer;

        function showToast(msg) {
            const t = document.getElementById('foto-toast');
            document.getElementById('foto-toast-msg').textContent = msg;
            t.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => t.classList.remove('show'), 3500);
        }

        /* ── Buat satu card item ──────────────────────────────────── */
        function createItemRow(index) {
            const today = new Date().toISOString().split('T')[0];

            const card = document.createElement('div');
            card.className = 'item-card';
            card.dataset.index = index;

            card.innerHTML = `
        <div class="item-grid">

            <!-- Kolom 1: Nama barang -->
            <div class="form-group col-full">
                <div class="field-label">Nama Barang <span style="color:#dc3545;">*</span></div>
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
                <div class="field-label">Jumlah <span style="color:#dc3545;">*</span></div>
                <input type="number"
                    name="items[${index}][jumlah]"
                    class="form-control"
                    placeholder="0"
                    min="1"
                    required>
            </div>

            <!-- Kolom 3: Tgl Keluar -->
            <div class="form-group">
                <div class="field-label">Tgl Keluar <span style="color:#dc3545;">*</span></div>
                <input type="date"
                    name="items[${index}][tgl_keluar]"
                    class="form-control"
                    value="${today}"
                    required>
            </div>

            <!-- Kolom 4: Rencana Kembali (hanya tools) -->
            <div class="form-group">
                <div class="field-label">Rencana Kembali</div>
                <div class="tools-only" style="display:none;">
                    <input type="date"
                        name="items[${index}][tgl_kembali_rencana]"
                        class="form-control"
                        min="${today}">
                </div>
                <div class="tools-placeholder">
                    <div class="field-disabled">-</div>
                </div>
            </div>

            <!-- Kolom 5: Upload foto -->
            <div class="form-group">
                <div class="field-label">Foto <span style="color:#dc3545;">*</span></div>
                <label class="upload-label foto-label" title="Upload foto">
                    <span class="upload-icon" style="display:flex; align-items:center; gap:5px;">
                        <i class="fa-solid fa-camera" style="font-size:15px;"></i>
                        <span style="font-size:11px; font-weight:600;">Foto</span>
                    </span>
                    <img class="thumb-preview" src="" alt="preview">
                    <input type="file"
                        name="items[${index}][foto]"
                        class="foto-input"
                        accept="image/*"
                        required>
                </label>
            </div>

            <!-- Kolom hapus (desktop, sejajar field) -->
            <div class="form-group col-del-desktop" style="align-items:flex-end; padding-bottom:0;">
                <button type="button"
                    class="btn btn-danger btn-icon btn-sm remove-item"
                    style="width:36px; height:38px; padding:0; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0;"
                    title="Hapus baris">
                    <i class="fa-solid fa-trash" style="font-size:13px;"></i>
                </button>
            </div>

        </div>

        <!-- Bar hapus -->
        <div class="card-delete-bar remove-bar" style="display:none;">
            <button type="button" class="btn btn-danger btn-remove btn-sm remove-item">
                <i class="fa-solid fa-trash"></i> Hapus Barang Ini
            </button>
        </div>
    `;

            /* Event: autocomplete */
            const input = card.querySelector('.barang-input');
            const hidden = card.querySelector('.barang-id');
            const sugBox = card.querySelector('.suggestions');
            input.addEventListener('input', () => searchBarang(input, hidden, sugBox, card));

            /* Event: foto preview & HEIC konversi */
            const fotoInput = card.querySelector('.foto-input');
            const uploadLabel = card.querySelector('.upload-label');
            const thumbImg = card.querySelector('.thumb-preview');

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
                    uploadLabel.title = 'Mengkonversi HEIC...';
                    try {
                        const convertedBlob = await heic2any({
                            blob: file,
                            toType: 'image/jpeg',
                            quality: 0.85
                        });
                        const convertedFile = new File(
                            [convertedBlob],
                            file.name.replace(/\.heic$/i, '.jpg').replace(/\.heif$/i, '.jpg'), {
                                type: 'image/jpeg'
                            }
                        );
                        const dt = new DataTransfer();
                        dt.items.add(convertedFile);
                        this.files = dt.files;
                        thumbImg.src = URL.createObjectURL(convertedFile);
                        uploadLabel.classList.add('has-photo');
                        uploadLabel.classList.remove('required-empty');
                    } catch (err) {
                        console.error('Gagal konversi HEIC:', err);
                        alert('Gagal memproses foto HEIC. Silakan convert manual ke JPG terlebih dahulu.');
                        this.value = '';
                    } finally {
                        uploadLabel.style.opacity = '';
                        uploadLabel.title = 'Upload foto';
                    }
                } else {
                    thumbImg.src = URL.createObjectURL(file);
                    uploadLabel.classList.add('has-photo');
                    uploadLabel.classList.remove('required-empty');
                }
            });

            return card;
        }

        /* ── Perbarui tombol hapus ── */
        function refreshCards() {
            const cards = document.querySelectorAll('.item-card');
            cards.forEach((c) => {
                const bar = c.querySelector('.remove-bar');
                if (bar) bar.style.display = cards.length > 1 ? 'flex' : 'none';
                const desktopBtn = c.querySelector('.col-del-desktop button');
                if (desktopBtn) desktopBtn.style.visibility = cards.length > 1 ? 'visible' : 'hidden';
            });
        }

        /* ── Cari barang ─────────────────────────────────────────── */
        function searchBarang(input, hidden, sugBox, card) {
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
                item.innerHTML = `
            <strong>${b.nama_barang}</strong>
            <small>${b.kategori.toUpperCase()} &middot; Stok: ${b.stok} ${b.satuan}</small>
        `;
                item.addEventListener('click', () => {
                    input.value = b.nama_barang;
                    hidden.value = b.id;
                    sugBox.innerHTML = '';

                    const isTools = b.is_tools || b.kategori.toLowerCase() === 'tools';
                    card.querySelector('.tools-only').style.display = isTools ? 'block' : 'none';
                    card.querySelector('.tools-placeholder').style.display = isTools ? 'none' : 'block';
                });
                sugBox.appendChild(item);
            });
        }

        /* ── Tambah baris ────────────────────────────────────────── */
        function addItem() {
            document.getElementById('items-container').appendChild(createItemRow(itemIndex));
            itemIndex++;
            refreshCards();
        }

        document.getElementById('add-item').addEventListener('click', addItem);

        /* ── Hapus & tutup dropdown ─────────────────────────────── */
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-item')) {
                const rows = document.querySelectorAll('.item-card');
                if (rows.length > 1) {
                    e.target.closest('.item-card').remove();
                    refreshCards();
                }
                return;
            }
            if (!e.target.closest('.autocomplete-wrapper')) {
                document.querySelectorAll('.suggestions').forEach(el => el.innerHTML = '');
            }
        });

        /* ── Validasi foto sebelum submit ───────────────────────── */
        document.getElementById('main-form').addEventListener('submit', function(e) {
            const cards = document.querySelectorAll('.item-card');
            let missingFoto = [];

            cards.forEach((card, i) => {
                const label = card.querySelector('.foto-label');
                const fotoInput = card.querySelector('.foto-input');
                const hasFile = fotoInput && fotoInput.files && fotoInput.files.length > 0;

                if (!hasFile) {
                    label.classList.add('required-empty');
                    missingFoto.push(i + 1);
                } else {
                    label.classList.remove('required-empty');
                }
            });

            if (missingFoto.length > 0) {
                e.preventDefault();
                const nums = missingFoto.join(', ');
                showToast(
                    missingFoto.length === 1 ?
                    'Gambar wajib terisi!' :
                    'Gambar wajib terisi!'
                );
                /* Scroll ke card pertama yang bermasalah */
                const firstBad = document.querySelectorAll('.item-card')[missingFoto[0] - 1];
                if (firstBad) firstBad.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });

        /* ── Init: 1 baris langsung tampil ──────────────────────── */
        document.addEventListener('DOMContentLoaded', addItem);
    </script>
@endpush
