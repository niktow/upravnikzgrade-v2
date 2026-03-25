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
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('type')->default('company')->after('name'); // 'company' ili 'individual'
            $table->string('bank_account')->nullable()->after('address');
            $table->string('bank_name')->nullable()->after('bank_account');
            $table->text('notes')->nullable()->after('bank_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['type', 'bank_account', 'bank_name', 'notes']);
        });
    }
};
