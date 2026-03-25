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
        Schema::create('unit_type_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('unit_type'); // stan, lokal, garaza, ostava
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->decimal('fee_per_sqm', 10, 2)->nullable(); // opciono - cena po m²
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->timestamps();

            // Unique po zajednici i tipu (ili globalno ako je community null)
            $table->unique(['housing_community_id', 'unit_type', 'valid_from'], 'unit_pricing_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_type_pricings');
    }
};
