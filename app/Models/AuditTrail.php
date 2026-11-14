<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditTrail extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'auditable_type',
        'auditable_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'prev_hash',
        'hash',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(
        string $eventType,
        string $description,
        ?Model $auditable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?int $userId = null
    ): self {
        $lastRecord = static::latest('id')->first();
        $prevHash = $lastRecord ? $lastRecord->hash : str_repeat('0', 64);

        $userId = $userId ?? auth()->id();
        $ip = request()->ip() ?? '127.0.0.1';
        $ua = request()->userAgent() ?? 'System';
        $time = now()->toIso8601String();

        $payload = json_encode([
            'prev_hash' => $prevHash,
            'event_type' => $eventType,
            'user_id' => $userId,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable ? $auditable->getKey() : null,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip' => $ip,
            'time' => $time,
        ]);

        $hash = hash('sha256', $payload);

        return static::create([
            'user_id' => $userId,
            'event_type' => $eventType,
            'auditable_type' => $auditable ? get_class($auditable) : null,
            'auditable_id' => $auditable ? $auditable->getKey() : null,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'prev_hash' => $prevHash,
            'hash' => $hash,
        ]);
    }
}
