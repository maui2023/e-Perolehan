<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            if ($user->role === 'vendor' && ! $user->vendor()->exists()) {
                Vendor::create([
                    'user_id' => $user->id,
                    'company_name' => $user->name,
                    'registration_number' => 'SSM-'.strtoupper(Str::random(8)),
                    'company_email' => $user->email,
                    'company_phone' => $user->phone,
                    'compliance_status' => 'pending',
                ]);
            }
        });
    }

    public function getFilamentName(): string
    {
        return "{$this->name} (".strtoupper(str_replace('_', ' ', $this->role)).')';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'admin' => in_array($this->role, ['admin', 'urus_setia']),
            'penilai' => in_array($this->role, ['admin', 'penilai_teknikal', 'penilai_kewangan', 'saksi_pembuka']),
            'vendor' => $this->role === 'vendor',
            default => false,
        };
    }

    public function vendor(): HasOne
    {
        return $this->hasOne(Vendor::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class, 'evaluator_user_id');
    }

    public function boxWitnessAffirmations(): HasMany
    {
        return $this->hasMany(TenderBoxWitness::class);
    }

    public function auditTrails(): HasMany
    {
        return $this->hasMany(AuditTrail::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUrusSetia(): bool
    {
        return in_array($this->role, ['admin', 'urus_setia']);
    }

    public function isPenilaiTeknikal(): bool
    {
        return in_array($this->role, ['admin', 'penilai_teknikal']);
    }

    public function isPenilaiKewangan(): bool
    {
        return in_array($this->role, ['admin', 'penilai_kewangan']);
    }

    public function isSaksiPembuka(): bool
    {
        return in_array($this->role, ['admin', 'saksi_pembuka']);
    }

    public function isVendor(): bool
    {
        return $this->role === 'vendor';
    }
}
