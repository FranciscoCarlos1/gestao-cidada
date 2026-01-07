<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'prefeitura_id',
        'two_factor_enabled',
        'last_login_at',
        'status',
        'metadata',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'metadata' => 'json',
        ];
    }

    // Relationships

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function prefeitura(): BelongsTo
    {
        return $this->belongsTo(Prefeitura::class);
    }

    public function twoFactorAuth(): HasOne
    {
        return $this->hasOne(TwoFactorAuth::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function problems(): HasMany
    {
        return $this->hasMany(Problema::class, 'assigned_to');
    }

    // Helpers

    public function hasPermission(string $permission): bool
    {
        if ($this->role && $this->role->hasPermission($permission)) {
            return true;
        }
        return false;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role || ($this->role && $this->role->name === $role);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super', 'admin']);
    }

    public function canAccessPrefeitura($prefeituraId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        return $this->prefeitura_id === $prefeituraId;
    }
}
