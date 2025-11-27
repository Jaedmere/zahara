<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. TABLA ABONOS (Cabecera del Recibo)
        Schema::create('abonos', function (Blueprint $table) {
            $table->id();
            
            // Relaciones principales
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('eds_id')->constrained('eds')->cascadeOnDelete();
            
            // Datos del pago
            $table->date('fecha');
            $table->decimal('valor', 18, 2); // Valor total del recibo
            
            // Detalles bancarios y forma de pago
            $table->string('medio_pago')->nullable(); // Efectivo, Transferencia...
            $table->string('referencia_bancaria')->nullable();
            $table->string('banco')->nullable();
            
            // Gestión financiera
            $table->decimal('descuento', 18, 2)->default(0);
            
            // Conciliación
            $table->boolean('conciliado')->default(false);
            $table->date('fecha_conciliacion')->nullable();
            
            $table->text('observaciones')->nullable();
            
            // AUDITORÍA (Quién registró el pago)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes(); // Borrado lógico del recibo
            $table->timestamps();
        });

        // 2. TABLA DETALLE (Distribución del pago en facturas)
        Schema::create('abono_detalle', function (Blueprint $table) {
            $table->id();
            
            // Relación Maestro-Detalle
            $table->foreignId('abono_id')->constrained('abonos')->cascadeOnDelete();
            
            // Relación con la deuda
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            
            // Valores aplicados a esta factura específica
            $table->decimal('valor_aplicado', 18, 2);
            $table->decimal('descuento_aplicado', 18, 2)->default(0);
            
            $table->softDeletes(); // <--- IMPORTANTE: Para mantener historial al anular
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('abono_detalle');
        Schema::dropIfExists('abonos');
    }
};