<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique()->index();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 100)->index();
            $table->string('procurement_type', 50)->default('open')->index(); // open, selective, quotation
            $table->longText('description')->nullable();
            $table->longText('scope_of_work')->nullable();
            $table->longText('eligibility_criteria')->nullable();
            $table->decimal('estimated_budget', 15, 2)->nullable();
            $table->decimal('document_price', 10, 2)->default(0.00);
            $table->decimal('tender_deposit_amount', 12, 2)->default(0.00);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('closing_at')->nullable()->index();
            $table->string('status', 40)->default('draft')->index(); // draft, published, closed, box_opened, evaluating_technical, evaluating_commercial, awarded, cancelled
            $table->unsignedInteger('min_technical_score')->default(70);
            $table->timestamp('dual_witness_opened_at')->nullable();
            $table->unsignedBigInteger('awarded_bid_id')->nullable();
            $table->timestamp('awarded_at')->nullable();
            $table->text('award_notes')->nullable();
            $table->string('sst_letter_pdf_path')->nullable();
            $table->string('opening_slip_pdf_path')->nullable();
            $table->string('evaluation_report_pdf_path')->nullable();
            $table->timestamp('archived_to_minio_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenders');
    }
};
