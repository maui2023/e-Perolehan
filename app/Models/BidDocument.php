<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BidDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'bid_id',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'sha256_checksum',
        'is_financial',
        'disk',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_financial' => 'boolean',
    ];

    public function bid(): BelongsTo
    {
        return $this->belongsTo(Bid::class);
    }
}
