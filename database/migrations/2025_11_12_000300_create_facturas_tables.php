<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->string('prefijo')->nullable();
            $table->unsignedBigInteger('consecutivo');
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('eds_id')->constrained('eds')->cascadeOnDelete();
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->decimal('subtotal', 18, 2)->default(0);
            $table->decimal('iva', 18, 2)->default(0);
            $table->decimal('retenciones', 18, 2)->default(0);
            $table->decimal('total', 18, 2)->default(0);
            $table->enum('estado', ['pendiente','parcial','pagado','vencido','anulada'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['prefijo','consecutivo']);
            $table->index(['cliente_id','eds_id','fecha_vencimiento','estado']);
        });

        Schema::create('factura_adjuntos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('tipo_mime')->nullable();
            $table->string('ruta');
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('factura_adjuntos');
        Schema::dropIfExists('facturas');
    }
};
