<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TenderPurchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'vendor_id',
        'purchase_reference',
        'amount',
        'status',
        'affanpay_bill_id',
        'affanpay_payment_ref',
        'payment_url',
        'idempotency_key',
        'paid_at',
        'receipt_pdf_path',
        'payment_payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_payload' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (TenderPurchase $purchase) {
            if (empty($purchase->purchase_reference)) {
                $purchase->purchase_reference = 'PUR-'.date('Ymd').'-'.strtoupper(Str::random(6));
            }
            if (empty($purchase->idempotency_key)) {
                $purchase->idempotency_key = hash('sha256', Str::uuid().microtime());
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

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
