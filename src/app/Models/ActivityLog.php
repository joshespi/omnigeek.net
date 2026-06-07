<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $table = 'activity_log';

    protected $fillable = ['user_id', 'action', 'subject_type', 'subject_id', 'subject_label', 'meta'];

    protected $casts = ['meta' => 'array', 'created_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(string $action, ?string $subjectType = null, ?int $subjectId = null, ?string $subjectLabel = null, array $meta = []): void
    {
        static::create([
            'user_id'       => auth()->id(),
            'action'        => $action,
            'subject_type'  => $subjectType,
            'subject_id'    => $subjectId,
            'subject_label' => $subjectLabel,
            'meta'          => $meta ?: null,
        ]);
    }
}
