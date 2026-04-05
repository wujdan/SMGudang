@extends('layouts.app')
@section('title', 'Tambah Barang')

@section('content')

    <a href="{{ route('barang.index') }}" class="back-link">
        <i class="fa-solid fa-chevron-left"></i> Kembali ke Data Barang
    </a>

    <div style="max-width:680px;">
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-plus" style="color:var(--accent);"></i> Tambah Barang Baru</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('barang.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Nama Barang <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="nama_barang"
                                class="form-control {{ $errors->has('nama_barang') ? 'is-invalid' : '' }}"
                                value="{{ old('nama_barang') }}" placeholder="cth: Batu Gerinda 4&quot;" required>
                            @error('nama_barang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kategori <span style="color:var(--danger);">*</span></label>
                            <select name="kategori" class="form-control {{ $errors->has('kategori') ? 'is-invalid' : '' }}"
                                required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="cons" {{ old('kategori') == 'cons' ? 'selected' : '' }}>🟡 Consumable —
                                    habis pakai</option>
                                <option value="material" {{ old('kategori') == 'material' ? 'selected' : '' }}>🔵 Material
                                </option>
                                <option value="tools" {{ old('kategori') == 'tools' ? 'selected' : '' }}>🟢 Tools —
                                    sistem pinjam</option>
                            </select>
                            @error('kategori')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Satuan <span style="color:var(--danger);">*</span></label>
                            <input type="text" name="satuan" class="form-control" value="{{ old('satuan') }}"
                                placeholder="pcs / kg / unit / set / roll..." list="satuan-list" required>
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

                        <div class="form-group">
                            <label class="form-label">Stok Awal <span style="color:var(--danger);">*</span></label>
                            <input type="number" name="stok"
                                class="form-control {{ $errors->has('stok') ? 'is-invalid' : '' }}"
                                value="{{ old('stok', 0) }}" min="0" required>
                            @error('stok')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Stok Minimum (Alert)</label>
                            <input type="number" name="stok_minimum" class="form-control"
                                value="{{ old('stok_minimum', 5) }}" min="0">
                            <div class="form-hint">Sistem alert jika stok ≤ nilai ini</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Foto Barang</label>
                            <input type="file" name="foto" class="form-control" accept="image/*"
                                onchange="previewImg(this)">
                            <img id="img-preview" src="#"
                                style="display:none; margin-top:8px; width:72px; height:72px; object-fit:cover; border-radius:8px; border:1px solid var(--border);">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Deskripsi tambahan...">{{ old('keterangan') }}</textarea>
                    </div>

                    <hr class="divider">

                    <div style="display:flex; gap:8px; justify-content:flex-end;">
                        <a href="{{ route('barang.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-dark">
                            <i class="fa-solid fa-floppy-disk"></i> Simpan Barang
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function previewImg(input) {
            const preview = document.getElementById('img-preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
