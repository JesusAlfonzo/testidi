<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 🔑 NOMBRE CORRECTO DE LA TABLA: request_items
        Schema::table('request_items', function (Blueprint $table) {
            
            // 1. Columna para definir el tipo de ítem (Producto o Kit)
            $table->enum('item_type', ['product', 'kit'])
                  ->after('inventory_request_id')
                  ->default('product'); 
            
            // 2. Columna para guardar el ID del Kit (nullable si es un producto)
            $table->foreignId('kit_id')
                  ->nullable()
                  ->after('product_id')
                  ->constrained('kits') 
                  ->onDelete('set null');

            // Opcional: Si necesitas un índice único compuesto, descomenta y ajusta:
            // $table->unique(['inventory_request_id', 'product_id', 'kit_id']);
        });
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            $table->dropForeign(['kit_id']);
            $table->dropColumn('kit_id');
            $table->dropColumn('item_type');
        });
    }
};