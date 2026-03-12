<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
            $table->integer('quantity')->nullable()->change();
            $table->decimal('unit_cost', 10, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->foreignId('product_id')->change();
            $table->integer('quantity')->change();
            $table->decimal('unit_cost', 10, 2)->change();
        });
    }
};
