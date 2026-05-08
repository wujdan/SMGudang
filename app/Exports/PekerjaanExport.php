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

    // KOLUMNYA DITAMBAH
    const COLS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
    const LAST_COL = 'I';

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
        if ($this->status === 'aktif') {
            return 'Aktif';
        }

        if ($this->status === 'selesai') {
            return 'Selesai';
        }

        return 'SEMUA';
    }

    private function formatKategori(?string $value): string
    {
        if ($value === 'cons') {
            return 'Consumable';
        }

        if ($value === 'material') {
            return 'Material';
        }

        if ($value === 'tools') {
            return 'Tools';
        }

        return $value ? strtoupper($value) : '-';
    }

    public function registerEvents(): array
    {
        $dari        = $this->dari
            ? date('d-m-Y', strtotime($this->dari))
            : '-';

        $sampai      = $this->sampai
            ? date('d-m-Y', strtotime($this->sampai))
            : '-';

        $statusLabel = $this->getStatusLabel();

        $search      = $this->search ?? '-';

        $pekerjaan   = $this->pekerjaan;

        $lastCol     = self::LAST_COL;

        return [

            AfterSheet::class => function (AfterSheet $event)
            use (
                $dari,
                $sampai,
                $statusLabel,
                $search,
                $pekerjaan,
                $lastCol
            ) {

                $sheet = $event->sheet->getDelegate();

                $row = 1;

                // =====================================================
                // JUDUL
                // =====================================================

                $sheet->setCellValue(
                    "A{$row}",
                    'REKAP BARANG PER PEKERJAAN'
                );

                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");

                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'name' => 'Arial',
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                $row++;

                // =====================================================
                // FILTER INFO
                // =====================================================

                $row++;

                $sheet->setCellValue(
                    "A{$row}",
                    "Periode: {$dari} s/d {$sampai}"
                );

                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");

                $row++;

                $sheet->setCellValue(
                    "A{$row}",
                    "Status: {$statusLabel} | Search: {$search}"
                );

                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");

                $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                    ->getFont()
                    ->setBold(true);

                $row += 2;

                // =====================================================
                // GRAND TOTAL
                // =====================================================

                $grandQty = 0;
                $grandHpp = 0;
                $grandTransaksi = 0;

                // =====================================================
                // LOOP PEKERJAAN
                // =====================================================

                foreach ($pekerjaan as $p) {

                    $totalQtyPekerjaan =
                        $p->transaksi->sum('jumlah');

                    $totalHppPekerjaan =
                        $p->transaksi->sum('total_hpp');

                    $totalTransaksiPekerjaan =
                        $p->transaksi->count();

                    $grandQty += $totalQtyPekerjaan;
                    $grandHpp += $totalHppPekerjaan;
                    $grandTransaksi += $totalTransaksiPekerjaan;

                    // =================================================
                    // HEADER PEKERJAAN
                    // =================================================

                    $sheet->setCellValue(
                        "A{$row}",
                        $p->kode_pekerjaan
                    );

                    $sheet->setCellValue(
                        "B{$row}",
                        $p->nama_pekerjaan
                    );

                    $sheet->setCellValue(
                        "C{$row}",
                        strtoupper($p->status)
                    );

                    $sheet->setCellValue(
                        "D{$row}",
                        $p->nama_peminjam
                    );

                    $sheet->setCellValue(
                        "E{$row}",
                        $p->lokasi ?? '-'
                    );

                    $sheet->setCellValue(
                        "F{$row}",
                        'Mulai: ' .
                        optional($p->tanggal_mulai)->format('d-m-Y')
                    );

                    $sheet->mergeCells("F{$row}:{$lastCol}{$row}");

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FF1E3A5F'],
                            ],

                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'FFD6E4F0'
                                ],
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                    $row++;

                    // =================================================
                    // SUMMARY PEKERJAAN
                    // =================================================

                    $sheet->setCellValue(
                        "A{$row}",
                        "Total Transaksi"
                    );

                    $sheet->setCellValue(
                        "B{$row}",
                        $totalTransaksiPekerjaan
                    );

                    $sheet->setCellValue(
                        "C{$row}",
                        "Total Qty"
                    );

                    $sheet->setCellValue(
                        "D{$row}",
                        $totalQtyPekerjaan
                    );

                    $sheet->setCellValue(
                        "E{$row}",
                        "Total HPP"
                    );

                    $sheet->setCellValue(
                        "F{$row}",
                        $totalHppPekerjaan
                    );

                    $sheet->mergeCells("F{$row}:{$lastCol}{$row}");

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->applyFromArray([
                            'font' => [
                                'bold' => true,
                            ],

                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'FFF8FAFC'
                                ],
                            ],
                        ]);

                    $sheet->getStyle("F{$row}")
                        ->getNumberFormat()
                        ->setFormatCode(
                            '"Rp" #,##0'
                        );

                    $row++;

                    // =================================================
                    // HEADER TABLE
                    // =================================================

                    $headers = [
                        'No',
                        'Barang',
                        'Kategori',
                        'Qty',
                        'Satuan',
                        'HPP / Unit',
                        'Total HPP',
                        'Tanggal',
                        'Status',
                    ];

                    foreach (self::COLS as $i => $col) {

                        $sheet->setCellValue(
                            "{$col}{$row}",
                            $headers[$i]
                        );
                    }

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->applyFromArray([

                            'font' => [
                                'bold' => true,
                                'color' => ['argb' => 'FFFFFFFF'],
                            ],

                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'FF2C5F8A'
                                ],
                            ],

                            'alignment' => [
                                'horizontal' =>
                                    Alignment::HORIZONTAL_CENTER,
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                    $row++;

                    // =================================================
                    // DATA BARANG
                    // =================================================

                    if ($p->transaksi->isEmpty()) {

                        $sheet->setCellValue(
                            "A{$row}",
                            'Belum ada transaksi'
                        );

                        $sheet->mergeCells(
                            "A{$row}:{$lastCol}{$row}"
                        );

                        $row++;

                    } else {

                        $no = 1;

                        foreach ($p->transaksi as $t) {

                            $statusBarang = $t->status_pinjam
                                ? $t->status_label
                                : 'Keluar Permanen';

                            if (
                                $t->status_pinjam &&
                                $t->tgl_kembali_aktual
                            ) {
                                $statusBarang .=
                                    ' (' .
                                    date(
                                        'd-m-Y',
                                        strtotime(
                                            $t->tgl_kembali_aktual
                                        )
                                    ) .
                                    ')';
                            }

                            $sheet->setCellValue(
                                "A{$row}",
                                $no
                            );

                            $sheet->setCellValue(
                                "B{$row}",
                                $t->barang->nama_barang ?? '-'
                            );

                            $sheet->setCellValue(
                                "C{$row}",
                                $this->formatKategori(
                                    $t->barang->kategori ?? null
                                )
                            );

                            $sheet->setCellValue(
                                "D{$row}",
                                $t->jumlah
                            );

                            $sheet->setCellValue(
                                "E{$row}",
                                $t->barang->satuan ?? '-'
                            );

                            $sheet->setCellValue(
                                "F{$row}",
                                $t->hpp_satuan
                            );

                            $sheet->setCellValue(
                                "G{$row}",
                                $t->total_hpp
                            );

                            $sheet->setCellValue(
                                "H{$row}",
                                optional(
                                    $t->tanggal_keluar
                                )->format('d-m-Y')
                            );

                            $sheet->setCellValue(
                                "I{$row}",
                                $statusBarang
                            );

                            // FORMAT RUPIAH
                            $sheet->getStyle("F{$row}:G{$row}")
                                ->getNumberFormat()
                                ->setFormatCode(
                                    '"Rp" #,##0'
                                );

                            // STYLE
                            $bgColor = ($no % 2 === 0)
                                ? 'FFF8FAFC'
                                : 'FFFFFFFF';

                            $sheet->getStyle(
                                "A{$row}:{$lastCol}{$row}"
                            )->applyFromArray([

                                'fill' => [
                                    'fillType' =>
                                        Fill::FILL_SOLID,

                                    'startColor' => [
                                        'argb' => $bgColor
                                    ],
                                ],

                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' =>
                                            Border::BORDER_THIN,
                                    ],
                                ],
                            ]);

                            $sheet->getStyle("B{$row}")
                                ->getAlignment()
                                ->setHorizontal(
                                    Alignment::HORIZONTAL_LEFT
                                );

                            $sheet->getStyle("I{$row}")
                                ->getAlignment()
                                ->setHorizontal(
                                    Alignment::HORIZONTAL_LEFT
                                );

                            $row++;
                            $no++;
                        }

                        // =============================================
                        // FOOTER TOTAL PEKERJAAN
                        // =============================================

                        $sheet->setCellValue(
                            "A{$row}",
                            'TOTAL'
                        );

                        $sheet->mergeCells(
                            "A{$row}:C{$row}"
                        );

                        $sheet->setCellValue(
                            "D{$row}",
                            $totalQtyPekerjaan
                        );

                        $sheet->setCellValue(
                            "G{$row}",
                            $totalHppPekerjaan
                        );

                        $sheet->getStyle(
                            "G{$row}"
                        )->getNumberFormat()
                            ->setFormatCode(
                                '"Rp" #,##0'
                            );

                        $sheet->getStyle(
                            "A{$row}:{$lastCol}{$row}"
                        )->applyFromArray([

                            'font' => [
                                'bold' => true,
                            ],

                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,

                                'startColor' => [
                                    'argb' => 'FFE2E8F0'
                                ],
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                        $row++;
                    }

                    $row++;
                }

                // =====================================================
                // GRAND TOTAL AKHIR
                // =====================================================

                $sheet->setCellValue(
                    "A{$row}",
                    'GRAND TOTAL'
                );

                $sheet->mergeCells("A{$row}:C{$row}");

                $sheet->setCellValue(
                    "D{$row}",
                    $grandQty
                );

                $sheet->setCellValue(
                    "G{$row}",
                    $grandHpp
                );

                $sheet->getStyle("G{$row}")
                    ->getNumberFormat()
                    ->setFormatCode(
                        '"Rp" #,##0'
                    );

                $sheet->getStyle(
                    "A{$row}:{$lastCol}{$row}"
                )->applyFromArray([

                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],

                    'fill' => [
                        'fillType' =>
                            Fill::FILL_SOLID,

                        'startColor' => [
                            'argb' => 'FFBBF7D0'
                        ],
                    ],

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                                Border::BORDER_THICK,
                        ],
                    ],
                ]);

                // =====================================================
                // AUTO WIDTH
                // =====================================================

                foreach (self::COLS as $col) {

                    $sheet->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                // =====================================================
                // DEFAULT STYLE
                // =====================================================

                $sheet->getDefaultRowDimension()
                    ->setRowHeight(18);
            },
        ];
    }
}