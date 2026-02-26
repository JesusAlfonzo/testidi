<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE purchase_orders RENAME COLUMN date_ordered TO date_issued');
        DB::statement('ALTER TABLE purchase_orders RENAME COLUMN user_id TO created_by');
        DB::statement('ALTER TABLE purchase_orders RENAME COLUMN payment_terms TO terms');

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->date('delivery_date')->nullable()->after('date_issued');
            $table->foreignId('approved_by')->nullable()->after('notes')->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });

        DB::statement('ALTER TABLE purchase_orders DROP COLUMN converted_to_oc');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE purchase_orders RENAME COLUMN date_issued TO date_ordered');
        DB::statement('ALTER TABLE purchase_orders RENAME COLUMN created_by TO user_id');
        DB::statement('ALTER TABLE purchase_orders RENAME COLUMN terms TO payment_terms');

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['delivery_date', 'approved_by', 'approved_at']);
            $table->boolean('converted_to_oc')->default(false);
        });
    }
};
