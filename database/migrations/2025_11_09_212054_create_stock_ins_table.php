<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_ins', function (Blueprint $table) {
            $table->id();

            // 🎯 Claves Foráneas
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');

            // ℹ️ Información del Movimiento
            $table->string('document_type')->nullable(); // Ej: Factura, Guía de Remisión, Ajuste
            $table->string('document_number')->nullable();
            $table->integer('quantity'); // Cantidad de unidades que ingresan
            $table->decimal('unit_cost', 10, 2); // Costo unitario al momento de la compra
            $table->string('reason')->nullable(); // Razón del ingreso (Ej: Compra, Donación, Ajuste)

            // 📅 Fechas
            $table->date('entry_date');

            // Trazabilidad
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_ins');
    }
};
