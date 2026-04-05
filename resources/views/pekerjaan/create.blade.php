@extends('layouts.app')
@section('title', 'Buat Pekerjaan Baru')

@section('content')
    <div style="max-width: 700px;">
        <div style="margin-bottom: 20px;">
            <a href="{{ route('pekerjaan.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-hard-hat" style="color: var(--primary); margin-right: 8px;"></i>Buat Pekerjaan Baru
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('pekerjaan.store') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Nama Pekerjaan <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nama_pekerjaan"
                            class="form-control {{ $errors->has('nama_pekerjaan') ? 'is-invalid' : '' }}"
                            value="{{ old('nama_pekerjaan') }}" placeholder="Contoh: Ganti Ducting Area B - Line 3"
                            required>
                        @error('nama_pekerjaan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Nama Peminjam / PIC <span
                                    style="color: var(--danger);">*</span></label>
                            <input type="text" name="nama_peminjam" class="form-control"
                                value="{{ old('nama_peminjam') }}" placeholder="Nama teknisi yang bertanggung jawab"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lokasi Pekerjaan</label>
                            <input type="text" name="lokasi" class="form-control" value="{{ old('lokasi') }}"
                                placeholder="Area / Section">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tanggal Mulai <span style="color: var(--danger);">*</span></label>
                        <input type="date" name="tanggal_mulai" class="form-control"
                            value="{{ old('tanggal_mulai', date('Y-m-d')) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Deskripsi pekerjaan...">{{ old('keterangan') }}</textarea>
                    </div>

                    <hr class="divider">
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('pekerjaan.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-save"></i> Buat Pekerjaan → Tambah Barang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
