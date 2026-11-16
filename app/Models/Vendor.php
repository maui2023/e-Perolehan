<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'registration_number',
        'tax_identification_number',
        'company_email',
        'company_phone',
        'address',
        'city',
        'state',
        'postcode',
        'mof_registration_number',
        'mof_codes',
        'cidb_registration_number',
        'cidb_grade',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'bumiputera_status',
        'compliance_status',
        'compliance_verified_at',
        'verified_by_user_id',
        'compliance_notes',
        'anti_bribery_agreed_at',
        'ssm_cert_path',
        'mof_cert_path',
        'cidb_cert_path',
        'bank_statement_path',
    ];

    protected $casts = [
        'bumiputera_status' => 'boolean',
        'compliance_verified_at' => 'datetime',
        'anti_bribery_agreed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(TenderPurchase::class);
    }

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class);
    }

    public function isVerified(): bool
    {
        return $this->compliance_status === 'verified';
    }

    /**
     * Cache IDs of tenders purchased by this vendor in-memory per request lifecycle.
     *
     * @var array<int, int>|null
     */
    protected ?array $memoizedPurchasedTenderIds = null;

    /**
     * Cache bids keyed by tender_id in-memory per request lifecycle.
     *
     * @var array<int, Bid>|null
     */
    protected ?array $memoizedBidsByTenderId = null;

    /**
     * @return array<int, int>
     */
    public function getPurchasedTenderIds(): array
    {
        if ($this->memoizedPurchasedTenderIds === null) {
            $this->memoizedPurchasedTenderIds = $this->purchases()
                ->where('status', 'paid')
                ->pluck('tender_id', 'tender_id')
                ->all();
        }

        return $this->memoizedPurchasedTenderIds;
    }

    public function hasPurchasedTender(int $tenderId): bool
    {
        return isset($this->getPurchasedTenderIds()[$tenderId]);
    }

    public function getBidForTender(int $tenderId): ?Bid
    {
        if ($this->memoizedBidsByTenderId === null) {
            $this->memoizedBidsByTenderId = $this->bids()->get()->keyBy('tender_id')->all();
        }

        return $this->memoizedBidsByTenderId[$tenderId] ?? null;
    }

    public function clearMemoizedCaches(): void
    {
        $this->memoizedPurchasedTenderIds = null;
        $this->memoizedBidsByTenderId = null;
    }
}
