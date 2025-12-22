@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Pengajuan Komisi</h2>
    <p class="text-xs text-slate-500 mt-1">
        Ringkasan seluruh pengajuan komisi.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 space-y-4">
    {{-- Filter & tombol --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <form method="GET" action="{{ route('deposits.index') }}" class="flex items-center gap-2 w-full md:w-auto">
            <label class="text-xs text-slate-600 whitespace-nowrap">Filter status:</label>
            <select name="status"
                    onchange="this.form.submit()"
                    class="flex-1 md:flex-none border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Semua</option>
                @foreach(['draft','pending','approved','rejected'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                        {{ ucfirst($st) }}
                    </option>
                @endforeach
            </select>
            @if(request('status'))
                <a href="{{ route('deposits.index') }}"
                   class="text-[11px] text-slate-500 hover:underline">
                    Reset
                </a>
            @endif
        </form>

        @if(auth()->user()->role === 'admin')
            <a href="{{ route('deposits.create') }}"
               class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700">
                + Tambah Pengajuan
            </a>
        @endif
    </div>

    {{-- Notif --}}
    @if(session('success'))
        <div class="px-3 py-2 bg-green-50 text-green-700 text-xs rounded-lg border border-green-100">
            {{ session('success') }}
        </div>
    @endif

    {{-- MOBILE: card list --}}
    <div class="space-y-3 md:hidden">
        @forelse($deposits as $d)
            <div class="border border-slate-200 rounded-lg p-3 shadow-xs">
                <div class="flex justify-between items-start gap-2">
                    <div>
                        <div class="text-xs font-mono bg-slate-100 inline-block px-2 py-1 rounded">
                            {{ $d->no_bilyet }}
                        </div>
                        <div class="mt-1 text-sm font-semibold text-slate-800">
                            {{ $d->nasabah->nama ?? '-' }}
                        </div>
                        <div class="text-[11px] text-slate-500">
                            Agen: {{ $d->agent->nama ?? '-' }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-semibold text-slate-800">
                            Rp {{ number_format($d->nominal, 0, ',', '.') }}
                        </div>
                        <div class="text-[11px] text-slate-500">
                            Tenor: {{ $d->tenor }} bln
                        </div>
                        <span class="inline-flex mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold
                            @switch($d->status)
                                @case('draft') bg-slate-100 text-slate-700 @break
                                @case('pending') bg-amber-100 text-amber-700 @break
                                @case('approved') bg-emerald-100 text-emerald-700 @break
                                @case('rejected') bg-red-100 text-red-700 @break
                                @default bg-slate-100 text-slate-700
                            @endswitch">
                            {{ strtoupper($d->status) }}
                        </span>
                    </div>
                </div>

                <div class="mt-2 text-[11px] text-slate-500 flex justify-between">
                    <span>
                        Transaksi:
                        {{ $d->tanggal_transaksi ? \Carbon\Carbon::parse($d->tanggal_transaksi)->format('d-m-Y') : '-' }}
                    </span>
                    <span>
                        Jt. Tempo:
                        {{ $d->tanggal_tempo ? \Carbon\Carbon::parse($d->tanggal_tempo)->format('d-m-Y') : '-' }}
                    </span>
                </div>

                 @if(auth()->user()->role === 'admin')
                    {{-- AKSI UNTUK ADMIN --}}
                    <a href="{{ route('deposits.edit', $d) }}"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] text-blue-700 hover:bg-blue-50">
                        Edit
                    </a>
                @elseif(auth()->user()->role === 'coa')
                    {{-- AKSI UNTUK COA --}}
                    <a href="{{ route('coa.deposits.show', $d) }}"
                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] bg-blue-600 text-white hover:bg-blue-700">
                        Lihat & Approve
                    </a>
                @endif
            </div>
        @empty
            <p class="text-center text-xs text-slate-500 py-4">Belum ada data pengajuan.</p>
        @endforelse
    </div>

    {{-- DESKTOP: table --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-[900px] w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                    <th class="px-4 py-2 text-left">No Bilyet</th>
                    <th class="px-4 py-2 text-left">Nasabah</th>
                    <th class="px-4 py-2 text-left">Agen</th>
                    <th class="px-4 py-2 text-left">Nominal</th>
                    <th class="px-4 py-2 text-left">Tenor</th>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-left w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($deposits as $d)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 align-top">
                            <div class="font-mono text-xs bg-slate-100 inline-block px-2 py-1 rounded">
                                {{ $d->no_bilyet }}
                            </div>
                            @if($d->BDP_ref)
                                <div class="text-[11px] text-slate-400 mt-1">
                                    BDP Ref: {{ $d->BDP_ref }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-2 align-top">
                            <div class="text-sm font-medium text-slate-800">
                                {{ $d->nasabah->nama ?? '-' }}
                            </div>
                            <div class="text-[11px] text-slate-500">
                                ID: {{ $d->nasabah->kode_nasabah ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-2 align-top">
                            <div class="text-sm text-slate-800">
                                {{ $d->agent->nama ?? '-' }}
                            </div>
                            <div class="text-[11px] text-slate-500">
                                {{ $d->agent->kode_agent ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-2 align-top">
                            <div class="text-sm font-semibold text-slate-800">
                                Rp {{ number_format($d->nominal, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-4 py-2 align-top text-sm">
                            {{ $d->tenor }} bln
                        </td>
                        <td class="px-4 py-2 align-top text-[11px] text-slate-600">
                            <div>Transaksi:
                                {{ $d->tanggal_transaksi ? \Carbon\Carbon::parse($d->tanggal_transaksi)->format('d-m-Y') : '-' }}
                            </div>
                            <div>Mulai:
                                {{ $d->tanggal_mulai ? \Carbon\Carbon::parse($d->tanggal_mulai)->format('d-m-Y') : '-' }}
                            </div>
                            <div>Tempo:
                                {{ $d->tanggal_tempo ? \Carbon\Carbon::parse($d->tanggal_tempo)->format('d-m-Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-2 align-top">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold
                                @switch($d->status)
                                    @case('draft') bg-slate-100 text-slate-700 @break
                                    @case('pending') bg-amber-100 text-amber-700 @break
                                    @case('approved') bg-emerald-100 text-emerald-700 @break
                                    @case('rejected') bg-red-100 text-red-700 @break
                                    @default bg-slate-100 text-slate-700
                                @endswitch">
                                {{ strtoupper($d->status) }}
                            </span>
                            @if($d->status === 'rejected' && $d->catatan)
                                <div class="mt-1 text-[11px] text-red-600">
                                    Alasan: {{ \Illuminate\Support\Str::limit($d->catatan, 60) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-2 align-top">
                             @if(auth()->user()->role === 'admin')
                                {{-- AKSI UNTUK ADMIN --}}
                                <a href="{{ route('deposits.edit', $d) }}"
                                class="inline-flex px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">
                                    Edit
                                </a>
                            @elseif(auth()->user()->role === 'coa')
                                {{-- AKSI UNTUK COA --}}
                                <a href="{{ route('coa.deposits.show', $d) }}"
                                class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] bg-blue-600 text-white hover:bg-blue-700">
                                    Lihat
                                </a>
                            @endif
                            {{-- @if(auth()->user()->role === 'admin')
                                <div class="flex flex-wrap gap-1">
                                    @if(!in_array($d->status, ['approved','pending']))
                                        <a href="{{ route('deposits.edit', $d) }}"
                                           class="px-2 py-1 rounded-md bg-amber-400/90 text-white text-[11px] hover:bg-amber-500">
                                            Edit
                                        </a>
                                    @endif
                                    @if($d->status !== 'approved')
                                        <form action="{{ route('deposits.destroy', $d) }}" method="POST"
                                              onsubmit="return confirm('Hapus deposit ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                class="px-2 py-1 rounded-md bg-red-500/90 text-white text-[11px] hover:bg-red-600">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <span class="text-[11px] text-slate-400">Aksi khusus admin</span>
                            @endif --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-xs text-slate-500">
                            Belum ada data.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginate --}}
    <div class="pt-2 border-t border-slate-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-[11px] text-slate-500">
            <div>
                Menampilkan {{ $deposits->count() }} dari {{ $deposits->total() }} deposit.
            </div>
            <div class="text-xs">
                {{ $deposits->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
