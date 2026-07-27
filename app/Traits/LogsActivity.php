<?php
// app/Traits/LogsActivity.php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($model) => $model->recordActivity('created'));
        static::updated(fn ($model) => $model->recordActivity('updated'));
        static::deleted(fn ($model) => $model->recordActivity('deleted'));
    }

    public function recordActivity(string $action): void
    {
        ActivityLog::create([
            'user_id'      => Auth::id(),
            'action'       => $action,
            'subject_type' => static::class,
            'subject_id'   => $this->id,
            'description'  => $this->buildActivityDescription($action),
        ]);
    }

    protected function buildActivityDescription(string $action): string
    {
        // Gunakan property_exists / isset check, JANGAN deklarasikan
        // property $activityLabel di trait ini
        $label = $this->activityLabel ?? class_basename($this);
        $name  = $this->{$this->activityNameField ?? 'name'} ?? '#' . $this->id;

        return match ($action) {
            'created' => "Menambah {$label}: {$name}",
            'updated' => "Mengubah {$label}: {$name}",
            'deleted' => "Menghapus {$label}: {$name}",
            default   => "{$label}: {$name}",
        };
    }
}