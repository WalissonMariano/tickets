<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TaskNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'note',
        'attachment',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditHistories(): MorphMany
    {
        return $this->morphMany(History::class, 'auditable')->latest();
    }
}
