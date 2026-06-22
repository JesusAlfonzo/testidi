<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_fractions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_product_id')->unique()->constrained('products')->cascadeOnDelete();
            $table->foreignId('child_product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('conversion_factor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_fractions');
    }
};
