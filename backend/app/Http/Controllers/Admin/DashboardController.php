<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Problema;
use App\Models\Prefeitura;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obter estatísticas do dashboard
     */
    public function getStats()
    {
        $totalUsers = User::count();
        $totalProblems = Problema::count();
        $totalPrefeituras = Prefeitura::count();
        $activeUsers = User::where('status', 'active')->count();

        // Problemas por status
        $problemsByStatus = Problema::groupBy('status')
            ->selectRaw('status, COUNT(*) as count')
            ->get();

        // Usuários por role
        $usersByRole = DB::table('role_user')
            ->join('roles', 'role_user.role_id', '=', 'roles.id')
            ->selectRaw('roles.name as role, COUNT(*) as count')
            ->groupBy('roles.id', 'roles.name')
            ->get();

        // Problemas nos últimos 7 dias
        $problemsLast7Days = Problema::where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->orderBy('date')
            ->get();

        return response()->json([
            'totalUsers' => $totalUsers,
            'totalProblems' => $totalProblems,
            'totalPrefeituras' => $totalPrefeituras,
            'activeUsers' => $activeUsers,
            'problemsByStatus' => $problemsByStatus,
            'usersByRole' => $usersByRole,
            'problemsLast7Days' => $problemsLast7Days,
        ]);
    }

    /**
     * Obter atividades recentes
     */
    public function getRecentActivities()
    {
        $activities = AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'model_type' => $log->model_type,
                    'user_name' => $log->user?->name ?? 'Sistema',
                    'created_at' => $log->created_at->format('d/m/Y H:i'),
                    'description' => $this->getActivityDescription($log),
                ];
            });

        return response()->json($activities);
    }

    /**
     * Relatório de problemas
     */
    public function getProblemsReport(Request $request)
    {
        $query = Problema::with(['prefeitura', 'user']);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('category')) {
            $query->where('categoria', $request->input('category'));
        }

        if ($request->has('prefeitura_id')) {
            $query->where('prefeitura_id', $request->input('prefeitura_id'));
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $problems = $query->paginate($request->input('per_page', 10));

        // Estatísticas
        $stats = [
            'total' => $query->count(),
            'byStatus' => $query->groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->get(),
            'byCategory' => $query->groupBy('categoria')
                ->selectRaw('categoria, COUNT(*) as count')
                ->get(),
        ];

        return response()->json([
            'problems' => $problems,
            'stats' => $stats,
        ]);
    }

    /**
     * Exportar relatório de problemas em CSV
     */
    public function exportProblemsReport(Request $request)
    {
        $query = Problema::with(['prefeitura', 'user']);

        // Aplicar os mesmos filtros
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('category')) {
            $query->where('categoria', $request->input('category'));
        }
        if ($request->has('prefeitura_id')) {
            $query->where('prefeitura_id', $request->input('prefeitura_id'));
        }

        $problems = $query->get();

        $csv = "ID,Título,Descrição,Status,Categoria,Prefeitura,Usuário,Data Criação\n";

        foreach ($problems as $problem) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%s\n",
                $problem->id,
                '"' . str_replace('"', '""', $problem->titulo) . '"',
                '"' . str_replace('"', '""', substr($problem->descricao, 0, 50)) . '"',
                $problem->status,
                $problem->categoria,
                '"' . str_replace('"', '""', $problem->prefeitura?->nome ?? 'N/A') . '"',
                '"' . str_replace('"', '""', $problem->user?->name ?? 'N/A') . '"',
                $problem->created_at->format('d/m/Y H:i')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="problemas_' . date('Y-m-d') . '.csv"');
    }

    /**
     * Relatório de usuários
     */
    public function getUsersReport(Request $request)
    {
        $query = User::with(['roles', 'prefeitura']);

        // Filtros
        if ($request->has('role_id')) {
            $query->whereHas('roles', fn($q) => $q->where('id', $request->input('role_id')));
        }

        if ($request->has('prefeitura_id')) {
            $query->where('prefeitura_id', $request->input('prefeitura_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->paginate($request->input('per_page', 10));

        // Estatísticas
        $stats = [
            'total' => $query->count(),
            'byStatus' => $query->groupBy('status')
                ->selectRaw('status, COUNT(*) as count')
                ->get(),
            'byRole' => DB::table('role_user')
                ->join('roles', 'role_user.role_id', '=', 'roles.id')
                ->whereIn('role_user.user_id', $query->pluck('id'))
                ->selectRaw('roles.name as role, COUNT(*) as count')
                ->groupBy('roles.id', 'roles.name')
                ->get(),
        ];

        return response()->json([
            'users' => $users,
            'stats' => $stats,
        ]);
    }

    /**
     * Exportar relatório de usuários em CSV
     */
    public function exportUsersReport(Request $request)
    {
        $query = User::with(['roles', 'prefeitura']);

        // Aplicar filtros
        if ($request->has('role_id')) {
            $query->whereHas('roles', fn($q) => $q->where('id', $request->input('role_id')));
        }
        if ($request->has('prefeitura_id')) {
            $query->where('prefeitura_id', $request->input('prefeitura_id'));
        }
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->get();

        $csv = "ID,Nome,Email,Roles,Prefeitura,Status,Data Criação\n";

        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->join(', ');
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s\n",
                $user->id,
                '"' . str_replace('"', '""', $user->name) . '"',
                $user->email,
                '"' . str_replace('"', '""', $roles) . '"',
                '"' . str_replace('"', '""', $user->prefeitura?->nome ?? 'N/A') . '"',
                $user->status,
                $user->created_at->format('d/m/Y H:i')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="usuarios_' . date('Y-m-d') . '.csv"');
    }

    /**
     * Listar audit logs
     */
    public function getAuditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        // Filtros
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->has('model_type')) {
            $query->where('model_type', $request->input('model_type'));
        }

        if ($request->has('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->input('date_from'));
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->latest()
            ->paginate($request->input('per_page', 10));

        return response()->json($logs);
    }

    /**
     * Exportar audit logs em CSV
     */
    public function exportAuditLogs(Request $request)
    {
        $query = AuditLog::with('user');

        // Aplicar filtros
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }
        if ($request->has('model_type')) {
            $query->where('model_type', $request->input('model_type'));
        }
        if ($request->has('action')) {
            $query->where('action', $request->input('action'));
        }

        $logs = $query->latest()->get();

        $csv = "ID,Usuário,Ação,Tipo de Modelo,ID do Modelo,Data\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%d,%s\n",
                $log->id,
                '"' . str_replace('"', '""', $log->user?->name ?? 'Sistema') . '"',
                $log->action,
                $log->model_type,
                $log->model_id,
                $log->created_at->format('d/m/Y H:i')
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="auditoria_' . date('Y-m-d') . '.csv"');
    }

    /**
     * Gerar descrição legível da atividade
     */
    private function getActivityDescription($log)
    {
        $actions = [
            'create' => 'Criou',
            'update' => 'Atualizou',
            'delete' => 'Deletou',
        ];

        $action = $actions[$log->action] ?? $log->action;
        return "{$action} {$log->model_type} #{$log->model_id}";
    }
}
