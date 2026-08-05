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

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        $productName = $this->mutation->product?->name ?? 'Barang';
        $url = route('mutations.approvals');

        return [
            'mutation_id' => $this->mutation->id,
            'title' => 'Permintaan Mutasi Baru',
            'message' => sprintf('%s diajukan oleh %s', $productName, $this->mutation->user?->name ?? 'User'),
            'url' => $url,
            'type' => 'pending',
        ];
    }
}
