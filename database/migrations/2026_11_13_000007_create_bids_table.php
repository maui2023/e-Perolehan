<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('bid_reference')->unique()->index();
            $table->string('blind_code', 50)->nullable()->index(); // e.g. "BIDDER-A", "BIDDER-B" for double-blind technical evaluation
            $table->text('encrypted_bid_amount'); // Sealed bid price encrypted at rest
            $table->decimal('decrypted_bid_amount', 15, 2)->nullable(); // Populated ONLY upon dual-witness unlocking
            $table->unsignedInteger('completion_duration_months')->nullable();
            $table->longText('technical_summary')->nullable();
            $table->string('status', 40)->default('draft')->index(); // draft, submitted, sealed, opened, passed_technical, failed_technical, awarded, rejected
            $table->string('sha256_package_hash', 64)->nullable(); // Digital fingerprint of entire submission
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('opened_at')->nullable();
            $table->string('submission_slip_pdf_path')->nullable();
            $table->decimal('technical_score', 5, 2)->nullable();
            $table->decimal('financial_score', 5, 2)->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['tender_id', 'vendor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bids');
    }
};
