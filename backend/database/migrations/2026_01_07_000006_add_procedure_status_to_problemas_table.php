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
        Schema::table('problemas', function (Blueprint $table) {
            $table->string('status')->default('aberto')->after('prefeitura_id'); // aberto, em_andamento, resolvido, fechado, rejeitado
            $table->text('status_history')->nullable()->after('status'); // JSON com histórico de mudanças
            $table->foreignId('assigned_to')->nullable()->after('status_history')->constrained('users')->onDelete('set null'); // Qual servidor está tratando
            $table->text('internal_notes')->nullable()->after('assigned_to'); // Notas internas do servidor
            $table->timestamp('resolved_at')->nullable()->after('internal_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problemas', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn(['status', 'status_history', 'assigned_to', 'internal_notes', 'resolved_at']);
        });
    }
};
