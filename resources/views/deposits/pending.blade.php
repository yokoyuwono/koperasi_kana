@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Approval Deposito (COA)</h2>
    <p class="text-xs text-slate-500 mt-1">
        Daftar deposito yang menunggu persetujuan Anda sebagai COA.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 space-y-4">
    @if(session('success'))
        <div class="px-3 py-2 bg-green-50 text-green-700 text-xs rounded-lg border border-green-100">
            {{ session('success') }}
        </div>
    @endif

    {{-- MOBILE --}}
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
                    </div>
                </div>

                <div class="mt-2 text-[11px] text-slate-500 flex justify-between">
                    <span>
                        Transaksi:
                        {{ $d->tanggal_transaksi ? \Carbon\Carbon::parse($d->tanggal_transaksi)->format('d-m-Y') : '-' }}
                    </span>
                    <span>
                        Tempo:
                        {{ $d->tanggal_tempo ? \Carbon\Carbon::parse($d->tanggal_tempo)->format('d-m-Y') : '-' }}
                    </span>
                </div>

                <div class="mt-2 flex gap-2">
                    <a href="{{ route('deposits.pending.show', $d) }}"
                       class="flex-1 px-3 py-1.5 text-center rounded-lg bg-blue-600 text-white text-[11px] font-medium hover:bg-blue-700">
                        Periksa
                    </a>
                </div>
            </div>
        @empty
            <p class="text-center text-xs text-slate-500 py-4">Tidak ada deposit pending.</p>
        @endforelse
    </div>

    {{-- DESKTOP --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="min-w-[820px] w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                    <th class="px-4 py-2 text-left">No Bilyet</th>
                    <th class="px-4 py-2 text-left">Nasabah</th>
                    <th class="px-4 py-2 text-left">Agen</th>
                    <th class="px-4 py-2 text-left">Nominal</th>
                    <th class="px-4 py-2 text-left">Tenor</th>
                    <th class="px-4 py-2 text-left">Tanggal</th>
                    <th class="px-4 py-2 text-left w-28">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($deposits as $d)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-2 align-top">
                            <div class="font-mono text-xs bg-slate-100 inline-block px-2 py-1 rounded">
                                {{ $d->no_bilyet }}
                            </div>
                        </td>
                        <td class="px-4 py-2 align-top">
                            <div class="text-sm font-medium text-slate-800">
                                {{ $d->nasabah->nama ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-2 align-top">
                            <div class="text-sm text-slate-800">
                                {{ $d->agent->nama ?? '-' }}
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
                            <div>Tempo:
                                {{ $d->tanggal_tempo ? \Carbon\Carbon::parse($d->tanggal_tempo)->format('d-m-Y') : '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-2 align-top">
                            <a href="{{ route('deposits.pending.show', $d) }}"
                               class="px-3 py-1.5 text-xs rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">
                                Periksa
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-xs text-slate-500">
                            Tidak ada deposit pending.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pt-2 border-t border-slate-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-[11px] text-slate-500">
            <div>
                Menampilkan {{ $deposits->count() }} dari {{ $deposits->total() }} deposit pending.
            </div>
            <div class="text-xs">
                {{ $deposits->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
