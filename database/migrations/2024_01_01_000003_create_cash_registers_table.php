<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->decimal('monto_apertura', 10, 2);
            $table->decimal('total_ventas', 10, 2)->default(0);
            $table->decimal('monto_esperado', 10, 2)->default(0);
            $table->decimal('dinero_contado', 10, 2)->nullable();
            $table->decimal('diferencia', 10, 2)->nullable();
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->string('observacion')->nullable();
            $table->timestamp('fecha_apertura')->useCurrent();
            $table->timestamp('fecha_cierre')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');

            // Regla: solo una caja abierta por usuario (se valida en app, pero índice ayuda)
            $table->index(['user_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_registers');
    }
};
