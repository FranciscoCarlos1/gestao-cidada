<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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

        // Dados completos de demonstração (prefeituras, usuários e problemas)
        $this->call(DemoDataSeeder::class);
    }
}
