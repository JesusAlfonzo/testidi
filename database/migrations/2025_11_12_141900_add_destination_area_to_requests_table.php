<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // En el método up() de la nueva migración:
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->string('destination_area', 255)->nullable()->after('justification');
        });
    }

    // En el método down() de la nueva migración:
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('destination_area');
        });
    }
};
