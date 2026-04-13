@extends('layouts.app')
@section('title', 'Detail Pekerjaan: ' . $pekerjaan->kode_pekerjaan)

@section('content')
    <!-- HEADER -->
    <div
        style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <a href="{{ route('pekerjaan.index') }}" class="btn btn-secondary btn-sm" style="margin-bottom: 10px;">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
            <h2 style="font-size: 20px; font-weight: 800;">{{ $pekerjaan->nama_pekerjaan }}</h2>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 6px; font-size: 13px; color: var(--muted);">
                <span><i class="fa-solid fa-hashtag"></i> {{ $pekerjaan->kode_pekerjaan }}</span>
                <span><i class="fa-solid fa-user"></i> {{ $pekerjaan->nama_peminjam }}</span>
                @if ($pekerjaan->lokasi)
                    <span><i class="fa-solid fa-location-dot"></i> {{ $pekerjaan->lokasi }}</span>
                @endif
                <span><i class="fa-regular fa-calendar"></i> {{ $pekerjaan->tanggal_mulai->format('d/m/Y') }}</span>
            </div>
        </div>
        <div style="display: flex; gap: 8px;">
            <span class="badge {{ $pekerjaan->status == 'aktif' ? 'badge-warning' : 'badge-success' }}"
                style="font-size: 13px; padding: 6px 14px;">
                {{ strtoupper($pekerjaan->status) }}
            </span>
            @if ($pekerjaan->status == 'aktif')
                <button onclick="document.getElementById('modal-tambah').classList.add('show')" class="btn btn-primary">
                    <i class="fa-solid fa-cart-plus"></i> Tambah Barang
                </button>
            @endif
            {{-- <a href="{{ route('pekerjaan.edit', $pekerjaan) }}" class="btn btn-warning">tes
                <i class="fa-solid fa-pen"></i>
            </a> --}}
        </div>
    </div>

    <!-- TABS BARANG -->
    @php
        $tools = $pekerjaan->transaksi->filter(fn($t) => $t->barang->kategori === 'tools');
        $cons = $pekerjaan->transaksi->filter(fn($t) => $t->barang->kategori === 'cons');
        $material = $pekerjaan->transaksi->filter(fn($t) => $t->barang->kategori === 'material');
    @endphp

    <!-- TOOLS TABLE -->
    @if ($tools->isNotEmpty())
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3><i class="fa-solid fa-screwdriver-wrench" style="color: var(--success); margin-right: 8px;"></i>Tools
                </h3>
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
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tools as $t)
                            <tr>
                                <td style="font-weight: 600;">{{ $t->barang->nama_barang }}</td>
                                <td>{{ $t->jumlah }} {{ $t->barang->satuan }}</td>
                                <td>{{ $t->tanggal_keluar->format('d/m/Y') }}</td>
                                <td>
                                    @if ($t->tgl_kembali_rencana)
                                        <span style="color: {{ $t->isTerlambat() ? 'var(--danger)' : 'inherit' }};">
                                            {{ $t->tgl_kembali_rencana->format('d/m/Y') }}
                                            @if ($t->isTerlambat())
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                            @endif
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $t->tgl_kembali_aktual ? $t->tgl_kembali_aktual->format('d/m/Y') : '-' }}</td>
                                <td><span class="badge {{ $t->status_badge }}">{{ $t->status_label }}</span></td>
                                <td>
                                    @if ($t->status_pinjam === 'dipinjam')
                                        <button
                                            onclick="openReturnModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ $t->barang->satuan }}')"
                                            class="btn btn-sm btn-success">
                                            <i class="fa-solid fa-rotate-left"></i> Kembalikan
                                        </button>
                                    @else
                                        <div style="display: flex; gap: 6px; align-items: center;">
                                            <span style="color: var(--success); font-size: 12px;">
                                                <i class="fa-solid fa-check"></i> Sudah Kembali
                                            </span>
                                            @if ($pekerjaan->status == 'aktif')
                                                <button
                                                    onclick="openDeleteModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ $t->barang->satuan }}')"
                                                    class="btn btn-sm btn-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- CONS TABLE -->
    @if ($cons->isNotEmpty())
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3><i class="fa-solid fa-fire" style="color: var(--warning); margin-right: 8px;"></i>Consumables</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Tanggal Keluar</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cons as $t)
                            <tr>
                                <td style="font-weight: 600;">{{ $t->barang->nama_barang }}</td>
                                <td>{{ $t->jumlah }}</td>
                                <td>{{ $t->barang->satuan }}</td>
                                <td>{{ $t->tanggal_keluar->format('d/m/Y') }}</td>
                                <td style="color: var(--muted);">{{ $t->keterangan ?? '-' }}</td>
                                <td style="display: flex; gap: 6px;">
                                    @if ($pekerjaan->status == 'aktif')
                                        <button
                                            onclick="openEditModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ addslashes($t->keterangan ?? '') }}')"
                                            class="btn btn-sm btn-warning">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button
                                            onclick="openDeleteModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ $t->barang->satuan }}')"
                                            class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @else
                                        <span style="color: var(--muted); font-size: 12px;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- MATERIAL TABLE -->
    @if ($material->isNotEmpty())
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3><i class="fa-solid fa-cube" style="color: var(--info); margin-right: 8px;"></i>Material</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nama Barang</th>
                            <th>Jumlah</th>
                            <th>Satuan</th>
                            <th>Tanggal Keluar</th>
                            <th>Keterangan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($material as $t)
                            <tr>
                                <td style="font-weight: 600;">{{ $t->barang->nama_barang }}</td>
                                <td>{{ $t->jumlah }}</td>
                                <td>{{ $t->barang->satuan }}</td>
                                <td>{{ $t->tanggal_keluar->format('d/m/Y') }}</td>
                                <td style="color: var(--muted);">{{ $t->keterangan ?? '-' }}</td>
                                <td style="display: flex; gap: 6px;">
                                    @if ($pekerjaan->status == 'aktif')
                                        <button
                                            onclick="openEditModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ addslashes($t->keterangan ?? '') }}')"
                                            class="btn btn-sm btn-warning">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button
                                            onclick="openDeleteModal({{ $t->id }}, '{{ addslashes($t->barang->nama_barang) }}', {{ $t->jumlah }}, '{{ $t->barang->satuan }}')"
                                            class="btn btn-sm btn-danger">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @else
                                        <span style="color: var(--muted); font-size: 12px;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($pekerjaan->transaksi->isEmpty())
        <div class="card">
            <div class="empty-state" style="padding: 60px;">
                <i class="fa-solid fa-cart-shopping"></i>
                <p>Belum ada barang yang dicatat untuk pekerjaan ini.<br>Klik "Tambah Barang" untuk mulai.</p>
            </div>
        </div>
    @endif

    <!-- MODAL TAMBAH BARANG (CART) -->
    <div class="modal-backdrop" id="modal-tambah">
        <div class="modal" style="max-width: 700px;">
            <div class="modal-header">
                <h4><i class="fa-solid fa-cart-plus" style="color: var(--primary); margin-right: 8px;"></i>Tambah Barang ke
                    Pekerjaan</h4>
                <button class="btn-close" onclick="document.getElementById('modal-tambah').classList.remove('show')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" action="{{ route('pekerjaan.add-item', $pekerjaan) }}" id="form-cart">
                @csrf
                <div class="modal-body">
                    <div id="cart-items">
                        <!-- Item pertama -->
                        <div class="cart-item"
                            style="border: 1.5px solid var(--border); border-radius: 10px; padding: 14px; margin-bottom: 12px;">
                            <div style="display: flex; gap: 10px; align-items: flex-start;">
                                <div style="flex: 1;">
                                    <label class="form-label">Barang</label>
                                    <select name="items[0][barang_id]" class="form-control barang-select"
                                        onchange="onBarangChange(this)" required>
                                        <option value="">-- Pilih Barang --</option>
                                        @foreach ($barang as $b)
                                            <option value="{{ $b->id }}" data-kategori="{{ $b->kategori }}"
                                                data-stok="{{ $b->stok }}" data-satuan="{{ $b->satuan }}">
                                                [{{ strtoupper($b->kategori) }}] {{ $b->nama_barang }} (Stok:
                                                {{ $b->stok }} {{ $b->satuan }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div style="width: 100px;">
                                    <label class="form-label">Jumlah</label>
                                    <input type="number" name="items[0][jumlah]" class="form-control" min="1"
                                        value="1" required>
                                </div>
                                <div style="width: 150px;">
                                    <label class="form-label">Tanggal Keluar</label>
                                    <input type="date" name="items[0][tgl_keluar]" class="form-control"
                                        value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div style="padding-top: 26px;">
                                    <button type="button" onclick="removeItem(this)" class="btn btn-sm btn-danger">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="tools-fields" style="display: none; margin-top: 10px;">
                                <div class="grid-2">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 12px;">Rencana Kembali</label>
                                        <input type="date" name="items[0][tgl_kembali_rencana]" class="form-control">
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label" style="font-size: 12px;">Keterangan</label>
                                        <input type="text" name="items[0][keterangan]" class="form-control"
                                            placeholder="Opsional">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" onclick="addItem()" class="btn btn-secondary" style="width: 100%;">
                        <i class="fa-solid fa-plus"></i> Tambah Item Lagi
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('modal-tambah').classList.remove('show')">Batal</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-check"></i> Konfirmasi Keluar Barang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL PENGEMBALIAN TOOLS -->
    <div class="modal-backdrop" id="modal-return">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-rotate-left" style="color: var(--success); margin-right: 8px;"></i>Konfirmasi
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
                        <label class="form-label">Tanggal Dikembalikan <span
                                style="color: var(--danger);">*</span></label>
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
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-check"></i> Konfirmasi Kembali — Stok Bertambah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT ITEM -->
    <div class="modal-backdrop" id="modal-edit">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-pen" style="color: var(--warning); margin-right: 8px;"></i>Edit Item</h4>
                <button class="btn-close" onclick="document.getElementById('modal-edit').classList.remove('show')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" id="form-edit">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-info" id="edit-info"></div>
                    <div class="form-group">
                        <label class="form-label">Jumlah <span style="color: var(--danger);">*</span></label>
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
                    <button type="submit" class="btn btn-warning">
                        <i class="fa-solid fa-check"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL HAPUS ITEM -->
    <div class="modal-backdrop" id="modal-delete">
        <div class="modal">
            <div class="modal-header">
                <h4><i class="fa-solid fa-triangle-exclamation"
                        style="color: var(--danger); margin-right: 8px;"></i>Konfirmasi Hapus</h4>
                <button class="btn-close" onclick="document.getElementById('modal-delete').classList.remove('show')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form method="POST" id="form-delete">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-danger" id="delete-info"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('modal-delete').classList.remove('show')">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-trash"></i> Ya, Hapus Item
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let itemCount = 1;

        function addItem() {
            const idx = itemCount++;
            const allBarang =
                `@foreach ($barang as $b)<option value="{{ $b->id }}" data-kategori="{{ $b->kategori }}" data-stok="{{ $b->stok }}" data-satuan="{{ $b->satuan }}">[{{ strtoupper($b->kategori) }}] {{ $b->nama_barang }} (Stok: {{ $b->stok }} {{ $b->satuan }})</option>@endforeach`;

            const div = document.createElement('div');
            div.className = 'cart-item';
            div.style = 'border: 1.5px solid var(--border); border-radius: 10px; padding: 14px; margin-bottom: 12px;';
            div.innerHTML = `
        <div style="display: flex; gap: 10px; align-items: flex-start;">
            <div style="flex: 1;">
                <label class="form-label">Barang</label>
                <select name="items[${idx}][barang_id]" class="form-control barang-select" onchange="onBarangChange(this)" required>
                    <option value="">-- Pilih Barang --</option>
                    ${allBarang}
                </select>
            </div>
            <div style="width: 100px;">
                <label class="form-label">Jumlah</label>
                <input type="number" name="items[${idx}][jumlah]" class="form-control" min="1" value="1" required>
            </div>
            <div style="padding-top: 26px;">
                <button type="button" onclick="removeItem(this)" class="btn btn-sm btn-danger">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        </div>
        <div class="tools-fields" style="display: none; margin-top: 10px;">
            <div class="grid-2">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px;">Rencana Kembali</label>
                    <input type="date" name="items[${idx}][tgl_kembali_rencana]" class="form-control">
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 12px;">Keterangan</label>
                    <input type="text" name="items[${idx}][keterangan]" class="form-control" placeholder="Opsional">
                </div>
            </div>
        </div>
    `;
            document.getElementById('cart-items').appendChild(div);
        }

        function removeItem(btn) {
            const items = document.querySelectorAll('.cart-item');
            if (items.length > 1) btn.closest('.cart-item').remove();
        }

        function onBarangChange(sel) {
            const opt = sel.options[sel.selectedIndex];
            const kategori = opt.dataset.kategori;
            const toolsFields = sel.closest('.cart-item').querySelector('.tools-fields');
            if (toolsFields) toolsFields.style.display = kategori === 'tools' ? 'block' : 'none';
        }

        function openReturnModal(id, nama, jumlah, satuan) {
            document.getElementById('form-return').action = `/transaksi/${id}/return`;
            document.getElementById('return-info').innerHTML =
                `<i class="fa-solid fa-info-circle"></i> Mengembalikan: <strong>${nama}</strong> — ${jumlah} ${satuan}<br>Stok akan bertambah otomatis setelah dikonfirmasi.`;
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
                `<i class="fa-solid fa-triangle-exclamation"></i> Hapus: <strong>${nama}</strong> — ${jumlah} ${satuan}`;
            document.getElementById('modal-delete').classList.add('show');
        }
    </script>
@endpush
