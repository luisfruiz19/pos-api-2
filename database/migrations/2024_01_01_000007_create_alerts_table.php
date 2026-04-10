<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('tipo');                      // ej: 'stock_bajo', 'stock_agotado'
            $table->string('mensaje');
            $table->text('detalle')->nullable();
            $table->enum('nivel', ['info', 'warning', 'critical']);
            $table->uuid('product_id')->nullable();
            $table->boolean('leido')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->index('nivel');
            $table->index(['leido', 'nivel']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
