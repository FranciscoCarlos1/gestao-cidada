<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('problemas', function (Blueprint $table) {
            $table->id();

            $table->string('titulo');
            $table->text('descricao');

            // Endereço (pode ser preenchido via geolocalização + reverse geocoding)
            $table->string('bairro');
            $table->string('rua')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('cep')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();

            // Coordenadas
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('status', 32)->default('aberto');

            $table->foreignId('prefeitura_id')->constrained('prefeituras')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('problemas');
    }
};
