<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE purchase_orders DROP CONSTRAINT purchase_orders_user_id_foreign');
        DB::statement('ALTER TABLE purchase_orders ADD CONSTRAINT purchase_orders_created_by_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE purchase_orders DROP CONSTRAINT purchase_orders_created_by_foreign');
        DB::statement('ALTER TABLE purchase_orders ADD CONSTRAINT purchase_orders_user_id_foreign FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE RESTRICT');
    }
};
