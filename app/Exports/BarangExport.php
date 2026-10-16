<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BarangExport implements FromCollection, WithColumnWidths, WithHeadings, WithMapping, WithStyles
{
    public function __construct(
        protected array $filters = []
    ) {}

    public function collection()
    {
        $search = trim((string) ($this->filters['search'] ?? ''));
        $categoryId = $this->filters['category_id'] ?? null;
        $roomId = $this->filters['room_id'] ?? null;
        $status = $this->filters['status'] ?? null;

        return Product::with(['category', 'room'])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('kode_barang', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('subcategory', 'like', "%{$search}%")
                        ->orWhere('edition', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('room', fn ($rq) => $rq->where('name', 'like', "%{$search}%"));
                });
            })
            ->when(! empty($categoryId), fn ($q) => $q->where('category_id', $categoryId))
            ->when(! empty($roomId), fn ($q) => $q->where('room_id', $roomId))
            ->when(! empty($status) && $status !== 'all', function ($q) use ($status) {
                if ($status === 'low_stock') {
                    $q->where('stock', '>', 0)->where('stock', '<=', 5);
                } elseif ($status === 'out_of_stock') {
                    $q->where(fn ($sq) => $sq->where('status', 'out_of_stock')->orWhere('stock', '<=', 0));
                } else {
                    $q->where('status', $status);
                }
            })
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Kode Barang',
            'Barcode',
            'Nama Barang',
            'Kategori',
            'Subkategori',
            'Ruangan',
            'Jumlah Stok',
            'Status',
            'Deskripsi / Spesifikasi',
        ];
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->kode_barang ?? '-',
            $product->barcode ?? '-',
            $product->name,
            $product->category_name,
            $product->subcategory ?? '-',
            $product->room_name ?? '-',
            ($product->stock ?? 0).' unit',
            $this->formatStatus($product->status, (int) ($product->stock ?? 0)),
            $product->description ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 16,
            'C' => 16,
            'D' => 32,
            'E' => 24,
            'F' => 20,
            'G' => 26,
            'H' => 14,
            'I' => 16,
            'J' => 36,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1E3A8A'],
            ],
        ]);

        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function formatStatus($status, int $stock = 0): string
    {
        if ($status === 'out_of_stock' || $stock <= 0) {
            return 'Stok Habis';
        }

        if ($status === 'active' || $status === 1 || $status === '1' || $status === true) {
            return 'Aktif';
        }

        return 'Tidak Aktif';
    }
}
