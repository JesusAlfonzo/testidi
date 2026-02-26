<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rfq_id')->constrained('request_for_quotations')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');

            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_items');
    }
};
