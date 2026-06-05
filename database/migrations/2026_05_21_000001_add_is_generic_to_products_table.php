<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // 🏷️ Flag para distinguir productos genéricos de estrictos
            $table->boolean('is_generic')->default(false)->after('is_kit');

            // 🔓 Hacer nullable las FK para productos genéricos
            // brand_id ya es nullable desde la migración original
            $table->foreignId('category_id')->nullable()->change();
            $table->foreignId('location_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_generic');

            // Revertir a NOT NULL (solo si no hay registros con NULL)
            $table->foreignId('category_id')->nullable(false)->change();
            $table->foreignId('location_id')->nullable(false)->change();
        });
    }
};
