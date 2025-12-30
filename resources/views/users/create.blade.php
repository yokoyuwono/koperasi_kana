@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 max-w-xl space-y-4">

    <h1 class="text-lg font-semibold text-slate-800">Tambah User</h1>

    <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="text-sm font-medium">Nama</label>
            <input name="nama" value="{{ old('nama') }}"
                   class="w-full border rounded-lg px-3 py-2 text-sm">
            @error('nama') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium">Email</label>
            <input name="email" type="email" value="{{ old('email') }}"
                   class="w-full border rounded-lg px-3 py-2 text-sm">
            @error('email') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-sm font-medium">Role</label>
            <select name="role" id="role"
                    class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">-- pilih --</option>
                <option value="superadmin">Superadmin</option>
                <option value="admin">Admin</option>
                <option value="coa">COA</option>
                <option value="rm">RM</option>
                <option value="bdp">BDP</option>
            </select>
        </div>

        <div id="agentWrap" class="hidden">
            <label class="text-sm font-medium">Agent</label>
            <select name="id_agent"
                    class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">-- pilih agent --</option>
                @foreach($agents as $a)
                    <option value="{{ $a->id }}">{{ $a->nama }} ({{ $a->jabatan }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-medium">Password</label>
            <input name="password" type="password"
                   class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="text-sm font-medium">Confirm Password</label>
            <input name="password_confirmation" type="password"
                   class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div class="flex gap-2">
            <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
                Simpan
            </button>
            <a href="{{ route('users.index') }}"
               class="px-4 py-2 border rounded-lg text-sm">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
const role = document.getElementById('role');
const agentWrap = document.getElementById('agentWrap');

role.addEventListener('change', () => {
    agentWrap.classList.toggle('hidden', !['rm','bdp'].includes(role.value));
});
</script>
@endsection
