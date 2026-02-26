<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_for_quotations', function (Blueprint $table) {
            $table->id();

            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();

            $table->date('date_required')->nullable();
            $table->date('delivery_deadline')->nullable();

            $table->enum('status', ['draft', 'sent', 'closed', 'cancelled'])->default('draft');

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_for_quotations');
    }
};
