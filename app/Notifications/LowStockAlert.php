<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    use Queueable;

    protected Product $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $stock = (int) ($this->product->stock ?? 0);
        $isOut = $stock <= 0 || $this->product->status === 'out_of_stock';
        $roomName = $this->product->room?->name ?? 'Gudang / Umum';

        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'kode_barang' => $this->product->kode_barang,
            'barcode' => $this->product->barcode,
            'stock' => $stock,
            'category' => $isOut ? 'out_of_stock' : 'low_stock',
            'priority' => $isOut ? 'critical' : 'high',
            'icon' => $isOut ? 'bi-exclamation-octagon-fill' : 'bi-exclamation-triangle-fill',
            'title' => $isOut ? 'Peringatan Stok Barang Habis!' : 'Pengingat Stok Barang Menipis',
            'url' => route('products.show', $this->product->id, false),
            'short_code' => 'p-'.$this->product->id,
            'message' => $isOut
                ? "Stok barang {$this->product->name} ({$this->product->kode_barang}) di {$roomName} telah HABIS (0 unit). Segera jadwalkan pengadaan."
                : "Stok barang {$this->product->name} ({$this->product->kode_barang}) di {$roomName} tersisa {$stock} unit.",
        ];
    }
}
