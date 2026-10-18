<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubRack extends Model
{
    use HasFactory;

    protected $fillable = ['rack_id', 'code', 'name'];

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
