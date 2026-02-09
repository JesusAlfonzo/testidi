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
        Schema::create('purchase_quote_items', function (Blueprint $table) {
            $table->id();

            // Relación con la cabecera
            $table->foreignId('purchase_quote_id')->constrained('purchase_quotes')->onDelete('cascade');

            // Producto de TU inventario
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');

            // Snapshot de Datos (Por si el proveedor cambia nombres o precios mañana)
            $table->string('product_name');
            $table->integer('quantity');

            // Costos
            $table->decimal('unit_cost', 12, 2); // Costo unitario ofertado
            $table->decimal('total_cost', 12, 2); // (cantidad * costo)

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_quote_items');
    }
};
