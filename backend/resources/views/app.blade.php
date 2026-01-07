<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão Cidadã - Sistema SAS Completo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-50">
    <div x-data="app()" class="min-h-screen">
        <!-- Navbar -->
        <nav class="bg-white shadow-sm sticky top-0 z-40">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-blue-400 rounded-lg flex items-center justify-center">
                        <span class="text-white font-bold">GC</span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestão Cidadã</h1>
                </div>

                <div class="flex items-center gap-4">
                    <template x-if="auth.token">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900" x-text="auth.user?.name || 'Usuário'"></p>
                            <p class="text-xs text-gray-500" x-text="'(' + (auth.user?.role || 'cidadao') + ')'"></p>
                        </div>
                    </template>
                    <button @click="logout()" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Sair
                    </button>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

            <!-- Login/Register (Guest) -->
            <template x-if="!auth.token && currentView === 'auth'">
                <div class="grid grid-cols-2 gap-8">
                    <!-- Login -->
                    <div class="bg-white p-8 rounded-lg shadow-md">
                        <h2 class="text-2xl font-bold mb-6">Login</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input x-model="loginForm.email" type="email" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="seu@email.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                                <input x-model="loginForm.password" type="password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="••••••••">
                            </div>
                            <div x-show="show2FA" class="border-t pt-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Código 2FA</label>
                                <input x-model="loginForm.totp_code" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="000000" maxlength="6">
                            </div>
                            <button @click="login()" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                                <span x-show="!loading">Entrar</span>
                                <span x-show="loading">Carregando...</span>
                            </button>
                            <p class="text-center text-sm text-gray-600">
                                Não tem conta? <a @click="currentView = 'register'" class="text-blue-600 cursor-pointer hover:underline">Registre-se</a>
                            </p>
                        </div>
                    </div>

                    <!-- Register -->
                    <div class="bg-white p-8 rounded-lg shadow-md">
                        <h2 class="text-2xl font-bold mb-6">Registrar</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                                <input x-model="registerForm.name" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Seu Nome">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input x-model="registerForm.email" type="email" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="seu@email.com">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                                <input x-model="registerForm.password" type="password" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Mínimo 8 caracteres">
                            </div>
                            <button @click="register()" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition font-medium">
                                <span x-show="!loading">Registrar</span>
                                <span x-show="loading">Criando...</span>
                            </button>
                            <p class="text-center text-sm text-gray-600">
                                Já tem conta? <a @click="currentView = 'auth'; show2FA = false;" class="text-blue-600 cursor-pointer hover:underline">Faça login</a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Anônimo -->
                <div class="mt-8 text-center">
                    <p class="text-gray-600 mb-4">Quer explorar sem login?</p>
                    <button @click="loginAnonymous()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium">
                        Continuar Anônimo
                    </button>
                </div>
            </template>

            <!-- Dashboard (Anônimo) -->
            <template x-if="auth.type === 'anonimo'">
                <div>
                    <h2 class="text-2xl font-bold mb-6">Problemas Públicos</h2>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="space-y-4">
                            <template x-for="problema in problems" :key="problema.id">
                                <div class="border-l-4 border-blue-500 pl-4 py-4">
                                    <h3 class="font-semibold text-lg" x-text="problema.titulo"></h3>
                                    <p class="text-gray-600 mt-1" x-text="problema.descricao.substring(0, 100) + '...'"></p>
                                    <div class="flex justify-between items-center mt-3 text-sm">
                                        <span class="text-gray-500" x-text="'📍 ' + problema.bairro"></span>
                                        <span :class="statusColor(problema.status)" x-text="problema.status.replace('_', ' ').toUpperCase()"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Dashboard (Cidadão) -->
            <template x-if="auth.user && auth.user.role === 'cidadao'">
                <div class="space-y-8">
                    <!-- Menu de abas -->
                    <div class="flex gap-4 border-b">
                        <button @click="currentTab = 'meus'" :class="currentTab === 'meus' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Meus Problemas
                        </button>
                        <button @click="currentTab = 'criar'" :class="currentTab === 'criar' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Criar Novo
                        </button>
                        <button @click="currentTab = 'publicos'" :class="currentTab === 'publicos' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Ver Públicos
                        </button>
                        <button @click="currentTab = '2fa'" :class="currentTab === '2fa' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Segurança 2FA
                        </button>
                    </div>

                    <!-- Meus Problemas -->
                    <template x-if="currentTab === 'meus'">
                        <div>
                            <h3 class="text-xl font-bold mb-4">Meus Problemas</h3>
                            <template x-if="myProblems.length > 0">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="p in myProblems" :key="p.id">
                                        <div class="bg-white p-6 rounded-lg shadow-md">
                                            <h4 class="font-semibold text-lg mb-2" x-text="p.titulo"></h4>
                                            <p class="text-gray-600 text-sm mb-3" x-text="p.descricao.substring(0, 80) + '...'"></p>
                                            <div class="space-y-1 text-sm text-gray-500 mb-4">
                                                <p><strong>Bairro:</strong> <span x-text="p.bairro"></span></p>
                                                <p><strong>Rua:</strong> <span x-text="p.rua"></span></p>
                                                <p><strong>Data:</strong> <span x-text="new Date(p.created_at).toLocaleDateString('pt-BR')"></span></p>
                                            </div>
                                            <div class="flex items-center justify-between">
                                                <span :class="statusColor(p.status)" x-text="p.status.replace('_', ' ').toUpperCase()" class="px-3 py-1 rounded-full text-xs font-semibold"></span>
                                                <button @click="viewDetail(p)" class="text-blue-600 hover:underline text-sm">Detalhes</button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="myProblems.length === 0">
                                <div class="bg-gray-100 p-8 rounded-lg text-center">
                                    <p class="text-gray-600">Você ainda não criou nenhum problema.</p>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Criar Novo Problema -->
                    <template x-if="currentTab === 'criar'">
                        <div class="bg-white p-8 rounded-lg shadow-md">
                            <h3 class="text-xl font-bold mb-6">Reportar Novo Problema</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Título</label>
                                    <input x-model="problemForm.titulo" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="Ex: Buraco na rua">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                                    <textarea x-model="problemForm.descricao" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" rows="4" placeholder="Descreva detalhadamente o problema"></textarea>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Bairro</label>
                                        <input x-model="problemForm.bairro" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Rua</label>
                                        <input x-model="problemForm.rua" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Número</label>
                                        <input x-model="problemForm.numero" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">CEP</label>
                                        <input x-model="problemForm.cep" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none" placeholder="12345-678">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Prefeitura</label>
                                        <select x-model="problemForm.prefeitura_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                            <option value="">Selecione...</option>
                                            <template x-for="pref in prefeituras" :key="pref.id">
                                                <option :value="pref.id" x-text="pref.nome"></option>
                                            </template>
                                        </select>
                                    </div>
                                </div>
                                <button @click="createProblem()" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition font-medium">
                                    Enviar Problema
                                </button>
                            </div>
                        </div>
                    </template>

                    <!-- 2FA Setup -->
                    <template x-if="currentTab === '2fa'">
                        <div class="bg-white p-8 rounded-lg shadow-md">
                            <h3 class="text-xl font-bold mb-6">Autenticação em Dois Fatores (2FA)</h3>
                            <template x-if="!show2FAQr">
                                <div>
                                    <p class="text-gray-600 mb-4">Adicione uma camada extra de segurança ativando 2FA.</p>
                                    <button @click="generate2FA()" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                                        Gerar QR Code
                                    </button>
                                </div>
                            </template>
                            <template x-if="show2FAQr">
                                <div class="space-y-4">
                                    <img :src="qrCodeData" class="mx-auto" style="max-width: 300px;">
                                    <p class="text-sm text-gray-600 text-center">Escaneie com seu autenticador favorito</p>
                                    <input x-model="twoFAForm.totp_code" type="text" maxlength="6" class="w-full px-4 py-2 border rounded-lg text-center text-lg tracking-widest" placeholder="000000">
                                    <button @click="confirm2FA()" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                                        Confirmar 2FA
                                    </button>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Dashboard (Admin Prefeitura) -->
            <template x-if="auth.user && auth.user.role === 'admin'">
                <div class="space-y-8">
                    <div class="flex gap-4 border-b">
                        <button @click="currentTab = 'problemas_admin'" :class="currentTab === 'problemas_admin' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Problemas da Prefeitura
                        </button>
                        <button @click="currentTab = 'estatisticas'" :class="currentTab === 'estatisticas' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Estatísticas
                        </button>
                    </div>

                    <!-- Problemas Admin -->
                    <template x-if="currentTab === 'problemas_admin'">
                        <div class="grid grid-cols-1 gap-4">
                            <template x-for="p in adminProblems" :key="p.id">
                                <div class="bg-white p-6 rounded-lg shadow-md border-l-4" :class="statusBorderColor(p.status)">
                                    <div class="flex justify-between items-start mb-3">
                                        <div>
                                            <h4 class="font-semibold text-lg" x-text="p.titulo"></h4>
                                            <p class="text-gray-600 text-sm" x-text="'Cidadão: ' + (p.user?.name || 'Anônimo')"></p>
                                        </div>
                                        <select @change="updateProblemStatus(p.id, $event.target.value)" :value="p.status" class="px-3 py-1 border rounded-lg text-sm">
                                            <option value="aberto">Aberto</option>
                                            <option value="em_andamento">Em Andamento</option>
                                            <option value="resolvido">Resolvido</option>
                                            <option value="fechado">Fechado</option>
                                            <option value="rejeitado">Rejeitado</option>
                                        </select>
                                    </div>
                                    <p class="text-gray-600 text-sm mb-3" x-text="p.descricao.substring(0, 150) + '...'"></p>
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <p><strong>Localização:</strong> <span x-text="p.rua + ', ' + p.numero + ' - ' + p.bairro"></span></p>
                                        <p><strong>Data:</strong> <span x-text="new Date(p.created_at).toLocaleDateString('pt-BR')"></span></p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Estatísticas -->
                    <template x-if="currentTab === 'estatisticas'">
                        <div class="grid grid-cols-3 gap-4 mb-8">
                            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                                <h4 class="text-gray-600 text-sm font-medium mb-2">Total de Problemas</h4>
                                <p class="text-4xl font-bold text-blue-600" x-text="stats.total"></p>
                            </div>
                            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                                <h4 class="text-gray-600 text-sm font-medium mb-2">Resolvidos</h4>
                                <p class="text-4xl font-bold text-green-600" x-text="stats.resolved"></p>
                            </div>
                            <div class="bg-white p-6 rounded-lg shadow-md text-center">
                                <h4 class="text-gray-600 text-sm font-medium mb-2">Taxa de Resolução</h4>
                                <p class="text-4xl font-bold text-orange-600" x-text="stats.resolution_rate + '%'"></p>
                            </div>
                        </div>
                        <div class="bg-white p-6 rounded-lg shadow-md">
                            <h4 class="font-semibold mb-4">Problemas por Status</h4>
                            <canvas id="statusChart"></canvas>
                        </div>
                    </template>
                </div>
            </template>

            <!-- Dashboard (Super Admin) -->
            <template x-if="auth.user && auth.user.role === 'super'">
                <div class="space-y-8">
                    <div class="flex gap-4 border-b">
                        <button @click="currentTab = 'usuarios'" :class="currentTab === 'usuarios' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Usuários
                        </button>
                        <button @click="currentTab = 'roles'" :class="currentTab === 'roles' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Roles & Permissões
                        </button>
                        <button @click="currentTab = 'prefeituras_admin'" :class="currentTab === 'prefeituras_admin' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Prefeituras
                        </button>
                        <button @click="currentTab = 'audit'" :class="currentTab === 'audit' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 font-medium">
                            Auditoria
                        </button>
                    </div>

                    <!-- Usuários -->
                    <template x-if="currentTab === 'usuarios'">
                        <div>
                            <div class="mb-6">
                                <button @click="showNewUserForm = !showNewUserForm" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                                    + Novo Usuário
                                </button>
                            </div>
                            <template x-if="showNewUserForm">
                                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                                    <h4 class="font-semibold mb-4">Criar Novo Usuário</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <input x-model="newUserForm.name" type="text" placeholder="Nome" class="px-4 py-2 border rounded-lg">
                                        <input x-model="newUserForm.email" type="email" placeholder="Email" class="px-4 py-2 border rounded-lg">
                                        <input x-model="newUserForm.password" type="password" placeholder="Senha" class="px-4 py-2 border rounded-lg">
                                        <select x-model="newUserForm.role" class="px-4 py-2 border rounded-lg">
                                            <option value="cidadao">Cidadão</option>
                                            <option value="admin">Admin Prefeitura</option>
                                            <option value="super">Super Admin</option>
                                        </select>
                                    </div>
                                    <button @click="createUser()" class="mt-4 w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">
                                        Criar
                                    </button>
                                </div>
                            </template>

                            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                                <table class="w-full">
                                    <thead class="bg-gray-100 border-b">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Nome</th>
                                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Email</th>
                                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Role</th>
                                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="user in users" :key="user.id">
                                            <tr class="border-b hover:bg-gray-50">
                                                <td class="px-6 py-4 text-sm" x-text="user.name"></td>
                                                <td class="px-6 py-4 text-sm" x-text="user.email"></td>
                                                <td class="px-6 py-4 text-sm" x-text="user.role"></td>
                                                <td class="px-6 py-4 text-sm">
                                                    <span :class="user.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-3 py-1 rounded-full text-xs font-semibold" x-text="user.status"></span>
                                                </td>
                                                <td class="px-6 py-4 text-sm space-x-2">
                                                    <button @click="toggleUserStatus(user.id, user.status)" class="text-orange-600 hover:underline">
                                                        <span x-show="user.status === 'active'">Suspender</span>
                                                        <span x-show="user.status !== 'active'">Ativar</span>
                                                    </button>
                                                    <button @click="deleteUser(user.id)" class="text-red-600 hover:underline">Deletar</button>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </template>

                    <!-- Auditoria -->
                    <template x-if="currentTab === 'audit'">
                        <div class="bg-white rounded-lg shadow-md p-6">
                            <h4 class="font-semibold mb-4">Log de Auditoria</h4>
                            <div class="space-y-3">
                                <template x-for="log in auditLogs" :key="log.id">
                                    <div class="border-b pb-3 text-sm">
                                        <p class="font-medium text-gray-900">
                                            <span x-text="log.user?.name || 'Sistema'"></span>
                                            -
                                            <span class="text-gray-600" x-text="log.action.toUpperCase()"></span>
                                        </p>
                                        <p class="text-xs text-gray-500" x-text="new Date(log.created_at).toLocaleString('pt-BR')"></p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    <script>
        function app() {
            return {
                auth: {
                    token: localStorage.getItem('auth_token'),
                    user: JSON.parse(localStorage.getItem('auth_user') || 'null'),
                    type: localStorage.getItem('auth_type') || 'guest'
                },
                currentView: 'auth',
                currentTab: 'meus',
                loading: false,
                show2FA: false,
                show2FAQr: false,
                qrCodeData: '',

                loginForm: { email: '', password: '', totp_code: '' },
                registerForm: { name: '', email: '', password: '' },
                problemForm: { titulo: '', descricao: '', bairro: '', rua: '', numero: '', cep: '', prefeitura_id: '' },
                newUserForm: { name: '', email: '', password: '', role: 'cidadao' },
                twoFAForm: { totp_code: '' },

                problems: [],
                myProblems: [],
                adminProblems: [],
                users: [],
                prefeituras: [],
                auditLogs: [],

                stats: { total: 0, resolved: 0, resolution_rate: 0 },
                showNewUserForm: false,

                async init() {
                    if (this.auth.token) {
                        this.currentView = 'dashboard';
                        await this.loadInitialData();
                    }
                },

                async login() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/auth/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                email: this.loginForm.email,
                                password: this.loginForm.password,
                                totp_code: this.loginForm.totp_code
                            })
                        });

                        const data = await response.json();
                        if (response.status === 422 && data.requires_2fa) {
                            this.show2FA = true;
                            return;
                        }

                        if (response.ok) {
                            this.auth.token = data.token;
                            this.auth.user = data.user;
                            this.auth.type = 'authenticated';
                            localStorage.setItem('auth_token', data.token);
                            localStorage.setItem('auth_user', JSON.stringify(data.user));
                            localStorage.setItem('auth_type', 'authenticated');
                            this.currentView = 'dashboard';
                            this.currentTab = 'meus';
                            this.show2FA = false;
                            await this.loadInitialData();
                        }
                    } catch (e) {
                        alert('Erro ao fazer login: ' + e.message);
                    }
                    this.loading = false;
                },

                async register() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/auth/register', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.registerForm)
                        });
                        const data = await response.json();
                        if (response.ok) {
                            this.auth.token = data.token;
                            this.auth.user = data.user;
                            this.auth.type = 'authenticated';
                            localStorage.setItem('auth_token', data.token);
                            localStorage.setItem('auth_user', JSON.stringify(data.user));
                            localStorage.setItem('auth_type', 'authenticated');
                            this.currentView = 'dashboard';
                            await this.loadInitialData();
                            alert('Registrado com sucesso!');
                        }
                    } catch (e) {
                        alert('Erro ao registrar: ' + e.message);
                    }
                    this.loading = false;
                },

                async loginAnonymous() {
                    this.loading = true;
                    try {
                        const response = await fetch('/api/auth/anonimo', { method: 'POST' });
                        const data = await response.json();
                        this.auth.token = data.token;
                        this.auth.type = 'anonimo';
                        localStorage.setItem('auth_token', data.token);
                        localStorage.setItem('auth_type', 'anonimo');
                        this.currentView = 'dashboard';
                        await this.loadPublicProblems();
                    } catch (e) {
                        alert('Erro: ' + e.message);
                    }
                    this.loading = false;
                },

                async logout() {
                    try {
                        await fetch('/api/auth/logout', {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + this.auth.token }
                        });
                    } catch (e) {}
                    this.auth = { token: '', user: null, type: 'guest' };
                    localStorage.clear();
                    this.currentView = 'auth';
                    this.loginForm = { email: '', password: '', totp_code: '' };
                    this.show2FA = false;
                },

                async loadInitialData() {
                    await this.loadPrefeituras();
                    if (this.auth.user?.role === 'cidadao') {
                        await this.loadMyProblems();
                        await this.loadPublicProblems();
                    } else if (this.auth.user?.role === 'admin') {
                        await this.loadAdminProblems();
                    } else if (this.auth.user?.role === 'super') {
                        await this.loadUsers();
                        await this.loadAuditLogs();
                    }
                },

                async loadPrefeituras() {
                    try {
                        const res = await fetch('/api/prefeituras');
                        this.prefeituras = (await res.json()).data;
                    } catch (e) {
                        console.error(e);
                    }
                },

                async loadPublicProblems() {
                    try {
                        const res = await fetch('/api/problemas');
                        this.problems = (await res.json()).data;
                    } catch (e) {
                        console.error(e);
                    }
                },

                async loadMyProblems() {
                    try {
                        const res = await fetch('/api/problemas/mine', {
                            headers: { 'Authorization': 'Bearer ' + this.auth.token }
                        });
                        this.myProblems = (await res.json()).data;
                    } catch (e) {
                        console.error(e);
                    }
                },

                async loadAdminProblems() {
                    try {
                        const res = await fetch('/api/admin/problemas', {
                            headers: { 'Authorization': 'Bearer ' + this.auth.token }
                        });
                        this.adminProblems = (await res.json()).data;
                        this.calculateStats();
                    } catch (e) {
                        console.error(e);
                    }
                },

                async loadUsers() {
                    try {
                        const res = await fetch('/api/admin/users', {
                            headers: { 'Authorization': 'Bearer ' + this.auth.token }
                        });
                        this.users = (await res.json()).data;
                    } catch (e) {
                        console.error(e);
                    }
                },

                async loadAuditLogs() {
                    try {
                        const res = await fetch('/api/admin/audit-logs', {
                            headers: { 'Authorization': 'Bearer ' + this.auth.token }
                        });
                        const data = await res.json();
                        this.auditLogs = data.data || [];
                    } catch (e) {
                        console.error(e);
                    }
                },

                async createProblem() {
                    if (!this.problemForm.titulo || !this.problemForm.descricao || !this.problemForm.prefeitura_id) {
                        alert('Preencha todos os campos obrigatórios');
                        return;
                    }

                    try {
                        const res = await fetch('/api/problemas', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.auth.token
                            },
                            body: JSON.stringify(this.problemForm)
                        });

                        if (res.ok) {
                            alert('Problema criado com sucesso!');
                            this.problemForm = { titulo: '', descricao: '', bairro: '', rua: '', numero: '', cep: '', prefeitura_id: '' };
                            await this.loadMyProblems();
                            this.currentTab = 'meus';
                        }
                    } catch (e) {
                        alert('Erro: ' + e.message);
                    }
                },

                async updateProblemStatus(id, status) {
                    try {
                        const res = await fetch(`/api/admin/problemas/${id}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.auth.token
                            },
                            body: JSON.stringify({ status })
                        });

                        if (res.ok) {
                            await this.loadAdminProblems();
                        }
                    } catch (e) {
                        console.error(e);
                    }
                },

                async createUser() {
                    try {
                        const res = await fetch('/api/admin/users', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.auth.token
                            },
                            body: JSON.stringify(this.newUserForm)
                        });

                        if (res.ok) {
                            alert('Usuário criado com sucesso!');
                            this.newUserForm = { name: '', email: '', password: '', role: 'cidadao' };
                            this.showNewUserForm = false;
                            await this.loadUsers();
                        }
                    } catch (e) {
                        alert('Erro: ' + e.message);
                    }
                },

                async deleteUser(id) {
                    if (!confirm('Tem certeza?')) return;
                    try {
                        const res = await fetch(`/api/admin/users/${id}`, {
                            method: 'DELETE',
                            headers: { 'Authorization': 'Bearer ' + this.auth.token }
                        });
                        if (res.ok) {
                            await this.loadUsers();
                        }
                    } catch (e) {
                        alert('Erro: ' + e.message);
                    }
                },

                async toggleUserStatus(id, currentStatus) {
                    const newStatus = currentStatus === 'active' ? 'suspended' : 'active';
                    try {
                        const res = await fetch(`/api/admin/users/${id}/toggle-status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.auth.token
                            },
                            body: JSON.stringify({ status: newStatus })
                        });
                        if (res.ok) await this.loadUsers();
                    } catch (e) {
                        alert('Erro: ' + e.message);
                    }
                },

                async generate2FA() {
                    try {
                        const res = await fetch('/api/2fa/generate', {
                            method: 'POST',
                            headers: { 'Authorization': 'Bearer ' + this.auth.token }
                        });
                        const data = await res.json();
                        this.qrCodeData = data.qr_code_url;
                        this.show2FAQr = true;
                    } catch (e) {
                        alert('Erro: ' + e.message);
                    }
                },

                async confirm2FA() {
                    try {
                        const res = await fetch('/api/2fa/confirm', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': 'Bearer ' + this.auth.token
                            },
                            body: JSON.stringify({ totp_code: this.twoFAForm.totp_code })
                        });
                        const data = await res.json();
                        if (res.ok) {
                            alert('2FA ativado com sucesso!\nCódigos de backup: ' + data.backup_codes.join(', '));
                            this.show2FAQr = false;
                            this.twoFAForm = { totp_code: '' };
                        }
                    } catch (e) {
                        alert('Erro: ' + e.message);
                    }
                },

                calculateStats() {
                    this.stats.total = this.adminProblems.length;
                    this.stats.resolved = this.adminProblems.filter(p => p.status === 'resolvido').length;
                    this.stats.resolution_rate = this.stats.total > 0 ? Math.round((this.stats.resolved / this.stats.total) * 100) : 0;
                },

                statusColor(status) {
                    const colors = {
                        'aberto': 'bg-red-100 text-red-800',
                        'em_andamento': 'bg-yellow-100 text-yellow-800',
                        'resolvido': 'bg-green-100 text-green-800',
                        'fechado': 'bg-gray-100 text-gray-800',
                        'rejeitado': 'bg-orange-100 text-orange-800'
                    };
                    return colors[status] || 'bg-gray-100 text-gray-800';
                },

                statusBorderColor(status) {
                    const colors = {
                        'aberto': 'border-red-500',
                        'em_andamento': 'border-yellow-500',
                        'resolvido': 'border-green-500',
                        'fechado': 'border-gray-500',
                        'rejeitado': 'border-orange-500'
                    };
                    return colors[status] || 'border-gray-500';
                },

                viewDetail(problema) {
                    console.log('Detalhes:', problema);
                }
            };
        }

        document.addEventListener('alpine:initialized', () => {
            const appInstance = app();
            appInstance.init();
        });
    </script>
</body>
</html>
