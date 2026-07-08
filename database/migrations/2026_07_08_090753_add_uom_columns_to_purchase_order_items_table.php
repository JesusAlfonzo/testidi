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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->foreignId('uom_id')->nullable()->after('product_id')->constrained('units')->nullOnDelete();
            $table->integer('quantity_uom')->nullable()->after('quantity');
            $table->decimal('unit_cost_uom', 12, 2)->nullable()->after('unit_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('uom_id');
            $table->dropColumn(['quantity_uom', 'unit_cost_uom']);
        });
    }
};
