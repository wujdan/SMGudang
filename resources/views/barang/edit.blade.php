@extends('layouts.app')
@section('title', 'Edit Barang')

@section('content')

    <a href="{{ route('barang.index') }}" class="back-link">
        <i class="fa-solid fa-chevron-left"></i> Kembali ke Data Barang
    </a>

    <div style="max-width:680px;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-pen" style="color:var(--accent);"></i> Edit Barang</h3>
                <span class="badge badge-{{ $barang->kategori }}">{{ strtoupper($barang->kategori) }}</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('barang.update', $barang) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Kode Barang</label>
                        <input type="text" class="form-control" value="{{ $barang->kode_barang }}" disabled>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Nama Barang <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="nama_barang"
                                class="form-control {{ $errors->has('nama_barang') ? 'is-invalid' : '' }}"
                                value="{{ old('nama_barang', $barang->nama_barang) }}" required>
                            @error('nama_barang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Satuan <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="satuan" class="form-control"
                                value="{{ old('satuan', $barang->satuan) }}" list="satuan-list" required>
                            <datalist id="satuan-list">
                                <option value="pcs">
                                <option value="kg">
                                <option value="liter">
                                <option value="roll">
                                <option value="unit">
                                <option value="set">
                                <option value="lembar">
                                <option value="batang">
                                <option value="meter">
                            </datalist>
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Stok Saat Ini</label>
                            <input type="text" class="form-control" value="{{ $barang->stok }} {{ $barang->satuan }}"
                                disabled>
                            <div class="form-hint">Ubah stok lewat transaksi masuk / keluar</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Stok Minimum (Alert)</label>
                            <input type="number" name="stok_minimum" class="form-control"
                                value="{{ old('stok_minimum', $barang->stok_minimum) }}" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Foto Barang</label>
                        @if ($barang->foto)
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
                                <img src="{{ asset('storage/' . $barang->foto) }}"
                                    style="width:64px; height:64px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
                                <span style="font-size:12px; color:var(--text-muted);">
                                    Foto saat ini. Upload baru untuk mengganti.
                                </span>
                            </div>
                        @endif
                        <input type="file" name="foto" class="form-control" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $barang->keterangan) }}</textarea>
                    </div>

                    <hr class="divider">

                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-floppy-disk"></i> Update Barang
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection
