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
                <div id="items-container">
                    <div class="item-row"
                        style="display: flex; gap: 10px; align-items: end; margin-bottom: 16px; padding: 12px; border: 1px solid var(--border); border-radius: 6px;">
                        <div style="flex: 2;">
                            <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Barang</label>
                            <div style="position: relative;">
                                <input type="text" class="form-control barang-input" placeholder="Ketik nama barang..."
                                    oninput="searchBarang(this)" autocomplete="off">

                                <input type="hidden" name="items[0][barang_id]" class="barang-id">

                                <div class="suggestions"
                                    style="position:absolute; background:#fff; width:100%; z-index:10; border:1px solid #ddd;">
                                </div>
                            </div>
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Jumlah</label>
                            <input type="number" name="items[0][jumlah]" class="form-control" min="1" required>
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Tanggal Keluar</label>
                            <input type="date" name="items[0][tgl_keluar]" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="tools-only" style="flex: 1; display: none;">
                            <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Rencana Kembali</label>
                            <input type="date" name="items[0][tgl_kembali_rencana]" class="form-control"
                                min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div style="flex: 2;">
                            <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Keterangan</label>
                            <input type="text" name="items[0][keterangan]" class="form-control" placeholder="Opsional">
                        </div>
                        <div style="flex: 0 0 auto;">
                            <button type="button" class="btn btn-sm btn-danger remove-item" style="display: none;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div style="margin-top:10px;">
                    <button type="button" id="add-item" class="btn btn-outline-primary btn-sm">
                        <i class="fa-solid fa-plus"></i> Tambah Barang Lain
                    </button>
                </div>
            </div>
            <div class="card-footer" style="display: flex; gap: 8px; justify-content: flex-end; padding: 12px 16px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Simpan Barang Keluar
                </button>
                <a href="{{ route('barang-keluar.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>

    <script>
        let itemIndex = 1;

        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const newRow = container.querySelector('.item-row').cloneNode(true);
            newRow.querySelector('.suggestions').innerHTML = '';
            newRow.querySelector('.barang-id').value = '';

            // Update names
            newRow.querySelectorAll('input').forEach(el => {
                const name = el.getAttribute('name');
                if (name) {
                    el.setAttribute('name', name.replace('[0]', '[' + itemIndex + ']'));
                }
                if (el.type === 'date') {
                    el.value = new Date().toISOString().split('T')[0];
                } else {
                    el.value = '';
                }
            });

            // Show remove button
            newRow.querySelector('.remove-item').style.display = 'block';

            container.appendChild(newRow);
            itemIndex++;
        });
    </script>
    <script>
        const barangList = @json($barang);

        function searchBarang(input) {
            let keyword = input.value.toLowerCase();
            let container = input.parentElement.querySelector('.suggestions');
            container.innerHTML = '';

            if (keyword.length < 1) return;

            let results = barangList.filter(b =>
                b.nama_barang.toLowerCase().includes(keyword)
            );

            results.forEach(b => {
                let div = document.createElement('div');

                div.innerHTML = `[${b.kategori.toUpperCase()}] ${b.nama_barang} (Stok: ${b.stok} ${b.satuan})`;
                div.style.cursor = "pointer";
                div.style.padding = "5px";

                div.onclick = function() {
                    input.value =
                        `[${b.kategori.toUpperCase()}] ${b.nama_barang} (Stok: ${b.stok} ${b.satuan})`;

                    input.parentElement.querySelector('.barang-id').value = b.id;

                    let row = input.closest('.item-row');
                    let toolsFields = row.querySelector('.tools-only');

                    if (b.is_tools || b.kategori.toLowerCase() === 'tools') {
                        toolsFields.style.display = 'block';
                    } else {
                        toolsFields.style.display = 'none';
                    }

                    container.innerHTML = '';
                };

                container.appendChild(div);
            });
        }
    </script>
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
                const row = e.target.closest('.item-row');
                if (row) {
                    row.remove();
                }
            }
        });
    </script>
@endsection
