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
        Schema::table('tenders', function (Blueprint $table) {
            $table->index('created_at', 'tenders_created_at_idx');
            $table->index(['status', 'closing_at'], 'tenders_status_closing_at_idx');
            $table->index('awarded_bid_id', 'tenders_awarded_bid_id_idx');
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->index('created_at', 'bids_created_at_idx');
            $table->index('updated_at', 'bids_updated_at_idx');
            $table->index(['vendor_id', 'status'], 'bids_vendor_id_status_idx');
            $table->index(['tender_id', 'status'], 'bids_tender_id_status_idx');
        });

        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->index('created_at', 'tender_purchases_created_at_idx');
            $table->index(['vendor_id', 'status'], 'tender_purchases_vendor_id_status_idx');
            $table->index(['tender_id', 'status'], 'tender_purchases_tender_id_status_idx');
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->index('created_at', 'audit_trails_created_at_idx');
            $table->index(['auditable_type', 'auditable_id'], 'audit_trails_auditable_composite_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenders', function (Blueprint $table) {
            $table->dropIndex('tenders_created_at_idx');
            $table->dropIndex('tenders_status_closing_at_idx');
            $table->dropIndex('tenders_awarded_bid_id_idx');
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->dropIndex('bids_created_at_idx');
            $table->dropIndex('bids_updated_at_idx');
            $table->dropIndex('bids_vendor_id_status_idx');
            $table->dropIndex('bids_tender_id_status_idx');
        });

        Schema::table('tender_purchases', function (Blueprint $table) {
            $table->dropIndex('tender_purchases_created_at_idx');
            $table->dropIndex('tender_purchases_vendor_id_status_idx');
            $table->dropIndex('tender_purchases_tender_id_status_idx');
        });

        Schema::table('audit_trails', function (Blueprint $table) {
            $table->dropIndex('audit_trails_created_at_idx');
            $table->dropIndex('audit_trails_auditable_composite_idx');
        });
    }
};
