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
        return ['No', 'Kode', 'Nama Barang', 'Kategori', 'Satuan', 'Stok', 'Status', 'Dipinjam'];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        if ($row->stok == 0) {
            $status = 'Habis';
        } elseif ($row->stok <= $row->stok_minimum) {
            $status = 'Menipis';
        } else {
            $status = 'Aman';
        }

        return [
            $no,
            $row->kode_barang,
            $row->nama_barang,
            $this->formatKategori($row->kategori),
            $row->satuan,
            $row->stok,
            $status,
            $row->dipinjam ?? '-',
        ];
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
        return $this->status ? strtoupper($this->status) : 'SEMUA';
    }

    public function registerEvents(): array
    {
        $kategoriLabel = $this->getKategoriLabel();
        $statusLabel = $this->getStatusLabel();
        $tanggal = date('d-m-Y');

        return [
            AfterSheet::class => function (AfterSheet $event) use ($kategoriLabel, $statusLabel, $tanggal) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 5);

                // Baris 1: Judul (tengah)
                $sheet->setCellValue('A1', 'LAPORAN STOK TERKINI');
                $sheet->mergeCells('A1:H1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 2: kosong
    
                // Baris 3: Tanggal (kiri)
                $sheet->setCellValue('A3', 'Tanggal: ' . $tanggal);
                $sheet->mergeCells('A3:H3');
                $sheet->getStyle('A3')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                // Baris 4: Kategori & Status (kiri)
                $sheet->setCellValue('A4', 'Kategori: ' . $kategoriLabel . '     Status: ' . $statusLabel);
                $sheet->mergeCells('A4:H4');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                // Baris 5: kosong
    
                // Baris 6: Header tabel
                $sheet->getStyle('A6:H6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF2C5F8A'],
                    ],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FFAAAAAA'],
                        ],
                    ],
                ]);

                // Baris 7 ke bawah: data
                $lastRow = $sheet->getHighestRow();
                if ($lastRow >= 7) {
                    $sheet->getStyle('A7:H' . $lastRow)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFCCCCCC'],
                            ],
                        ],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);
                }

                // Auto width semua kolom
                foreach (range('A', 'H') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}