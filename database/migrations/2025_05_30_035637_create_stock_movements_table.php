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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_id')->constrained()->onDelete('cascade');
            $table->enum('movement_type', ['Entrada', 'Salida', 'Ajuste', 'Devolucion']);
            $table->integer('quantity');
            $table->string('reason');
            $table->decimal('unit_price', 10, 2);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('voucher_number')->nullable(); // Fixed typo in column name
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
