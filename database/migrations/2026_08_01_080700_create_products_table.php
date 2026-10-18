<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique()->comment('Auto-generated, e.g. SKN-000001');
            $table->string('sku')->nullable()->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('qr_code')->nullable()->unique();

            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit', 30)->default('pcs');

            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sub_rack_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedBigInteger('harga_modal')->default(0);
            $table->unsignedBigInteger('harga_jual')->default(0);
            $table->unsignedBigInteger('harga_marketplace')->default(0);

            $table->unsignedInteger('berat')->default(0)->comment('Weight in grams');
            $table->integer('current_stock')->default(0)->comment('Denormalized cache, source of truth is stock_movements');
            $table->unsignedInteger('min_stock')->default(0);

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('foto')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
            $table->index('current_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
