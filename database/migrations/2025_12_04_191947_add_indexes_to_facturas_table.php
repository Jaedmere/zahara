<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->index('saldo_pendiente', 'idx_facturas_saldo');
            $table->index('fecha_vencimiento', 'idx_facturas_venc');
            $table->index('eds_id', 'idx_facturas_eds');
            $table->index('cliente_id', 'idx_facturas_cliente');
        });
    }

    public function down(): void
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropIndex('idx_facturas_saldo');
            $table->dropIndex('idx_facturas_venc');
            $table->dropIndex('idx_facturas_eds');
            $table->dropIndex('idx_facturas_cliente');
        });
    }
};
