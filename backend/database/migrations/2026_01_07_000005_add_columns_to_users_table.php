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
        Schema::table('users', function (Blueprint $table) {
            // Novos campos
            $table->foreignId('role_id')->nullable()->after('role')->constrained('roles')->onDelete('set null');
            $table->boolean('two_factor_enabled')->default(false)->after('role_id');
            $table->timestamp('last_login_at')->nullable()->after('two_factor_enabled');
            $table->string('status')->default('active')->after('last_login_at'); // active, suspended, inactive
            $table->text('metadata')->nullable()->after('status'); // JSON para dados customizados
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn(['role_id', 'two_factor_enabled', 'last_login_at', 'status', 'metadata']);
        });
    }
};
