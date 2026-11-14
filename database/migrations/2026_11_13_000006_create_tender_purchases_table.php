<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tender_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('purchase_reference')->unique()->index();
            $table->decimal('amount', 10, 2);
            $table->string('status', 30)->default('pending')->index(); // pending, paid, failed, cancelled
            $table->string('affanpay_bill_id')->nullable()->index();
            $table->string('affanpay_payment_ref')->nullable();
            $table->string('payment_url')->nullable();
            $table->string('idempotency_key', 64)->unique();
            $table->timestamp('paid_at')->nullable();
            $table->string('receipt_pdf_path')->nullable();
            $table->json('payment_payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_purchases');
    }
};
