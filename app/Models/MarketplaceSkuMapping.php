<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceSkuMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'marketplace', 'marketplace_sku', 'marketplace_product_name',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
