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
        // 1. Modificar rfq_items
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->enum('item_type', ['product', 'kit'])->default('product')->after('rfq_id');
            $table->foreignId('kit_id')->nullable()->after('product_id')->constrained('kits')->onDelete('restrict');
        });

        // Hacer product_id nullable en rfq_items (requiere desactivar/activar FK en algunos drivers)
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
        });

        // 2. Modificar purchase_order_items
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->enum('item_type', ['product', 'kit'])->default('product')->after('purchase_order_id');
            $table->foreignId('kit_id')->nullable()->after('product_id')->constrained('kits')->onDelete('restrict');
        });

        // Hacer product_id nullable en purchase_order_items
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
        });

        // 3. Modificar stock_in_items
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')->nullable()->after('stock_in_id')->constrained('purchase_order_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropColumn('purchase_order_item_id');
        });

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['kit_id']);
            $table->dropColumn(['item_type', 'kit_id']);
            $table->foreignId('product_id')->nullable(false)->change();
        });

        Schema::table('rfq_items', function (Blueprint $table) {
            $table->dropForeign(['kit_id']);
            $table->dropColumn(['item_type', 'kit_id']);
            $table->foreignId('product_id')->nullable(false)->change();
        });
    }
};
