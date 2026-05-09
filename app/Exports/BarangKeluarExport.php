<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class BarangKeluarExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected Collection $data;
    protected ?string $dari;
    protected ?string $sampai;
    protected ?string $kategori;
    protected ?string $status;

    public function __construct(
        Collection $data,
        ?string $dari = null,
        ?string $sampai = null,
        ?string $kategori = null,
        ?string $status = null
    ) {
        $this->data = $data;
        $this->dari = $dari;
        $this->sampai = $sampai;
        $this->kategori = $kategori;
        $this->status = $status;
    }

    public function collection(): Collection
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'No Transaksi',
            'Tgl Keluar',
            'Pekerjaan',
            'PIC',
            'Barang',
            'Kategori',
            'Jumlah',
            'Harga Satuan',
            'Total HPP',
            'Tipe',
            'Rencana Kembali',
            'Status',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $hargaSatuan = $row->barang->prices ?? ($row->barang->harga ?? 0);

        return [
            $no,

            $row->no_transaksi ?? '-',

            $row->tanggal_keluar
                ? $row->tanggal_keluar->format('d-m-Y')
                : '-',

            $row->pekerjaan->nama_pekerjaan ?? '-',

            $row->pekerjaan->nama_peminjam ?? '-',

            $row->barang->nama_barang ?? '-',

            strtoupper($row->barang->kategori ?? '-'),

            number_format($row->jumlah),

            $row->barang->kategori === 'tools'
                ? '-'
                : 'Rp ' . number_format($hargaSatuan, 0, ',', '.'),

            $row->barang->kategori === 'tools'
                ? '-'
                : 'Rp ' . number_format($row->total_hpp ?? 0, 0, ',', '.'),

            $row->status_pinjam
                ? 'PINJAM'
                : 'PERMANEN',

            $row->tgl_kembali_rencana
                ? $row->tgl_kembali_rencana->format('d-m-Y')
                : '-',

            strtoupper($row->status_label ?? '-'),
        ];
    }

    private function getKategoriLabel(): string
    {
        if ($this->kategori === 'cons')
            return 'Consumable';

        if ($this->kategori === 'material')
            return 'Material';

        if ($this->kategori === 'tools')
            return 'Tools';

        if ($this->kategori)
            return strtoupper($this->kategori);

        return 'SEMUA';
    }

    private function getStatusLabel(): string
    {
        if ($this->status === 'permanen')
            return 'PERMANEN';

        if ($this->status === 'dipinjam')
            return 'DIPINJAM';

        if ($this->status === 'dikembalikan')
            return 'DIKEMBALIKAN';

        return 'SEMUA';
    }

    public function registerEvents(): array
    {
        $kategoriLabel = $this->getKategoriLabel();
        $statusLabel = $this->getStatusLabel();

        $dari = $this->dari
            ? date('d-m-Y', strtotime($this->dari))
            : '-';

        $sampai = $this->sampai
            ? date('d-m-Y', strtotime($this->sampai))
            : '-';

        return [
            AfterSheet::class => function (AfterSheet $event) use (
                $kategoriLabel,
                $statusLabel,
                $dari,
                $sampai
            ) {

                $sheet = $event->sheet->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | HEADER
                |--------------------------------------------------------------------------
                */

                $sheet->insertNewRowBefore(1, 5);

                // Judul
                $sheet->setCellValue('A1', 'LAPORAN BARANG KELUAR');
                $sheet->mergeCells('A1:M1');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Periode
                $sheet->setCellValue(
                    'A3',
                    'Periode : ' . $dari . ' s/d ' . $sampai
                );

                $sheet->mergeCells('A3:M3');

                // Filter
                $sheet->setCellValue(
                    'A4',
                    'Kategori : ' . $kategoriLabel .
                        '    |    Status : ' . $statusLabel
                );

                $sheet->mergeCells('A4:M4');

                $sheet->getStyle('A4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | HEADER TABLE
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A6:M6')->applyFromArray([
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
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],

                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'argb' => 'FFAAAAAA',
                            ],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | DATA STYLE
                |--------------------------------------------------------------------------
                */

                $lastRow = $sheet->getHighestRow();

                if ($lastRow >= 7) {

                    $sheet->getStyle('A7:M' . $lastRow)
                        ->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => [
                                        'argb' => 'FFD0D0D0',
                                    ],
                                ],
                            ],

                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);

                    // Wrap text
                    $sheet->getStyle('A7:M' . $lastRow)
                        ->getAlignment()
                        ->setWrapText(true);

                    // Alignment khusus
                    $sheet->getStyle('D7:F' . $lastRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    $sheet->getStyle('I7:J' . $lastRow)
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle('A7:M' . $lastRow)
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                /*
                |--------------------------------------------------------------------------
                | TOTAL
                |--------------------------------------------------------------------------
                */

                $footerRow = $lastRow + 1;

                $totalJumlah = $this->data->sum('jumlah');
                $totalHpp = $this->data->sum('total_hpp');

                $sheet->mergeCells("A{$footerRow}:G{$footerRow}");

                $sheet->setCellValue(
                    "A{$footerRow}",
                    'GRAND TOTAL'
                );

                $sheet->setCellValue(
                    "H{$footerRow}",
                    number_format($totalJumlah)
                );

                $sheet->setCellValue(
                    "J{$footerRow}",
                    'Rp ' . number_format($totalHpp, 0, ',', '.')
                );

                $sheet->getStyle("A{$footerRow}:M{$footerRow}")
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],

                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'argb' => 'FFF3F4F6',
                            ],
                        ],

                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => [
                                    'argb' => 'FFD0D0D0',
                                ],
                            ],
                        ],
                    ]);

                /*
                |--------------------------------------------------------------------------
                | COLUMN WIDTH
                |--------------------------------------------------------------------------
                */

                $widths = [
                    'A' => 6,
                    'B' => 20,
                    'C' => 14,
                    'D' => 28,
                    'E' => 20,
                    'F' => 30,
                    'G' => 14,
                    'H' => 10,
                    'I' => 18,
                    'J' => 20,
                    'K' => 14,
                    'L' => 16,
                    'M' => 16,
                ];

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)
                        ->setWidth($width);
                }

                /*
                |--------------------------------------------------------------------------
                | ROW HEIGHT
                |--------------------------------------------------------------------------
                */

                $sheet->getDefaultRowDimension()
                    ->setRowHeight(22);

                $sheet->getRowDimension(1)
                    ->setRowHeight(28);

                /*
                |--------------------------------------------------------------------------
                | PAGE SETUP
                |--------------------------------------------------------------------------
                */

                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

                $sheet->getPageSetup()
                    ->setFitToWidth(1);

                $sheet->freezePane('A7');
            },
        ];
    }
}