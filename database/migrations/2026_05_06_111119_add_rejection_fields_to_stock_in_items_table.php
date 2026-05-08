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
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->enum('status', ['received', 'rejected', 'replaced'])->default('received');
            $table->text('rejection_reason')->nullable();
            $table->foreignId('replaced_item_id')->nullable()->constrained('stock_in_items')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('replaced_item_id');
            $table->dropColumn(['status', 'rejection_reason']);
        });
    }
};
