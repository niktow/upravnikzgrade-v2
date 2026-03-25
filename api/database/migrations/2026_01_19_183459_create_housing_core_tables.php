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
        Schema::create('housing_communities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address_line');
            $table->string('city');
            $table->string('postal_code', 12)->nullable();
            $table->string('registry_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->date('established_at')->nullable();
            $table->string('status')->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('national_id', 32)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('identifier');
            $table->string('type')->default('apartment');
            $table->string('floor')->nullable();
            $table->decimal('area', 8, 2)->nullable();
            $table->unsignedInteger('occupant_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('owner_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('owners')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->decimal('ownership_share', 5, 2)->default(0);
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->text('obligation_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->morphs('documentable');
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('storage_path');
            $table->date('issued_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('community_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('version');
            $table->string('status')->default('draft');
            $table->date('adopted_at')->nullable();
            $table->text('content_summary')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('house_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->date('effective_from')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('notice_board_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_community_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('category')->nullable();
            $table->text('body');
            $table->string('author_name')->nullable();
            $table->dateTime('posted_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notice_board_posts');
        Schema::dropIfExists('house_rules');
        Schema::dropIfExists('community_rules');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('owner_unit');
        Schema::dropIfExists('units');
        Schema::dropIfExists('owners');
        Schema::dropIfExists('housing_communities');
    }
};
