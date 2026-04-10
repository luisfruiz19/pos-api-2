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
        Schema::create('credit_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('credit_sale_id');
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'yape', 'plin', 'transferencia'])->default('efectivo');
            $table->text('observacion')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Relaciones
            $table->foreign('credit_sale_id')->references('id')->on('credit_sales')->onDelete('cascade');

            // Índices
            $table->index('credit_sale_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_payments');
    }
};
