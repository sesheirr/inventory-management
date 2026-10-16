<?php

namespace App\Notifications;

use App\Models\Mutation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MutationPending extends Notification
{
    use Queueable;

    protected Mutation $mutation;

    public function __construct(Mutation $mutation)
    {
        $this->mutation = $mutation;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $typeLabels = [
            'masuk' => 'Barang Masuk',
            'keluar' => 'Barang Keluar',
            'pindah_ruang' => 'Pindah Ruangan',
        ];
        $typeLabel = $typeLabels[$this->mutation->type] ?? ucfirst((string) $this->mutation->type);
        $productName = $this->mutation->product?->name ?? 'Barang';
        $userName = $this->mutation->user?->name ?? 'Pengguna';

        return [
            'mutation_id' => $this->mutation->id,
            'product_id' => $this->mutation->product_id,
            'product_name' => $productName,
            'user_name' => $userName,
            'type' => $this->mutation->type,
            'quantity' => $this->mutation->quantity,
            'category' => 'approval',
            'priority' => 'high',
            'icon' => 'bi-shield-exclamation',
            'title' => 'Persetujuan Mutasi Diperlukan',
            'url' => route('mutations.index', ['status' => 'pending', 'highlight' => $this->mutation->id], false),
            'short_code' => 'm-'.$this->mutation->id,
            'message' => "Pengajuan mutasi ({$typeLabel} — {$this->mutation->quantity} unit) untuk barang {$productName} oleh {$userName} menunggu persetujuan Anda.",
        ];
    }
}
