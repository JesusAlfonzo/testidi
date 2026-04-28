<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar code a requests (InventoryRequest)
        Schema::table('requests', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
        });

        // Agregar code a kits
        Schema::table('kits', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('code');
        });

        Schema::table('kits', function (Blueprint $table) {
            $table->dropColumn('code');
        });
    }
};