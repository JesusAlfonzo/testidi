<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('stock_in_item_id')->nullable()->constrained('stock_in_items')->onDelete('set null');

            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_number')->nullable();

            $table->integer('quantity')->default(0);
            $table->decimal('unit_cost', 10, 2)->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'batch_number', 'serial_number']);
            $table->index(['product_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_batches');
    }
};
