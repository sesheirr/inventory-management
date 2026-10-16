<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $attributes = [
        'subject_type' => 'system',
    ];

    protected $fillable = [
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record a rich audit activity log safely using the exact schema columns.
     *
     * @param  array<string, mixed>|null  $properties
     */
    public static function record(
        string $action,
        string $description = '',
        mixed $subject = null,
        ?int $subjectId = null,
        ?int $userId = null,
        ?string $module = null,
        ?string $subjectType = null,
        ?string $subjectLabel = null,
        ?array $properties = null,
        mixed $request = null
    ): ?self {
        try {
            $resolvedUserId = $userId ?? auth()->id();
            $resolvedSubjectType = $subjectType ?? ($module ? strtolower($module) : 'system');
            $resolvedSubjectId = $subjectId;

            if (is_object($subject)) {
                $resolvedSubjectType = class_basename($subject);
                $resolvedSubjectId = $resolvedSubjectId ?? (isset($subject->id) ? (int) $subject->id : null);
            } elseif (is_string($subject) && $subject !== '') {
                $resolvedSubjectType = $subject;
            } elseif (is_string($subjectType) && str_contains($subjectType, '\\')) {
                $resolvedSubjectType = class_basename($subjectType);
            }

            return self::create([
                'user_id' => $resolvedUserId,
                'action' => strtolower($action),
                'subject_type' => $resolvedSubjectType,
                'subject_id' => $resolvedSubjectId,
                'description' => mb_substr($description, 0, 250),
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
