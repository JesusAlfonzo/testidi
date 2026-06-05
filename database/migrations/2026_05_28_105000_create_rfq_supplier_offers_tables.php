<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_supplier_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained('request_for_quotations')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('rfq_supplier_offer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_supplier_offer_id')->constrained('rfq_supplier_offers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('unit_price', 15, 2);
            $table->string('currency', 3);
            $table->string('tax_status'); // 'exento' o 'gravado'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_supplier_offer_items');
        Schema::dropIfExists('rfq_supplier_offers');
    }
};
