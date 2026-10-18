<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['income', 'expense']);
            $table->string('category')->comment('e.g. Packing, Ongkir, Admin Marketplace, Iklan, Lain-lain, Penjualan, Pembelian');
            $table->unsignedBigInteger('amount');
            $table->date('transaction_date');
            $table->string('description')->nullable();
            $table->nullableMorphs('reference');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'transaction_date']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
    }
};
