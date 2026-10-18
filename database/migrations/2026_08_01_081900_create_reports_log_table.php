<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports_log', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('e.g. laba-rugi, cashflow, stok, penjualan');
            $table->enum('format', ['pdf', 'excel']);
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->string('file_path')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports_log');
    }
};
