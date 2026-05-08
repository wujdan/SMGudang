@extends('layouts.app')
@section('title', 'Tambah Barang Keluar - ' . $pekerjaan->nama_pekerjaan)

@section('content')
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <a href="{{ route('barang-keluar.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h3><i class="fa-solid fa-truck-ramp-box" style="color: var(--primary); margin-right: 8px;"></i>Tambah Barang
                    Keluar</h3>
            </div>
            <div style="font-size: 13px; color: var(--muted); margin-top: 4px;">
                <strong>{{ $pekerjaan->nama_pekerjaan }}</strong> - {{ $pekerjaan->nama_peminjam }}
                @if ($pekerjaan->lokasi)
                    | {{ $pekerjaan->lokasi }}
                @endif
            </div>
        </div>
        <form method="POST" action="{{ route('barang-keluar.store', $pekerjaan) }}">
            @csrf
            <div class="card-body">

                <div class="col-headers">
                    <span>Barang *</span>
                    <span>Jumlah</span>
                    <span>Tgl Keluar</span>
                    <span>Rencana Kembali</span>
                    <span>Keterangan</span>
                    <span></span>
                </div>

                <div id="items-container"></div>

                <button type="button" id="add-item" class="btn btn-secondary btn-add">

                    <i class="fa-solid fa-plus"></i>
                    Tambah Barang
                </button>

            </div>
            <div class="card-footer" style="display: flex; gap: 8px; justify-content: flex-end; padding: 12px 16px;">
                <a href="{{ route('barang-keluar.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            let itemIndex = 0;

            const barangList = @json($barang);

            function createItemRow(index) {

                const today = new Date()
                    .toISOString()
                    .split('T')[0];

                return `

        <div class="item-row">

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
                        id="suggestions-${index}">
                    </div>

                </div>

            </div>

            <div class="form-group">

                <input type="number"
                    name="items[${index}][jumlah]"
                    class="form-control"
                    min="1"
                    required>

            </div>

            <div class="form-group">

                <input type="date"
                    name="items[${index}][tgl_keluar]"
                    class="form-control"
                    value="${today}"
                    required>

            </div>

            <div class="form-group tools-only"
                style="display:none;">

                <input type="date"
                    name="items[${index}][tgl_kembali_rencana]"
                    class="form-control"
                    min="${today}">

            </div>

            <div class="form-group">

                <input type="text"
                    name="items[${index}][keterangan]"
                    class="form-control"
                    placeholder="Opsional">

            </div>

            <div class="form-group">

                <button type="button"
                    class="btn btn-danger btn-icon remove-item">

                    <i class="fa-solid fa-trash"></i>

                </button>

            </div>

        </div>
        `;
            }

            function addItem() {

                document.getElementById('items-container')
                    .insertAdjacentHTML(
                        'beforeend',
                        createItemRow(itemIndex)
                    );

                itemIndex++;
            }

            document.getElementById('add-item')
                .addEventListener('click', addItem);

            function searchBarang(input, index) {

                const keyword = input.value.toLowerCase();

                const box =
                    document.getElementById(
                        'suggestions-' + index
                    );

                box.innerHTML = '';

                if (keyword.length < 1) return;

                const results = barangList.filter(b =>
                    b.nama_barang.toLowerCase()
                    .includes(keyword)
                );

                results.forEach(b => {

                    const div =
                        document.createElement('div');

                    div.className = 'sug-item';

                    div.innerHTML = `
                <strong>${b.nama_barang}</strong>

                <small>
                    ${b.kategori.toUpperCase()}
                    ·
                    Stok: ${b.stok} ${b.satuan}
                </small>
            `;

                    div.onclick = function() {

                        const row =
                            input.closest('.item-row');

                        input.value = b.nama_barang;

                        row.querySelector('.barang-id')
                            .value = b.id;

                        const toolsField =
                            row.querySelector('.tools-only');

                        if (
                            b.is_tools ||
                            b.kategori.toLowerCase() === 'tools'
                        ) {

                            toolsField.style.display = 'block';

                        } else {

                            toolsField.style.display = 'none';
                        }

                        box.innerHTML = '';
                    };

                    box.appendChild(div);
                });
            }

            document.addEventListener('click', function(e) {

                if (
                    e.target.classList.contains('remove-item') ||
                    e.target.closest('.remove-item')
                ) {

                    const row =
                        e.target.closest('.item-row');

                    const totalRows =
                        document.querySelectorAll('.item-row');

                    if (totalRows.length > 1) {

                        row.remove();
                    }
                }

                if (
                    !e.target.closest('.autocomplete-wrapper')
                ) {

                    document.querySelectorAll('.suggestions')
                        .forEach(el => el.innerHTML = '');
                }
            });

            document.addEventListener(
                'DOMContentLoaded',
                addItem
            );
        </script>
    @endpush
    @push('styles')
        <style>
            .col-headers {
                display: grid;
                grid-template-columns:
                    2fr 90px 150px 160px 1fr 50px;

                gap: 10px;

                margin-bottom: 6px;
                padding: 0 8px;
            }

            .col-headers span {
                font-size: 11px;
                font-weight: 700;
                color: var(--muted);
                text-transform: uppercase;
            }

            .item-row {
                display: grid;

                grid-template-columns:
                    2fr 90px 150px 160px 1fr 50px;

                gap: 10px;
                align-items: center;

                padding: 10px;

                border: 1px solid var(--border);
                border-radius: 10px;

                background: var(--bg-light);

                margin-bottom: 8px;
            }

            .item-row .form-group {
                margin: 0;
            }

            .item-row .form-control {
                height: 36px;
                font-size: 13px;
            }

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

                z-index: 20;

                box-shadow: 0 6px 20px rgba(0, 0, 0, .08);
            }

            .sug-item {
                padding: 10px 12px;
                cursor: pointer;
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

            .btn-add {
                margin-top: 10px;
            }

            .btn-icon {
                width: 32px;
                height: 32px;

                padding: 0;

                display: flex;
                align-items: center;
                justify-content: center;

                border-radius: 8px;

                font-size: 12px;
            }

            @media (max-width: 900px) {

                .col-headers {
                    display: none;
                }

                .item-row {
                    grid-template-columns: 1fr 1fr;
                }
            }

            @media (max-width: 640px) {

                .item-row {
                    grid-template-columns: 1fr;
                }

                .btn-icon {
                    width: 100%;
                }
            }
        </style>
    @endpush

@endsection
