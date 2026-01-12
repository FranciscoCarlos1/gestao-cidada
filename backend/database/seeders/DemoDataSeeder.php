<?php

namespace Database\Seeders;

use App\Models\Prefeitura;
use App\Models\Problema;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $prefeiturasData = [
            [
                'nome' => 'Prefeitura Municipal de São Bento do Sul',
                'slug' => Str::slug('Prefeitura Municipal de São Bento do Sul'),
                'cnpj' => '00.000.000/0000-00',
                'email_contato' => 'contato@saobentodosul.sc.gov.br',
                'telefone' => '(47) 3211-0000',
                'cidade' => 'São Bento do Sul',
                'uf' => 'SC',
            ],
            [
                'nome' => 'Prefeitura Municipal de Joinville',
                'slug' => Str::slug('Prefeitura Municipal de Joinville'),
                'cnpj' => '11.111.111/1111-11',
                'email_contato' => 'contato@joinville.sc.gov.br',
                'telefone' => '(47) 3461-0000',
                'cidade' => 'Joinville',
                'uf' => 'SC',
            ],
        ];

        $prefeituras = [];
        foreach ($prefeiturasData as $data) {
            $prefeituras[$data['slug']] = Prefeitura::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $roles = Role::whereIn('name', ['super', 'admin', 'prefeitura_admin', 'cidadao', 'anonimo'])
            ->get()
            ->keyBy('name');

        $usersData = [
            [
                'name' => 'Super Admin',
                'email' => 'super@demo.test',
                'password' => Hash::make('Super@12345'),
                'role' => 'super',
                'role_id' => $roles['super']->id ?? null,
                'prefeitura_id' => null,
                'status' => 'active',
            ],
            [
                'name' => 'Admin São Bento',
                'email' => 'admin.sbs@demo.test',
                'password' => Hash::make('Admin@12345'),
                'role' => 'admin',
                'role_id' => $roles['admin']->id ?? null,
                'prefeitura_id' => $prefeituras['prefeitura-municipal-de-sao-bento-do-sul']->id ?? null,
                'status' => 'active',
            ],
            [
                'name' => 'Admin Joinville',
                'email' => 'admin.joinville@demo.test',
                'password' => Hash::make('Admin@12345'),
                'role' => 'admin',
                'role_id' => $roles['admin']->id ?? null,
                'prefeitura_id' => $prefeituras['prefeitura-municipal-de-joinville']->id ?? null,
                'status' => 'active',
            ],
            [
                'name' => 'Cidadão Centro',
                'email' => 'cidadao.centro@demo.test',
                'password' => Hash::make('Cidadao@12345'),
                'role' => 'cidadao',
                'role_id' => $roles['cidadao']->id ?? null,
                'prefeitura_id' => $prefeituras['prefeitura-municipal-de-sao-bento-do-sul']->id ?? null,
                'status' => 'active',
            ],
            [
                'name' => 'Cidadão Zona Norte',
                'email' => 'cidadao.zonanorte@demo.test',
                'password' => Hash::make('Cidadao@12345'),
                'role' => 'cidadao',
                'role_id' => $roles['cidadao']->id ?? null,
                'prefeitura_id' => $prefeituras['prefeitura-municipal-de-joinville']->id ?? null,
                'status' => 'active',
            ],
        ];

        $users = [];
        foreach ($usersData as $data) {
            $users[$data['email']] = User::updateOrCreate(
                ['email' => $data['email']],
                $data
            );
        }

        $problemasData = [
            [
                'titulo' => 'Buraco em via principal',
                'descricao' => 'Buraco grande na faixa da direita, risco para motos.',
                'bairro' => 'Centro',
                'rua' => 'Av. Principal',
                'numero' => '123',
                'complemento' => 'Próximo à praça central',
                'cep' => '89280-000',
                'cidade' => 'São Bento do Sul',
                'uf' => 'SC',
                'latitude' => -26.2500,
                'longitude' => -49.3839,
                'status' => 'aberto',
                'prefeitura_slug' => 'prefeitura-municipal-de-sao-bento-do-sul',
                'user_email' => 'cidadao.centro@demo.test',
                'assigned_email' => 'admin.sbs@demo.test',
            ],
            [
                'titulo' => 'Iluminação pública queimada',
                'descricao' => 'Poste apagado há 3 dias, rua fica totalmente escura.',
                'bairro' => 'Itaum',
                'rua' => 'Rua das Palmeiras',
                'numero' => '45',
                'cep' => '89210-000',
                'cidade' => 'Joinville',
                'uf' => 'SC',
                'latitude' => -26.3045,
                'longitude' => -48.8450,
                'status' => 'em_andamento',
                'prefeitura_slug' => 'prefeitura-municipal-de-joinville',
                'user_email' => 'cidadao.zonanorte@demo.test',
                'assigned_email' => 'admin.joinville@demo.test',
            ],
            [
                'titulo' => 'Coleta de lixo atrasada',
                'descricao' => 'Coleta não ocorreu esta semana, sacos acumulados.',
                'bairro' => 'Bela Vista',
                'rua' => 'Rua das Acácias',
                'numero' => '200',
                'cep' => '89211-000',
                'cidade' => 'Joinville',
                'uf' => 'SC',
                'latitude' => -26.2901,
                'longitude' => -48.8502,
                'status' => 'resolvido',
                'prefeitura_slug' => 'prefeitura-municipal-de-joinville',
                'user_email' => 'cidadao.zonanorte@demo.test',
                'assigned_email' => 'admin.joinville@demo.test',
            ],
        ];

        foreach ($problemasData as $data) {
            $prefeitura = $prefeituras[$data['prefeitura_slug']] ?? null;
            $autor = $users[$data['user_email']] ?? null;
            $assigned = $users[$data['assigned_email']] ?? null;

            if (!$prefeitura || !$autor) {
                continue;
            }

            Problema::updateOrCreate(
                [
                    'titulo' => $data['titulo'],
                    'prefeitura_id' => $prefeitura->id,
                ],
                [
                    'descricao' => $data['descricao'],
                    'bairro' => $data['bairro'],
                    'rua' => $data['rua'] ?? null,
                    'numero' => $data['numero'] ?? null,
                    'complemento' => $data['complemento'] ?? null,
                    'cep' => $data['cep'] ?? null,
                    'cidade' => $data['cidade'] ?? null,
                    'uf' => $data['uf'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'status' => $data['status'],
                    'status_history' => [
                        [
                            'from' => null,
                            'to' => $data['status'],
                            'reason' => 'Carga inicial de demonstração',
                            'changed_at' => Carbon::now()->toIso8601String(),
                            'changed_by' => $assigned?->id,
                        ],
                    ],
                    'prefeitura_id' => $prefeitura->id,
                    'user_id' => $autor->id,
                    'assigned_to' => $assigned?->id,
                    'resolved_at' => $data['status'] === 'resolvido' ? Carbon::now()->subDays(1) : null,
                ]
            );
        }
    }
}
