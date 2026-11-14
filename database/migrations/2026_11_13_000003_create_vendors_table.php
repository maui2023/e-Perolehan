<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->index();
            $table->string('registration_number')->unique(); // SSM / Corporate Reg
            $table->string('tax_identification_number')->nullable();
            $table->string('company_email')->nullable();
            $table->string('company_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postcode', 15)->nullable();
            $table->string('mof_registration_number')->nullable();
            $table->string('cidb_registration_number')->nullable();
            $table->boolean('bumiputera_status')->default(false);
            $table->string('compliance_status', 30)->default('pending')->index(); // pending, verified, rejected, suspended
            $table->timestamp('compliance_verified_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('compliance_notes')->nullable();
            $table->timestamp('anti_bribery_agreed_at')->nullable();
            $table->string('ssm_cert_path')->nullable();
            $table->string('mof_cert_path')->nullable();
            $table->string('cidb_cert_path')->nullable();
            $table->string('bank_statement_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
