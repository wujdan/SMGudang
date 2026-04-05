@extends('layouts.app')
@section('title', 'Input Barang Masuk')

@section('content')
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
        <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-truck-ramp-box" style="color: var(--success); margin-right: 8px;"></i>Input Barang Masuk
            </h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('barang-masuk.store') }}" id="form-masuk">
                @csrf

                <div id="items-container">
                    <div class="masuk-item"
                        style="border: 1.5px solid var(--border); border-radius: 10px; padding: 16px; margin-bottom: 14px;">
                        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
                            <div style="flex: 2; min-width: 200px;" class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Barang <span style="color: var(--danger);">*</span></label>
                                <select name="items[0][barang_id]" class="form-control" required>
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach ($barang as $b)
                                        <option value="{{ $b->id }}">[{{ strtoupper($b->kategori) }}]
                                            {{ $b->nama_barang }} (Stok: {{ $b->stok }} {{ $b->satuan }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="width: 100px;" class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Jumlah <span style="color: var(--danger);">*</span></label>
                                <input type="number" name="items[0][jumlah]" class="form-control" min="1"
                                    value="1" required>
                            </div>
                            <div style="width: 160px;" class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Tanggal <span style="color: var(--danger);">*</span></label>
                                <input type="date" name="items[0][tanggal]" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div style="flex: 1; min-width: 140px;" class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Sumber / Supplier</label>
                                <input type="text" name="items[0][sumber]" class="form-control"
                                    placeholder="Nama supplier...">
                            </div>
                            <div style="flex: 1; min-width: 140px;" class="form-group" style="margin-bottom: 0;">
                                <label class="form-label">Keterangan</label>
                                <input type="text" name="items[0][keterangan]" class="form-control"
                                    placeholder="Opsional">
                            </div>
                            <div class="form-group" style="margin-bottom: 0;">
                                <button type="button" onclick="removeItem(this)" class="btn btn-danger btn-sm"
                                    style="margin-top: 26px;">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="addItem()" class="btn btn-secondary"
                    style="width: 100%; margin-bottom: 20px;">
                    <i class="fa-solid fa-plus"></i> Tambah Barang Lagi
                </button>

                <hr class="divider">
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-save"></i> Simpan — Stok Otomatis Bertambah
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let idx = 1;
        const barangOptions =
            `@foreach ($barang as $b)<option value="{{ $b->id }}">[{{ strtoupper($b->kategori) }}] {{ $b->nama_barang }} (Stok: {{ $b->stok }} {{ $b->satuan }})</option>@endforeach`;

        function addItem() {
            const today = '{{ date('Y-m-d') }}';
            const div = document.createElement('div');
            div.className = 'masuk-item';
            div.style = 'border: 1.5px solid var(--border); border-radius: 10px; padding: 16px; margin-bottom: 14px;';
            div.innerHTML = `
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 2; min-width: 200px;" class="form-group">
                <label class="form-label">Barang <span style="color: var(--danger);">*</span></label>
                <select name="items[${idx}][barang_id]" class="form-control" required>
                    <option value="">-- Pilih Barang --</option>
                    ${barangOptions}
                </select>
            </div>
            <div style="width: 100px;" class="form-group">
                <label class="form-label">Jumlah</label>
                <input type="number" name="items[${idx}][jumlah]" class="form-control" min="1" value="1" required>
            </div>
            <div style="width: 160px;" class="form-group">
                <label class="form-label">Tanggal</label>
                <input type="date" name="items[${idx}][tanggal]" class="form-control" value="${today}" required>
            </div>
            <div style="flex: 1; min-width: 140px;" class="form-group">
                <label class="form-label">Sumber</label>
                <input type="text" name="items[${idx}][sumber]" class="form-control" placeholder="Supplier...">
            </div>
            <div style="flex: 1; min-width: 140px;" class="form-group">
                <label class="form-label">Keterangan</label>
                <input type="text" name="items[${idx}][keterangan]" class="form-control" placeholder="Opsional">
            </div>
            <div class="form-group">
                <button type="button" onclick="removeItem(this)" class="btn btn-danger btn-sm" style="margin-top: 26px;">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
    `;
            document.getElementById('items-container').appendChild(div);
            idx++;
        }

        function removeItem(btn) {
            const items = document.querySelectorAll('.masuk-item');
            if (items.length > 1) btn.closest('.masuk-item').remove();
        }
    </script>
@endpush
