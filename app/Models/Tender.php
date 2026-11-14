<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tender extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'title',
        'slug',
        'category',
        'procurement_type',
        'description',
        'scope_of_work',
        'eligibility_criteria',
        'estimated_budget',
        'document_price',
        'tender_deposit_amount',
        'published_at',
        'closing_at',
        'status',
        'min_technical_score',
        'dual_witness_opened_at',
        'awarded_bid_id',
        'awarded_at',
        'award_notes',
        'sst_letter_pdf_path',
        'opening_slip_pdf_path',
        'evaluation_report_pdf_path',
        'archived_to_minio_at',
        'created_by_user_id',
    ];

    protected $casts = [
        'estimated_budget' => 'decimal:2',
        'document_price' => 'decimal:2',
        'tender_deposit_amount' => 'decimal:2',
        'published_at' => 'datetime',
        'closing_at' => 'datetime',
        'dual_witness_opened_at' => 'datetime',
        'awarded_at' => 'datetime',
        'archived_to_minio_at' => 'datetime',
        'min_technical_score' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Tender $tender) {
            if (empty($tender->slug)) {
                $tender->slug = Str::slug($tender->title).'-'.strtolower(Str::random(6));
            }
            if (empty($tender->reference_no)) {
                $year = date('Y');
                $count = static::whereYear('created_at', $year)->count() + 1;
                $tender->reference_no = sprintf('TDR-%s-%04d', $year, $count);
            }
        });
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function awardedBid(): BelongsTo
    {
        return $this->belongsTo(Bid::class, 'awarded_bid_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TenderDocument::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(TenderPurchase::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function witnesses(): HasMany
    {
        return $this->hasMany(TenderBoxWitness::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function isOpenForBidding(): bool
    {
        if ($this->status !== 'published') {
            return false;
        }

        $now = now();
        if ($this->published_at && $now->lt($this->published_at)) {
            return false;
        }

        if ($this->closing_at && $now->gt($this->closing_at)) {
            return false;
        }

        return true;
    }

    public function isClosed(): bool
    {
        if (in_array($this->status, ['closed', 'box_opened', 'evaluating_technical', 'evaluating_commercial', 'awarded'])) {
            return true;
        }

        return $this->closing_at && now()->gte($this->closing_at);
    }

    public function isBoxOpened(): bool
    {
        return in_array($this->status, ['box_opened', 'evaluating_technical', 'evaluating_commercial', 'awarded']);
    }

    public function isAwarded(): bool
    {
        return $this->status === 'awarded';
    }
}
