<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            // 🔑 CORRECCIÓN: Hacer que la columna product_id acepte nulos
            $table->foreignId('product_id')->nullable()->change();

            // 💡 REVISIÓN: Asegúrate de que kit_id también sea nullable (debe serlo, pero verifica)
            $table->foreignId('kit_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('request_items', function (Blueprint $table) {
            // NOTA: Revertir la columna a NOT NULL requiere recrear la restricción,
            // pero para simplificar, a menudo se deja como estaba en la migración original.
            // Si la columna originalmente era NOT NULL, y tu DB lo permite, puedes intentar:
            // $table->foreignId('product_id')->change(); // Esto la haría NOT NULL por defecto si no se especifica nullable()
        });
    }
};