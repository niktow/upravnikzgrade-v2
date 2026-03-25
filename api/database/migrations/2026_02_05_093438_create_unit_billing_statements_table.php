<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unit_billing_statements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('period'); // Format: 2026-01
            $table->decimal('opening_balance', 14, 2)->default(0); // Početni saldo (prenos)
            $table->decimal('charges', 14, 2)->default(0); // Zaduženja u periodu
            $table->decimal('payments', 14, 2)->default(0); // Uplate u periodu
            $table->decimal('closing_balance', 14, 2)->default(0); // Krajnji saldo
            $table->date('generated_at');
            $table->string('pdf_path')->nullable(); // Putanja do PDF fajla
            $table->timestamps();

            $table->unique(['unit_id', 'period']);
            $table->index(['housing_community_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_billing_statements');
    }
};
