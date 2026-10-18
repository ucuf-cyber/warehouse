<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per category prefix. ProductObserver locks the row (lockForUpdate)
     * and increments it inside a transaction so concurrent product creation
     * never generates duplicate kode_barang values.
     */
    public function up(): void
    {
        Schema::create('product_code_counters', function (Blueprint $table) {
            $table->id();
            $table->string('prefix', 5)->unique();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_code_counters');
    }
};
