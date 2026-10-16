<?php

namespace App\Notifications;

use App\Models\Mutation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MutationStatusUpdated extends Notification
{
    use Queueable;

    protected Mutation $mutation;

    protected string $decision;

    public function __construct(Mutation $mutation, string $decision)
    {
        $this->mutation = $mutation;
        $this->decision = $decision;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isApproved = $this->decision === 'approved';
        $productName = $this->mutation->product?->name ?? 'Barang';
        $approverName = $this->mutation->approver?->name ?? 'Administrator';
        $statusLabel = $isApproved ? 'DISETUJUI' : 'DITOLAK';

        return [
            'mutation_id' => $this->mutation->id,
            'product_id' => $this->mutation->product_id,
            'product_name' => $productName,
            'decision' => $this->decision,
            'category' => $isApproved ? 'approved' : 'rejected',
            'priority' => $isApproved ? 'normal' : 'high',
            'icon' => $isApproved ? 'bi-check2-circle' : 'bi-x-octagon',
            'title' => $isApproved ? 'Pengajuan Mutasi Disetujui' : 'Pengajuan Mutasi Ditolak',
            'url' => $this->mutation->product_id
                ? route('products.show', $this->mutation->product_id, false)
                : route('mutations.index', ['highlight' => $this->mutation->id], false),
            'short_code' => 'm-'.$this->mutation->id,
            'message' => "Pengajuan mutasi {$this->mutation->quantity} unit untuk barang {$productName} telah {$statusLabel} oleh {$approverName}.",
        ];
    }
}
