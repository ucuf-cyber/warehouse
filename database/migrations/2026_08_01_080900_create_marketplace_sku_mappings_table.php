<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_sku_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->enum('marketplace', ['shopee', 'tiktok']);
            $table->string('marketplace_sku');
            $table->string('marketplace_product_name')->nullable();
            $table->timestamps();

            $table->unique(['marketplace', 'marketplace_sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_sku_mappings');
    }
};
