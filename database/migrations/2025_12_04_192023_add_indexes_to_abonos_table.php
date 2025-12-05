<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abonos', function (Blueprint $table) {
            $table->index('fecha', 'idx_abonos_fecha');
            $table->index('eds_id', 'idx_abonos_eds');
        });
    }

    public function down(): void
    {
        Schema::table('abonos', function (Blueprint $table) {
            $table->dropIndex('idx_abonos_fecha');
            $table->dropIndex('idx_abonos_eds');
        });
    }
};
