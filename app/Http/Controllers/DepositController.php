<?php

namespace App\Http\Controllers;

use App\Models\Komisi;
use App\Models\Deposit;
use App\Models\Nasabah;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;
use Illuminate\Support\Facades\Storage;



class DepositController extends Controller
{
    /**COA */
    public function coaIndex(Request $request)
    {
        $this->ensureCoa();

        // ambil filter status dari query string, default: 'all'
        $statusFilter = $request->query('status', 'all'); // all|pending|approved|rejected|draft

        $query = Deposit::with(['nasabah', 'agent'])
            ->orderByDesc('tanggal_transaksi');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $deposits = $query->paginate(15);

        return view('deposits.index', [
            'deposits'     => $deposits,
            'statusFilter' => $statusFilter,
        ]);
    }


    public function coaShow(Deposit $deposit)
    {
        $this->ensureCoa();

        // if ($deposit->status !== 'pending') {
        //     abort(404, 'Deposit ini tidak dalam status pending.');
        // }

        $deposit->load(['nasabah', 'agent.atasan', 'agent.refferredBy', 'komisi.agent']);

        // hitung ulang komisi sistem
        $persenSystem = $this->getSystemPersenKomisi((float) $deposit->nominal);
        $rmSystem  = $persenSystem['rm'];
        $bdpSystem = $persenSystem['bdp'];


        // kalau masih pending: ambil draft/pending
        // kalau sudah approved: ambil approved
        // kalau rejected/draft: tetap bisa tampil, ambil semua komisi
        if ($deposit->status === 'pending') {
            $komisi = $deposit->komisi()
                ->whereIn('status', ['draft', 'pending'])
                ->with('agent')
                ->get();
        } elseif ($deposit->status === 'approved') {
            $komisi = $deposit->komisi()
                ->where('status', 'approved')
                ->with('agent')
                ->get();
        } else {
            $komisi = $deposit->komisi()->with('agent')->get();
        }

        return view('coa.deposits.show', [
            'deposit'    => $deposit,
            'rmSystem'   => $rmSystem,
            'bdpSystem'  => $bdpSystem,
            'komisiList' => $komisi,   // ganti nama variabel biar generik
        ]);
    }

    public function coaApprove(Request $request, Deposit $deposit)
    {
        $this->ensureCoa();

        if ($deposit->status !== 'pending') {
            abort(403, 'Hanya deposito dengan status pending yang bisa disetujui.');
        }

        $data = $request->validate([
            'komisi' => 'required|array',
            'komisi.*' => 'required|numeric|min:0|max:100',
            'catatan' => 'nullable|string',
        ]);

        DB::transaction(function () use ($deposit, $data) {

            // pastikan komisi tambahan (override/referral) ada terlebih dahulu
            $this->ensureExtraKomisiDraft($deposit);

            $nominalDeposit = (float) $deposit->nominal;

            // update persen_komisi + nominal berdasarkan input COA (FINAL)
            foreach ($data['komisi'] as $komisiId => $persen) {
                $k = Komisi::where('id', (int)$komisiId)
                    ->where('id_deposit', $deposit->id)
                    ->first();

                if (!$k) continue;

                $old = $k->toArray();

                $persen = (float) $persen;
                $nominal = $nominalDeposit * $persen / 100;

                $k->update([
                    'persen_komisi' => $persen,
                    'nominal'       => $nominal,
                ]);

            //    Audit::log('komisi_log', 'komisi_id', $k->id, 'update', $old, $k->fresh()->toArray(), 'Penyesuaian komisi oleh COA');
            }

            // approve deposit
            $oldDeposit = $deposit->toArray();

            $deposit->update([
                'status'           => 'approved',
                'tanggal_approval' => now()->toDateString(),
                'catatan'          => $data['catatan'] ?? null,
            ]);

          //      Audit::log('deposits_log', 'deposit_id', $deposit->id, 'approve', $oldDeposit, $deposit->fresh()->toArray());

            // set semua komisi draft/pending menjadi approved + set tanggal_periode
            $komisiPending = Komisi::where('id_deposit', $deposit->id)
                ->whereIn('status', ['draft', 'pending'])
                ->get();

            foreach ($komisiPending as $k) {
                $old = $k->toArray();

                $k->update([
                    'status'          => 'approved',
                    'tanggal_periode' => $deposit->tanggal_approval,
                ]);

            //    Audit::log('komisi_log', 'komisi_id', $k->id, 'approve', $old, $k->fresh()->toArray(), 'Komisi approved bersama deposit');
            }
        });

        return redirect()->route('coa.deposits.index')
            ->with('success', 'Deposit berhasil disetujui. Persen komisi sudah difinalkan oleh COA.');
    }

    
    public function coaReject(Request $request, Deposit $deposit)
    {
        $this->ensureCoa();

        if ($deposit->status !== 'pending') {
            abort(403, 'Hanya deposito dengan status pending yang bisa ditolak.');
        }

        $request->validate([
            'catatan' => 'required|string',
        ]);

        $deposit->status           = 'rejected';
        $deposit->tanggal_approval = now();
        $deposit->catatan          = $request->input('catatan');
        $deposit->save();

        // opsional: tandai komisi draft/pending sebagai rejected
        $deposit->komisi()
            ->whereIn('status', ['draft', 'pending'])
            ->update(['status' => 'rejected']);

        return redirect()->route('coa.deposits.index')
            ->with('success', 'Deposit ditolak. Admin dapat memperbaiki data dan mengajukan ulang.');
    }


    
    /**Komisi */
    private function getSystemPersenKomisi(float $nominal): array
    {
        // aturan: < 200jt dan >= 200jt
        if ($nominal < 200_000_000) {
            return [
                'rm'  => 3.0, // persen
                'bdp' => 4.0,
            ];
        }

        return [
            'rm'  => 4.0,
            'bdp' => 5.0,
        ];
    }

        // helper untuk sinkron draft komisi RM & BDP
    private function syncDraftKomisi(Deposit $deposit, float $rmFinalPercent, float $bdpFinalPercent): void
    {
        \Log::info('SYNC KOMISI DIPANGGIL UNTUK DEPOSIT', [
            'deposit_id' => $deposit->id,
            'id_agent'   => $deposit->id_agent,
        ]);
        // pastikan relasi agent & atasan sudah ke-load
        $deposit->load('agent.atasan');
        // Hapus komisi draft lama untuk deposit ini
        Komisi::where('id_deposit', $deposit->id)
            ->whereIn('status', ['draft', 'pending'])
            ->delete();

        $agent = $deposit->agent; // agent utama di deposit
        if (!$agent) {
            return; // safety
        }

        $statusKomisi = $deposit->status?: 'draft'; // biasanya draft / pending
        
        // 1. Komisi RM (jika agent-nya RM)
        if ($agent->jabatan === 'RM') {
            $nominalKomisiRm = $deposit->nominal * $rmFinalPercent / 100;

            Komisi::create([
                'id_deposit'      => $deposit->id,
                'id_agent'        => $agent->id,
                'tanggal_periode' => now()->toDateString(),
                'nominal'         => $nominalKomisiRm,
                'persen_komisi'   => $rmFinalPercent,
                'status'          => $statusKomisi, // 'draft' / 'pending'
            ]);

            // cari BDP atasan untuk komisi BDP utama
            $bdp = $agent->atasan; // relasi atasan_id BDP yang sudah kita buat

            if ($bdp) {
                $nominalKomisiBdp = $deposit->nominal * $bdpFinalPercent / 100;

                Komisi::create([
                    'id_deposit'      => $deposit->id,
                    'id_agent'        => $bdp->id,
                    'tanggal_periode' => now()->toDateString(),
                    'nominal'         => $nominalKomisiBdp,
                    'persen_komisi'   => $bdpFinalPercent,
                    'status'          => $statusKomisi,
                ]);
            }
        }
        // 2. Komisi bila agent langsung BDP (tanpa RM)
        elseif ($agent->jabatan === 'BDP') {
            $nominalKomisiBdp = $deposit->nominal * $bdpFinalPercent / 100;

            Komisi::create([
                'id_deposit'      => $deposit->id,
                'id_agent'        => $agent->id,
                'tanggal_periode' => now()->toDateString(),
                'nominal'         => $nominalKomisiBdp,
                'persen_komisi'   => $bdpFinalPercent,
                'status'          => $statusKomisi,
            ]);

            // (kalau suatu saat ada skema RM langsung diisi juga, bisa ditambah di sini)
        }
    }
    /**
     * Pastikan komisi tambahan (BDP override 1% + referral BDP 0.5%) sudah ada sebagai DRAFT/PENDING,
     * agar bisa diedit COA di halaman detail sebelum approve.
     */
    private function ensureExtraKomisiDraft(Deposit $deposit): void
    {
        $agent = $deposit->agent;
        if (!$agent) return;

        $nominalDeposit = (float) $deposit->nominal;
        $statusDraft = in_array($deposit->status, ['pending','draft']) ? $deposit->status : 'pending';

        // 2) Referral BDP 0.5% jika deposit BDP_ref aktif dan BDP utama punya referred_by (BDP referral)
        if (!empty($deposit->BDP_ref)) {
            $bdpUtama = null;

            if ($agent->jabatan === 'RM' && $agent->atasan) {
                $bdpUtama = $agent->atasan;
            } elseif ($agent->jabatan === 'BDP') {
                $bdpUtama = $agent;
            }

            if ($bdpUtama && $bdpUtama->refferredBy) {
                $bdpReferral = $bdpUtama->refferredBy;

                $existsRef = Komisi::where('id_deposit', $deposit->id)
                    ->where('id_agent', $bdpReferral->id)
                    ->where('persen_komisi', 0.5)
                    ->whereIn('status', ['draft','pending'])
                    ->exists();

                if (!$existsRef) {
                    $persen = 0.5;
                    Komisi::create([
                        'id_deposit'      => $deposit->id,
                        'id_agent'        => $bdpReferral->id,
                        'tanggal_periode' => now()->toDateString(),
                        'nominal'         => $nominalDeposit * $persen / 100,
                        'persen_komisi'   => $persen,
                        'status'          => $statusDraft,
                        'tanggal_pembayaran' => null,
                    ]);
                }
            }
        }
    }



    /** helper: cek role admin */
    private function ensureAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang boleh melakukan aksi ini.');
        }
    }

    /** helper: cek role coa */
    private function ensureCoa()
    {
        if (!auth()->check() || auth()->user()->role !== 'coa') {
            abort(403, 'Hanya COA yang boleh melakukan aksi ini.');
        }
    }

    // LIST DEPOSIT - bisa dilihat semua user login
    public function index(Request $request)
    {
        $query = Deposit::with(['nasabah', 'agent']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deposits = $query->latest()->paginate(10);

        return view('deposits.index', compact('deposits'));
    }

    // FORM TAMBAH (admin saja)
    public function create()
    {
        $this->ensureAdmin();

        $nasabah = Nasabah::orderBy('nama')->get();
        $agents  = Agent::orderBy('nama')->get();

        return view('deposits.create', compact('nasabah', 'agents'));
    }

    // SIMPAN DEPOSIT BARU (admin)
    public function store(Request $request)
    {
        $this->ensureAdmin();

        $validated = $request->validate([
            'id_nasabah'        => 'required|exists:nasabah,id',
            'id_agent'          => 'required|exists:agents,id',
            'no_bilyet'         => 'required|string|max:50|unique:deposits,no_bilyet',
            'nominal'           => 'required|numeric|min:0',
            'tanggal_transaksi' => 'required|date',
            'tenor'             => 'required|integer|min:1',
            'tanggal_mulai'     => 'required|date',
            'tanggal_tempo'     => 'required|date|after_or_equal:tanggal_mulai',
            'catatan_admin'     => 'nullable|string',
            'BDP_ref'           => 'nullable|boolean',
            'bukti_transfer'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'dokumen_pendukung' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'submit_to_coa'     => 'nullable|boolean',

            'komisi_rm_persen_final'  => 'nullable|numeric|min:0',
            'komisi_bdp_persen_final' => 'nullable|numeric|min:0',
        ]);
        // status draft / pending
        $validated['status'] = $request->boolean('submit_to_coa') ? 'pending' : 'draft';

        // flag BDP_ref (checkbox)
        $validated['BDP_ref'] = $request->boolean('BDP_ref');

        // admin pembuat
        $validated['id_admin'] = auth()->id();

        // handle upload file
        if ($request->hasFile('bukti_transfer')) {
            $validated['bukti_transfer'] = $request->file('bukti_transfer')
                ->store('deposits/bukti_transfer', 'public');
        }

        if ($request->hasFile('dokumen_pendukung')) {
            $validated['dokumen_pendukung'] = $request->file('dokumen_pendukung')
                ->store('deposits/dokumen_pendukung', 'public');
        }

        

        $deposit = Deposit::create($validated);
        // hitung % sistem
        $system = $this->getSystemPersenKomisi((float) $deposit->nominal);
        $rmSystem  = $system['rm'];
        $bdpSystem = $system['bdp'];

        $selectedAgent = Agent::findOrFail($deposit->id_agent);
        // baca % final dari form (kalau kosong, pakai sistem)
        // RULE BARU: kalau agent BDP, komisi RM harus NULL (tidak dipakai)
        if ($selectedAgent->jabatan === 'BDP') {
            $rmFinal = null;
        } else {
            $rmFinal = $request->filled('komisi_rm_persen_final')
                ? (float) $request->input('komisi_rm_persen_final')
                : $rmSystem;
        }

        $bdpFinal = $request->filled('komisi_bdp_persen_final')
            ? (float) $request->input('komisi_bdp_persen_final')
            : $bdpSystem;
        // kalau final beda dengan sistem → sebaiknya wajib catatan / dokumen (bisa ditambah validasi nanti)
        
         $this->syncDraftKomisi($deposit, (float)($rmFinal ?? 0), $bdpFinal);

        return redirect()->route('deposits.index')
            ->with('success', 'Deposit berhasil disimpan.');
    }
    

    // FORM EDIT (admin) - HANYA jika belum approved
    public function edit(Deposit $deposit)
    {
        $this->ensureAdmin();

        if (in_array($deposit->status, ['approved'])) {
            abort(403, 'Deposit yang sudah disetujui tidak boleh diubah.');
        }

        $nasabah = Nasabah::orderBy('nama')->get();
        $agents  = Agent::orderBy('nama')->get();

        $komisiDraft = $deposit->komisi()
            ->whereIn('status', ['draft', 'pending'])
            ->with('agent')
            ->get();

        $rmFinalPercent  = null;
        $bdpFinalPercent = null;

        foreach ($komisiDraft as $k) {
            if ($k->agent && $k->agent->jabatan === 'RM') {
                $rmFinalPercent = $k->persen_komisi;
            }
            if ($k->agent && $k->agent->jabatan === 'BDP') {
                $bdpFinalPercent = $k->persen_komisi;
            }
        }

        return view('deposits.edit', compact(
            'deposit',
            'nasabah',
            'agents',
            'rmFinalPercent',
            'bdpFinalPercent',
        ));
    }

    // UPDATE (admin)
    public function update(Request $request, Deposit $deposit)
    {
        $this->ensureAdmin();

        if (in_array($deposit->status, ['approved'])) {
            abort(403, 'Deposit yang sudah disetujui tidak boleh diubah.');
        }
         if (!in_array($deposit->status, ['draft', 'rejected'])) {
            abort(403, 'Deposit yang sudah pending/approved tidak boleh diubah.');
        }

        $validated = $request->validate([
            'id_nasabah'        => 'required|exists:nasabah,id',
            'id_agent'          => 'required|exists:agents,id',
            'no_bilyet'         => 'required|string|max:50|unique:deposits,no_bilyet,' . $deposit->id,
            'nominal'           => 'required|numeric|min:0',
            'tanggal_transaksi' => 'required|date',
            'tenor'             => 'required|integer|min:1',
            'tanggal_mulai'     => 'required|date',
            'tanggal_tempo'     => 'required|date|after_or_equal:tanggal_mulai',
            'catatan_admin'     => 'nullable|string',
            'BDP_ref'           => 'nullable|boolean',
            'bukti_transfer'    => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'dokumen_pendukung' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'submit_to_coa'     => 'nullable|boolean',

            'komisi_rm_persen_final'  => 'nullable|numeric|min:0',
            'komisi_bdp_persen_final' => 'nullable|numeric|min:0',
        ]);
        
        $validated['status'] = $request->boolean('submit_to_coa') ? 'pending' : 'draft';
        $validated['BDP_ref'] = $request->boolean('BDP_ref');

        // kalau dari rejected lalu dikirim lagi ke COA → bersihkan catatan & tanggal_approval
        if ($deposit->status === 'rejected' && $validated['status'] === 'pending') {
            $validated['catatan']          = null;
            $validated['tanggal_approval'] = null;
        }

        if ($request->hasFile('bukti_transfer')) {
            $validated['bukti_transfer'] = $request->file('bukti_transfer')->store('deposits/bukti_transfer', 'public');
        }

        if ($request->hasFile('dokumen_pendukung')) {
            $validated['dokumen_pendukung'] = $request->file('dokumen_pendukung')
                ->store('deposits/dokumen_pendukung', 'public');
        }

        $persenSystem = $this->getSystemPersenKomisi((float) $validated['nominal']);
        $rmSystem  = $persenSystem['rm'];
        $bdpSystem = $persenSystem['bdp'];

        $rmFinal  = $request->filled('komisi_rm_persen_final')
            ? (float) $request->input('komisi_rm_persen_final')
            : $rmSystem;

        $bdpFinal = $request->filled('komisi_bdp_persen_final')
            ? (float) $request->input('komisi_bdp_persen_final')
            : $bdpSystem;

        $deposit->update($validated);
        $this->syncDraftKomisi($deposit, $rmFinal, $bdpFinal);

        return redirect()->route('deposits.index')
            ->with('success', 'Deposit berhasil diperbarui sebagai ' . $validated['status'] . '.');
        // kalau status sekarang rejected, setelah edit admin bisa pilih:
        // - simpan draft lagi
        // - atau langsung kirim pending lagi
        
        // if ($deposit->status === 'rejected') {
        //     $data['status'] = $request->boolean('submit_to_coa') ? 'pending' : 'draft';
        //     // kalau kirim lagi ke COA, bersihkan catatan & tanggal_approval lama
        //     if ($data['status'] === 'pending') {
        //         $data['catatan']          = null;
        //         $data['tanggal_approval'] = null;
        //     }
        // } else {
        //     // status draft atau pending
        //     $data['status'] = $request->boolean('submit_to_coa') ? 'pending' : 'draft';
        // }

        // $deposit->update($data);

        // return redirect()->route('deposits.index')
        //     ->with('success', 'Deposit berhasil diperbarui sebagai ' . $deposit->status . '.');
    }

    // HAPUS (opsional, admin)
    public function destroy(Deposit $deposit)
    {
        $this->ensureAdmin();

        if ($deposit->status === 'approved') {
            abort(403, 'Deposit yang sudah disetujui tidak boleh dihapus.');
        }

        $deposit->delete();

        return redirect()->route('deposits.index')
            ->with('success', 'Deposit berhasil dihapus.');
    }

    /* =======================
     * AREA APPROVAL UNTUK COA
     * ======================= */

    // list khusus pending (COA)
    public function pendingForCoa()
    {
        $this->ensureCoa();

        $deposits = Deposit::with(['nasabah', 'agent'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(10);

        return view('deposits.pending', compact('deposits'));
    }

    // detail untuk COA
    public function showForCoa(Deposit $deposit)
    {
        $this->ensureCoa();

        if ($deposit->status !== 'pending') {
            abort(403, 'Deposit ini tidak dalam status pending.');
        }

        return view('deposits.show_for_coa', compact('deposit'));
    }

    // APPROVE oleh COA
    public function approve(Deposit $deposit)
    {
        $this->ensureCoa();

        if ($deposit->status !== 'pending') {
            abort(403, 'Hanya deposit pending yang bisa disetujui.');
        }

        $deposit->update([
            'status'           => 'approved',
            'catatan'          => null, // catatan reject dibersihkan
            'tanggal_approval' => now()->toDateString(),
        ]);

        return redirect()->route('deposits.pending')
            ->with('success', 'Deposit berhasil disetujui.');
    }

    // REJECT oleh COA (catatan wajib)
    public function reject(Request $request, Deposit $deposit)
    {
        $this->ensureCoa();

        if ($deposit->status !== 'pending') {
            abort(403, 'Hanya deposit pending yang bisa ditolak.');
        }

        $data = $request->validate([
            'catatan' => 'required|string',
        ]);

        $deposit->update([
            'status'           => 'rejected',
            'catatan'          => $data['catatan'], // alasan penolakan
            'tanggal_approval' => now()->toDateString(),
        ]);

        return redirect()->route('deposits.pending')
            ->with('success', 'Deposit ditolak, admin dapat memperbaiki data.');
    }
}
