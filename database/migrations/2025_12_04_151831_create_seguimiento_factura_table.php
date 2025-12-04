<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla pivote para relacionar un seguimiento con muchas facturas
        Schema::create('seguimiento_factura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seguimiento_id')->constrained('seguimientos')->cascadeOnDelete();
            $table->foreignId('factura_id')->constrained('facturas')->cascadeOnDelete();
            $table->timestamps();
        });

        // Opcional: Eliminar la columna factura_id antigua de la tabla seguimientos si ya existía
        // Schema::table('seguimientos', function (Blueprint $table) {
        //     $table->dropForeign(['factura_id']);
        //     $table->dropColumn('factura_id');
        // });
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimiento_factura');
    }
};