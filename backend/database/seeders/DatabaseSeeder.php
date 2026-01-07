<?php

namespace Database\Seeders;

use App\Models\Prefeitura;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Executar seeder de roles e permissões primeiro
        $this->call(RolePermissionSeeder::class);

        // Criar prefeitura exemplo
        $prefeitura = Prefeitura::create([
            'nome' => 'Prefeitura Municipal de São Bento do Sul',
            'slug' => Str::slug('Prefeitura Municipal de São Bento do Sul'),
            'cnpj' => '00.000.000/0000-00',
            'email_contato' => 'contato@saobentodosul.sc.gov.br',
            'telefone' => '(47) 3211-0000',
            'cidade' => 'São Bento do Sul',
            'uf' => 'SC',
        ]);

        // Obter roles
        $superRole = Role::where('name', 'super')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $cidadaoRole = Role::where('name', 'cidadao')->first();

        // Super admin (vê tudo)
        User::create([
            'name' => 'Super Admin',
            'email' => 'super@demo.test',
            'password' => Hash::make('password'),
            'role' => 'super',
            'role_id' => $superRole->id,
            'prefeitura_id' => null,
            'status' => 'active',
        ]);

        // Admin da prefeitura (vê só sua prefeitura)
        User::create([
            'name' => 'Admin Prefeitura',
            'email' => 'admin@demo.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'role_id' => $adminRole->id,
            'prefeitura_id' => $prefeitura->id,
            'status' => 'active',
        ]);

        // Cidadão comum
        User::create([
            'name' => 'Cidadão Demo',
            'email' => 'cidadao@demo.test',
            'password' => Hash::make('password'),
            'role' => 'cidadao',
            'role_id' => $cidadaoRole->id,
            'prefeitura_id' => null,
            'status' => 'active',
        ]);
    }
}
