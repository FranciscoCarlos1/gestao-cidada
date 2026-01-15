<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TwoFactorAuth extends Model
{
    use HasFactory;

    protected $table = 'two_factor_auths';

    protected $fillable = ['user_id', 'secret', 'enabled', 'backup_codes', 'confirmed_at'];

    protected $hidden = ['secret', 'backup_codes'];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'backup_codes' => 'json',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }
}
