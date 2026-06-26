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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evaluation_period_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('evaluatee_id')->constrained('users')->cascadeOnDelete();
            $table->enum('evaluation_type', ['student', 'peer', 'supervisor']);
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('section_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'finalized'])->default('draft');
            $table->decimal('total_score', 5, 2)->nullable();
            $table->string('performance_rating')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(
                ['evaluation_period_id', 'evaluator_id', 'evaluatee_id', 'evaluation_type'],
                'unique_evaluation'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
