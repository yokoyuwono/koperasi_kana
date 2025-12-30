<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Support\Audit;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct()
    {
        // ADMIN CRUD (resource)
        $this->middleware('role:admin')->only([
            'index', 'show', 'create', 'store', 'edit', 'update', 'destroy',
        ]);

        // COA Read-only
        $this->middleware('role:coa')->only([
            'coaIndex', 'coaShow',
        ]);
    }


    private function ensureAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang boleh melakukan aksi ini.');
        }
    }

    private function ensureCoa(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'coa') {
            abort(403, 'Hanya COA yang boleh mengakses halaman ini.');
        }
    }

    /** LIST AGENT UNTUK COA (READ ONLY) */
    public function coaIndex()
    {
        // $this->ensureCoa();

        $agents = Agent::orderBy('nama')->paginate(20);

        // pakai view yang sama dengan admin, nanti tombol aksinya dibedakan di Blade
        return view('agents.index', compact('agents'));
    }

    /** DETAIL AGENT UNTUK COA (READ ONLY) */
    public function coaShow(Agent $agent)
    {
        // $this->ensureCoa();

        // kalau perlu relasi lain, load di sini
        $agent->load(['atasan', 'refferredBy']);
        $bdpAgents = Agent::where('jabatan', 'BDP')
            ->orderBy('nama')
            ->get();

        return view('agents.edit', [
            'agent'     => $agent,
            'bdpAgents' => $bdpAgents, // atau apapun nama variabel yg dipakai di edit.blade.php
            'refferralAgents' => $bdpAgents,
            'readOnly'  => true,       // flag untuk mengunci form
        ]);

        // return view('agents.show', compact('agent'));
    }


    public function index()
    {
        $agents = Agent::latest()->paginate(10);
        return view('agents.index', compact('agents'));
    }

    public function create()
    {
        $this->ensureAdmin();
        
        // $bdpAgents = Agent::where('jabatan', 'BDP')->orderBy('nama')->get();
        // return view('agents.create', compact('bdpAgents'));

        $refferralAgents = Agent::where('jabatan', 'BDP')
            ->orderBy('nama')
            ->get();

        return view('agents.create', compact('refferralAgents'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'kode_agent'             => 'required|string|max:50|unique:agents,kode_agent',
            'nama'                   => 'required|string|max:150',
            'jabatan'                => 'required|in:BDP,RM',
            'tempat_lahir'           => 'nullable|string|max:100',
            'tanggal_lahir'          => 'nullable|date',
            'jenis_kelamin'          => 'nullable|string|max:10',
            'NIK'                    => 'nullable|string|max:50',
            'alamat_KTP'             => 'nullable|string',
            'telepon'                => 'nullable|string|max:50',
            'alamat_sekarang'        => 'nullable|string',
            'rekening_bank'          => 'nullable|string|max:100',
            'email'                  => 'nullable|email|max:150',
            'tanggal_daftar'         => 'nullable|date',
            'refferred_by_agent_id'  => 'nullable|exists:agents,id',
            'atasan_id'              => 'nullable|exists:agents,id',
        ]);

        // logika aturan bisnis:
        // - BDP: boleh punya Agent Referal, tidak punya atasan
        // - RM : harus punya atasan BDP, tidak perlu Agent Referal
        if ($data['jabatan'] === 'BDP') {
            $data['atasan_id'] = null; // tidak punya atasan
        } else { // RM
            if (empty($data['atasan_id'])) {
                return back()
                    ->withErrors(['atasan_id' => 'Atasan wajib dipilih untuk jabatan RM.'])
                    ->withInput();
            }
            // kalau mau strict: referral hanya untuk BDP, maka kosongkan
            $data['refferred_by_agent_id'] = null;
        }
            
        $data['id_admin'] = auth()->id();

        if (empty($data['tanggal_daftar'])) {
            $data['tanggal_daftar'] = now()->toDateString();
        }

        // Agent::create($data);

        $agent = Agent::create($data);
        Audit::agent(
            $agent->id,
            'create',
            null,
            $agent->fresh()->toArray(),
            'Admin membuat agent',
            auth()->id()
        );
        return redirect()->route('agents.index')
            ->with('success', 'Agen berhasil ditambahkan.');
    }

    public function edit(Agent $agent)
    {
        $this->ensureAdmin();

        $refferralAgents = Agent::where('jabatan', 'BDP')
            ->where('id', '!=', $agent->id)
            ->orderBy('nama')
            ->get();

        return view('agents.edit', compact('agent', 'refferralAgents'));

        $agents = Agent::orderBy('nama')->get();
        $bdpAgents = Agent::where('jabatan', 'BDP')->orderBy('nama')->get();

        return view('agents.edit', [
            'agent'           => $agent,
            'agents'          => $agents,
            'bdpAgents'       => $bdpAgents,
            'refferalAgents'  => $bdpAgents, // <— tambahkan ini
        ]);
    }

    public function update(Request $request, Agent $agent)
    {
        $this->ensureAdmin();

         $data = $request->validate([
        'kode_agent'             => 'required|string|max:50|unique:agents,kode_agent,' . $agent->id,
        'nama'                   => 'required|string|max:150',
        'jabatan'                => 'required|in:BDP,RM',
        'tempat_lahir'           => 'nullable|string|max:100',
        'tanggal_lahir'          => 'nullable|date',
        'jenis_kelamin'          => 'nullable|string|max:10',
        'NIK'                    => 'nullable|string|max:50',
        'alamat_KTP'             => 'nullable|string',
        'telepon'                => 'nullable|string|max:50',
        'alamat_sekarang'        => 'nullable|string',
        'rekening_bank'          => 'nullable|string|max:100',
        'email'                  => 'nullable|email|max:150',
        'tanggal_daftar'         => 'nullable|date',
        'refferred_by_agent_id'  => 'nullable|exists:agents,id',
        'atasan_id'              => 'nullable|exists:agents,id',
        ]);

        if (empty($data['tanggal_daftar'])) {
            $data['tanggal_daftar'] = $agent->tanggal_daftar ?? now()->toDateString();
        }

        if ($data['jabatan'] === 'BDP') {
        $data['atasan_id'] = null;
        } else { // RM
            if (empty($data['atasan_id'])) {
                return back()
                    ->withErrors(['atasan_id' => 'Atasan wajib dipilih untuk jabatan RM.'])
                    ->withInput();
            }
            $data['refferred_by_agent_id'] = null;
        }
        $oldAgent = $agent->toArray();
        $agent->update($data);
        Audit::agent(
            $agent->id,
            'update',
            $oldAgent,
            $agent->fresh()->toArray(),
            'Admin mengubah agent',
            auth()->id()
        );

        return redirect()->route('agents.index')
            ->with('success', 'Agen berhasil diperbarui.');
    }

    // public function destroy(Agent $agent)
    // {
    //     $this->ensureAdmin();

    //     $agent->delete();

    //     return redirect()->route('agents.index')
    //         ->with('success', 'Agen berhasil dihapus.');
    // }
}
