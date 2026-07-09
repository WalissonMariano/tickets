<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'code',
        'requested_at',
        'resolved_at',
        'reporter_id',
        'assignee_id',
        'severity',
        'status',
        'title',
        'description',
        'attachment',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TaskNote::class);
    }

    public function histories(): MorphMany
    {
        return $this->auditHistories();
    }

    public function auditHistories(): MorphMany
    {
        return $this->morphMany(History::class, 'auditable')->latest();
    }
}
