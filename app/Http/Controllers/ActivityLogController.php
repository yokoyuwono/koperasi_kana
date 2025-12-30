<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    private function ensureAdmin(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'superadmin') {
            abort(403, 'Akses ditolak. Hanya Superadmin yang boleh mengakses.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureAdmin();

        // Menggabungkan semua tabel log dengan UNION ALL
        // Kita tambahkan kolom virtual 'source' untuk membedakan asal log
        $agents = DB::table('agents_log')
            ->select('id', 'action', 'old_data', 'new_data', 'note', 'created_by', 'created_at', DB::raw('"Agent" as source'));

        $nasabah = DB::table('nasabah_log')
            ->select('id', 'action', 'old_data', 'new_data', 'note', 'created_by', 'created_at', DB::raw('"Nasabah" as source'));

        $deposits = DB::table('deposits_log')
            ->select('id', 'action', 'old_data', 'new_data', 'note', 'created_by', 'created_at', DB::raw('"Deposit" as source'));

        $komisi = DB::table('komisi_log')
            ->select('id', 'action', 'old_data', 'new_data', 'note', 'created_by', 'created_at', DB::raw('"Komisi" as source'));

        $promosi = DB::table('promosi_agent_log')
            ->select('id', 'action', 'old_data', 'new_data', 'note', 'created_by', 'created_at', DB::raw('"Promosi" as source'));

        // Gabungkan semuanya
        $query = $agents->unionAll($nasabah)
            ->unionAll($deposits)
            ->unionAll($komisi)
            ->unionAll($promosi);

        // Buat subquery agar bisa sorting dan join dengan users
        $logs = DB::table(DB::raw("({$query->toSql()}) as combined_logs"))
            ->mergeBindings($query)
            ->join('users', 'combined_logs.created_by', '=', 'users.id')
            ->select('combined_logs.*', 'users.nama as admin_nama', 'users.role as admin_role')
            ->orderByDesc('combined_logs.created_at')
            ->paginate(20);

        return view('admin.activity_logs.index', compact('logs'));
    }
}
