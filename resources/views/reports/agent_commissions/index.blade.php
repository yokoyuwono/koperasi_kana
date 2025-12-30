@extends('layouts.app')

@section('content')
@php
    $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp

<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Laporan Komisi Per Agent</h1>
            <p class="text-xs text-slate-500 mt-1">
                Ringkasan total komisi untuk setiap agen dari deposit yang sudah <span class="font-semibold">APPROVED</span>.
            </p>
        </div>
    </div>

    {{-- Search Bar --}}
    <div class="bg-white border border-slate-100 rounded-xl p-4">
        <form method="GET" action="{{ route('agent.komisi.report') }}" class="flex flex-col md:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Cari nama atau kode agen..."
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
            </div>
            <button type="submit" 
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                Cari Agent
            </button>
            @if($search)
                <a href="{{ route('agent.komisi.report') }}" 
                   class="px-6 py-2 bg-slate-100 text-slate-600 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors text-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table / Mobile Cards --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Kode Agent</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Nama Agent</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Jabatan</th>
                        <th class="text-right px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Total Komisi</th>
                        <th class="text-center px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($agents as $a)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 font-mono text-xs text-slate-600">{{ $a->kode_agent }}</td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $a->nama }}</div>
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs">
                                <span class="px-2 py-0.5 rounded-full {{ $a->jabatan == 'BDP' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }} font-medium">
                                    {{ $a->jabatan }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-semibold text-slate-900">{{ $fmt($a->total_komisi) }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('agent.komisi.report.show', $a->id) }}" 
                                   class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-100 transition-colors">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center">
                                    <span class="text-3xl mb-2">🔍</span>
                                    <p>Tidak ada agent yang ditemukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($agents as $a)
                <div class="p-4 space-y-3 hover:bg-slate-50 transition-colors">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="text-xs font-mono text-slate-500 mb-0.5">{{ $a->kode_agent }}</div>
                            <div class="font-bold text-slate-900">{{ $a->nama }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full {{ $a->jabatan == 'BDP' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }} text-[10px] font-bold">
                            {{ $a->jabatan }}
                        </span>
                    </div>
                    <div class="flex justify-between items-end pt-2 border-t border-slate-50">
                        <div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider font-bold">Total Komisi</div>
                            <div class="text-base font-bold text-slate-900">{{ $fmt($a->total_komisi) }}</div>
                        </div>
                        <a href="{{ route('agent.komisi.report.show', $a->id) }}" 
                           class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs font-bold hover:bg-blue-700 shadow-sm">
                            Detail
                        </a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400">
                    <p>Tidak ada agent yang ditemukan.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    <div class="pt-4">
        {{ $agents->links() }}
    </div>
</div>
@endsection
