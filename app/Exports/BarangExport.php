<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    public function collection()
    {
        return Product::with(['category', 'room'])->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Barang',
            'Model / Tipe',
            'Kapasitas',
            'Jumlah Stok',
            'Kategori',
            'Ruangan',
            'Status',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->edition ?? '-',
            $product->description ?? '-',
            ($product->stock ?? 0) . ' pcs',
            $product->category?->name ?? '-',
            $product->room?->name ?? '-',
            $this->formatStatus($product->status),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 28,
            'C' => 18,
            'D' => 16,
            'E' => 16,
            'F' => 20,
            'G' => 20,
            'H' => 16,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'],
            ],
        ]);

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function formatStatus($status): string
    {
        if ($status === 1 || $status === '1' || $status === true) {
            return 'Aktif';
        }

        return 'Tidak Aktif';
    }
}
