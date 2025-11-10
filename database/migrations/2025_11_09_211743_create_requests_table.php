<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('requests', function (Blueprint $table) {
            $table->id();

            // 🎯 Solicitante y Aprobador
            $table->foreignId('requester_id')->constrained('users')->onDelete('restrict'); // Quién pide
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null'); // Quién aprueba

            // ℹ️ Estado y Justificación
            $table->string('status')->default('Pending'); // Enum: Pending, Approved, Rejected
            $table->text('justification'); // Razón por la que se solicitan los insumos
            $table->text('rejection_reason')->nullable(); // Razón del rechazo (si aplica)

            // 📅 Fechas
            $table->timestamp('requested_at')->useCurrent();
            $table->timestamp('processed_at')->nullable(); // Fecha de aprobación o rechazo

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
