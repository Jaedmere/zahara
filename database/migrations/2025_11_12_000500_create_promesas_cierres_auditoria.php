<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('promesas_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->foreignId('eds_id')->constrained('eds')->cascadeOnDelete();
            $table->date('fecha_compromiso');
            $table->decimal('valor', 18, 2);
            $table->enum('estado', ['pendiente','cumplida','incumplida'])->default('pendiente');
            $table->text('notas')->nullable();
            $table->timestamps();
        });

        Schema::create('cierres_mensuales', function (Blueprint $table) {
            $table->id();
            $table->string('mes'); // YYYY-MM
            $table->foreignId('eds_id')->constrained('eds')->cascadeOnDelete();
            $table->json('totales_json')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rol')->nullable();
            $table->string('eds_context')->nullable();
            $table->string('accion');
            $table->string('tabla');
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->json('antes_json')->nullable();
            $table->json('despues_json')->nullable();
            $table->string('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_log');
        Schema::dropIfExists('cierres_mensuales');
        Schema::dropIfExists('promesas_pago');
    }
};
