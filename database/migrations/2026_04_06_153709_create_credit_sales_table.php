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
        Schema::create('credit_sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->decimal('total', 10, 2);
            $table->decimal('total_pagado', 10, 2)->default(0);
            $table->decimal('saldo_pendiente', 10, 2);
            $table->enum('estado', ['abierta', 'pagada', 'parcial', 'incobrable'])->default('abierta');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Relaciones
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');

            // Índices
            $table->index('customer_id');
            $table->index('estado');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_sales');
    }
};
