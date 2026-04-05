@extends('layouts.app')
@section('title', 'Laporan Stok Terkini')

@section('content')
    <div class="card">
        <div class="card-header">
            <h3><i class="fa-solid fa-clipboard-list" style="color: var(--primary); margin-right: 8px;"></i>Laporan Stok
                Terkini</h3>
            <div style="display: flex; gap: 8px;">
                <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
        <div class="card-body" style="padding-bottom: 0;">
            <form method="GET" style="display: flex; gap: 10px; margin-bottom: 16px;">
                <select name="kategori" class="form-control" style="width: 160px;">
                    <option value="">Semua Kategori</option>
                    <option value="cons" {{ request('kategori') == 'cons' ? 'selected' : '' }}>Consumable</option>
                    <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="tools" {{ request('kategori') == 'tools' ? 'selected' : '' }}>Tools</option>
                </select>
                <select name="status" class="form-control" style="width: 150px;">
                    <option value="">Semua Status</option>
                    <option value="aman" {{ request('status') == 'aman' ? 'selected' : '' }}>Aman</option>
                    <option value="menipis" {{ request('status') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                    <option value="habis" {{ request('status') == 'habis' ? 'selected' : '' }}>Habis</option>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Filter</button>
                @if (request()->anyFilled(['kategori', 'status']))
                    <a href="{{ route('laporan.stok') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th>Stok</th>
                        <th>Min Stok</th>
                        <th>Status</th>
                        @if (request('kategori') == 'tools' || !request('kategori'))
                            <th>Dipinjam</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($barang as $i => $b)
                        <tr>
                            <td style="color: var(--muted);">{{ $i + 1 }}</td>
                            <td><code
                                    style="font-size: 12px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $b->kode_barang }}</code>
                            </td>
                            <td style="font-weight: 600;">{{ $b->nama_barang }}</td>
                            <td><span class="badge badge-{{ $b->kategori_badge }}">{{ strtoupper($b->kategori) }}</span>
                            </td>
                            <td>{{ $b->satuan }}</td>
                            <td>
                                <span
                                    style="font-weight: 700; font-size: 15px; color: {{ $b->stok == 0 ? 'var(--danger)' : ($b->isStokMenipis() ? 'var(--warning)' : 'var(--success)') }}">
                                    {{ $b->stok }}
                                </span>
                            </td>
                            <td>{{ $b->stok_minimum }}</td>
                            <td>
                                @if ($b->stok == 0)
                                    <span class="badge badge-danger">HABIS</span>
                                @elseif($b->isStokMenipis())
                                    <span class="badge badge-warning">MENIPIS</span>
                                @else<span class="badge badge-success">AMAN</span>
                                @endif
                            </td>
                            @if (request('kategori') == 'tools' || !request('kategori'))
                                <td>
                                    @if ($b->isTools() && $b->stok_dipinjam > 0)
                                        <span class="badge badge-warning">{{ $b->stok_dipinjam }} dipinjam</span>
                                    @else
                                        <span style="color: var(--muted);">-</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state"><i class="fa-solid fa-clipboard-list"></i>
                                    <p>Tidak ada data</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <small style="color: var(--muted);">Total: {{ $barang->count() }} item | Diperbarui:
                {{ now()->format('d/m/Y H:i') }}</small>
        </div>
    </div>
@endsection
