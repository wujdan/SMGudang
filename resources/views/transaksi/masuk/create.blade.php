@extends('layouts.app')
@section('title', 'Input Barang Masuk')

@section('content')
    <div>
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fa-solid fa-truck-ramp-box" style="color: var(--success); margin-right: 8px;"></i>
                    Input Barang Masuk
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('barang-masuk.store') }}" id="form-masuk">
                    @csrf

                    <div class="col-headers">
                        <span>Barang *</span>
                        <span>Jumlah</span>
                        <span>Harga Satuan</span>
                        <span>Tanggal Masuk</span>
                        <span>Supplier</span>
                        <span>Keterangan</span>
                        <span></span>
                    </div>

                    <div id="items-container"></div>

                    <button type="button" onclick="addItem()" class="btn btn-secondary btn-add">
                        <i class="fa-solid fa-plus"></i> Tambah Barang
                    </button>
                    <hr class="divider">

                    <div class="footer-actions">
                        <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let itemCounter = 0;
        let barangList = [];

        try {
            barangList = {!! json_encode(
                $barang->map(
                        fn($b) => [
                            'id' => $b->id,
                            'nama' => $b->nama_barang,
                            'kategori' => strtoupper($b->kategori),
                            'stok' => $b->stok,
                            'satuan' => $b->satuan,
                            'prices' => $b->prices ?? 0,
                        ],
                    )->toArray(),
            ) !!};
        } catch (e) {
            barangList = [];
        }

        // FORMAT RUPIAH
        function formatRupiah(angka) {

            angka = angka.toString().replace(/\D/g, '');

            return new Intl.NumberFormat('id-ID').format(angka);
        }

        // HAPUS FORMAT
        function cleanRupiah(angka) {
            return angka.replace(/\./g, '');
        }

        function createItemHTML(index) {

            const today = '{{ date('Y-m-d') }}';

            return `
            <div class="item-row" data-index="${index}">

                <div class="form-group">
                    <div class="autocomplete-wrapper">

                        <input type="text"
                            class="form-control barang-input"
                            placeholder="Ketik nama barang..."
                            autocomplete="off"
                            oninput="searchBarang(this, ${index})">

                        <input type="hidden"
                            name="items[${index}][barang_id]"
                            class="barang-id">

                        <div class="suggestions"
                            id="sug-${index}">
                        </div>

                    </div>
                </div>

                <div class="form-group">
                    <input type="number"
                        name="items[${index}][jumlah]"
                        class="form-control"
                        min="1"
                        value="1"
                        required>
                </div>

                <div class="form-group">

                    <input type="text"
                        name="items[${index}][harga_satuan]"
                        class="form-control harga-input"
                        value="0"
                        autocomplete="off"
                        required>

                </div>

                <div class="form-group">
                    <input type="date"
                        name="items[${index}][tanggal]"
                        class="form-control"
                        value="${today}"
                        required>
                </div>

                <div class="form-group">
                    <input type="text"
                        name="items[${index}][sumber]"
                        class="form-control"
                        placeholder="Nama supplier">
                </div>

                <div class="form-group">
                    <input type="text"
                        name="items[${index}][keterangan]"
                        class="form-control"
                        placeholder="Opsional">
                </div>

                <div class="form-group">
                    <button type="button"
                        onclick="removeItem(this)"
                        class="btn btn-danger btn-icon"
                        title="Hapus">

                        <i class="fa-solid fa-trash"></i>

                    </button>
                </div>

            </div>
        `;
        }

        function addItem() {

            const container = document.getElementById('items-container');

            container.insertAdjacentHTML(
                'beforeend',
                createItemHTML(itemCounter)
            );

            itemCounter++;
        }

        function removeItem(button) {

            const items = document.querySelectorAll('.item-row');

            if (items.length > 1) {

                button.closest('.item-row').remove();

            } else {

                alert('Minimal harus ada satu item barang');
            }
        }

        let searchTimeout;

        function searchBarang(input, index) {

            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {

                const keyword = input.value.toLowerCase().trim();

                const box = document.getElementById('sug-' + index);

                box.innerHTML = '';

                if (!keyword || keyword.length < 2) return;

                const filtered = barangList.filter(b =>
                    b.nama.toLowerCase().includes(keyword) ||
                    b.kategori.toLowerCase().includes(keyword)
                );

                if (filtered.length === 0) {

                    box.innerHTML =
                        '<div class="sug-item sug-empty">Barang tidak ditemukan</div>';

                    return;
                }

                filtered.slice(0, 10).forEach(b => {

                    const el = document.createElement('div');

                    el.className = 'sug-item';

                    el.innerHTML = `
                    <strong>${b.nama}</strong>
                    <small>
                        ${b.kategori}
                        &middot;
                        Stok: ${b.stok} ${b.satuan}
                    </small>
                `;

                    el.onclick = () => {

                        const row = input.closest('.item-row');

                        const hargaInput =
                            row.querySelector('.harga-input');

                        input.value = b.nama;

                        row.querySelector('.barang-id').value = b.id;

                        // JIKA TOOLS
                        if (b.kategori.toLowerCase() === 'tools') {

                            hargaInput.value = '';

                            hargaInput.setAttribute('disabled', true);

                            hargaInput.removeAttribute('required');

                            hargaInput.placeholder = 'Tidak digunakan';

                            hargaInput.style.background = '#f1f5f9';

                            hargaInput.style.cursor = 'not-allowed';

                        } else {

                            hargaInput.removeAttribute('disabled');

                            hargaInput.setAttribute('required', true);

                            hargaInput.placeholder = '';

                            hargaInput.style.background = '';

                            hargaInput.style.cursor = '';

                            hargaInput.value =
                                new Intl.NumberFormat('id-ID').format(
                                    Math.floor(b.prices || 0)
                                );
                        }

                        box.innerHTML = '';
                    };

                    box.appendChild(el);
                });

            }, 300);
        }

        // FORMAT OTOMATIS INPUT HARGA
        document.addEventListener('input', function(e) {

            if (e.target.classList.contains('harga-input')) {

                let angka = e.target.value.replace(/\D/g, '');

                e.target.value = formatRupiah(angka);
            }
        });

        // BERSIHKAN FORMAT SEBELUM SUBMIT
        document.getElementById('form-masuk')
            .addEventListener('submit', function() {

                document.querySelectorAll('.harga-input')
                    .forEach(input => {

                        input.value = cleanRupiah(input.value);
                    });
            });

        // TUTUP SUGGESTION
        document.addEventListener('click', function(e) {

            if (!e.target.closest('.autocomplete-wrapper')) {

                document.querySelectorAll('.suggestions')
                    .forEach(el => el.innerHTML = '');
            }
        });

        // INIT
        document.addEventListener('DOMContentLoaded', function() {

            addItem();
        });
    </script>

    <style>
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
            letter-spacing: 0.4px;
        }

        .item-row {
            display: grid;
            grid-template-columns: 2fr 80px 140px 140px 130px 120px 40px;
            gap: 8px;
            align-items: center;
            padding: 10px;
            background: var(--bg-light);
            border: 1px solid var(--border);
            border-radius: 8px;
            margin-bottom: 6px;
        }

        .item-row .form-group {
            margin: 0;
        }

        .item-row .form-control {
            height: 34px;
            padding: 0 8px;
            font-size: 13px;
        }

        .btn-add {
            margin-top: 8px;
            margin-bottom: 4px;
            font-size: 13px;
        }

        .btn-icon {
            width: 34px;
            height: 34px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-actions {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .footer-info {
            flex: 1;
            font-size: 12px;
            color: var(--muted);
        }

        .autocomplete-wrapper {
            position: relative;
        }

        .suggestions {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid var(--border);
            border-radius: 8px;
            max-height: 220px;
            overflow-y: auto;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            margin-top: 3px;
        }

        .sug-item {
            padding: 8px 12px;
            cursor: pointer;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }

        .sug-item:last-child {
            border-bottom: none;
        }

        .sug-item:hover {
            background: var(--bg-light);
        }

        .sug-item strong {
            display: block;
            margin-bottom: 2px;
        }

        .sug-item small {
            color: var(--muted);
            font-size: 11px;
        }

        .sug-empty {
            color: var(--muted);
            cursor: default;
            font-size: 12px;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .col-headers {
                display: none;
            }

            .item-row {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .item-row .form-group:first-child {
                grid-column: span 2;
            }

            .item-row .form-group:last-child {
                grid-column: span 2;
            }

            .btn-icon {
                width: 100%;
                height: 34px;
            }
        }

        @media (max-width: 480px) {
            .item-row {
                grid-template-columns: 1fr;
            }

            .item-row .form-group:first-child,
            .item-row .form-group:last-child {
                grid-column: span 1;
            }

            .form-control {
                font-size: 16px;
            }
        }
    </style>
@endpush
