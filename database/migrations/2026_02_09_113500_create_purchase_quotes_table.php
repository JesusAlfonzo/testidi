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
        Schema::create('purchase_quotes', function (Blueprint $table) {
            $table->id();

            // 🏭 Relaciones
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users'); // Quién registró la cotización

            // 📄 Referencias
            $table->string('code')->unique(); // Tu código interno (ej. COT-C-001)
            $table->string('supplier_reference')->nullable(); // El número de cotización DEL PROVEEDOR

            // 📅 Fechas
            $table->date('date_issued'); // Fecha de emisión del proveedor
            $table->date('valid_until')->nullable(); // Vencimiento de la oferta
            $table->date('delivery_date')->nullable(); // Fecha estimada de entrega

            // 💰 Moneda (Opcional pero recomendado para compras)
            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 4)->default(1);

            // 💵 Totales
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // 📎 Archivos
            $table->string('attachment_path')->nullable(); // PDF del proveedor

            // 📊 Estados: pending, approved, rejected, converted
            $table->string('status')->default('pending')->index();

            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_quotes');
    }
};
