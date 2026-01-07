<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Problema extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'descricao',
        'bairro',
        'rua',
        'numero',
        'complemento',
        'cep',
        'cidade',
        'uf',
        'latitude',
        'longitude',
        'status',
        'status_history',
        'prefeitura_id',
        'user_id',
        'assigned_to',
        'internal_notes',
        'resolved_at',
    ];

    protected $casts = [
        'status_history' => 'json',
        'resolved_at' => 'datetime',
    ];

    public function prefeitura(): BelongsTo
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // Status helper methods
    public function isOpen(): bool
    {
        return $this->status === 'aberto';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'em_andamento';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolvido';
    }

    public function updateStatus(string $newStatus, string $reason = null): void
    {
        $oldStatus = $this->status;
        $this->status = $newStatus;

        $history = $this->status_history ?? [];
        $history[] = [
            'from' => $oldStatus,
            'to' => $newStatus,
            'reason' => $reason,
            'changed_at' => now()->toIso8601String(),
            'changed_by' => auth()->id(),
        ];

        $this->status_history = $history;

        if ($newStatus === 'resolvido') {
            $this->resolved_at = now();
        }

        $this->save();

        // Log da ação
        AuditLog::log('status_changed', 'Problema', $this->id, [
            'from' => $oldStatus,
            'to' => $newStatus,
        ]);
    }
}

