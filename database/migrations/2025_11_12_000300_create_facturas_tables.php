<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            
            // Identificación
            $table->string('prefijo', 10)->nullable();
            $table->string('consecutivo', 20); // String por si es alfanumérico
            
            // Relaciones
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('eds_id')->constrained('eds')->restrictOnDelete();
            
            // Fechas
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            
            // Valores Económicos (Simplificados para Cartera)
            $table->decimal('valor_neto', 18, 2)->comment('Valor antes de descuentos');
            $table->decimal('descuento', 18, 2)->default(0);
            $table->decimal('valor_total', 18, 2)->comment('Neto - Descuento. Valor real de la deuda');
            
            // Control de Saldo (Vital para rendimiento)
            $table->decimal('saldo_pendiente', 18, 2)->comment('Disminuye con cada abono');
            
            $table->enum('estado', ['pendiente', 'parcial', 'pagada', 'vencida', 'anulada'])->default('pendiente');
            $table->text('notas')->nullable();
            
            $table->softDeletes(); // Inactivar en lugar de borrar
            $table->timestamps();

            // Índices para reportes rápidos
            $table->unique(['prefijo', 'consecutivo', 'eds_id'], 'factura_unica_por_eds'); // Evitar duplicados
            $table->index(['cliente_id', 'estado']); // Para "Ver deuda del cliente X"
            $table->index('fecha_vencimiento'); // Para "Ver qué se vence mañana"
            $table->index('saldo_pendiente'); // Para "Ver facturas con deuda"
        });

        // Adjuntos (Evidencia física de la factura)
        Schema::create('factura_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('ruta');
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('factura_adjuntos');
        Schema::dropIfExists('facturas');
    }
};