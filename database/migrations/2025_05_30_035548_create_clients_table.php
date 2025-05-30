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
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('type', ['persona', 'empresa']);
            $table->unsignedBigInteger('identity_document');
            $table->enum('type_document', ['DNI', 'RUC', 'CE', 'PASAPORTE']);
            $table->string('phone');
            $table->timestamps();
            $table->softDeletes();
            
            // Create unique index for identity_document
            $table->unique(['identity_document', 'type_document']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
