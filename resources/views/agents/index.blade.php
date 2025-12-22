@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Manajemen Agen</h2>
    <p class="text-xs text-slate-500 mt-1">
        Kelola data agen yang mereferensikan nasabah deposito.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
    <div class="px-4 py-3 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-700">Daftar Agen</h3>
            <p class="text-xs text-slate-500">Data agen aktif di sistem.</p>
        </div>
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input
                type="text"
                placeholder="Cari nama / kode agen..."
                class="flex-1 md:flex-none border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            >
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('agents.create') }}"
                class="inline-flex items-center gap-1 px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">
                    <span>+ Tambah Agen</span>
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 bg-green-50 text-green-700 text-xs border-b border-green-100">
            {{ session('success') }}
        </div>
    @endif

    {{-- WRAPPER TABEL RESPONSIVE --}}
    <div class="overflow-x-auto">
        <table class="min-w-[640px] w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                    <th class="px-4 py-2 text-left">Kode</th>
                    <th class="px-4 py-2 text-left">Nama & Kontak</th>
                    <th class="px-4 py-2 text-left hidden sm:table-cell">Jabatan</th>
                    <th class="px-4 py-2 text-left w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($agents as $agent)
                    <tr class="hover:bg-slate-50">
                        {{-- KODE --}}
                        <td class="px-4 py-2 align-top">
                            <div class="font-mono text-xs bg-slate-100 inline-block px-2 py-1 rounded">
                                {{ $agent->kode_agent }}
                            </div>
                        </td>

                        {{-- NAMA + DETAIL KECIL, supaya di mobile tetap informatif --}}
                        <td class="px-4 py-2 align-top">
                            <div class="font-medium text-slate-800 text-sm">
                                {{ $agent->nama }}
                            </div>
                            <div class="mt-0.5 space-y-0.5">
                                @if($agent->telepon)
                                    <div class="text-[11px] text-slate-500">
                                        📞 {{ $agent->telepon }}
                                    </div>
                                @endif
                                @if($agent->email)
                                    <div class="text-[11px] text-slate-500">
                                        ✉️ {{ $agent->email }}
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- JABATAN (DISembunyikan di layar sangat kecil) --}}
                        <td class="px-4 py-2 align-top hidden sm:table-cell">
                            <div class="inline-flex px-2 py-1 rounded-full text-[11px]
                                        {{ $agent->jabatan === 'BDP' ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }}">
                                {{ $agent->jabatan ?? '-' }}
                            </div>
                        </td>

                        {{-- AKSI --}}
                        {{-- <td class="px-4 py-2 align-top">
                            @if(auth()->user()->role === 'admin')
                            <div class="flex flex-wrap gap-1">
                                <a href="{{ route('agents.edit', $agent) }}"
                                   class="px-2 py-1 rounded-md bg-amber-400/90 text-white text-[11px] hover:bg-amber-500">
                                    Edit
                                </a>
                                <form action="{{ route('agents.destroy', $agent) }}" method="POST"
                                      onsubmit="return confirm('Hapus agen ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="px-2 py-1 rounded-md bg-red-500/90 text-white text-[11px] hover:bg-red-600">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                             @else
                                <span class="text-[11px] text-slate-400">Hanya admin yang bisa mengubah</span>
                            @endif
                        </td> --}}
                        <td class="inline-flex px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">
                            @if(auth()->user()->role === 'admin')
                                {{-- ADMIN: boleh edit / delete --}}
                                <a href="{{ route('agents.edit', $agent) }}">
                                    Edit
                                </a>
                                {{-- <form action="{{ route('agents.destroy', $agent) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700"
                                            onclick="return confirm('Hapus agent ini?')">
                                        Hapus
                                    </button>
                                </form> --}}
                            @elseif(auth()->user()->role === 'coa')
                                {{-- COA: hanya bisa lihat detail --}}
                                <a href="{{ route('coa.agents.show', $agent) }}">
                                    Lihat
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-xs text-slate-500">
                            Belum ada data agen yang tersimpan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-slate-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-[11px] text-slate-500">
            <div>
                Menampilkan {{ $agents->count() }} dari total {{ $agents->total() }} agen.
            </div>
            <div class="text-xs">
                {{ $agents->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
