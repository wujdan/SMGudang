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

class BarangMasukExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected Collection $data;
    protected ?string $dari;
    protected ?string $sampai;
    protected ?string $kategori;

    public function __construct(
        Collection $data,
        ?string $dari = null,
        ?string $sampai = null,
        ?string $kategori = null
    ) {
        $this->data = $data;
        $this->dari = $dari;
        $this->sampai = $sampai;
        $this->kategori = $kategori;
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
            'Tanggal',
            'Barang',
            'Kategori',
            'Jumlah',
            'Stok Sebelum',
            'Stok Sesudah',
            'Sumber',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->no_transaksi ?? '-',
            date('d-m-Y', strtotime($row->tanggal)),
            $row->barang->nama_barang ?? '-',
            $this->formatKategori($row->barang->kategori ?? '-'),
            $row->jumlah,
            $row->stok_sebelum,
            $row->stok_sesudah,
            $row->sumber ?? '-',
        ];
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

    private function getKategoriLabel(): string
    {
        if ($this->kategori === 'cons') {
            return 'Consumable';
        }

        if ($this->kategori === 'material') {
            return 'Material';
        }

        if ($this->kategori === 'tools') {
            return 'Tools';
        }

        if ($this->kategori) {
            return strtoupper($this->kategori);
        }

        return 'SEMUA';
    }

    public function registerEvents(): array
    {
        $kategoriLabel = $this->getKategoriLabel();

        $dari = $this->dari
            ? date('d-m-Y', strtotime($this->dari))
            : '-';

        $sampai = $this->sampai
            ? date('d-m-Y', strtotime($this->sampai))
            : '-';

        return [
            AfterSheet::class => function (AfterSheet $event) use (
                $kategoriLabel,
                $dari,
                $sampai
            ) {
                $sheet = $event->sheet->getDelegate();

                $lastRow = $sheet->getHighestRow();

                // Tambah ruang atas
                $sheet->insertNewRowBefore(1, 5);

                /*
                |--------------------------------------------------------------------------
                | TITLE
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue('A1', 'LAPORAN BARANG MASUK');
                $sheet->mergeCells('A1:I1');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 15,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | INFO
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue(
                    'A3',
                    'Periode : ' . $dari . ' s/d ' . $sampai
                );

                $sheet->mergeCells('A3:I3');

                $sheet->setCellValue(
                    'A4',
                    'Kategori : ' . $kategoriLabel
                );

                $sheet->mergeCells('A4:I4');

                $sheet->getStyle('A3:I4')->applyFromArray([
                    'font' => [
                        'size' => 11,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | HEADER TABLE
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A6:I6')->applyFromArray([
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
                                'argb' => 'FFBFBFBF',
                            ],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | DATA TABLE
                |--------------------------------------------------------------------------
                */

                if ($lastRow >= 7) {
                    $sheet->getStyle('A7:I' . ($lastRow + 5))
                        ->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => [
                                        'argb' => 'FFD9D9D9',
                                    ],
                                ],
                            ],
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_CENTER,
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                            ],
                        ]);

                    // Rata kiri kolom teks
                    $sheet->getStyle('B7:E' . ($lastRow + 5))
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                    $sheet->getStyle('I7:I' . ($lastRow + 5))
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                }

                /*
                |--------------------------------------------------------------------------
                | COLUMN WIDTH
                |--------------------------------------------------------------------------
                */

                $widths = [
                    'A' => 6,
                    'B' => 22,
                    'C' => 15,
                    'D' => 35,
                    'E' => 16,
                    'F' => 12,
                    'G' => 16,
                    'H' => 16,
                    'I' => 28,
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

                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(6)->setRowHeight(22);

                /*
                |--------------------------------------------------------------------------
                | WRAP TEXT
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:I' . ($lastRow + 5))
                    ->getAlignment()
                    ->setWrapText(true);

                /*
                |--------------------------------------------------------------------------
                | FREEZE HEADER
                |--------------------------------------------------------------------------
                */

                $sheet->freezePane('A7');
            },
        ];
    }
}