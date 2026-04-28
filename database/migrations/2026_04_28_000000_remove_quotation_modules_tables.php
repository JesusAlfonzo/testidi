<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        // Eliminar tablas del módulo de cotizaciones (PurchaseQuote)
        Schema::dropIfExists('purchase_quote_items');
        Schema::dropIfExists('purchase_quotes');
    }

    public function down(): void
    {
        // No se puede revertir - las tablas han sido eliminadas permanentemente
    }
};
