<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tender_box_witnesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tender_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('witness_role', 50)->default('saksi_pembuka');
            $table->string('digital_signature_hash', 64); // SHA-256 hash of witness affirmation payload
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('witness_remarks')->nullable();
            $table->timestamp('confirmed_at');
            $table->timestamps();

            $table->unique(['tender_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tender_box_witnesses');
    }
};
