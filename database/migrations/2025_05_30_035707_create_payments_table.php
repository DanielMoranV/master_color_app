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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('restrict');
            $table->enum('payment_method', ['Efectivo', 'Tarjeta', 'Yape', 'Plin', 'TC', 'Transferencia']);
            $table->string('payment_code');
            $table->enum('document_type', ['Boleta', 'Factura', 'Ticket', 'NC'])->default('Ticket');
            $table->string('nc_reference')->nullable();
            $table->text('observations')->nullable(); // Changed from BIGINT to TEXT
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
