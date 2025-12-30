<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Komisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentCommissionReportController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        $search = $request->query('search');

        $query = Agent::query()
            ->leftJoin('komisi', 'agents.id', '=', 'komisi.id_agent')
            ->leftJoin('deposits', 'deposits.id', '=', 'komisi.id_deposit')
            // Hanya menghitung komisi dari deposit yang sudah approved
            ->where(function($q) {
                $q->where('deposits.status', 'approved')
                  ->orWhereNull('deposits.id'); // Tetap tampilkan agent tanpa komisi approved jika diinginkan
            })
            ->select([
                'agents.id',
                'agents.kode_agent',
                'agents.nama',
                'agents.jabatan',
                DB::raw('SUM(CASE WHEN deposits.status = "approved" THEN komisi.nominal ELSE 0 END) as total_komisi')
            ])
            ->groupBy('agents.id', 'agents.kode_agent', 'agents.nama', 'agents.jabatan')
            ->orderBy('agents.nama');

        if ($search) {
            $query->where('agents.nama', 'LIKE', '%' . $search . '%')
                  ->orWhere('agents.kode_agent', 'LIKE', '%' . $search . '%');
        }

        $agents = $query->paginate(15)->withQueryString();

        return view('reports.agent_commissions.index', compact('agents', 'search'));
    }

    public function show(Agent $agent)
    {
        $this->ensureAdmin();

        $commissions = Komisi::with(['deposit'])
            ->join('deposits', 'deposits.id', '=', 'komisi.id_deposit')
            ->where('komisi.id_agent', $agent->id)
            ->where('deposits.status', 'approved')
            ->select('komisi.*')
            ->orderByDesc('komisi.tanggal_periode')
            ->paginate(20);

        return view('reports.agent_commissions.show', compact('agent', 'commissions'));
    }
}
