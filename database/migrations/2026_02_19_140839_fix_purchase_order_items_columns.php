<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE purchase_order_items RENAME COLUMN received_quantity TO quantity_received');
        DB::statement('ALTER TABLE purchase_order_items ALTER COLUMN quantity TYPE integer USING quantity::integer');
        DB::statement('ALTER TABLE purchase_order_items ALTER COLUMN quantity_received TYPE integer USING quantity_received::integer');

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->string('product_code')->nullable()->after('product_name');
        });
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE purchase_order_items RENAME COLUMN quantity_received TO received_quantity');
        DB::statement('ALTER TABLE purchase_order_items ALTER COLUMN quantity TYPE numeric');
        DB::statement('ALTER TABLE purchase_order_items ALTER COLUMN quantity_received TYPE numeric');

        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn('product_code');
        });
    }
};
