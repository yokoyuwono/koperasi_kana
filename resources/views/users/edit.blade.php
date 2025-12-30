@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 max-w-xl space-y-6">

    <h1 class="text-lg font-semibold text-slate-800">Edit User</h1>

    <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
        @csrf @method('PUT')

        <div>
            <label class="text-sm font-medium">Nama</label>
            <input name="nama" value="{{ old('nama', $user->nama) }}"
                   class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="text-sm font-medium">Email</label>
            <input name="email" value="{{ old('email', $user->email) }}"
                   class="w-full border rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="text-sm font-medium">Role</label>
            <select name="role" id="role"
                    class="w-full border rounded-lg px-3 py-2 text-sm">
                @foreach(['superadmin','admin','coa','rm','bdp'] as $r)
                    <option value="{{ $r }}" {{ $user->role === $r ? 'selected' : '' }}>
                        {{ strtoupper($r) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div id="agentWrap" class="{{ in_array($user->role,['rm','bdp']) ? '' : 'hidden' }}">
            <label class="text-sm font-medium">Agent</label>
            <select name="id_agent"
                    class="w-full border rounded-lg px-3 py-2 text-sm">
                <option value="">-- pilih agent --</option>
                @foreach($agents as $a)
                    <option value="{{ $a->id }}"
                        {{ $user->id_agent == $a->id ? 'selected' : '' }}>
                        {{ $a->nama }} ({{ $a->jabatan }})
                    </option>
                @endforeach
            </select>
        </div>

        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm">
            Update
        </button>
    </form>

    <hr>

    <form method="POST" action="{{ route('users.resetPassword', $user) }}" class="space-y-3">
        @csrf
        <h2 class="font-semibold text-sm">Reset Password</h2>

        <input type="password" name="new_password"
               placeholder="Password baru"
               class="w-full border rounded-lg px-3 py-2 text-sm">

        <input type="password" name="new_password_confirmation"
               placeholder="Confirm password"
               class="w-full border rounded-lg px-3 py-2 text-sm">

        <button class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm">
            Reset Password
        </button>
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
