@extends('layouts.app')
@section('title', 'Edit Pekerjaan')

@section('content')
    <div style="max-width: 700px;">
        <div style="margin-bottom: 20px;">
            <a href="{{ route('pekerjaan.show', $pekerjaan) }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card">
            <div class="card-header">
                <h3><i class="fa-solid fa-pen" style="color: var(--warning); margin-right: 8px;"></i>Edit Pekerjaan</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('pekerjaan.update', $pekerjaan) }}">
                    @csrf @method('PUT')

                    <div class="form-group">
                        <label class="form-label">Kode Pekerjaan</label>
                        <input type="text" class="form-control" value="{{ $pekerjaan->kode_pekerjaan }}" disabled
                            style="background: #f8fafc;">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Pekerjaan <span style="color: var(--danger);">*</span></label>
                        <input type="text" name="nama_pekerjaan" class="form-control"
                            value="{{ old('nama_pekerjaan', $pekerjaan->nama_pekerjaan) }}" required>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Nama Peminjam / PIC <span
                                    style="color: var(--danger);">*</span></label>
                            <input type="text" name="nama_peminjam" class="form-control"
                                value="{{ old('nama_peminjam', $pekerjaan->nama_peminjam) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Lokasi</label>
                            <input type="text" name="lokasi" class="form-control"
                                value="{{ old('lokasi', $pekerjaan->lokasi) }}">
                        </div>
                    </div>

                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Tanggal Mulai <span style="color: var(--danger);">*</span></label>
                            <input type="date" name="tanggal_mulai" class="form-control"
                                value="{{ old('tanggal_mulai', $pekerjaan->tanggal_mulai->format('Y-m-d')) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control"
                                value="{{ old('tanggal_selesai', $pekerjaan->tanggal_selesai?->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="aktif" {{ $pekerjaan->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="selesai" {{ $pekerjaan->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                        @if ($pekerjaan->hasToolsBelumKembali())
                            <div style="font-size: 12px; color: var(--warning); margin-top: 4px;">
                                <i class="fa-solid fa-triangle-exclamation"></i> Masih ada tools yang belum dikembalikan!
                                Selesaikan pengembalian dulu.
                            </div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan', $pekerjaan->keterangan) }}</textarea>
                    </div>

                    <hr class="divider">
                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                        <a href="{{ route('pekerjaan.show', $pekerjaan) }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fa-solid fa-save"></i> Update Pekerjaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
