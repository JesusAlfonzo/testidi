<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // 🎯 Claves Foráneas (Maestros)
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->foreignId('location_id')->constrained('locations')->onDelete('restrict');
            $table->foreignId('brand_id')->nullable()->constrained('brands')->onDelete('set null'); // La marca puede ser opcional

            // ℹ️ Información General
            $table->string('code')->unique(); // SKU o código interno
            $table->string('name');
            $table->text('description')->nullable();

            // 💰 Gestión y Stock
            $table->decimal('cost', 10, 2)->default(0); // Precio de costo
            $table->decimal('price', 10, 2)->default(0); // Precio de venta sugerido
            $table->integer('stock')->default(0); // Cantidad en inventario
            $table->integer('min_stock')->default(0); // Mínimo para alerta de reposición
            $table->boolean('is_active')->default(true); // Estado del producto

            // Trazabilidad
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
