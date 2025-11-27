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
            $table->string('consecutivo', 20);
            
            // Relaciones
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('eds_id')->constrained('eds')->restrictOnDelete();
            
            // Fechas de Documento
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');

            // --- NUEVO: PERIODO DE CORTE ---
            $table->date('corte_desde')->comment('Inicio del periodo facturado');
            $table->date('corte_hasta')->comment('Fin del periodo facturado');
            
            // Valores
            $table->decimal('valor_neto', 18, 2);
            $table->decimal('descuento', 18, 2)->default(0);
            $table->decimal('valor_total', 18, 2);
            $table->decimal('saldo_pendiente', 18, 2);
            
            $table->enum('estado', ['pendiente', 'parcial', 'pagada', 'vencida', 'anulada'])->default('pendiente');
            $table->text('notas')->nullable();
            
            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->unique(['eds_id', 'prefijo', 'consecutivo'], 'unique_factura_eds');
            $table->index(['cliente_id', 'estado']);
            $table->index('corte_desde'); // Útil para reportes cronológicos
        });

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