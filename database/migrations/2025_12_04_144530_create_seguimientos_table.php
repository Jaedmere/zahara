<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users'); // Quien hizo la gestión
            // Opcional: Si la gestión es sobre una factura específica
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();
            
            // Datos de la gestión
            $table->date('fecha_gestion'); // Cuándo llamamos
            $table->text('observacion'); // Qué se habló
            
            // Compromiso (Lo vital)
            $table->date('fecha_compromiso')->nullable(); // Cuándo prometió pagar
            $table->decimal('monto_compromiso', 18, 2)->nullable();
            
            // Estado del compromiso
            $table->enum('estado', ['pendiente', 'cumplido', 'vencido', 'cancelado'])->default('pendiente');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};