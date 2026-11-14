<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'vendor_id',
        'bid_reference',
        'blind_code',
        'bid_amount',
        'encrypted_bid_amount',
        'decrypted_bid_amount',
        'completion_duration_months',
        'technical_summary',
        'status',
        'sha256_package_hash',
        'submitted_at',
        'opened_at',
        'submission_slip_pdf_path',
        'technical_score',
        'financial_score',
        'total_score',
    ];

    protected $casts = [
        'decrypted_bid_amount' => 'decimal:2',
        'completion_duration_months' => 'integer',
        'submitted_at' => 'datetime',
        'opened_at' => 'datetime',
        'technical_score' => 'decimal:2',
        'financial_score' => 'decimal:2',
        'total_score' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Bid $bid) {
            if (empty($bid->bid_reference)) {
                $bid->bid_reference = 'BID-'.date('Y').'-'.strtoupper(Str::random(6));
            }
        });
    }

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BidDocument::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function setBidAmountAttribute(float $amount): void
    {
        $this->attributes['encrypted_bid_amount'] = Crypt::encryptString((string) $amount);
    }

    public function getEncryptedBidAmountRawAttribute(): string
    {
        return $this->attributes['encrypted_bid_amount'] ?? '';
    }

    public function unlockBidAmount(): ?float
    {
        if (filled($this->decrypted_bid_amount)) {
            return (float) $this->decrypted_bid_amount;
        }

        if (empty($this->encrypted_bid_amount)) {
            return null;
        }

        try {
            $decrypted = (float) Crypt::decryptString($this->encrypted_bid_amount);
            $this->decrypted_bid_amount = $decrypted;
            $this->save();

            return $decrypted;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isSealed(): bool
    {
        return in_array($this->status, ['submitted', 'sealed']);
    }

    public function isOpened(): bool
    {
        return in_array($this->status, ['opened', 'passed_technical', 'failed_technical', 'awarded', 'rejected']);
    }
}
