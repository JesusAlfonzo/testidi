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
        Schema::create('dispatches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_request_id')->constrained('requests')->cascadeOnDelete();
            $table->foreignId('dispatcher_id')->constrained('users')->restrictOnDelete();
            $table->string('dispatch_number')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('dispatch_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dispatch_id')->constrained('dispatches')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('product_batches')->nullOnDelete();
            $table->integer('quantity_requested');
            $table->integer('quantity_dispatched');
            $table->string('status', 20)->default('approved'); // approved, rejected
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dispatch_items');
        Schema::dropIfExists('dispatches');
    }
};
