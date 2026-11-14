<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'tender_id',
        'bid_id',
        'evaluator_user_id',
        'evaluation_type',
        'score_criteria_1',
        'score_criteria_2',
        'score_criteria_3',
        'total_score',
        'is_passed',
        'comments',
        'completed_at',
        'digital_sign_hash',
    ];

    protected $casts = [
        'score_criteria_1' => 'decimal:2',
        'score_criteria_2' => 'decimal:2',
        'score_criteria_3' => 'decimal:2',
        'total_score' => 'decimal:2',
        'is_passed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function tender(): BelongsTo
    {
        return $this->belongsTo(Tender::class);
    }

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }
}
