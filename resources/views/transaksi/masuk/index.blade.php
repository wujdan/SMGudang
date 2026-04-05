@extends('layouts.app')
@section('title', 'Barang Masuk')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-truck-ramp-box" style="color: var(--success); margin-right: 8px;"></i>Riwayat Barang
                Masuk</h3>
            <a href="{{ route('barang-masuk.create') }}" class="btn btn-success">
                <i class="fa-solid fa-plus"></i> Input Barang Masuk
            </a>
        </div>
        <div class="card-body" style="padding-bottom: 0;">
            <form method="GET" style="display: flex; gap: 10px; margin-bottom: 16px; flex-wrap: wrap;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama barang / no transaksi..."
                    value="{{ request('search') }}" style="flex: 1; min-width: 200px;">
                <select name="kategori" class="form-control" style="width: 150px;">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>
                <input type="date" name="dari" class="form-control" value="{{ request('dari') }}"
                    style="width: 150px;">
                <input type="date" name="sampai" class="form-control" value="{{ request('sampai') }}"
                    style="width: 150px;">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Filter</button>
                @if (request()->anyFilled(['search', 'kategori', 'dari', 'sampai']))
                    <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Barang</th>
                        <th>Kategori</th>
                        <th>Jumlah</th>
                        <th>Stok Sebelum</th>
                        <th>Stok Sesudah</th>
                        <th>Sumber</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangMasuk as $bm)
                        <tr>
                            <td><code
                                    style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $bm->no_transaksi }}</code>
                            </td>
                            <td>{{ $bm->tanggal->format('d/m/Y') }}</td>
                            <td style="font-weight: 600;">{{ $bm->barang->nama_barang }}</td>
                            <td><span
                                    class="badge badge-{{ $bm->barang->kategori_badge }}">{{ strtoupper($bm->barang->kategori) }}</span>
                            </td>
                            <td><span style="color: var(--success); font-weight: 700;">+{{ $bm->jumlah }}</span>
                                {{ $bm->barang->satuan }}</td>
                            <td style="color: var(--muted);">{{ $bm->stok_sebelum }}</td>
                            <td style="font-weight: 600;">{{ $bm->stok_sesudah }}</td>
                            <td style="color: var(--muted);">{{ $bm->sumber ?? '-' }}</td>
                            <td>
                                <form method="POST" action="{{ route('barang-masuk.destroy', $bm) }}"
                                    onsubmit="return confirm('Hapus & rollback stok?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Hapus & rollback stok">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state"><i class="fa-solid fa-truck-ramp-box"></i>
                                    <p>Belum ada data barang masuk</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body" style="padding-top: 12px;">
            {{ $barangMasuk->links('vendor.pagination.custom') }}
        </div>
    </div>
@endsection
