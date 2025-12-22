@extends('layouts.app')

@section('content')
@php
    $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp

<div class="space-y-4">
    <div>
        <h1 class="text-xl font-semibold text-slate-800">Dashboard Admin</h1>
        <p class="text-xs text-slate-500 mt-1">Ringkasan operasional & antrian kerja admin.</p>
    </div>

    {{-- Quick actions (mobile first) --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        <a href="{{ url('/agents/create') }}" class="bg-white border border-slate-100 rounded-xl p-3 hover:bg-slate-50">
            <div class="text-xs text-slate-500">Tambah</div>
            <div class="font-semibold text-slate-800 mt-1">Agent</div>
        </a>
        <a href="{{ url('/nasabah/create') }}" class="bg-white border border-slate-100 rounded-xl p-3 hover:bg-slate-50">
            <div class="text-xs text-slate-500">Tambah</div>
            <div class="font-semibold text-slate-800 mt-1">Nasabah</div>
        </a>
        <a href="{{ url('/deposits/create') }}" class="bg-white border border-slate-100 rounded-xl p-3 hover:bg-slate-50">
            <div class="text-xs text-slate-500">Input</div>
            <div class="font-semibold text-slate-800 mt-1">Deposit</div>
        </a>
        <a href="{{ url('/promosi-agent/create') }}" class="bg-white border border-slate-100 rounded-xl p-3 hover:bg-slate-50">
            <div class="text-xs text-slate-500">Ajukan</div>
            <div class="font-semibold text-slate-800 mt-1">Promosi</div>
        </a>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white border border-slate-100 rounded-xl p-4">
            <div class="text-xs text-slate-500">Master Data</div>
            <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                <div class="rounded-lg bg-slate-50 p-3 border border-slate-100">
                    <div class="text-slate-500 text-[11px]">Total Agent</div>
                    <div class="text-lg font-semibold text-slate-800">{{ $totalAgents }}</div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 border border-slate-100">
                    <div class="text-slate-500 text-[11px]">Total Nasabah</div>
                    <div class="text-lg font-semibold text-slate-800">{{ $totalNasabah }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-xl p-4">
            <div class="text-xs text-slate-500">Deposit</div>
            <div class="mt-2 space-y-2 text-xs">
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 border border-slate-100">
                    <span class="text-slate-600">Draft (belum submit)</span>
                    <span class="font-semibold text-slate-800">{{ $depositDraftCount }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-amber-50 p-3 border border-amber-100">
                    <span class="text-amber-700">Pending COA</span>
                    <span class="font-semibold text-amber-800">{{ $depositPendingCount }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-red-50 p-3 border border-red-100">
                    <span class="text-red-700">Rejected (perlu revisi)</span>
                    <span class="font-semibold text-red-800">{{ $depositRejectedCount }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-xl p-4">
            <div class="text-xs text-slate-500">Promosi Agent</div>
            <div class="mt-2 space-y-2 text-xs">
                <div class="flex items-center justify-between rounded-lg bg-amber-50 p-3 border border-amber-100">
                    <span class="text-amber-700">Pending COA</span>
                    <span class="font-semibold text-amber-800">{{ $promosiPendingCount }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-red-50 p-3 border border-red-100">
                    <span class="text-red-700">Rejected (perlu revisi)</span>
                    <span class="font-semibold text-red-800">{{ $promosiRejectedCount }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Work queue --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        {{-- Deposit needing admin action --}}
        <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-slate-800">Deposit Ditolak</div>
                    <div class="text-xs text-slate-500 mt-1">Rejected (perlu perbaikan).</div>
                </div>
                <a href="{{ url('/deposits') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($depositNeedsAction as $d)
                    <div class="p-4 flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $d->no_bilyet }}</div>
                            <div class="text-xs text-slate-500 mt-1">
                                Nasabah: <span class="text-slate-700 font-medium">{{ $d->nasabah->nama ?? '-' }}</span>
                                · Agen: <span class="text-slate-700 font-medium">{{ $d->agent->nama ?? '-' }}</span>
                            </div>
                            <div class="text-xs text-slate-600 mt-1">
                                Nominal: <span class="font-semibold text-slate-800">{{ $fmt($d->nominal) }}</span>
                                · Status: <span class="font-semibold">{{ strtoupper($d->status) }}</span>
                            </div>
                        </div>

                        <a href="{{ url('/deposits/'.$d->id.'/edit') }}"
                           class="shrink-0 inline-flex px-3 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                            Edit
                        </a>
                    </div>
                @empty
                    <div class="p-4 text-xs text-slate-500">Tidak ada deposit yang perlu aksi.</div>
                @endforelse
            </div>
        </div>

        {{-- Deposit pending COA (visibility for admin) --}}
        <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
            <div class="p-4 flex items-center justify-between">
                <div>
                    <div class="font-semibold text-slate-800">Deposit Pending</div>
                    <div class="text-xs text-slate-500 mt-1">Menunggu update status dari COA</div>
                </div>
                <a href="{{ url('/deposits?status=pending') }}" class="text-xs text-blue-600 hover:underline">Filter pending</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($depositPendingQueue as $d)
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ $d->no_bilyet }}</div>
                                <div class="text-xs text-slate-500 mt-1">
                                    {{ $d->nasabah->nama ?? '-' }} · {{ $d->agent->nama ?? '-' }}
                                </div>
                                <div class="text-xs text-slate-600 mt-1">
                                    Nominal: <span class="font-semibold">{{ $fmt($d->nominal) }}</span>
                                </div>
                            </div>
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px]">PENDING</span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 text-xs text-slate-500">Tidak ada deposit pending.</div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Promosi rejected queue --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="p-4 flex items-center justify-between">
            <div>
                <div class="font-semibold text-slate-800">Promosi Agent Ditolak (Perlu Revisi)</div>
                <div class="text-xs text-slate-500 mt-1">perbaiki lalu submit ulang.</div>
            </div>
            <a href="{{ url('/promosi-agent') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($promosiNeedsFix as $p)
                <div class="p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-slate-800">{{ $p->agent->nama ?? '-' }} <span class="text-xs text-slate-500">(RM → BDP)</span></div>
                        <div class="text-xs text-slate-500 mt-1">
                            Atasan BDP: <span class="text-slate-700 font-medium">{{ $p->atasanBdp->nama ?? '-' }}</span>
                        </div>
                        @if($p->alasan_reject)
                            <div class="mt-2 text-[11px] text-red-700 bg-red-50 border border-red-100 rounded-lg p-2">
                                <div class="font-semibold">Alasan COA:</div>
                                <div class="whitespace-pre-line">{{ $p->alasan_reject }}</div>
                            </div>
                        @endif
                    </div>

                    <a href="{{ url('/promosi-agent/'.$p->id.'/edit') }}"
                       class="inline-flex md:w-auto w-full justify-center px-3 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                        Revisi
                    </a>
                </div>
            @empty
                <div class="p-4 text-xs text-slate-500">Tidak ada promosi rejected.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
