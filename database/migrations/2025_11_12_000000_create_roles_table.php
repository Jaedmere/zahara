<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->json('permisos_json')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rol_id')) {
                $table->foreignId('rol_id')->nullable()->constrained('roles')->nullOnDelete();
            }
            if (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true);
            }
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'rol_id')) $table->dropConstrainedForeignId('rol_id');
            if (Schema::hasColumn('users', 'activo')) $table->dropColumn('activo');
        });
        Schema::dropIfExists('roles');
    }
};
