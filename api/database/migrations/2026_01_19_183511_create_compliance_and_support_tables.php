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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('inspection_type');
            $table->string('conducted_by');
            $table->date('scheduled_at')->nullable();
            $table->date('conducted_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->text('findings')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('inspection_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->string('order_reference');
            $table->text('description');
            $table->date('issued_at');
            $table->date('deadline')->nullable();
            $table->string('status')->default('open');
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('compliance_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_order_id')->nullable()->constrained('inspection_orders')->nullOnDelete();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('action_type');
            $table->text('description')->nullable();
            $table->string('responsible_party')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('status')->default('planned');
            $table->timestamps();
        });

        Schema::create('housing_support_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('program_type')->nullable();
            $table->string('authority')->nullable();
            $table->string('funding_source')->nullable();
            $table->date('application_window_start')->nullable();
            $table->date('application_window_end')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
        });

        Schema::create('support_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_support_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('housing_community_id')->nullable()->constrained()->nullOnDelete();
            $table->string('applicant_name');
            $table->string('applicant_contact')->nullable();
            $table->unsignedInteger('household_size')->default(1);
            $table->decimal('household_income', 12, 2)->nullable();
            $table->string('housing_status')->nullable();
            $table->date('submitted_at')->nullable();
            $table->string('status')->default('review');
            $table->timestamps();
        });

        Schema::create('eligibility_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_application_id')->constrained()->cascadeOnDelete();
            $table->string('reviewer_name');
            $table->date('review_date');
            $table->string('decision');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('nonprofit_leases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_support_program_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('tenant_name');
            $table->date('lease_start')->nullable();
            $table->date('lease_end')->nullable();
            $table->decimal('rent_amount', 12, 2)->nullable();
            $table->string('status')->default('active');
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('support_subsidies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('support_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tenancy_id')->nullable()->constrained('tenancies')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('owners')->nullOnDelete();
            $table->string('subsidy_type');
            $table->decimal('amount', 12, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_subsidies');
        Schema::dropIfExists('nonprofit_leases');
        Schema::dropIfExists('eligibility_reviews');
        Schema::dropIfExists('support_applications');
        Schema::dropIfExists('housing_support_programs');
        Schema::dropIfExists('compliance_actions');
        Schema::dropIfExists('inspection_orders');
        Schema::dropIfExists('inspections');
    }
};
