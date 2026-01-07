<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prefeitura_id')->constrained('prefeituras')->onDelete('cascade');
            $table->string('event'); // problema.created, problema.status_changed, usuario.created
            $table->string('url');
            $table->string('secret')->nullable(); // Para HMAC signature
            $table->boolean('active')->default(true);
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
