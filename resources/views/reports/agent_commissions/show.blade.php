@extends('layouts.app')

@section('content')
@php
    $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp

<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('agent.komisi.report') }}" 
               class="h-10 w-10 flex items-center justify-center rounded-full bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors shadow-sm">
                <span class="text-xl">←</span>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-slate-800">Detail Komisi Agent</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan seluruh komisi yang sudah disetujui untuk agen ini.
                </p>
            </div>
        </div>
    </div>

    {{-- Agent Info Card --}}
    <div class="bg-white border border-slate-100 rounded-xl p-5 shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-1">Nama Agent</div>
                <div class="text-sm font-semibold text-slate-900">{{ $agent->nama }}</div>
            </div>
            <div>
                <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-1">Kode Agent</div>
                <div class="text-sm font-mono text-slate-700 uppercase">{{ $agent->kode_agent }}</div>
            </div>
            <div>
                <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-1">Jabatan</div>
                <div>
                    <span class="px-2 py-0.5 rounded-full {{ $agent->jabatan == 'BDP' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }} text-[10px] font-bold">
                        {{ $agent->jabatan }}
                    </span>
                </div>
            </div>
            <div class="col-span-2 md:col-span-1 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6">
                <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold mb-1">Total Komisi Diapproved</div>
                <div class="text-lg font-bold text-blue-600">
                    {{ $fmt($commissions->sum('nominal')) }}
                    <span class="text-[10px] text-slate-400 font-normal"> (Halaman ini)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Commissions List --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">ID</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">No Bilyet</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Tgl Periode</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Status</th>
                        <th class="text-right px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Nominal</th>
                        <th class="text-right px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">% Komisi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($commissions as $c)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-slate-400 font-mono text-xs">#{{ $c->id }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $c->deposit->no_bilyet ?? '-' }}</div>
                                <div class="text-[10px] text-slate-400">{{ optional($c->deposit->nasabah)->nama }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs">
                                {{ optional($c->tanggal_periode)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($c->tanggal_pembayaran)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                                        PAID ({{ $c->tanggal_pembayaran->format('d/m/Y') }})
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold">
                                        UNPAID
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-slate-900">
                                {{ $fmt($c->nominal) }}
                            </td>
                            <td class="px-6 py-4 text-right text-slate-500 font-mono text-xs">
                                {{ number_format($c->persen_komisi, 2) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <p>Belum ada rekaman komisi untuk agen ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($commissions as $c)
                <div class="p-4 space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-[10px] text-slate-400 font-mono mb-0.5">#{{ $c->id }} · {{ optional($c->tanggal_periode)->format('d/m/Y') }}</div>
                            <div class="font-bold text-slate-900">{{ $c->deposit->no_bilyet ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ optional($c->deposit->nasabah)->nama }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-bold text-slate-900">{{ $fmt($c->nominal) }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ number_format($c->persen_komisi, 2) }}%</div>
                        </div>
                    </div>
                    <div class="flex justify-end pt-2 border-t border-slate-50">
                        @if($c->tanggal_pembayaran)
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[10px] font-bold">
                                PAID ({{ $c->tanggal_pembayaran->format('d/m/Y') }})
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[10px] font-bold">
                                UNPAID
                            </span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-sm">
                    <p>Belum ada rekaman komisi untuk agen ini.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    <div class="pt-4">
        {{ $commissions->links() }}
    </div>
</div>
@endsection
