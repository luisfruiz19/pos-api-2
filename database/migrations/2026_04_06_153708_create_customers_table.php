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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nombre');
            $table->string('telefono')->nullable();
            $table->text('direccion')->nullable();
            $table->decimal('saldo_deuda', 10, 2)->default(0);
            $table->enum('estado', ['activo', 'inactivo', 'incobrable'])->default('activo');
            $table->datetime('ultima_compra_at')->nullable();
            $table->datetime('ultima_pago_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            // Índices
            $table->index('estado');
            $table->index('saldo_deuda');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
