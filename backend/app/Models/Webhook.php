<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Webhook extends Model
{
    use HasFactory;

    protected $fillable = ['prefeitura_id', 'event', 'url', 'secret', 'active', 'retry_count', 'last_triggered_at'];

    protected $casts = [
        'active' => 'boolean',
        'last_triggered_at' => 'datetime',
    ];

    public function prefeitura(): BelongsTo
    {
        return $this->belongsTo(Prefeitura::class);
    }
}
