<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_in_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_in_id')->constrained('stock_ins')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');

            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);

            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('warehouse_location')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();
        });

        Schema::table('stock_ins', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('document_number');
            $table->string('delivery_note_number')->nullable()->after('invoice_number');
        });
    }

    public function down(): void
    {
        Schema::table('stock_ins', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'delivery_note_number']);
        });

        Schema::dropIfExists('stock_in_items');
    }
};
