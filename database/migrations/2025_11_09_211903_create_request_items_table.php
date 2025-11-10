<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_items', function (Blueprint $table) {
            $table->id();

            // 🎯 Claves Foráneas
            $table->foreignId('request_id')->constrained('requests')->onDelete('cascade'); // Si la solicitud se borra, los ítems se borran
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');

            // ℹ️ Información del Detalle
            $table->integer('quantity_requested'); // Cantidad solicitada
            $table->decimal('unit_price_at_request', 10, 2); // Precio o costo unitario del producto al momento de la solicitud (para reportes históricos)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_items');
    }
};
