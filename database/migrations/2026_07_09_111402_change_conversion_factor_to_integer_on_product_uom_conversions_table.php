<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('product_uom_conversions', function (Blueprint $table) {
            // Convierte la columna a entero para cumplir con el estándar ERP (cero decimales)
            $table->integer('conversion_factor')->change();
        });
    }

    public function down(): void
    {
        Schema::table('product_uom_conversions', function (Blueprint $table) {
            $table->decimal('conversion_factor', 10, 4)->change();
        });
    }
};