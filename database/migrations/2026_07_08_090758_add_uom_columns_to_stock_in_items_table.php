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
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->foreignId('uom_id')->nullable()->after('product_id')->constrained('units')->nullOnDelete();
            $table->integer('quantity_received_uom')->nullable()->after('quantity');
            $table->integer('quantity_received_base')->nullable()->after('quantity_received_uom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('uom_id');
            $table->dropColumn(['quantity_received_uom', 'quantity_received_base']);
        });
    }
};
