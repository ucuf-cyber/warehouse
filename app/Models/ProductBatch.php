<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'batch_number', 'expired_date', 'qty'];

    protected $casts = [
        'expired_date' => 'date',
        'qty' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeExpiringWithin($query, int $days)
    {
        return $query->whereNotNull('expired_date')
            ->whereBetween('expired_date', [now(), now()->addDays($days)]);
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expired_date')->where('expired_date', '<', now());
    }
}
