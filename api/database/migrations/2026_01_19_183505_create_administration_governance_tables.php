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
        Schema::create('assembly_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->dateTime('scheduled_for');
            $table->string('location')->nullable();
            $table->text('agenda')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('called_by')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('meeting_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('owners')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('representative_name')->nullable();
            $table->string('attendance_type')->default('owner');
            $table->boolean('is_present')->default(false);
            $table->timestamps();
        });

        Schema::create('assembly_decisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_meeting_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('legal_basis')->nullable();
            $table->string('required_majority')->default('simple');
            $table->unsignedInteger('votes_for')->default(0);
            $table->unsignedInteger('votes_against')->default(0);
            $table->unsignedInteger('votes_abstained')->default(0);
            $table->string('status')->default('draft');
            $table->date('effective_from')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('decision_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assembly_decision_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('owners')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('vote_value');
            $table->decimal('weight', 5, 2)->default(1);
            $table->timestamps();
        });

        Schema::create('professional_managers', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('manager_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('professional_manager_id')->constrained()->cascadeOnDelete();
            $table->string('license_number');
            $table->string('issued_by')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('status')->default('valid');
            $table->string('insurance_policy_number')->nullable();
            $table->timestamps();
        });

        Schema::create('manager_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->foreignId('professional_manager_id')->constrained()->cascadeOnDelete();
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->string('contract_reference')->nullable();
            $table->string('appointment_basis')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('manager_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manager_assignment_id')->constrained()->cascadeOnDelete();
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('summary')->nullable();
            $table->json('financial_overview')->nullable();
            $table->date('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_contractors', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('license_number')->nullable();
            $table->string('service_types')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('scope')->nullable();
            $table->decimal('budget_amount', 12, 2)->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('maintenance_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_program_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_contractor_id')->nullable()->constrained('maintenance_contractors')->nullOnDelete();
            $table->string('title');
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->date('planned_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->decimal('cost', 12, 2)->nullable();
            $table->string('status')->default('planned');
            $table->timestamps();
        });

        Schema::create('emergency_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->dateTime('reported_at');
            $table->string('reported_by')->nullable();
            $table->text('description');
            $table->string('severity')->default('medium');
            $table->dateTime('resolved_at')->nullable();
            $table->text('actions_taken')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reserve_funds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('name')->default('Rezervni fond');
            $table->decimal('minimum_monthly_contribution', 10, 2)->default(0);
            $table->string('currency', 8)->default('RSD');
            $table->string('bank_account_reference')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('reserve_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserve_fund_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('owners')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->date('due_date');
            $table->decimal('amount', 10, 2);
            $table->date('paid_at')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('reserve_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserve_fund_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_type');
            $table->decimal('amount', 12, 2);
            $table->date('occurred_on');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('common_area_transfer_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->text('description');
            $table->string('proposed_to')->nullable();
            $table->date('offer_date');
            $table->date('expiry_date')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('right_of_first_refusal_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('common_area_transfer_offer_id');
            $table->foreign('common_area_transfer_offer_id', 'rof_common_offer_fk')
                ->references('id')
                ->on('common_area_transfer_offers')
                ->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('owners')->nullOnDelete();
            $table->date('notified_at')->nullable();
            $table->date('responded_at')->nullable();
            $table->string('response')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('right_of_first_refusal_events');
        Schema::dropIfExists('common_area_transfer_offers');
        Schema::dropIfExists('reserve_transactions');
        Schema::dropIfExists('reserve_contributions');
        Schema::dropIfExists('reserve_funds');
        Schema::dropIfExists('emergency_incidents');
        Schema::dropIfExists('maintenance_tasks');
        Schema::dropIfExists('maintenance_programs');
        Schema::dropIfExists('maintenance_contractors');
        Schema::dropIfExists('manager_reports');
        Schema::dropIfExists('manager_assignments');
        Schema::dropIfExists('manager_licenses');
        Schema::dropIfExists('professional_managers');
        Schema::dropIfExists('decision_votes');
        Schema::dropIfExists('assembly_decisions');
        Schema::dropIfExists('meeting_attendees');
        Schema::dropIfExists('assembly_meetings');
    }
};
