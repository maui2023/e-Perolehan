<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bid_id')->constrained()->cascadeOnDelete();
            $table->foreignId('evaluator_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('evaluation_type', 30)->index(); // technical or financial

            // Detailed scoring criteria
            $table->decimal('score_criteria_1', 5, 2)->default(0.00); // e.g. Compliance (40%)
            $table->decimal('score_criteria_2', 5, 2)->default(0.00); // e.g. Experience / Track Record (30%)
            $table->decimal('score_criteria_3', 5, 2)->default(0.00); // e.g. Methodology (30%)
            $table->decimal('total_score', 5, 2)->default(0.00);

            $table->boolean('is_passed')->default(false);
            $table->text('comments')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('digital_sign_hash', 64)->nullable();
            $table->timestamps();

            $table->unique(['bid_id', 'evaluator_user_id', 'evaluation_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
