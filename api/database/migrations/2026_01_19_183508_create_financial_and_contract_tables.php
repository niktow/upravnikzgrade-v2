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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tax_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('payment_interval')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('expense_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('type')->default('one_time');
            $table->string('status')->default('pending');
            $table->decimal('amount', 12, 2);
            $table->date('incurred_on')->nullable();
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('currency', 8)->default('RSD');
            $table->decimal('opening_balance', 14, 2)->default(0);
            $table->decimal('current_balance', 14, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('owners')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->string('direction');
            $table->decimal('amount', 14, 2);
            $table->date('transaction_date');
            $table->date('value_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('purpose_code')->nullable();
            $table->string('counterparty_name')->nullable();
            $table->string('status')->default('recorded');
            $table->json('raw_payload')->nullable();
            $table->timestamps();
        });

        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('insurer_name');
            $table->string('policy_number');
            $table->text('coverage_description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('premium_amount', 12, 2)->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('liability_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('insurance_policy_id')->nullable()->constrained('insurance_policies')->nullOnDelete();
            $table->date('incident_date')->nullable();
            $table->text('description');
            $table->decimal('claim_amount', 12, 2)->nullable();
            $table->string('status')->default('open');
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('tenancies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->string('tenant_name');
            $table->string('tenant_contact')->nullable();
            $table->decimal('rent_amount', 12, 2)->nullable();
            $table->date('lease_start')->nullable();
            $table->date('lease_end')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('rental_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_id')->constrained('tenancies')->cascadeOnDelete();
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->date('paid_at')->nullable();
            $table->string('status')->default('pending');
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('eviction_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenancy_id')->constrained('tenancies')->cascadeOnDelete();
            $table->string('reason');
            $table->date('opened_at');
            $table->string('status')->default('open');
            $table->date('decision_date')->nullable();
            $table->string('outcome')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eviction_cases');
        Schema::dropIfExists('rental_payments');
        Schema::dropIfExists('tenancies');
        Schema::dropIfExists('liability_claims');
        Schema::dropIfExists('insurance_policies');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('vendors');
    }
};
