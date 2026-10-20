<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class UserLoggedInNotice extends Notification
{
    use Queueable;

    protected User $actor;

    protected string $ipAddress;

    protected string $loginMethod;

    public function __construct(User $actor, ?string $ipAddress = null, string $loginMethod = 'standar')
    {
        $this->actor = $actor;
        $this->ipAddress = $ipAddress ?: '127.0.0.1';
        $this->loginMethod = $loginMethod;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $roleLabel = $this->actor->roleLabel();
        $methodLabel = $this->loginMethod === 'quick' ? 'Quick Sign-In' : 'Login Standar';

        return [
            'user_id' => $this->actor->id,
            'user_name' => $this->actor->name,
            'user_email' => $this->actor->email,
            'user_role' => $roleLabel,
            'ip_address' => $this->ipAddress,
            'login_method' => $this->loginMethod,
            'category' => 'info',
            'priority' => 'normal',
            'icon' => 'bi-person-check-fill',
            'title' => 'Pengguna Lain Baru Saja Login',
            'url' => route('activity-logs.index', [], false),
            'message' => "{$this->actor->name} ({$roleLabel}) baru saja masuk ke sistem melalui {$methodLabel} (IP: {$this->ipAddress}).",
        ];
    }
}
