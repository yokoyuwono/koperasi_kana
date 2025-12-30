<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\PromosiAgent;
use App\Models\Agent;
use App\Models\Nasabah;

class SuperAdminDashboardController extends Controller
{
    private function ensureSuperAdmin(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'superadmin') {
            abort(403, 'Akses ditolak. Hanya Superadmin yang boleh masuk.');
        }
    }

    public function index()
    {
        $this->ensureSuperAdmin();

        // Ringkasan operasional
        $totalAgents   = Agent::count();
        $totalNasabah  = Nasabah::count();

        $depositDraftCount   = Deposit::where('status', 'draft')->count();
        $depositPendingCount = Deposit::where('status', 'pending')->count();
        $depositRejectedCount = Deposit::where('status', 'rejected')->count();

        $promosiPendingCount  = PromosiAgent::where('status', 'pending')->count();
        $promosiRejectedCount = PromosiAgent::where('status', 'rejected')->count();

        // Queue
        $depositNeedsAction = Deposit::with(['nasabah','agent'])
            ->whereIn('status', ['draft','rejected'])
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $depositPendingQueue = Deposit::with(['nasabah','agent'])
            ->where('status', 'pending')
            ->orderByDesc('tanggal_transaksi')
            ->limit(5)
            ->get();

        $promosiNeedsFix = PromosiAgent::with(['agent','atasanBdp'])
            ->where('status', 'rejected')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        return view('superadmin.dashboard', compact(
            'totalAgents',
            'totalNasabah',
            'depositDraftCount',
            'depositPendingCount',
            'depositRejectedCount',
            'promosiPendingCount',
            'promosiRejectedCount',
            'depositNeedsAction',
            'depositPendingQueue',
            'promosiNeedsFix'
        ));
    }
}
