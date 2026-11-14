<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bid_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bid_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40)->index(); // technical_proposal, financial_proposal, compliance_statement, company_profile, bank_guarantee, other
            $table->string('file_path');
            $table->string('file_name');
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();
            $table->string('sha256_checksum', 64)->nullable();
            $table->boolean('is_financial')->default(false)->index(); // Crucial for blind evaluation: hidden until commercial phase
            $table->string('disk', 20)->default('private'); // private (local active) or minio (cold archive)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bid_documents');
    }
};
