<?php

namespace App\Http\Controllers;
use App\Models\Agent;
use App\Models\PromosiAgent;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PromosiAgentController extends Controller
{
    private function ensureAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') abort(403);
    }

    private function ensureCoa()
    {
        if (!auth()->check() || auth()->user()->role !== 'coa') abort(403);
    }

    /** ADMIN LIST */
    public function adminIndex()
    {
        $this->ensureAdmin();

        $items = PromosiAgent::with(['agent', 'atasanBdp'])
            ->orderByDesc('id')
            ->paginate(15);

        return view('promosi_agent.admin_index', compact('items'));
    }

    /** ADMIN FORM CREATE */
    public function create()
    {
        $this->ensureAdmin();

        // hanya RM yang bisa diajukan naik
        $rmAgents = Agent::where('jabatan', 'RM')->orderBy('nama')->get();
        $bdpAgents = Agent::where('jabatan', 'BDP')->orderBy('nama')->get();

        return view('promosi_agent.create', compact('rmAgents', 'bdpAgents'));
    }

    /** ADMIN STORE */
    public function store(Request $request)
    {
        $this->ensureAdmin();

        // VALIDASI INPUT (tanpa atasan_bdp_id)
        $data = $request->validate([
            'id_agent'      => 'required|exists:agents,id',
            'catatan_admin' => 'nullable|string',
        ]);

        $agent = Agent::findOrFail($data['id_agent']);

        // pastikan hanya RM
        if ($agent->jabatan !== 'RM') {
            return back()->withErrors([
                'id_agent' => 'Hanya agent dengan jabatan RM yang bisa diajukan naik.'
            ])->withInput();
        }

        // pastikan RM punya atasan (BDP)
        if (!$agent->atasan_id) {
            return back()->withErrors([
                'id_agent' => 'Agent RM ini belum memiliki atasan (BDP).'
            ])->withInput();
        }

        // OPTIONAL: cegah pengajuan ganda (recommended)
        $exists = PromosiAgent::where('id_agent', $agent->id)
            ->whereIn('status', ['pending'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'id_agent' => 'Agent ini sudah memiliki pengajuan promosi yang masih pending.'
            ])->withInput();
        }

        // SIMPAN PROMOSI (atasan BDP DIAMBIL OTOMATIS)
        $item = PromosiAgent::create([
            'id_admin'      => auth()->id(),
            'id_agent'      => $agent->id,
            'jabatan_lama'  => 'RM',
            'jabatan_baru'  => 'BDP',
            'atasan_bdp_id' => $agent->atasan_id, // 🔥 INI KUNCINYA
            'tanggal_apply' => now()->toDateString(),
            'status'        => 'pending',
            'catatan_admin' => $data['catatan_admin'] ?? null,
        ]);

        // LOG
        // Audit::log(
        //     'promosi_agent_log',
        //     'promosi_agent_id',
        //     $item->id,
        //     'create',
        //     null,
        //     $item->toArray(),
        //     'Pengajuan promosi RM ke BDP (atasan otomatis)'
        // );

        return redirect()->route('promosi.index')
            ->with('success', 'Pengajuan promosi berhasil dibuat (pending COA).');
    }

    /** ADMIN EDIT (hanya kalau masih pending / rejected) */
    public function edit(PromosiAgent $promosi)
    {
        $this->ensureAdmin();

        if (!in_array($promosi->status, ['pending', 'rejected'])) {
            abort(403, 'Pengajuan sudah diproses.');
        }

        $rmAgents = Agent::where('jabatan', 'RM')->orderBy('nama')->get();
        $bdpAgents = Agent::where('jabatan', 'BDP')->orderBy('nama')->get();

        return view('promosi_agent.edit', compact('promosi', 'rmAgents', 'bdpAgents'));
    }

    /** ADMIN UPDATE */
    public function update(Request $request, PromosiAgent $promosi)
    {
        $this->ensureAdmin();

        if (!in_array($promosi->status, ['pending', 'rejected'])) {
            abort(403);
        }

        $data = $request->validate([
            'id_agent'       => 'required|exists:agents,id',
            'atasan_bdp_id'  => 'required|exists:agents,id',
            'catatan_admin'  => 'nullable|string',
        ]);

        $agent = Agent::findOrFail($data['id_agent']);
        if ($agent->jabatan !== 'RM') {
            return back()->withErrors(['id_agent' => 'Hanya agent RM yang bisa diajukan naik.']);
        }

        $promosi->update([
            'id_agent'      => $data['id_agent'],
            'atasan_bdp_id' => $data['atasan_bdp_id'],
            'catatan_admin' => $data['catatan_admin'] ?? null,
            'status'        => 'pending',
            'alasan_reject' => null,
            'tanggal_approval' => null,
        ]);

        return redirect()->route('promosi.index')
            ->with('success', 'Pengajuan berhasil diperbarui dan dikirim ulang ke COA.');
    }

    /** COA LIST */
    public function coaIndex(Request $request)
    {
        $this->ensureCoa();

        $status = $request->query('status', 'all');

        $q = PromosiAgent::with(['agent', 'atasanBdp'])
            ->orderByDesc('id');

        if ($status !== 'all') $q->where('status', $status);

        $items = $q->paginate(15);

        return view('promosi_agent.coa_index', compact('items', 'status'));
    }

    /** COA DETAIL */
    public function coaShow(PromosiAgent $promosi)
    {
        $this->ensureCoa();

        $promosi->load(['agent', 'atasanBdp', 'admin']);

        return view('promosi_agent.coa_show', compact('promosi'));
    }

    /** COA APPROVE */
    public function approve(PromosiAgent $promosi)
    {
        $this->ensureCoa();

        if ($promosi->status !== 'pending') abort(403);

        DB::transaction(function () use ($promosi) {
            // ambil agent RM yang dipromosikan
            $agent = Agent::lockForUpdate()->findOrFail($promosi->id_agent);

            // pastikan masih RM
            if ($agent->jabatan !== 'RM') {
                throw new \Exception('Agent sudah bukan RM.');
            }

            // LOGIKA PENTING:
            // ketika RM -> BDP, referral diisi dari atasan BDP sebelumnya (atasan_id)
            // di sistem kamu: atasan RM adalah BDP, jadi ambil dari promosi->atasan_bdp_id
            $agent->jabatan = 'BDP';

            // isi referral
            $agent->refferred_by_agent_id = $promosi->atasan_bdp_id;

            // BDP tidak perlu atasan
            $agent->atasan_id = null;

            $agent->save();

            $promosi->update([
                'status'           => 'approved',
                'alasan_reject'    => null,
                'tanggal_approval' => now()->toDateString(),
            ]);
        });

        return redirect()->route('coa.promosi.index')
            ->with('success', 'Promosi disetujui. Agent RM berhasil naik menjadi BDP.');
    }

    /** COA REJECT */
    public function reject(Request $request, PromosiAgent $promosi)
    {
        $this->ensureCoa();

        if ($promosi->status !== 'pending') abort(403);

        $data = $request->validate([
            'alasan_reject' => 'required|string|min:3',
        ]);

        $promosi->update([
            'status'        => 'rejected',
            'alasan_reject' => $data['alasan_reject'],
            'tanggal_approval' => now()->toDateString(),
        ]);

        return redirect()->route('coa.promosi.index')
            ->with('success', 'Promosi ditolak dan alasan sudah disimpan.');
    }
}
