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

class BarangKeluarExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    protected Collection $data;
    protected ?string $dari;
    protected ?string $sampai;
    protected ?string $kategori;
    protected ?string $status;

    public function __construct(Collection $data, ?string $dari = null, ?string $sampai = null, ?string $kategori = null, ?string $status = null)
    {
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
            'Tipe',
            'Rencana Kembali',
            'Status',
        ];
    }

    public function map($row): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $row->no_transaksi ?? '-',
            date('d-m-Y', strtotime($row->tanggal_keluar)),
            $row->pekerjaan->nama_pekerjaan ?? '-',
            $row->pekerjaan->nama_peminjam ?? '-',
            $row->barang->nama_barang ?? '-',
            strtoupper($row->barang->kategori ?? '-'),
            $row->jumlah,
            $row->status_pinjam ? 'PINJAM' : 'PERMANEN',
            $row->tgl_kembali_rencana ? $row->tgl_kembali_rencana->format('d-m-Y') : '-',
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
        return $this->status ? strtoupper($this->status) : 'Semua';
    }

    public function registerEvents(): array
    {
        $kategoriLabel = $this->getKategoriLabel();
        $statusLabel = $this->getStatusLabel();
        $dari = $this->dari ? date('d-m-Y', strtotime($this->dari)) : '-';
        $sampai = $this->sampai ? date('d-m-Y', strtotime($this->sampai)) : '-';

        return [
            AfterSheet::class => function (AfterSheet $event) use ($kategoriLabel, $statusLabel, $dari, $sampai) {
                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 5);

                // Baris 1: Judul (tengah)
                $sheet->setCellValue('A1', 'LAPORAN BARANG KELUAR');
                $sheet->mergeCells('A1:K1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                ]);

                // Baris 2: kosong
    
                // Baris 3: Periode (kiri)
                $sheet->setCellValue('A3', 'Periode: ' . $dari . ' s/d ' . $sampai);
                $sheet->mergeCells('A3:K3');
                $sheet->getStyle('A3')->applyFromArray([
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                // Baris 4: Kategori & Status (kiri)
                $sheet->setCellValue('A4', 'Kategori: ' . $kategoriLabel . '     Status: ' . $statusLabel);
                $sheet->mergeCells('A4:K4');
                $sheet->getStyle('A4')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);

                // Baris 5: kosong
    
                // Baris 6: Header tabel
                $sheet->getStyle('A6:K6')->applyFromArray([
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
                    $sheet->getStyle('A7:K' . $lastRow)->applyFromArray([
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
                foreach (range('A', 'K') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}