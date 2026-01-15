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
            // A coluna 'status' já existe na tabela de criação; não recriar aqui.
            if (!Schema::hasColumn('problemas', 'status_history')) {
                $table->text('status_history')->nullable()->after('status');
            }
            if (!Schema::hasColumn('problemas', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('status_history')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('problemas', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('assigned_to');
            }
            if (!Schema::hasColumn('problemas', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('internal_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('problemas', function (Blueprint $table) {
            if (Schema::hasColumn('problemas', 'assigned_to')) {
                $table->dropForeign(['assigned_to']);
            }
            $cols = collect(['status_history', 'assigned_to', 'internal_notes', 'resolved_at'])
                ->filter(fn ($c) => Schema::hasColumn('problemas', $c))
                ->all();
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};
