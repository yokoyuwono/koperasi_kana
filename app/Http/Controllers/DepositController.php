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
        // FIX: calling with correct arguments
        $persenSystem = $this->getSystemPersenKomisi(
            $deposit->agent->jabatan,
            (float) $deposit->nominal,
            (int) $deposit->tenor
        );
        $rmSystem     = $persenSystem['rm'];
        $bdpSystem    = $persenSystem['bdp'];
        $bdpRefSystem = $persenSystem['bdp_ref'];
        $refMode      = $persenSystem['ref_mode'];


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

        // Determine current/draft values to show in input fields
        $jabatanPengaju = $deposit->agent->jabatan;

        $rmPercentCurrent     = null;
        $bdpPercentCurrent    = null;
        $bdpRefPercentCurrent = null;

        foreach ($komisi as $k) {
            $j = strtoupper($k->agent->jabatan ?? '');
            
            // Check logic: if agent is RM -> it is RM commission
            // if agent is BDP -> it is BDP commission (unless it's referral?)
            // We need a way to distinguish main BDP vs referral BDP if both are BDP.
            // Usually referral has 0.5% or matching the ref system rate, 
            // but safer to check if it's the 'atasan' or 'referredBy'.
            
            // Simpler approach based on controller store/sync logic:
            // RM Commission is assigned to the agent (if RM).
            // BDP Commission is assigned to the agent (if BDP) OR agent->atasan (if agent is RM).
            // Referral Commission is assigned to agent->atasan->referredBy (if RM) or agent->referredBy (if BDP).

            if ($j === 'RM' && $k->id_agent === $deposit->id_agent) {
                // This is the Pengaju (RM)
                $rmPercentCurrent = $k->persen_komisi;
            } elseif ($j === 'BDP') {
                // Could be the main BDP (atasan of RM or the Pengaju itself) OR the Referral BDP.
                // We assume the higher alloc usually main BDP, small one referral. 
                // BUT better: check if this agent is the Refferal target?
                // For now, let's use the assumption that usually we have max 2 BDP records.
                // 1. Main BDP
                // 2. Ref BDP
                
                // Let's check against logical IDs if possible, but $deposit->agent might be incomplete if we don't traverse deep.
                // Simplest heuristic: 
                // If it matches $bdpRefSystem (approx) or is small? No that's risky.

                // Let's try to match against expected agents if we can?
                // $agent = $deposit->agent;
                // $mainBdpId = ($agent->jabatan === 'RM') ? $agent->atasan?->id : $agent->id;
                
                // If this k->id_agent == $mainBdpId -> BDP Percent
                // Else -> BDP Ref Percent
                
                // We need to re-derive who is Main BDP
                $mainAgent = $deposit->agent;
                $mainBdpId = ($mainAgent->jabatan === 'RM') ? ($mainAgent->atasan->id ?? null) : $mainAgent->id;

                if ($k->id_agent == $mainBdpId) {
                    $bdpPercentCurrent = $k->persen_komisi;
                } else {
                    $bdpRefPercentCurrent = $k->persen_komisi;
                }
            }
        }

        // BDP Ref Enabled? 
        // 1. If $deposit->BDP_ref == 1
        // 2. OR if we found a referral commission
        $bdpRefEnabled = ($deposit->BDP_ref == 1) || ($bdpRefPercentCurrent !== null && $bdpRefPercentCurrent > 0);

        return view('coa.deposits.show', [
            'deposit'              => $deposit,
            'rmSystem'            => $rmSystem,
            'bdpSystem'           => $bdpSystem,
            'bdpRefSystem'        => $bdpRefSystem,
            'refMode'             => $refMode,
            
            'jabatanPengaju'      => $jabatanPengaju,
            'rmPercentCurrent'    => $rmPercentCurrent,
            'bdpPercentCurrent'   => $bdpPercentCurrent,
            'bdpRefPercentCurrent'=> $bdpRefPercentCurrent,
            'bdpRefEnabled'       => $bdpRefEnabled,

            'komisiList'          => $komisi,
        ]);
    }

    public function coaApprove(Request $request, Deposit $deposit)
    {
        $this->ensureCoa();

        if ($deposit->status !== 'pending') {
            abort(403, 'Hanya deposito dengan status pending yang bisa disetujui.');
        }

        $data = $request->validate([
            'rm_percent'      => 'nullable|numeric|min:0|max:100',
            'bdp_percent'     => 'nullable|numeric|min:0|max:100',
            'bdp_ref_enabled' => 'nullable', // checkbox sends '1' or nothing
            'bdp_ref_percent' => 'nullable|numeric|min:0|max:100',
            'catatan'         => 'nullable|string',
        ]);

        $bdpRefEnabled = $request->boolean('bdp_ref_enabled');

        DB::transaction(function () use ($deposit, $data, $bdpRefEnabled) {

            // 1. Update Deposit BDP_ref flag (karena bisa diubah COA)
            $oldDepositRef = $deposit->BDP_ref;
            $deposit->BDP_ref = $bdpRefEnabled ? 1 : 0;
            $deposit->save();

            // 2. Ensure Referral Commission Logic
            // Jika enabled, pastikan row komisi ada.
            if ($bdpRefEnabled) {
                $this->ensureExtraKomisiDraft($deposit);
            }
            
            // 3. Update Persen & Nominal masing-masing Komisi
            $komisiList = Komisi::where('id_deposit', $deposit->id)
                    ->whereIn('status', ['draft', 'pending'])
                    ->get();
                    
            $nominalDeposit = (float) $deposit->nominal;
            $deposit->load('agent.atasan');
            $mainAgent = $deposit->agent;
            
            // Tentukan Main BDP ID (Atasan jika RM, atau Agent itu sendiri jika BDP)
            $mainBdpId = ($mainAgent->jabatan === 'RM') 
                ? ($mainAgent->atasan->id ?? null) 
                : $mainAgent->id;

            foreach ($komisiList as $k) {
                $targetPercent = 0;
                $updateRequired = false;
                $isRef = false;

                // Identify Role Logic
                if ($mainAgent->jabatan === 'RM' && $k->id_agent == $deposit->id_agent) {
                    // Ini Komisi Pengaju (RM)
                    $targetPercent = $data['rm_percent'] ?? 0;
                    $updateRequired = true;
                } elseif ($k->id_agent == $mainBdpId) {
                    // Ini Komisi BDP Utama
                    $targetPercent = $data['bdp_percent'] ?? 0;
                    $updateRequired = true;
                } else {
                    // Sisanya dianggap Komisi Referral (atau overriding lain)
                    $isRef = true;
                    if (!$bdpRefEnabled) {
                        // Jika ref disabled, hapus komisi ini
                        $k->delete();
                        continue; // skip update
                    }
                    $targetPercent = $data['bdp_ref_percent'] ?? 0;
                    $updateRequired = true;
                }

                if ($updateRequired) {
                    $old = $k->toArray();
                    $persen = (float)$targetPercent;
                    $nominal = $nominalDeposit * $persen / 100;

                    $k->update([
                        'persen_komisi' => $persen,
                        'nominal'       => $nominal,
                    ]);

                    Audit::komisi($k->id, 'update', $old, $k->fresh()->toArray(), 'Penyesuaian komisi oleh COA');
                }
            }

            // 4. Approve Deposit
            $oldDeposit = $deposit->toArray();

            $deposit->update([
                'status'           => 'approved',
                'tanggal_approval' => now()->toDateString(),
                'catatan'          => $data['catatan'] ?? null,
            ]);

            Audit::deposit($deposit->id, 'approve', $oldDeposit, $deposit->fresh()->toArray(), 'Deposit disetujui COA');

            // 5. Set semua komisi draft/pending menjadi approved + set tanggal_periode (jika belum)
            // Retrieve again to avoid working on deleted ones
            $komisiPending = Komisi::where('id_deposit', $deposit->id)
                ->whereIn('status', ['draft', 'pending'])
                ->get();

            foreach ($komisiPending as $k) {
                $old = $k->toArray();

                $k->update([
                    'status'          => 'approved',
                    'tanggal_periode' => $deposit->tanggal_approval,
                ]);

                Audit::komisi($k->id, 'approve', $old, $k->fresh()->toArray(), 'Komisi approved bersama deposit');
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

        $oldData = $deposit->toArray();
        $deposit->status           = 'rejected';
        $deposit->tanggal_approval = now();
        $deposit->catatan          = $request->input('catatan');
        $deposit->save();

        Audit::deposit(
            $deposit->id,
            'reject',
            $oldData,
            $deposit->fresh()->toArray(),
            'Deposit ditolak COA. Alasan: ' . $request->input('catatan')
        );

        // opsional: tandai komisi draft/pending sebagai rejected
        $deposit->komisi()
            ->whereIn('status', ['draft', 'pending'])
            ->update(['status' => 'rejected']);

        return redirect()->route('coa.deposits.index')
            ->with('success', 'Deposit ditolak. Admin dapat memperbaiki data dan mengajukan ulang.');
    }


    
    /**Komisi */
    private function getSystemPersenKomisi(string $jabatan, float $nominal, int $tenor): array
    {
        $jabatan = strtoupper($jabatan);

        $rmRow  = $this->findKomisiDefault($jabatan, 'RM', $nominal);
        $bdpRow = $this->findKomisiDefault($jabatan, 'BDP', $nominal);
        $refRow = $this->findKomisiDefault($jabatan, 'BDP_REF', $nominal);

        $annualRm  = $rmRow ? (float) $rmRow->annual_rate : 0.0;
        $annualBdp = $bdpRow ? (float) $bdpRow->annual_rate : 0.0;
        $annualRef = $refRow ? (float) $refRow->annual_rate : 0.0;

        $refMode = $refRow ? ($refRow->ref_mode ?? 'none') : 'none';

        return [
            'rm'       => $annualRm > 0 ? $this->prorataPercent($annualRm, $tenor) : null,
            'bdp'      => $annualBdp > 0 ? $this->prorataPercent($annualBdp, $tenor) : null,
            'bdp_ref'  => $annualRef > 0 ? $this->prorataPercent($annualRef, $tenor) : null,
            'ref_mode' => $refMode, // none|optional|mandatory
        ];
    }

    private function prorataPercent(float $annualRate, int $tenor): float
    {
        return round(($annualRate / 12) * $tenor, 4);
    }

    private function findKomisiDefault(string $pengajuJabatan, string $jenisKomisi, float $nominal): ?object
    {
        $pengajuJabatan = strtoupper($pengajuJabatan);
        $jenisKomisi    = strtoupper($jenisKomisi);

        return DB::table('komisi_defaults')
            ->where('aktif', 1)
            ->where('pengaju_jabatan', $pengajuJabatan)
            ->where('jenis_komisi', $jenisKomisi)
            ->where('nominal_min', '<=', (int) $nominal)
            ->where(function ($q) use ($nominal) {
                $q->whereNull('nominal_max')
                  ->orWhere('nominal_max', '>=', (int) $nominal);
            })
            ->orderByDesc('nominal_min')
            ->first();
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

        $statusKomisi = 'pending'; // biasanya draft / pending
        
        // 1. Komisi RM (jika agent-nya RM)
        if ($agent->jabatan === 'RM') {
            $nominalKomisiRm = $deposit->nominal * $rmFinalPercent / 100;

            $kRm = Komisi::create([
                'id_deposit'      => $deposit->id,
                'id_agent'        => $agent->id,
                'tanggal_periode' => now()->toDateString(),
                'nominal'         => $nominalKomisiRm,
                'persen_komisi'   => $rmFinalPercent,
                'status'          => $statusKomisi, // 'draft' / 'pending'
            ]);

            Audit::komisi($kRm->id, 'create', null, $kRm->toArray(), 'Komisi RM generated (Deposit ' . $deposit->status . ')');

            // cari BDP atasan untuk komisi BDP utama
            $bdp = $agent->atasan; // relasi atasan_id BDP yang sudah kita buat

            if ($bdp) {
                $nominalKomisiBdp = $deposit->nominal * $bdpFinalPercent / 100;

                $kBdp = Komisi::create([
                    'id_deposit'      => $deposit->id,
                    'id_agent'        => $bdp->id,
                    'tanggal_periode' => now()->toDateString(),
                    'nominal'         => $nominalKomisiBdp,
                    'persen_komisi'   => $bdpFinalPercent,
                    'status'          => $statusKomisi,
                ]);

                Audit::komisi($kBdp->id, 'create', null, $kBdp->toArray(), 'Komisi BDP (via Atasan) generated (Deposit ' . $deposit->status . ')');
            }
        }
        // 2. Komisi bila agent langsung BDP (tanpa RM)
        elseif ($agent->jabatan === 'BDP') {
            $nominalKomisiBdp = $deposit->nominal * $bdpFinalPercent / 100;

            $kBdp = Komisi::create([
                'id_deposit'      => $deposit->id,
                'id_agent'        => $agent->id,
                'tanggal_periode' => now()->toDateString(),
                'nominal'         => $nominalKomisiBdp,
                'persen_komisi'   => $bdpFinalPercent,
                'status'          => $statusKomisi,
            ]);

            Audit::komisi($kBdp->id, 'create', null, $kBdp->toArray(), 'Komisi BDP generated (Deposit ' . $deposit->status . ')');

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
                    $kRef = Komisi::create([
                        'id_deposit'      => $deposit->id,
                        'id_agent'        => $bdpReferral->id,
                        'tanggal_periode' => now()->toDateString(),
                        'nominal'         => $nominalDeposit * $persen / 100,
                        'persen_komisi'   => $persen,
                        'status'          => $statusDraft,
                        'tanggal_pembayaran' => null,
                    ]);

                    Audit::komisi($kRef->id, 'create', null, $kRef->toArray(), 'Komisi Referral generated (Deposit ' . $deposit->status . ')');
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

                $komisiDefaults = DB::table('komisi_defaults')->where('aktif', 1)->get();

        return view('deposits.create', compact('nasabah', 'agents', 'komisiDefaults'));
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
                $agent = Agent::findOrFail($deposit->id_agent);
        $system = $this->getSystemPersenKomisi($agent->jabatan, (float) $deposit->nominal, (int) $deposit->tenor);

        $rmSystem  = $system['rm'];
        $bdpSystem = $system['bdp'];
        $refSystem = $system['bdp_ref'];
        $refMode   = $system['ref_mode']; // none|optional|mandatory

        // final percent (editable)
        $rmFinal = $request->filled('komisi_rm_persen_final')
            ? (float) $request->input('komisi_rm_persen_final')
            : ($rmSystem ?? 0);

        $bdpFinal = $request->filled('komisi_bdp_persen_final')
            ? (float) $request->input('komisi_bdp_persen_final')
            : ($bdpSystem ?? 0);

        // enforce RM eligibility
        if ($agent->jabatan === 'BDP') {
            $rmFinal = null;
        }

        // BDP Ref: enforce by ref_mode
        $bdpRefEnabled = false;
        if ($refMode === 'mandatory') {
            $bdpRefEnabled = true;
        } elseif ($refMode === 'optional') {
            $bdpRefEnabled = $request->boolean('BDP_ref');
        }

        $deposit->BDP_ref = $bdpRefEnabled ? 1 : 0;
        $deposit->save();

        $bdpRefFinal = null;
        if ($bdpRefEnabled) {
            $bdpRefFinal = $request->filled('komisi_bdp_ref_persen_final')
                ? (float) $request->input('komisi_bdp_ref_persen_final')
                : ($refSystem ?? 0);
        }

        $this->syncDraftKomisi($deposit, (float) ($rmFinal ?? 0), (float) ($bdpFinal ?? 0));

        Audit::deposit(
            $deposit->id,
            'create',
            null,
            $deposit->fresh()->toArray(),
            'Deposit baru dibuat oleh Admin'
        );

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

                $komisiDefaults = DB::table('komisi_defaults')->where('aktif', 1)->get();
        $bdpRefPercent = null; // TODO: isi dari komisi BDP Ref jika kamu punya penandanya

        return view('deposits.edit', compact(
            'deposit',
            'nasabah',
            'agents',
            'rmFinalPercent',
            'bdpFinalPercent',
            'bdpRefPercent',
            'komisiDefaults',
        ));
    }

    // UPDATE (admin)
    public function update(Request $request, Deposit $deposit)
    {
        $this->ensureAdmin();

        if (in_array($deposit->status, ['approved'])) {
            abort(403, 'Deposit yang sudah disetujui tidak boleh diubah.');
        }
        //  if (!in_array($deposit->status, ['draft', 'rejected'])) {
        //     abort(403, 'Deposit yang sudah pending/approved tidak boleh diubah.');
        // }

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

                $agent = Agent::findOrFail($validated['id_agent']);
        $persenSystem = $this->getSystemPersenKomisi($agent->jabatan, (float) $validated['nominal'], (int) $validated['tenor']);

        $rmSystem  = $persenSystem['rm'];
        $bdpSystem = $persenSystem['bdp'];
        $refSystem = $persenSystem['bdp_ref'];
        $refMode   = $persenSystem['ref_mode'];

        $rmFinal = $request->filled('komisi_rm_persen_final')
            ? (float) $request->input('komisi_rm_persen_final')
            : ($rmSystem ?? 0);

        $bdpFinal = $request->filled('komisi_bdp_persen_final')
            ? (float) $request->input('komisi_bdp_persen_final')
            : ($bdpSystem ?? 0);

        if ($agent->jabatan === 'BDP') {
            $rmFinal = null;
        }

        $bdpRefEnabled = false;
        if ($refMode === 'mandatory') {
            $bdpRefEnabled = true;
        } elseif ($refMode === 'optional') {
            $bdpRefEnabled = $request->boolean('BDP_ref');
        }
        $validated['BDP_ref'] = $bdpRefEnabled ? 1 : 0;

        $bdpRefFinal = null;
        if ($bdpRefEnabled) {
            $bdpRefFinal = $request->filled('komisi_bdp_ref_persen_final')
                ? (float) $request->input('komisi_bdp_ref_persen_final')
                : ($refSystem ?? 0);
        }

        $oldData = $deposit->toArray();

        $this->syncDraftKomisi($deposit, (float) ($rmFinal ?? 0), (float) ($bdpFinal ?? 0));
        $deposit->update($validated);

        Audit::deposit(
            $deposit->id,
            'update',
            $oldData,
            $deposit->fresh()->toArray(),
            'Deposit diperbarui oleh Admin'
        );

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
