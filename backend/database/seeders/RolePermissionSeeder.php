<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar Roles
        $superRole = Role::firstOrCreate(['name' => 'super'], [
            'description' => 'Super administrador - acesso total',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin'], [
            'description' => 'Administrador de prefeitura - gerencia sua prefeitura',
        ]);

        $prefeituraRole = Role::firstOrCreate(['name' => 'prefeitura_admin'], [
            'description' => 'Administrador de prefeitura (alternativa)',
        ]);

        $cidadaoRole = Role::firstOrCreate(['name' => 'cidadao'], [
            'description' => 'Cidadão - pode criar problemas',
        ]);

        $anonRole = Role::firstOrCreate(['name' => 'anonimo'], [
            'description' => 'Anônimo - apenas visualiza',
        ]);

        // Criar Permissões
        $permissions = [
            // Problemas
            ['name' => 'view_problems', 'description' => 'Ver problemas'],
            ['name' => 'view_problems_public', 'description' => 'Ver problemas públicos'],
            ['name' => 'create_problem', 'description' => 'Criar problema'],
            ['name' => 'view_own_problems', 'description' => 'Ver seus próprios problemas'],
            ['name' => 'view_prefeitura_problems', 'description' => 'Ver problemas de sua prefeitura'],
            ['name' => 'update_problem_status', 'description' => 'Alterar status de problema'],
            ['name' => 'assign_problem', 'description' => 'Atribuir problema para servidor'],
            ['name' => 'add_internal_notes', 'description' => 'Adicionar notas internas'],

            // Usuários
            ['name' => 'manage_users', 'description' => 'Gerenciar usuários'],
            ['name' => 'create_user', 'description' => 'Criar usuário'],
            ['name' => 'edit_user', 'description' => 'Editar usuário'],
            ['name' => 'delete_user', 'description' => 'Deletar usuário'],
            ['name' => 'manage_roles', 'description' => 'Gerenciar roles e permissões'],

            // Prefeituras
            ['name' => 'manage_prefeituras', 'description' => 'Gerenciar prefeituras'],
            ['name' => 'view_prefeitura', 'description' => 'Ver prefeitura'],
            ['name' => 'edit_prefeitura', 'description' => 'Editar prefeitura'],

            // Webhooks
            ['name' => 'manage_webhooks', 'description' => 'Gerenciar webhooks'],

            // Relatórios e Logs
            ['name' => 'view_audit_log', 'description' => 'Ver logs de auditoria'],
            ['name' => 'export_reports', 'description' => 'Exportar relatórios'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // Sincronizar Permissions com Roles
        $allPermissions = Permission::pluck('id')->toArray();
        $superPermissions = $allPermissions; // Super tem tudo

        $adminPermissions = Permission::whereIn('name', [
            'view_problems', 'view_prefeitura_problems', 'update_problem_status',
            'assign_problem', 'add_internal_notes',
            'manage_users', 'create_user', 'edit_user',
            'view_prefeitura', 'edit_prefeitura',
            'manage_webhooks',
            'view_audit_log', 'export_reports',
        ])->pluck('id')->toArray();

        $cidadaoPermissions = Permission::whereIn('name', [
            'view_problems_public', 'create_problem', 'view_own_problems',
        ])->pluck('id')->toArray();

        $anonPermissions = Permission::whereIn('name', [
            'view_problems_public',
        ])->pluck('id')->toArray();

        $superRole->permissions()->sync($superPermissions);
        $adminRole->permissions()->sync($adminPermissions);
        $cidadaoRole->permissions()->sync($cidadaoPermissions);
        $anonRole->permissions()->sync($anonPermissions);
    }
}
