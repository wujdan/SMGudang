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

class StokExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected Collection $barang;
    protected ?string $kategori;
    protected ?string $status;

    public function __construct(Collection $barang, ?string $kategori = null, ?string $status = null)
    {
        $this->barang = $barang;
        $this->kategori = $kategori;
        $this->status = $status;
    }

    public function collection(): Collection
    {
        return $this->barang;
    }

    public function headings(): array
    {
        $headings = [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Stok',
            'Harga',
            'Total Nilai',
            'Status',
        ];

        if ($this->kategori == 'tools' || !$this->kategori) {
            $headings[] = 'Dipinjam';
        }

        return $headings;
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        $harga = $row->prices ?? ($row->harga ?? 0);
        $totalNilai = $row->stok * $harga;

        // Status stok
        if ($row->stok == 0) {
            $status = 'HABIS';
        } elseif ($row->stok <= $row->stok_minimum) {
            $status = 'MENIPIS';
        } else {
            $status = 'AMAN';
        }

        $data = [
            $no,
            $row->kode_barang,
            $row->nama_barang,
            $this->formatKategori($row->kategori),
            $row->satuan,
            $row->stok,

            $row->kategori === 'tools'
                ? '-'
                : 'Rp ' . number_format($harga, 0, ',', '.'),

            $row->kategori === 'tools'
                ? '-'
                : 'Rp ' . number_format($totalNilai, 0, ',', '.'),

            $status,
        ];

        if ($this->kategori == 'tools' || !$this->kategori) {
            $data[] = $row->isTools() && ($row->stok_dipinjam ?? 0) > 0
                ? $row->stok_dipinjam
                : '-';
        }

        return $data;
    }

    private function formatKategori(?string $value): string
    {
        if ($value === 'cons')
            return 'Consumable';

        if ($value === 'material')
            return 'Material';

        if ($value === 'tools')
            return 'Tools';

        return $value ? strtoupper($value) : '-';
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
        return $this->status
            ? strtoupper($this->status)
            : 'SEMUA';
    }

    public function registerEvents(): array
    {
        $kategoriLabel = $this->getKategoriLabel();
        $statusLabel = $this->getStatusLabel();

        $tanggalCetak = date('d-m-Y H:i:s');

        $showDipinjam = $this->kategori == 'tools' || !$this->kategori;

        $lastColumn = $showDipinjam ? 'J' : 'I';

        return [
            AfterSheet::class => function (AfterSheet $event) use (
                $kategoriLabel,
                $statusLabel,
                $tanggalCetak,
                $showDipinjam,
                $lastColumn
            ) {

                $sheet = $event->sheet->getDelegate();

                // Tambah row atas
                $sheet->insertNewRowBefore(1, 5);

                /*
                |--------------------------------------------------------------------------
                | HEADER
                |--------------------------------------------------------------------------
                */

                // Judul
                $sheet->setCellValue('A1', 'LAPORAN STOK BARANG');
                $sheet->mergeCells("A1:{$lastColumn}1");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);

                // Tanggal cetak
                $sheet->setCellValue('A3', 'Tanggal Cetak : ' . $tanggalCetak);
                $sheet->mergeCells("A3:{$lastColumn}3");

                // Kategori & status
                $sheet->setCellValue(
                    'A4',
                    'Kategori : ' . $kategoriLabel . '    |    Status : ' . $statusLabel
                );

                $sheet->mergeCells("A4:{$lastColumn}4");

                $sheet->getStyle('A4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | TABLE HEADER
                |--------------------------------------------------------------------------
                */

                $headerRange = "A6:{$lastColumn}6";

                $sheet->getStyle($headerRange)->applyFromArray([
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

                $dataRange = "A7:{$lastColumn}{$lastRow}";

                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => [
                                'argb' => 'FFDADADA',
                            ],
                        ],
                    ],

                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | ALIGNMENT
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle("A6:{$lastColumn}{$lastRow}")
                    ->getAlignment()
                    ->setWrapText(true);

                $sheet->getStyle("A7:A{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("D7:D{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $sheet->getStyle("G7:H{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                /*
                |--------------------------------------------------------------------------
                | TOTAL ASET
                |--------------------------------------------------------------------------
                */

                $totalNilaiAset = $this->barang->sum(function ($item) {

                    $harga = $item->prices ?? ($item->harga ?? 0);

                    return $item->kategori === 'tools'
                        ? 0
                        : ($item->stok * $harga);
                });

                $footerRow = $lastRow + 1;

                $sheet->setCellValue(
                    "A{$footerRow}",
                    'TOTAL NILAI ASET STOK'
                );

                $sheet->mergeCells("A{$footerRow}:G{$footerRow}");

                $sheet->setCellValue(
                    "H{$footerRow}",
                    'Rp ' . number_format($totalNilaiAset, 0, ',', '.')
                );

                $sheet->getStyle("A{$footerRow}:{$lastColumn}{$footerRow}")
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
                    ]);

                /*
                |--------------------------------------------------------------------------
                | COLUMN WIDTH
                |--------------------------------------------------------------------------
                */

                $widths = [
                    'A' => 6,
                    'B' => 18,
                    'C' => 35,
                    'D' => 16,
                    'E' => 12,
                    'F' => 10,
                    'G' => 18,
                    'H' => 20,
                    'I' => 14,
                    'J' => 12,
                ];

                foreach ($widths as $column => $width) {

                    if ($column === 'J' && !$showDipinjam) {
                        continue;
                    }

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
                    ->setOrientation(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
                    );

                $sheet->getPageSetup()
                    ->setFitToWidth(1);

                $sheet->freezePane('A7');
            },
        ];
    }
}