<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ProductCodeCounter;
use Illuminate\Support\Facades\DB;

/**
 * Generates thread-safe, sequential kode_barang values per category prefix
 * (e.g. SKN-000001, HRB-000001). Concurrent product creation is safe because
 * the counter row is locked for the duration of the increment transaction.
 */
class ProductCodeService
{
    public function generateFor(Category $category): string
    {
        return DB::transaction(function () use ($category) {
            ProductCodeCounter::firstOrCreate(['prefix' => $category->prefix]);

            $counter = ProductCodeCounter::query()
                ->where('prefix', $category->prefix)
                ->lockForUpdate()
                ->first();

            $counter->increment('last_number');

            return sprintf('%s-%06d', $category->prefix, $counter->last_number);
        });
    }
}
