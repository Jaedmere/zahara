<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_id', 5); // NIT, CC, CE, etc.
            $table->string('documento')->unique();
            $table->string('razon_social');
            $table->string('email')->nullable();
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            // Se retiraron plazo_dias y lista_precios
            $table->enum('estado', ['activo','bloqueado'])->default('activo');
            $table->text('notas')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('clientes_eds', function (Blueprint $table) {
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('eds_id')->constrained('eds')->cascadeOnDelete();
            $table->primary(['cliente_id','eds_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('clientes_eds');
        Schema::dropIfExists('clientes');
    }
};