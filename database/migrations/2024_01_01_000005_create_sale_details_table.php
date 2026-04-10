<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sale_id');
            $table->uuid('product_id');
            $table->integer('cantidad');
            $table->decimal('precio_venta', 10, 2);   // Precio al momento de la venta (snapshot)
            $table->decimal('precio_compra', 10, 2);  // Costo al momento de la venta (snapshot)
            $table->decimal('subtotal', 10, 2);        // cantidad * precio_venta
            $table->decimal('ganancia', 10, 2);        // cantidad * (precio_venta - precio_compra)

            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');

            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_details');
    }
};
