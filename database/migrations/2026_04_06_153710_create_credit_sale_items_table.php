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
        Schema::create('credit_sale_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('credit_sale_id');
            $table->uuid('product_id');
            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal', 10, 2);
            $table->timestamp('created_at')->useCurrent();

            // Relaciones
            $table->foreign('credit_sale_id')->references('id')->on('credit_sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');

            // Índices
            $table->index('credit_sale_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('credit_sale_items');
    }
};
