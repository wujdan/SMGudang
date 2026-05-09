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
        $this->dari = $dari;
        $this->sampai = $sampai;
        $this->search = $search;
        $this->status = $status;
    }

    public function title(): string
    {
        return 'Rekap Per Pekerjaan';
    }

    private function getStatusLabel(): string
    {
        if ($this->status === 'aktif') {
            return 'AKTIF';
        }

        if ($this->status === 'selesai') {
            return 'SELESAI';
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
        $dari = $this->dari
            ? date('d-m-Y', strtotime($this->dari))
            : '-';

        $sampai = $this->sampai
            ? date('d-m-Y', strtotime($this->sampai))
            : '-';

        $statusLabel = $this->getStatusLabel();

        $search = $this->search ?? '-';

        $pekerjaan = $this->pekerjaan;

        $lastCol = self::LAST_COL;

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

                /*
                |--------------------------------------------------------------------------
                | TITLE
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    "A{$row}",
                    'REKAP BARANG PER PEKERJAAN'
                );

                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");

                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],

                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $sheet->getRowDimension($row)->setRowHeight(28);

                $row += 2;

                /*
                |--------------------------------------------------------------------------
                | INFO FILTER
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    "A{$row}",
                    "Periode : {$dari} s/d {$sampai}"
                );

                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");

                $row++;

                $sheet->setCellValue(
                    "A{$row}",
                    "Status : {$statusLabel}"
                );

                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");

                $row++;

                $sheet->setCellValue(
                    "A{$row}",
                    "Search : {$search}"
                );

                $sheet->mergeCells("A{$row}:{$lastCol}{$row}");

                $sheet->getStyle("A3:{$lastCol}5")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 10,
                    ],
                ]);

                $row += 2;

                /*
                |--------------------------------------------------------------------------
                | GRAND TOTAL
                |--------------------------------------------------------------------------
                */

                $grandQty = 0;
                $grandHpp = 0;

                /*
                |--------------------------------------------------------------------------
                | LOOP PEKERJAAN
                |--------------------------------------------------------------------------
                */

                foreach ($pekerjaan as $p) {

                    $totalQtyPekerjaan =
                        $p->transaksi->sum('jumlah');

                    $totalHppPekerjaan =
                        $p->transaksi->sum('total_hpp');

                    $grandQty += $totalQtyPekerjaan;
                    $grandHpp += $totalHppPekerjaan;

                    /*
                    |--------------------------------------------------------------------------
                    | HEADER PEKERJAAN
                    |--------------------------------------------------------------------------
                    */

                    $sheet->setCellValue(
                        "A{$row}",
                        'Kode'
                    );

                    $sheet->setCellValue(
                        "B{$row}",
                        $p->kode_pekerjaan
                    );

                    $sheet->setCellValue(
                        "C{$row}",
                        'Pekerjaan'
                    );

                    $sheet->setCellValue(
                        "D{$row}",
                        $p->nama_pekerjaan
                    );

                    $sheet->setCellValue(
                        "E{$row}",
                        'PIC'
                    );

                    $sheet->setCellValue(
                        "F{$row}",
                        $p->nama_peminjam
                    );

                    $sheet->mergeCells("F{$row}:{$lastCol}{$row}");

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->applyFromArray([

                            'font' => [
                                'bold' => true,
                                'color' => [
                                    'argb' => 'FF1E293B',
                                ],
                            ],

                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'FFE2E8F0',
                                ],
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => [
                                        'argb' => 'FFCBD5E1',
                                    ],
                                ],
                            ],
                        ]);

                    $row++;

                    $sheet->setCellValue(
                        "A{$row}",
                        'Status'
                    );

                    $sheet->setCellValue(
                        "B{$row}",
                        strtoupper($p->status)
                    );

                    $sheet->setCellValue(
                        "C{$row}",
                        'Lokasi'
                    );

                    $sheet->setCellValue(
                        "D{$row}",
                        $p->lokasi ?? '-'
                    );

                    $sheet->setCellValue(
                        "E{$row}",
                        'Mulai'
                    );

                    $sheet->setCellValue(
                        "F{$row}",
                        optional($p->tanggal_mulai)->format('d-m-Y')
                    );

                    $sheet->mergeCells("F{$row}:{$lastCol}{$row}");

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")
                        ->applyFromArray([

                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'FFF8FAFC',
                                ],
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => [
                                        'argb' => 'FFE2E8F0',
                                    ],
                                ],
                            ],
                        ]);

                    $row += 2;

                    /*
                    |--------------------------------------------------------------------------
                    | HEADER TABLE
                    |--------------------------------------------------------------------------
                    */

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
                                'color' => [
                                    'argb' => 'FFFFFFFF',
                                ],
                            ],

                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => [
                                    'argb' => 'FF2C5F8A',
                                ],
                            ],

                            'alignment' => [
                                'horizontal' =>
                                    Alignment::HORIZONTAL_CENTER,
                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                    'color' => [
                                        'argb' => 'FFAAAAAA',
                                    ],
                                ],
                            ],
                        ]);

                    $sheet->getRowDimension($row)->setRowHeight(22);

                    $row++;

                    /*
                    |--------------------------------------------------------------------------
                    | DATA TRANSAKSI
                    |--------------------------------------------------------------------------
                    */

                    if ($p->transaksi->isEmpty()) {

                        $sheet->setCellValue(
                            "A{$row}",
                            'Belum ada transaksi'
                        );

                        $sheet->mergeCells(
                            "A{$row}:{$lastCol}{$row}"
                        );

                        $sheet->getStyle(
                            "A{$row}:{$lastCol}{$row}"
                        )->applyFromArray([

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
                    } else {

                        $no = 1;

                        foreach ($p->transaksi as $t) {

                            $statusBarang = $t->status_pinjam
                                ? strtoupper($t->status_label)
                                : 'PERMANEN';

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

                            $sheet->getStyle(
                                "F{$row}:G{$row}"
                            )->getNumberFormat()
                                ->setFormatCode(
                                    '"Rp" #,##0'
                                );

                            $sheet->getStyle(
                                "A{$row}:{$lastCol}{$row}"
                            )->applyFromArray([

                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' =>
                                            Border::BORDER_THIN,
                                        'color' => [
                                            'argb' => 'FFDDDDDD',
                                        ],
                                    ],
                                ],

                                'alignment' => [
                                    'horizontal' =>
                                        Alignment::HORIZONTAL_CENTER,
                                    'vertical' =>
                                        Alignment::VERTICAL_CENTER,
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

                        /*
                        |--------------------------------------------------------------------------
                        | TOTAL PEKERJAAN
                        |--------------------------------------------------------------------------
                        */

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
                                    'argb' => 'FFF1F5F9',
                                ],
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                        $row += 2;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | GRAND TOTAL
                |--------------------------------------------------------------------------
                */

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
                        'size' => 11,
                    ],

                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'argb' => 'FFE2E8F0',
                        ],
                    ],

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' =>
                                Border::BORDER_THICK,
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | AUTO WIDTH
                |--------------------------------------------------------------------------
                */

                foreach (self::COLS as $col) {

                    $sheet->getColumnDimension($col)
                        ->setAutoSize(true);
                }

                /*
                |--------------------------------------------------------------------------
                | DEFAULT HEIGHT
                |--------------------------------------------------------------------------
                */

                $sheet->getDefaultRowDimension()
                    ->setRowHeight(20);
            },
        ];
    }
}