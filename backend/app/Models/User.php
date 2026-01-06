<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'prefeitura_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function prefeitura()
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function problemas()
    {
        return $this->hasMany(Problema::class);
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super'], true);
    }
}
