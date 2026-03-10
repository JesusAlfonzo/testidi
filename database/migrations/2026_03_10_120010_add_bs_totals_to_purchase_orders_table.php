<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('subtotal_bs', 14, 2)->default(0)->after('subtotal');
            $table->decimal('tax_amount_bs', 14, 2)->default(0)->after('tax_amount');
            $table->decimal('total_bs', 14, 2)->default(0)->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['subtotal_bs', 'tax_amount_bs', 'total_bs']);
        });
    }
};
