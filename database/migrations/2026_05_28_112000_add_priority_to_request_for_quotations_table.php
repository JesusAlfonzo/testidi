<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('request_for_quotations', function (Blueprint $table) {
            $table->string('priority', 20)->default('baja');
        });
    }

    public function down(): void
    {
        Schema::table('request_for_quotations', function (Blueprint $table) {
            $table->dropColumn('priority');
        });
    }
};
