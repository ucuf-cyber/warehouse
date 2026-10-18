<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    /**
     * Owner decided per-product minimum stock wasn't worth the extra form
     * field — every product now shares this one restock threshold.
     */
    const MIN_STOCK_THRESHOLD = 5;

    protected $fillable = [
        'kode_barang', 'sku', 'barcode', 'qr_code', 'name', 'description', 'unit',
        'category_id', 'brand_id', 'supplier_id', 'sub_rack_id',
        'harga_modal', 'harga_jual', 'harga_marketplace', 'berat',
        'current_stock', 'status', 'foto', 'catatan',
    ];

    protected $casts = [
        'harga_modal' => 'integer',
        'harga_jual' => 'integer',
        'harga_marketplace' => 'integer',
        'berat' => 'integer',
        'current_stock' => 'integer',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'harga_modal', 'harga_jual', 'status', 'category_id'])
            ->logOnlyDirty()
            ->useLogName('produk');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function subRack(): BelongsTo
    {
        return $this->belongsTo(SubRack::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function marketplaceSkuMappings(): HasMany
    {
        return $this->hasMany(MarketplaceSkuMapping::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isBelowMinimumStock(): bool
    {
        return $this->current_stock <= self::MIN_STOCK_THRESHOLD;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeBelowMinimumStock($query)
    {
        return $query->where('current_stock', '<=', self::MIN_STOCK_THRESHOLD);
    }
}
