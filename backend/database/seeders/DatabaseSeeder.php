<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Prefeitura;
use App\Models\Problema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sbs = Prefeitura::firstOrCreate(
            ['slug' => 'sao-bento-do-sul'],
            [
                'nome' => 'Prefeitura de São Bento do Sul',
                'cnpj' => '00.000.000/0000-00',
                'email_contato' => 'contato@sbs.sc.gov.br',
                'telefone' => '(47) 0000-0000',
                'cidade' => 'São Bento do Sul',
                'uf' => 'SC',
            ]
        );

        $super = User::firstOrCreate(
            ['email' => 'super@gestaocidada.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'role' => 'super',
                'prefeitura_id' => null,
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@sbs.local'],
            [
                'name' => 'Admin SBS',
                'password' => Hash::make('12345678'),
                'role' => 'admin',
                'prefeitura_id' => $sbs->id,
            ]
        );

        $cid = User::firstOrCreate(
            ['email' => 'cidadao@demo.local'],
            [
                'name' => 'Cidadão Demo',
                'password' => Hash::make('12345678'),
                'role' => 'cidadao',
                'prefeitura_id' => null,
            ]
        );

        // cria alguns problemas de exemplo (com endereço)
        if (Problema::count() === 0) {
            Problema::create([
                'titulo' => 'Buraco na via',
                'descricao' => 'Buraco grande na rua, risco de acidentes.',
                'bairro' => 'Centro',
                'rua' => 'Rua Exemplo',
                'numero' => '123',
                'cep' => '89290-000',
                'cidade' => 'São Bento do Sul',
                'uf' => 'SC',
                'status' => 'aberto',
                'prefeitura_id' => $sbs->id,
                'user_id' => $cid->id,
                'latitude' => -26.2500000,
                'longitude' => -49.3830000,
            ]);

            Problema::create([
                'titulo' => 'Lixo acumulado',
                'descricao' => 'Entulho e lixo na praça. Precisa de limpeza.',
                'bairro' => 'Rio Vermelho',
                'rua' => 'Estrada Exemplo',
                'status' => 'resolvido',
                'prefeitura_id' => $sbs->id,
                'user_id' => $cid->id,
            ]);
        }
    }
}
