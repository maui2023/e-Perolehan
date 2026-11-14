<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_settings', function (Blueprint $table) {
            $table->id();
            $table->string('org_name')->default('Universal Procurement System');
            $table->string('org_short_name')->default('E-Perolehan');
            $table->string('org_reg_no')->nullable();
            $table->string('org_email')->nullable();
            $table->string('org_phone')->nullable();
            $table->text('org_address')->nullable();
            $table->string('org_logo_path')->nullable();
            $table->string('currency', 10)->default('MYR');
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->text('anti_bribery_policy_text')->nullable();
            $table->text('procurement_terms_text')->nullable();
            $table->string('letterhead_footer_text')->nullable();
            $table->json('extra_config')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_settings');
    }
};
