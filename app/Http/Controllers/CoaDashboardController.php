<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\PromosiAgent;
use App\Models\Komisi;
use Illuminate\Http\Request;

class CoaDashboardController extends Controller
{
    private function ensureCoa(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'coa') {
            abort(403);
        }
    }

    public function index()
    {
        $this->ensureCoa();

        // Ringkasan
        $depositPendingCount = Deposit::where('status', 'pending')->count();
        $depositPendingTotal = (float) Deposit::where('status', 'pending')->sum('nominal');

        $promosiPendingCount = PromosiAgent::where('status', 'pending')->count();

        // Komisi "butuh tindakan" (contoh: approved tapi belum dibayar)
        $komisiUnpaidCount = Komisi::where('status', operator: 'approved')
            ->whereNull('tanggal_pembayaran')
            ->count();

        $komisiUnpaidTotal = (float) Komisi::where('status', 'approved')
            ->whereNull('tanggal_pembayaran')
            ->sum('nominal');

        // Antrian review (limit kecil biar cepat)
        $depositQueue = Deposit::with(['nasabah', 'agent'])
            ->where('status', 'pending')
            ->orderByDesc('tanggal_transaksi')
            ->limit(5)
            ->get();

        $promosiQueue = PromosiAgent::with(['agent', 'atasanBdp'])
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('coa.dashboard', compact(
            'depositPendingCount',
            'depositPendingTotal',
            'promosiPendingCount',
            'komisiUnpaidCount',
            'komisiUnpaidTotal',
            'depositQueue',
            'promosiQueue'
        ));
    }
}
