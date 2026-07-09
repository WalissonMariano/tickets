<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'group_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    public function reportedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'reporter_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assignee_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(History::class);
    }

    public function auditHistories(): MorphMany
    {
        return $this->morphMany(History::class, 'auditable')->latest();
    }

    public function taskNotes(): HasMany
    {
        return $this->hasMany(TaskNote::class);
    }

    public function isInGroup(string ...$codes): bool
    {
        return in_array($this->group?->code, $codes, true);
    }

    public function canAssignTasks(): bool
    {
        return $this->isInGroup('ADMIN', 'SUPORTE');
    }

    public function isAdmin(): bool
    {
        return $this->isInGroup('ADMIN');
    }

    public function canDeleteTask(Task $task): bool
    {
        return $task->status === 'open' || $this->isAdmin();
    }
}
