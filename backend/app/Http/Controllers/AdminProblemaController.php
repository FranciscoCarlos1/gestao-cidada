<?php

namespace App\Http\Controllers;

use App\Models\Problema;
use Illuminate\Http\Request;

class AdminProblemaController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $q = Problema::query()
            ->with(['prefeitura','user'])
            ->orderByDesc('created_at');

        // Admin comum: restringe pela prefeitura vinculada
        if ($user->role === 'admin' && $user->prefeitura_id) {
            $q->where('prefeitura_id', $user->prefeitura_id);
        }

        // filtros opcionais
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        if ($request->filled('bairro')) {
            $q->where('bairro', 'ilike', '%'.$request->string('bairro').'%');
        }

        return $q->paginate(20);
    }

    public function updateStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => ['required','in:aberto,em_andamento,resolvido'],
        ]);

        $problema = Problema::findOrFail($id);
        $problema->status = $data['status'];
        $problema->save();

        return response()->json($problema);
    }
}
