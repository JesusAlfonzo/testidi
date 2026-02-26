<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->foreignId('purchase_quote_id')->nullable()->constrained('purchase_quotes')->onDelete('set null');
            $table->foreignId('supplier_id')->constrained('suppliers')->onDelete('restrict');

            $table->date('date_issued');
            $table->date('delivery_date')->nullable();
            $table->string('delivery_address')->nullable();

            $table->string('currency', 3)->default('USD');
            $table->decimal('exchange_rate', 10, 4)->default(1);

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->enum('status', ['draft', 'issued', 'completed', 'cancelled'])->default('draft');

            $table->text('terms')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
