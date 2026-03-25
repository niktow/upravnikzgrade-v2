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
        Schema::create('unit_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('type', ['charge', 'payment']); // charge = zaduženje, payment = uplata
            $table->string('description');
            $table->decimal('amount', 12, 2); // pozitivan iznos
            $table->string('reference_type')->nullable(); // billing_statement, bank_transaction, vendor_invoice
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('period')->nullable(); // YYYY-MM format za mesečna zaduženja
            $table->timestamps();

            $table->index(['unit_id', 'date']);
            $table->index(['unit_id', 'type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_ledger');
    }
};
