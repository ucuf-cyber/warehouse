<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'prefix', 'icon', 'description', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category) {
            $category->slug ??= Str::slug($category->name);

            // The admin form no longer collects a prefix — it's derived from
            // the category name. Seeders/factories may still pass one
            // explicitly (kept verbatim, just normalized to uppercase).
            $category->prefix = $category->prefix
                ? strtoupper($category->prefix)
                : static::generatePrefix($category->name);
        });
    }

    /**
     * First 3 letters of the name, uppercased; on a collision with an
     * existing category's prefix, the tail is replaced with a counter
     * (SKI, SKI1, SKI2, ...) until it's unique. Capped at 5 chars to match
     * the column's original manual-entry limit.
     */
    protected static function generatePrefix(string $name): string
    {
        $letters = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));
        $base = substr($letters, 0, 3) ?: 'CAT';

        $prefix = $base;
        $counter = 1;

        while (static::withTrashed()->where('prefix', $prefix)->exists()) {
            $suffix = (string) $counter;
            $prefix = substr($base, 0, 5 - strlen($suffix)).$suffix;
            $counter++;
        }

        return $prefix;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
