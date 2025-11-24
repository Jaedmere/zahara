<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('abonos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('eds_id')->constrained('eds')->cascadeOnDelete();
            $table->date('fecha');
            $table->decimal('valor', 18, 2);
            $table->string('medio_pago')->nullable();
            $table->string('referencia_bancaria')->nullable();
            $table->string('banco')->nullable();
            $table->decimal('descuento', 18, 2)->default(0);
            $table->boolean('conciliado')->default(false);
            $table->date('fecha_conciliacion')->nullable();
            $table->text('observaciones')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('abono_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abono_id')->constrained('abonos')->cascadeOnDelete();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->decimal('valor_aplicado', 18, 2);
            $table->decimal('descuento_aplicado', 18, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('abono_detalle');
        Schema::dropIfExists('abonos');
    }
};
