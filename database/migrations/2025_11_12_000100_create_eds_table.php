<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('eds', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre');
            $table->string('nit')->nullable();
            $table->string('ciudad')->nullable();
            $table->string('email_alertas')->nullable();
            $table->string('direccion')->nullable();
            $table->string('telefono')->nullable();
            $table->boolean('activo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('user_eds', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('eds_id')->constrained('eds')->cascadeOnDelete();
            $table->primary(['user_id','eds_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_eds');
        Schema::dropIfExists('eds');
    }
};
