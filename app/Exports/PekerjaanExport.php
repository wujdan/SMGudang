<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PekerjaanExport implements WithEvents, WithTitle
{
    protected Collection $pekerjaan;
    protected ?string $dari;
    protected ?string $sampai;
    protected ?string $search;
    protected ?string $status;

    // Kolom tabel barang
    const COLS = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
    const LAST_COL = 'G';

    public function __construct(
        Collection $pekerjaan,
        ?string $dari = null,
        ?string $sampai = null,
        ?string $search = null,
        ?string $status = null
    ) {
        $this->pekerjaan = $pekerjaan;
        $this->dari      = $dari;
        $this->sampai    = $sampai;
        $this->search    = $search;
        $this->status    = $status;
    }

    public function title(): string
    {
        return 'Rekap Per Pekerjaan';
    }

    private function getStatusLabel(): string
    {
        if ($this->status === 'aktif')   return 'Aktif';
        if ($this->status === 'selesai') return 'Selesai';
        return 'SEMUA';
    }

    private function formatKategori(?string $value): string
    {
        if ($value === 'cons')     return 'Consumable';
        if ($value === 'material') return 'Material';
        if ($value === 'tools')    return 'Tools';
        return $value ? strtoupper($value) : '-';
    }

    public function registerEvents(): array
    {
        $dari        = $this->dari   ? date('d-m-Y', strtotime($this->dari))   : '-';
        $sampai      = $this->sampai ? date('d-m-Y', strtotime($this->sampai)) : '-';
        $statusLabel = $this->getStatusLabel();
        $search      = $this->search ?? '-';
        $pekerjaan   = $this->pekerjaan;
        $lastCol     = self::LAST_COL;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($dari, $sampai, $statusLabel, $search, $pekerjaan, $lastCol) {
                $sheet = $event->sheet->getDelegate();
                $row   = 1;

                // ── JUDUL ────────────────────────────────────────────────
                $sheet->setCellValue("A{$row}", 'REKAP BARANG PER PEKERJAAN');
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'name' => 'Arial'],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);
                $row++;

                // ── PERIODE ──────────────────────────────────────────────
                $row++; // baris kosong
                $sheet->setCellValue("A{$row}", "Periode: {$dari} s/d {$sampai}");
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font'      => ['name' => 'Arial', 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $row++;

                // ── STATUS & PENCARIAN ───────────────────────────────────
                $sheet->setCellValue("A{$row}", "Status: {$statusLabel}     Pencarian: {$search}");
                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
                $row++;
                $row++; // baris kosong sebelum data

                // ── LOOP PER PEKERJAAN ───────────────────────────────────
                foreach ($pekerjaan as $p) {

                    // Header pekerjaan (background biru muda)
                    $statusPekerjaan = strtoupper($p->status);
                    $lokasi          = $p->lokasi ?? '-';
                    $tglMulai        = $p->tanggal_mulai ? date('d-m-Y', strtotime($p->tanggal_mulai)) : '-';

                    $sheet->setCellValue("A{$row}", $p->kode_pekerjaan);
                    $sheet->setCellValue("B{$row}", $p->nama_pekerjaan);
                    $sheet->setCellValue("C{$row}", $statusPekerjaan);
                    $sheet->setCellValue("D{$row}", $p->nama_peminjam);
                    $sheet->setCellValue("E{$row}", $lokasi);
                    $sheet->setCellValue("F{$row}", "Mulai: {$tglMulai}");
                    $sheet->mergeCells("F{$row}:{$lastCol}{$row}");
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10, 'color' => ['argb' => 'FF1E3A5F']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD6E4F0']],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF9DB8CC']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $row++;

                    // Header kolom barang (background biru tua)
                    $headers = ['No', 'Barang', 'Kategori', 'Total Jumlah', 'Satuan', 'Ket.', 'Status Barang'];
                    foreach (self::COLS as $i => $col) {
                        $sheet->setCellValue("{$col}{$row}", $headers[$i]);
                    }
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF2C5F8A']],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $row++;

                    // ── BARIS BARANG ─────────────────────────────────────
                    if ($p->transaksi->isEmpty()) {
                        $sheet->setCellValue("A{$row}", '-');
                        $sheet->setCellValue("B{$row}", 'Belum ada barang dicatat');
                        $sheet->mergeCells("B{$row}:{$lastCol}{$row}");
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'font'      => ['name' => 'Arial', 'size' => 10, 'color' => ['argb' => 'FF999999']],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        ]);
                        $row++;
                    } else {
                        // Group barang yang sama nama+kategori+status, lalu jumlahkan
                        $grouped = $p->transaksi
                            ->groupBy(fn($t) =>
                                ($t->barang->nama_barang ?? '') . '|' .
                                ($t->barang->kategori    ?? '') . '|' .
                                ($t->status_pinjam       ?? 'permanen')
                            )
                            ->map(fn($group) => [
                                'nama_barang'        => $group->first()->barang->nama_barang ?? '-',
                                'kategori'           => $group->first()->barang->kategori    ?? null,
                                'satuan'             => $group->first()->barang->satuan      ?? '-',
                                'status_pinjam'      => $group->first()->status_pinjam,
                                'status_label'       => $group->first()->status_label        ?? null,
                                'tgl_kembali_aktual' => $group->first()->tgl_kembali_aktual,
                                'total_jumlah'       => $group->sum('jumlah'),
                            ]);

                        $no = 1;
                        foreach ($grouped as $row_data) {
                            $statusBarang = $row_data['status_pinjam']
                                ? $row_data['status_label']
                                : 'Keluar Permanen';
                            if ($row_data['status_pinjam'] && $row_data['tgl_kembali_aktual']) {
                                $statusBarang .= ' (' . date('d-m-Y', strtotime($row_data['tgl_kembali_aktual'])) . ')';
                            }

                            $sheet->setCellValue("A{$row}", $no);
                            $sheet->setCellValue("B{$row}", $row_data['nama_barang']);
                            $sheet->setCellValue("C{$row}", $this->formatKategori($row_data['kategori']));
                            $sheet->setCellValue("D{$row}", $row_data['total_jumlah']);
                            $sheet->setCellValue("E{$row}", $row_data['satuan']);
                            $sheet->setCellValue("F{$row}", '-');
                            $sheet->setCellValue("G{$row}", $statusBarang);

                            $bgColor = ($no % 2 === 0) ? 'FFF0F4F8' : 'FFFFFFFF';
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                                'font'      => ['name' => 'Arial', 'size' => 10],
                                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
                                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            ]);

                            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                            $no++;
                            $row++;
                        }
                    }

                    $row++; // jarak antar pekerjaan
                } // ── END foreach pekerjaan ──────────────────────────────

                // ── AUTO WIDTH ───────────────────────────────────────────
                foreach (self::COLS as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }

                // Tinggi baris default
                $sheet->getDefaultRowDimension()->setRowHeight(16);
            },
        ];
    }
}