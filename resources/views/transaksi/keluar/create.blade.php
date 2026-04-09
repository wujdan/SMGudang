@extends('layouts.app')
@section('title', 'Tambah Barang Keluar - ' . $pekerjaan->nama_pekerjaan)

@section('content')
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; gap: 8px;">
                <a href="{{ route('barang-keluar.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <h3><i class="fa-solid fa-truck-ramp-box" style="color: var(--primary); margin-right: 8px;"></i>Tambah Barang Keluar</h3>
            </div>
            <div style="font-size: 13px; color: var(--muted); margin-top: 4px;">
                <strong>{{ $pekerjaan->nama_pekerjaan }}</strong> - {{ $pekerjaan->nama_peminjam }}
                @if ($pekerjaan->lokasi) | {{ $pekerjaan->lokasi }} @endif
            </div>
        </div>
        <form method="POST" action="{{ route('barang-keluar.store', $pekerjaan) }}">
            @csrf
            <div class="card-body">
                <div id="items-container">
                    <div class="item-row" style="display: flex; gap: 10px; align-items: end; margin-bottom: 16px; padding: 12px; border: 1px solid var(--border); border-radius: 6px;">
                        <div style="flex: 2;">
                            <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Barang</label>
                            <select name="items[0][barang_id]" class="form-control" required>
                                <option value="">Pilih Barang</option>
                                @foreach($barang as $b)
                                    <option value="{{ $b->id }}" data-kategori="{{ $b->kategori }}" data-satuan="{{ $b->satuan }}" data-stok="{{ $b->stok }}" data-is-tools="{{ $b->isTools() ? 1 : 0 }}">
                                        {{ $b->nama_barang }} ({{ $b->stok }} {{ $b->satuan }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Jumlah</label>
                            <input type="number" name="items[0][jumlah]" class="form-control" min="1" required>
                        </div>
                        <div class="tools-only" style="flex: 1; display: none;">
                            <label style="font-size: 12px; font-weight: 600; margin-bottom: 4px;">Rencana Kembali</label>
                            <input type="date" name="items[0][tgl_kembali_rencana]" class="form-control" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
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
                <button type="button" id="add-item" class="btn btn-outline-primary btn-sm">
                    <i class="fa-solid fa-plus"></i> Tambah Barang Lain
                </button>
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

            // Update names
            newRow.querySelectorAll('select, input').forEach(el => {
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

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
                e.target.closest('.item-row').remove();
            }
        });

        // Show/hide tgl kembali for tools
        document.addEventListener('change', function(e) {
            if (e.target.matches('select[name*="barang_id"]')) {
                const row = e.target.closest('.item-row');
                const option = e.target.options[e.target.selectedIndex];
                const isTools = option?.getAttribute('data-is-tools') === '1';
                const toolsField = row.querySelector('.tools-only');
                if (toolsField) {
                    toolsField.style.display = isTools ? 'block' : 'none';
                }
            }
        });
    </script>
@endsection