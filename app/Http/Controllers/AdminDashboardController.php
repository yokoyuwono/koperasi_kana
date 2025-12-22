<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\PromosiAgent;
use App\Models\Agent;
use App\Models\Nasabah;

class AdminDashboardController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403);
        }
    }

    public function index()
    {
        $this->ensureAdmin();

        // Ringkasan operasional
        $totalAgents   = Agent::count();
        $totalNasabah  = Nasabah::count();

        $depositDraftCount   = Deposit::where('status', 'draft')->count();   // kalau kamu pakai draft
        $depositPendingCount = Deposit::where('status', 'pending')->count(); // menunggu COA
        $depositRejectedCount = Deposit::where('status', 'rejected')->count(); // revisi admin

        // Promosi agent
        $promosiPendingCount  = PromosiAgent::where('status', 'pending')->count();
        $promosiRejectedCount = PromosiAgent::where('status', 'rejected')->count();

        // Queue untuk kerja admin
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

        return view('dashboard', compact(
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
