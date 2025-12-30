<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Nasabah;
use App\Models\Deposit;
use App\Models\Komisi;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private function ensureRmOrBdp($user, Agent $agent): void
    {
        $role = strtolower(trim((string) ($user->role ?? '')));
        abort_unless(in_array($role, ['rm', 'bdp']), 403, 'Akses ditolak.');

        $jabatan = strtoupper(trim((string) ($agent->jabatan ?? '')));

        // sinkronisasi role vs jabatan
        if ($role === 'rm' && $jabatan !== 'RM') {
            abort(403, 'Akses ditolak (role RM tapi jabatan agent bukan RM).');
        }

        if ($role === 'bdp' && $jabatan !== 'BDP') {
            abort(403, 'Akses ditolak (role BDP tapi jabatan agent bukan BDP).');
        }
    }

    public function dashboard()
    {
        $user = auth()->user();
        abort_unless($user && $user->id_agent, 403, 'User tidak terhubung ke Agent.');

        $agent = Agent::findOrFail($user->id_agent);
        $this->ensureRmOrBdp($user, $agent);

        $role = strtoupper($agent->jabatan);

        // default
        $stats = [
            'role' => $role,
            'rm_count' => 0,
            'nasabah_count' => 0,
            'deposit_pending' => 0,
            'deposit_approved' => 0,
            'komisi_approved' => 0,
            'komisi_paid' => 0,
        ];

        $latestNasabah = collect();
        $latestDeposit = collect();

        if ($role === 'rm') {
            $nasabahIds = Nasabah::where('id_agent', $agent->id)->pluck('id');
            $depositQuery = Deposit::whereIn('id_nasabah', $nasabahIds);

            $stats['nasabah_count'] = $nasabahIds->count();
            $stats['deposit_pending'] = (clone $depositQuery)->where('status', 'pending')->count();
            $stats['deposit_approved'] = (clone $depositQuery)->where('status', 'approved')->count();

            $stats['komisi_approved'] = (float) Komisi::where('id_agent', $agent->id)->where('status', 'approved')->sum('nominal');
            $stats['komisi_paid'] = (float) Komisi::where('id_agent', $agent->id)->where('status', 'paid')->sum('nominal');

            $latestNasabah = Nasabah::where('id_agent', $agent->id)->latest('id')->limit(5)->get();
            $latestDeposit = (clone $depositQuery)->latest('id')->limit(5)->get();
        }

        if ($role === 'bdp') {
            $rmIds = Agent::where('atasan_id', $agent->id)
                ->where('role', 'rm')
                ->pluck('id');

            $nasabahIds = Nasabah::whereIn('id_agent', $rmIds)->pluck('id');
            $depositQuery = Deposit::whereIn('id_nasabah', $nasabahIds);

            $stats['rm_count'] = $rmIds->count();
            $stats['nasabah_count'] = $nasabahIds->count();
            $stats['deposit_pending'] = (clone $depositQuery)->where('status', 'pending')->count();
            $stats['deposit_approved'] = (clone $depositQuery)->where('status', 'approved')->count();

            // komisi BDP sendiri
            $stats['komisi_approved'] = (float) Komisi::where('id_agent', $agent->id)->where('status', 'approved')->sum('nominal');
            $stats['komisi_paid'] = (float) Komisi::where('id_agent', $agent->id)->where('status', 'paid')->sum('nominal');

            $latestDeposit = (clone $depositQuery)->latest('id')->limit(5)->get();
        }

        return view('user.dashboard', compact('agent', 'stats', 'latestNasabah', 'latestDeposit'));
    }

   public function komisi(Request $request)
    {
        $user = auth()->user();
        abort_unless($user && $user->id_agent, 403, 'User tidak terhubung ke Agent.');

        $agent = Agent::findOrFail($user->id_agent);
        $this->ensureRmOrBdp($user, $agent);

        $role = strtoupper($agent->jabatan); // RM / BDP

        // filter komisi
        $qStatus = $request->get('status', 'all'); // all|approved|paid
        $qBulan  = $request->get('bulan');         // YYYY-MM (opsional)

        // scope agent yang boleh dilihat
        $allowedAgentIds = collect([$agent->id]);

        if ($role === 'BDP') {
            $rmIds = Agent::where('atasan_id', $agent->id)
                ->where('jabatan', 'RM')
                ->pluck('id');

            $allowedAgentIds = $allowedAgentIds->merge($rmIds)->unique()->values();
        }

        // =====================
        // 1) LIST KOMISI (yang sudah ada)
        // =====================
        $komisiQuery = Komisi::query()
            ->whereIn('id_agent', $allowedAgentIds)
            ->orderByDesc('tanggal_periode')
            ->orderByDesc('id');

        if (in_array($qStatus, ['approved', 'paid'], true)) {
            $komisiQuery->where('status', $qStatus);
        }

        if ($qBulan) {
            $komisiQuery->whereRaw("DATE_FORMAT(tanggal_periode, '%Y-%m') = ?", [$qBulan]);
        }

        // tampil nama agent di card komisi
        $komisiQuery->with(['agent:id,nama,jabatan']);

        $rows = $komisiQuery->paginate(20)->withQueryString();

        $sumApproved = (clone $komisiQuery)->cloneWithout(['orders', 'limit', 'offset'])
            ->where('status', 'approved')->sum('nominal');

        $sumPaid = (clone $komisiQuery)->cloneWithout(['orders', 'limit', 'offset'])
            ->where('status', 'paid')->sum('nominal');

        // =====================
        // 2) LIST PENGAJUAN DEPOSIT (BARU)
        // =====================
        $dStatus = $request->get('d_status', 'all'); // all|pending|approved|rejected

        $depositQuery = Deposit::query()
            ->whereIn('id_agent', $allowedAgentIds)
            ->with([
                'nasabah:id,kode_nasabah,nama',
                'agent:id,kode_agent,nama,jabatan',
            ])
            ->orderByDesc('id');

        // filter bulan sama (mengacu ke tanggal_mulai)
        if ($qBulan) {
            $depositQuery->whereRaw("DATE_FORMAT(tanggal_mulai, '%Y-%m') = ?", [$qBulan]);
        }

        if (in_array($dStatus, ['pending', 'approved', 'rejected'], true)) {
            $depositQuery->where('status', $dStatus);
        }

        // paginator TERPISAH supaya tidak bentrok dengan pagination komisi
        $depositRows = $depositQuery->paginate(10, ['*'], 'deposits_page')->withQueryString();

        return view('user.komisi', compact(
            'agent',
            'rows',
            'qStatus',
            'qBulan',
            'sumApproved',
            'sumPaid',
            'depositRows',
            'dStatus'
        ));
    }
}
