<?php

namespace Database\Seeders;

use App\Models\Prefeitura;
use App\Models\User;
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
        // Criar prefeitura exemplo
        $prefeitura = Prefeitura::create([
            'nome' => 'Prefeitura Municipal de São Paulo',
            'slug' => Str::slug('Prefeitura Municipal de São Paulo'),
            'cidade' => 'São Paulo',
            'uf' => 'SP',
        ]);

        // Super admin (vê tudo)
        User::create([
            'name' => 'Super Admin',
            'email' => 'super@demo.test',
            'password' => Hash::make('password'),
            'role' => 'super',
            'prefeitura_id' => null,
        ]);

        // Admin da prefeitura (vê só sua prefeitura)
        User::create([
            'name' => 'Admin Prefeitura',
            'email' => 'admin@demo.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'prefeitura_id' => $prefeitura->id,
        ]);

        // Cidadão comum
        User::create([
            'name' => 'Cidadão Demo',
            'email' => 'cidadao@demo.test',
            'password' => Hash::make('password'),
            'role' => 'cidadao',
            'prefeitura_id' => null,
        ]);
    }
}
