<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('cash_register_id');
            $table->decimal('total', 10, 2);
            $table->decimal('ganancia_total', 10, 2)->default(0);
            $table->enum('metodo_pago', ['efectivo', 'yape', 'plin']);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('cash_register_id')->references('id')->on('cash_registers')->onDelete('restrict');

            // Índice para reportes por fecha
            $table->index('created_at');
            $table->index(['cash_register_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
