<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->index('status', 'idx_requests_status');
            $table->index('requester_id', 'idx_requests_requester_id');
            $table->index(['requested_at'], 'idx_requests_requested_at');
            $table->index(['processed_at'], 'idx_requests_processed_at');
        });

        Schema::table('stock_ins', function (Blueprint $table) {
            $table->index('product_id', 'idx_stock_ins_product_id');
            $table->index('entry_date', 'idx_stock_ins_entry_date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('is_active', 'idx_products_is_active');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->index('status', 'idx_purchase_orders_status');
            $table->index('date_issued', 'idx_purchase_orders_date_issued');
        });

        Schema::table('purchase_quotes', function (Blueprint $table) {
            $table->index('status', 'idx_purchase_quotes_status');
        });

        Schema::table('request_items', function (Blueprint $table) {
            $table->index('product_id', 'idx_request_items_product_id');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropIndex('idx_requests_status');
            $table->dropIndex('idx_requests_requester_id');
            $table->dropIndex('idx_requests_requested_at');
            $table->dropIndex('idx_requests_processed_at');
        });

        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropIndex('idx_stock_ins_product_id');
            $table->dropIndex('idx_stock_ins_entry_date');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_is_active');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropIndex('idx_purchase_orders_status');
            $table->dropIndex('idx_purchase_orders_date_issued');
        });

        Schema::table('purchase_quotes', function (Blueprint $table) {
            $table->dropIndex('idx_purchase_quotes_status');
        });

        Schema::table('request_items', function (Blueprint $table) {
            $table->dropIndex('idx_request_items_product_id');
        });
    }
};
