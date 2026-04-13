@extends('layouts.app')
@section('title', 'Edit Barang')

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('barang.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Data Barang
        </a>
    </div>
    <div>
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
                            <label class="form-label">Kategori <span style="color:var(--danger);">*</span></label>
                            <select name="kategori" class="form-control {{ $errors->has('kategori') ? 'is-invalid' : '' }}"
                                required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="tools"
                                    {{ old('kategori', $barang->kategori) == 'tools' ? 'selected' : '' }}>Tools</option>
                                <option value="cons"
                                    {{ old('kategori', $barang->kategori) == 'cons' ? 'selected' : '' }}>Consumable
                                </option>
                                <option value="material"
                                    {{ old('kategori', $barang->kategori) == 'material' ? 'selected' : '' }}>Material
                                </option>
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">
                                Satuan <span style="color:var(--danger);">*</span>
                            </label>

                            <select name="satuan" class="form-control {{ $errors->has('satuan') ? 'is-invalid' : '' }}"
                                required>
                                <option value="">-- Pilih Satuan --</option>
                                <option value="pcs" {{ old('satuan', $barang->satuan) == 'pcs' ? 'selected' : '' }}>pcs
                                </option>
                                <option value="kg" {{ old('satuan', $barang->satuan) == 'kg' ? 'selected' : '' }}>kg
                                </option>
                                <option value="liter" {{ old('satuan', $barang->satuan) == 'liter' ? 'selected' : '' }}>
                                    liter</option>
                                <option value="roll" {{ old('satuan', $barang->satuan) == 'roll' ? 'selected' : '' }}>
                                    roll</option>
                                <option value="unit" {{ old('satuan', $barang->satuan) == 'unit' ? 'selected' : '' }}>
                                    unit</option>
                                <option value="set" {{ old('satuan', $barang->satuan) == 'set' ? 'selected' : '' }}>set
                                </option>
                                <option value="lembar" {{ old('satuan', $barang->satuan) == 'lembar' ? 'selected' : '' }}>
                                    lembar</option>
                                <option value="batang" {{ old('satuan', $barang->satuan) == 'batang' ? 'selected' : '' }}>
                                    batang</option>
                                <option value="meter" {{ old('satuan', $barang->satuan) == 'meter' ? 'selected' : '' }}>
                                    meter</option>
                                <option value="botol" {{ old('satuan') == 'botol' ? 'selected' : '' }}>botol</option>

                            </select>
                            @error('satuan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Stok Minimum (Alert)</label>
                            <input type="number" name="stok_minimum" class="form-control"
                                value="{{ old('stok_minimum', $barang->stok_minimum) }}" min="0">
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
