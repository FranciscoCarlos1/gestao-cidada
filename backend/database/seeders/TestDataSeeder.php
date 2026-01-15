<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Prefeitura;
use App\Models\Problema;
use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Criar mais prefeituras
        $prefeituras = [
            [
                'nome' => 'Prefeitura de São Paulo',
                'cidade' => 'São Paulo',
                'estado' => 'SP',
                'cnpj' => '12345678000199',
                'telefone' => '(11) 3113-9000',
                'email' => 'atendimento@prefeitura.sp.gov.br',
            ],
            [
                'nome' => 'Prefeitura de Fortaleza',
                'cidade' => 'Fortaleza',
                'estado' => 'CE',
                'cnpj' => '07040108000138',
                'telefone' => '(85) 3105-1000',
                'email' => 'atendimento@fortaleza.ce.gov.br',
            ],
            [
                'nome' => 'Prefeitura de Recife',
                'cidade' => 'Recife',
                'estado' => 'PE',
                'cnpj' => '10468988000110',
                'telefone' => '(81) 3355-8000',
                'email' => 'atendimento@recife.pe.gov.br',
            ],
            [
                'nome' => 'Prefeitura de Salvador',
                'cidade' => 'Salvador',
                'estado' => 'BA',
                'cnpj' => '13927682000107',
                'telefone' => '(71) 3202-3000',
                'email' => 'atendimento@salvador.ba.gov.br',
            ],
        ];

        foreach ($prefeituras as $prefeituraData) {
            Prefeitura::firstOrCreate(
                ['cnpj' => $prefeituraData['cnpj']],
                $prefeituraData
            );
        }

        // Pegar algumas prefeituras para associar aos usuários
        $todasPrefeituras = Prefeitura::all();
        $prefeituraSP = Prefeitura::where('cidade', 'São Paulo')->first();
        $prefeituraFortaleza = Prefeitura::where('cidade', 'Fortaleza')->first();

        // Criar usuários administradores de diferentes prefeituras
        $admins = [
            [
                'name' => 'João Silva',
                'email' => 'joao.silva@prefeitura.sp.gov.br',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'prefeitura_id' => $prefeituraSP?->id,
            ],
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@fortaleza.ce.gov.br',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'prefeitura_id' => $prefeituraFortaleza?->id,
            ],
        ];

        foreach ($admins as $adminData) {
            User::firstOrCreate(
                ['email' => $adminData['email']],
                $adminData
            );
        }

        // Criar usuários cidadãos
        $cidadaos = [
            [
                'name' => 'Carlos Eduardo',
                'email' => 'carlos.eduardo@gmail.com',
                'password' => Hash::make('senha123'),
                'role' => 'cidadao',
            ],
            [
                'name' => 'Ana Paula',
                'email' => 'ana.paula@hotmail.com',
                'password' => Hash::make('senha123'),
                'role' => 'cidadao',
            ],
            [
                'name' => 'Pedro Henrique',
                'email' => 'pedro.henrique@outlook.com',
                'password' => Hash::make('senha123'),
                'role' => 'cidadao',
            ],
            [
                'name' => 'Juliana Costa',
                'email' => 'juliana.costa@gmail.com',
                'password' => Hash::make('senha123'),
                'role' => 'cidadao',
            ],
            [
                'name' => 'Roberto Lima',
                'email' => 'roberto.lima@yahoo.com',
                'password' => Hash::make('senha123'),
                'role' => 'cidadao',
            ],
        ];

        foreach ($cidadaos as $cidadaoData) {
            User::firstOrCreate(
                ['email' => $cidadaoData['email']],
                $cidadaoData
            );
        }

        // Criar problemas de teste
        $usuarios = User::where('role', 'cidadao')->get();

        $problemasExemplo = [
            [
                'titulo' => 'Buraco grande na Av. Paulista',
                'descricao' => 'Há um buraco de aproximadamente 1 metro de diâmetro na altura do número 1500 da Av. Paulista, causando risco aos motoristas.',
                'categoria' => 'infraestrutura',
                'endereco' => 'Av. Paulista, 1500',
                'latitude' => -23.561684,
                'longitude' => -46.656139,
                'status' => 'pendente',
            ],
            [
                'titulo' => 'Poste de iluminação queimado',
                'descricao' => 'O poste de iluminação pública está queimado há 3 dias, deixando a rua escura e perigosa durante a noite.',
                'categoria' => 'iluminacao',
                'endereco' => 'Rua Augusta, 2000',
                'latitude' => -23.553785,
                'longitude' => -46.661789,
                'status' => 'em_analise',
            ],
            [
                'titulo' => 'Lixo acumulado na calçada',
                'descricao' => 'Há lixo acumulado na calçada há mais de uma semana, causando mau cheiro e atraindo insetos.',
                'categoria' => 'limpeza',
                'endereco' => 'Rua da Consolação, 500',
                'latitude' => -23.547778,
                'longitude' => -46.653056,
                'status' => 'pendente',
            ],
            [
                'titulo' => 'Semáforo com defeito',
                'descricao' => 'O semáforo do cruzamento está piscando amarelo constantemente, causando confusão no trânsito.',
                'categoria' => 'transito',
                'endereco' => 'Av. Faria Lima com Rua Funchal',
                'latitude' => -23.586111,
                'longitude' => -46.682778,
                'status' => 'em_andamento',
            ],
            [
                'titulo' => 'Vazamento de água na rua',
                'descricao' => 'Há um vazamento de água constante no meio da rua, causando desperdício e risco de alagamento.',
                'categoria' => 'saneamento',
                'endereco' => 'Rua Haddock Lobo, 300',
                'latitude' => -23.556944,
                'longitude' => -46.662222,
                'status' => 'pendente',
            ],
            [
                'titulo' => 'Calçada danificada',
                'descricao' => 'A calçada está completamente irregular e quebrada, dificultando a passagem de pedestres e cadeirantes.',
                'categoria' => 'infraestrutura',
                'endereco' => 'Av. Ipiranga, 1000',
                'latitude' => -23.543889,
                'longitude' => -46.641667,
                'status' => 'resolvido',
            ],
            [
                'titulo' => 'Praça precisa de manutenção',
                'descricao' => 'A Praça da República está com bancos quebrados, lixeiras transbordando e precisa de jardinagem.',
                'categoria' => 'espacos_publicos',
                'endereco' => 'Praça da República',
                'latitude' => -23.543611,
                'longitude' => -46.642778,
                'status' => 'em_analise',
            ],
            [
                'titulo' => 'Árvore caída bloqueando via',
                'descricao' => 'Uma árvore grande caiu após a chuva e está bloqueando parcialmente a rua.',
                'categoria' => 'meio_ambiente',
                'endereco' => 'Av. Angélica, 800',
                'latitude' => -23.545556,
                'longitude' => -46.655278,
                'status' => 'pendente',
            ],
        ];

        foreach ($problemasExemplo as $index => $problemaData) {
            $usuario = $usuarios->random();
            $prefeitura = $todasPrefeituras->random();

            Problema::firstOrCreate(
                [
                    'titulo' => $problemaData['titulo'],
                    'user_id' => $usuario->id,
                ],
                array_merge($problemaData, [
                    'user_id' => $usuario->id,
                    'prefeitura_id' => $prefeitura->id,
                ])
            );
        }

        $this->command->info('✅ Dados de teste criados com sucesso!');
        $this->command->info('📊 Total de usuários: ' . User::count());
        $this->command->info('🏛️  Total de prefeituras: ' . Prefeitura::count());
        $this->command->info('🚨 Total de problemas: ' . Problema::count());
    }
}
