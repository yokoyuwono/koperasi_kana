<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use App\Models\Agent;
use App\Support\Audit;
use Illuminate\Http\Request;


class NasabahController extends Controller
{
    /** kecil: helper untuk batasi ke admin saja */
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
    
    /** LIST NASABAH UNTUK COA */
    public function coaIndex()
    {
        $this->ensureCoa();

        $nasabah = Nasabah::orderBy('nama')->paginate(20);

        return view('nasabah.index', compact('nasabah'));
    }

    /** DETAIL NASABAH UNTUK COA */
    public function coaShow(Nasabah $nasabah)
    {
        $this->ensureCoa();

        // kalau ada relasi agent, bisa load:
        $nasabah->load(['agent']);
          // sama seperti edit(): ambil semua agent untuk dropdown
    $agents = Agent::orderBy('nama')->get();
        // gunakan view edit tapi dalam mode READ ONLY
            return view('nasabah.edit', [
                'nasabah' => $nasabah,
                'agents'   => $agents,
                'readOnly' => true, // tambahkan flag
            ]);
        // return view('nasabah.show', compact('nasabah'));
    }


    public function index()
    {
        $nasabah = Nasabah::with('agent')
            ->latest()
            ->paginate(10);

        return view('nasabah.index', compact('nasabah'));
    }

    public function create()
    {
        $this->ensureAdmin();

        $agents = Agent::orderBy('nama')->get();

        return view('nasabah.create', compact('agents'));
    }

    public function store(Request $request)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'id_agent'           => 'required|exists:agents,id',
            'nama'               => 'required|string|max:150',
            'tempat_lahir'       => 'nullable|string|max:100',
            'tanggal_lahir'      => 'nullable|date',
            'jenis_kelamin'      => 'nullable|string|max:10',
            'NIK'                => 'nullable|string|max:50',
            'NPWP'               => 'nullable|string|max:50',
            'alamat_KTP'         => 'nullable|string',
            'telepon'            => 'nullable|string|max:50',
            'alamat_sekarang'    => 'nullable|string',
            'nama_ibu'           => 'nullable|string|max:150',
            'agama'              => 'nullable|string|max:50',
            'nama_wali'          => 'nullable|string|max:150',
            'NIK_wali'           => 'nullable|string|max:50',
            'email'              => 'nullable|email|max:150',
            'jenis_pekerjaan'    => 'nullable|string|max:100',
            'bidang_usaha'       => 'nullable|string|max:100',
            'alamat_perusahaan'  => 'nullable|string',
            'penghasilan'        => 'nullable|numeric',
            'sumber_dana'        => 'nullable|string|max:100',
            'aktivitas_transaksi'=> 'nullable|string',
            'rekening_bank'      => 'nullable|string|max:100',
            'nomor_rekening'     => 'nullable|string|max:50',
            'nama_rekening'      => 'nullable|string|max:150',
            'tujuan_rekening'    => 'nullable|string|max:150',
            'tanggal_daftar'     => 'nullable|date',
        ]);

        $data['id_admin'] = auth()->id(); // admin yang input

        if (empty($data['tanggal_daftar'])) {
            $data['tanggal_daftar'] = now()->toDateString();
        }

        $nasabah = Nasabah::create($data);
        Audit::nasabah(
            $nasabah->id,
            'create',
            null,
            $nasabah->fresh()->toArray(),
            'Admin membuat nasabah',
            auth()->id()
        );
        return redirect()->route('nasabah.index')
            ->with('success', 'Nasabah berhasil ditambahkan.');
    }

    public function edit(Nasabah $nasabah)
    {
        $this->ensureAdmin();

        $agents = Agent::orderBy('nama')->get();

        return view('nasabah.edit', compact('nasabah', 'agents'));
    }

    public function update(Request $request, Nasabah $nasabah)
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'id_agent'           => 'required|exists:agents,id',
            'nama'               => 'required|string|max:150',
            'tempat_lahir'       => 'nullable|string|max:100',
            'tanggal_lahir'      => 'nullable|date',
            'jenis_kelamin'      => 'nullable|string|max:10',
            'NIK'                => 'nullable|string|max:50',
            'NPWP'               => 'nullable|string|max:50',
            'alamat_KTP'         => 'nullable|string',
            'telepon'            => 'nullable|string|max:50',
            'alamat_sekarang'    => 'nullable|string',
            'nama_ibu'           => 'nullable|string|max:150',
            'agama'              => 'nullable|string|max:50',
            'nama_wali'          => 'nullable|string|max:150',
            'NIK_wali'           => 'nullable|string|max:50',
            'email'              => 'nullable|email|max:150',
            'jenis_pekerjaan'    => 'nullable|string|max:100',
            'bidang_usaha'       => 'nullable|string|max:100',
            'alamat_perusahaan'  => 'nullable|string',
            'penghasilan'        => 'nullable|numeric',
            'sumber_dana'        => 'nullable|string|max:100',
            'aktivitas_transaksi'=> 'nullable|string',
            'rekening_bank'      => 'nullable|string|max:100',
            'nomor_rekening'     => 'nullable|string|max:50',
            'nama_rekening'      => 'nullable|string|max:150',
            'tujuan_rekening'    => 'nullable|string|max:150',
            'tanggal_daftar'     => 'nullable|date',
        ]);

        if (empty($data['tanggal_daftar'])) {
            $data['tanggal_daftar'] = $nasabah->tanggal_daftar ?? now()->toDateString();
        }
        $oldNasabah = $nasabah->toArray();
        $nasabah->update($data);
        
        Audit::nasabah(
            $nasabah->id,
            'update',
            $oldNasabah,
            $nasabah->fresh()->toArray(),
            'Admin mengubah nasabah',
            auth()->id()
        );
        return redirect()->route('nasabah.index')
            ->with('success', 'Nasabah berhasil diperbarui.');
    }

    public function destroy(Nasabah $nasabah)
    {
        $this->ensureAdmin();

        $nasabah->delete();

        return redirect()->route('nasabah.index')
            ->with('success', 'Nasabah berhasil dihapus.');
    }
}
