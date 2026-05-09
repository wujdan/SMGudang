@extends('layouts.app')
@section('title', 'Laporan Barang Masuk')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-file-import" style="color: var(--success); margin-right: 8px;"></i>Laporan Barang Masuk
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
                    <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 4px;">Dari
                        Tanggal</label>
                    <input type="date" name="dari" class="form-control"
                        value="{{ request('dari', now()->subDays(30)->format('Y-m-d')) }}" style="width: 160px;">
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 4px;">Sampai
                        Tanggal</label>
                    <input type="date" name="sampai" class="form-control"
                        value="{{ request('sampai', now()->format('Y-m-d')) }}" style="width: 160px;">
                </div>
                <select name="kategori" class="form-control" style="width: 150px;">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Filter</button>
                @if (request()->anyFilled(['dari', 'sampai', 'kategori']))
                    <a href="{{ route('laporan.masuk') }}" class="btn btn-secondary">Reset</a>
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
                        <th>Harga Satuan</th>
                        <th>Total Nominal</th>
                        <th>Stok Sebelum</th>
                        <th>Stok Sesudah</th>
                        <th>Sumber</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data as $d)
                        <tr>
                            <td><code
                                    style="font-size: 11px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $d->no_transaksi }}</code>
                            </td>
                            <td>{{ $d->tanggal->format('d/m/Y') }}</td>
                            <td style="font-weight: 600;">{{ $d->barang->nama_barang }}</td>
                            <td>
                                <span class="badge badge-{{ $d->barang->kategori_badge ?? 'secondary' }}">
                                    {{ strtoupper($d->barang->kategori) }}
                                </span>
                            </td>
                            <td><span style="color: var(--success); font-weight: 700;">+{{ $d->jumlah }}</span></td>
                            <td>
                                <span style="font-weight: 600;">
                                    Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <span style="color: var(--primary); font-weight: 700;">
                                    Rp {{ number_format($d->jumlah * $d->harga_satuan, 0, ',', '.') }}
                                </span>
                            </td>
                            <td style="color: var(--muted);">{{ $d->stok_sebelum }}</td>
                            <td style="font-weight: 600;">{{ $d->stok_sesudah }}</td>
                            <td style="color: var(--muted);">{{ $d->sumber ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="empty-state">
                                    <i class="fa-solid fa-file-import"></i>
                                    <p>Tidak ada data pada periode ini</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($data->count() > 0)
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-right"><strong>GRAND TOTAL</strong></td>
                            <td><strong>{{ $totalJumlah }}</strong></td>
                            <td colspan="2"><strong>Rp {{ number_format($totalNominal, 0, ',', '.') }}</strong></td>
                            <td colspan="3"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        <div class="card-body">
            <small style="color: var(--muted);">
                Periode: {{ request('dari', now()->subDays(30)->format('d/m/Y')) }} -
                {{ request('sampai', now()->format('d/m/Y')) }}
                @if (request('kategori'))
                    | Kategori:
                    {{ request('kategori') == 'cons' ? 'Consumable' : (request('kategori') == 'material' ? 'Material' : 'Tools') }}
                @endif
            </small>
        </div>
    </div>
@endsection
