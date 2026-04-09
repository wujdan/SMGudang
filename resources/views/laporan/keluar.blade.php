@extends('layouts.app')
@section('title', 'Laporan Barang Keluar')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-file-export" style="color: var(--warning); margin-right: 8px;"></i>Laporan Barang Keluar
            </h3>
            <div style="display: flex; gap: 8px;">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-success btn-sm"><i
                        class="fa-solid fa-file-excel"></i> Excel</a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm"><i
                        class="fa-solid fa-file-pdf"></i> PDF</a>
            </div>
        </div>
        <div class="card-body" style="padding-bottom: 0;">
            <form method="GET"
                style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap; align-items: flex-end;">
                <div>
                    <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 4px;">Dari Tanggal</label>
                    <input type="date" name="dari" class="form-control"
                        value="{{ request('dari', now()->subDays(30)->format('Y-m-d')) }}" style="width: 160px;">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 4px;">Sampai Tanggal</label>
                    <input type="date" name="sampai" class="form-control"
                        value="{{ request('sampai', now()->format('Y-m-d')) }}" style="width: 160px;">
                </div>
                <select name="kategori" class="form-control" style="width: 150px;">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>
                <select name="status" class="form-control" style="width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="permanen" {{ request('status') == 'permanen' ? 'selected' : '' }}>Permanen (Cons/Mat)
                    </option>
                    <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                    <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Dikembalikan
                    </option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Filter</button>
                @if (request()->anyFilled(['dari', 'sampai', 'kategori']))
                    <a href="{{ route('laporan.keluar') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tgl Keluar</th>
                        <th>Pekerjaan</th>
                        <th>PIC</th>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Tipe</th>
                        <th>Rencana Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $d)
                        <tr>
                            <td><code
                                    style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $d->no_transaksi }}</code>
                            </td>
                            <td>{{ $d->tanggal_keluar->format('d/m/Y') }}</td>
                            <td style="font-size: 12px; font-weight: 600;">{{ $d->pekerjaan->nama_pekerjaan }}</td>
                            <td style="font-size: 12px;">{{ $d->pekerjaan->nama_peminjam }}</td>
                            <td style="font-weight: 600;">{{ $d->barang->nama_barang }}</td>
                            <td><span
                                    class="badge badge-{{ $d->barang->kategori_badge }}">{{ strtoupper($d->barang->kategori) }}</span>
                            </td>
                            <td><span style="color: var(--danger); font-weight: 700;">-{{ $d->jumlah }}</span></td>
                            <td>
                                @if ($d->status_pinjam)
                                    <span class="badge badge-warning">PINJAM</span>
                                @else<span class="badge badge-secondary">PERMANEN</span>
                                @endif
                            </td>
                            <td style="font-size: 12px; color: {{ $d->isTerlambat() ? 'var(--danger)' : 'inherit' }};">
                                {{ $d->tgl_kembali_rencana?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td><span class="badge {{ $d->status_badge }}">{{ $d->status_label }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state"><i class="fa-solid fa-file-export"></i>
                                    <p>Tidak ada data</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
