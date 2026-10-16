<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ProductCreatedNotice extends Notification
{
    use Queueable;

    protected Product $product;

    protected ?User $actor;

    public function __construct(Product $product, ?User $actor = null)
    {
        $this->product = $product;
        $this->actor = $actor;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $actorName = $this->actor?->name ?? 'Petugas';
        $roomName = $this->product->room?->name ?? 'Diskominfo Garut';

        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'kode_barang' => $this->product->kode_barang,
            'barcode' => $this->product->barcode,
            'category' => 'info',
            'priority' => 'normal',
            'icon' => 'bi-box-seam-fill',
            'title' => 'Data Barang Baru Ditambahkan',
            'url' => route('products.show', $this->product->id, false),
            'short_code' => 'p-'.$this->product->id,
            'message' => "{$actorName} menambahkan barang baru: {$this->product->name} ({$this->product->stock} unit) di {$roomName}.",
        ];
    }
}
