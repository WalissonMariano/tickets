<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class History extends Model
{
    public const UPDATED_AT = null;

    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_ASSIGNED = 'assigned';
    public const ACTION_RESOLVED = 'resolved';
    public const ACTION_CLOSED = 'closed';

    public const ACTION_LABELS = [
        self::ACTION_CREATED => 'Cadastrado',
        self::ACTION_UPDATED => 'Atualizado',
        self::ACTION_DELETED => 'Excluído',
        self::ACTION_ASSIGNED => 'Atribuída',
        self::ACTION_RESOLVED => 'Resolvida',
        self::ACTION_CLOSED => 'Fechada',
    ];

    protected $fillable = [
        'user_id',
        'auditable_type',
        'auditable_id',
        'action',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function actionLabel(string $action): string
    {
        return self::ACTION_LABELS[$action] ?? $action;
    }

    public function auditableLabel(): string
    {
        $type = class_basename($this->auditable_type);

        if (! $this->auditable) {
            return match ($type) {
                'User' => 'Usuário',
                'Project' => 'Projeto',
                'Task' => 'Tarefa',
                'TaskNote' => 'Anotação',
                default => $type,
            } . ' #' . $this->auditable_id;
        }

        return match ($type) {
            'User' => 'Usuário: ' . $this->auditable->name,
            'Project' => 'Projeto: ' . $this->auditable->name,
            'Task' => 'Tarefa #' . $this->auditable->code . ' — ' . $this->auditable->title,
            'TaskNote' => 'Anotação da tarefa #' . ($this->auditable->task?->code ?? $this->auditable->task_id),
            default => $type . ' #' . $this->auditable_id,
        };
    }
}
