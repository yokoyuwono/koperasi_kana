<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    private function ensureSuperAdmin(): void
    {
        if (!auth()->check() || auth()->user()->role !== 'superadmin') {
            abort(403, 'Akses ditolak. Hanya Superadmin yang boleh mengakses.');
        }
    }

    public function index(Request $request)
    {
        $this->ensureSuperAdmin();

        $q = trim((string) $request->query('q', ''));

        $users = User::query()
            ->when($q, function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('users.index', compact('users', 'q'));
    }

    public function create()
    {
        $this->ensureSuperAdmin();

        $agents = Agent::orderBy('nama')->get(['id','kode_agent','nama','jabatan']);
        return view('users.create', compact('agents'));
    }

    public function store(Request $request)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'nama'     => 'required|string|max:120',
            'email'    => 'required|email|max:120|unique:users,email',
            'role'     => 'required|in:superadmin,admin,coa,rm,bdp',
            'id_agent' => 'nullable|exists:agents,id',

            // admin set password awal
            'password' => 'required|string|min:6',
        ]);

        // rules: role rm/bdp wajib punya agent, admin/coa tidak perlu
        if (in_array($data['role'], ['rm','bdp'], true) && empty($data['id_agent'])) {
            return back()->withErrors(['id_agent' => 'Role RM/BDP wajib terhubung ke Agent.'])->withInput();
        }
        if (in_array($data['role'], ['superadmin','admin','coa'], true)) {
            $data['id_agent'] = null;
        }

        User::create([
            'nama'     => $data['nama'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'id_agent' => $data['id_agent'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $this->ensureSuperAdmin();

        $agents = Agent::orderBy('nama')->get(['id','kode_agent','nama','jabatan']);
        return view('users.edit', compact('user', 'agents'));
    }

    public function update(Request $request, User $user)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'nama'     => 'required|string|max:120',
            'email'    => 'required|email|max:120|unique:users,email,' . $user->id,
            // Allow superadmin to be assigned, and admin, coa, rm, bdp
            'role'     => 'required|in:superadmin,admin,coa,rm,bdp',
            'id_agent' => 'nullable|exists:agents,id',
        ]);

        if (in_array($data['role'], ['rm','bdp'], true) && empty($data['id_agent'])) {
            return back()->withErrors(['id_agent' => 'Role RM/BDP wajib terhubung ke Agent.'])->withInput();
        }
        if (in_array($data['role'], ['superadmin','admin','coa'], true)) {
            $data['id_agent'] = null;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diupdate.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $this->ensureSuperAdmin();

        $data = $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return back()->with('success', 'Password berhasil direset.');
    }
}
