@extends('layouts.app')

@section('content')
<div class="p-4 md:p-6 space-y-4">

    <div class="flex items-center justify-between">
        <h1 class="text-lg font-semibold text-slate-800">Manajemen User</h1>
        <a href="{{ route('users.create') }}"
           class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm hover:bg-blue-700">
            + Tambah User
        </a>
    </div>

    @if(session('success'))
        <div class="p-3 rounded bg-emerald-50 text-emerald-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-4 py-3 text-left">Nama</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Role</th>
                    <th class="px-4 py-3 text-left">Agent</th>
                    <th class="px-4 py-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($users as $u)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $u->nama }}</td>
                        <td class="px-4 py-3">{{ $u->email }}</td>
                        <td class="px-4 py-3 uppercase text-xs font-semibold">{{ $u->role }}</td>
                        <td class="px-4 py-3">
                            {{ $u->agent?->nama ?? '-' }}
                        </td>
                        <td class="px-4 py-3 flex gap-2">
                            <a href="{{ route('users.edit', $u) }}"
                               class="px-3 py-1 rounded bg-slate-100 text-slate-700 text-xs hover:bg-slate-200">
                                Edit
                            </a>

                            <form method="POST" action="{{ route('users.resetPassword', $u) }}">
                                @csrf
                                <input type="hidden" name="new_password" value="password123">
                                <input type="hidden" name="new_password_confirmation" value="password123">
                                <button onclick="return confirm('Reset password ke password123 ?')"
                                        class="px-3 py-1 rounded bg-red-50 text-red-600 text-xs hover:bg-red-100">
                                    Reset Password
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                            Belum ada user
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $users->links() }}
    </div>

</div>
@endsection
