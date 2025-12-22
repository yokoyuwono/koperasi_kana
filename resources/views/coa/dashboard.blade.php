@extends('layouts.app')

@section('content')
@php
    $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp

<div class="space-y-4">
    <div>
        <h1 class="text-xl font-semibold text-slate-800">Dashboard COA</h1>
        <p class="text-xs text-slate-500 mt-1">Pusat review: deposit, promosi, dan komisi.</p>
    </div>

    {{-- Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white border border-slate-100 rounded-xl p-4">
            <div class="text-xs text-slate-500">Deposit Pending</div>
            <div class="mt-1 flex items-end justify-between gap-3">
                <div>
                    <div class="text-2xl font-semibold text-slate-800">{{ $depositPendingCount }}</div>
                    <div class="text-xs text-slate-500 mt-1">Total: <span class="font-semibold text-slate-700">{{ $fmt($depositPendingTotal) }}</span></div>
                </div>
                <a href="{{ url('/coa/deposits') }}"
                   class="inline-flex px-3 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                    Lihat
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-xl p-4">
            <div class="text-xs text-slate-500">Promosi Agent Pending</div>
            <div class="mt-1 flex items-end justify-between gap-3">
                <div>
                    <div class="text-2xl font-semibold text-slate-800">{{ $promosiPendingCount }}</div>
                    <div class="text-xs text-slate-500 mt-1">RM → BDP</div>
                </div>
                <a href="{{ url('/coa/promosi-agent') }}"
                   class="inline-flex px-3 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                    Review
                </a>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-xl p-4">
            <div class="text-xs text-slate-500">Komisi Belum Dibayar</div>
            <div class="mt-1 flex items-end justify-between gap-3">
                <div>
                    <div class="text-2xl font-semibold text-slate-800">{{ $komisiUnpaidCount }}</div>
                    <div class="text-xs text-slate-500 mt-1">Total: <span class="font-semibold text-slate-700">{{ $fmt($komisiUnpaidTotal) }}</span></div>
                </div>
                <a href="{{ url('/coa/komisi') }}"
                   class="inline-flex px-3 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                    Kelola
                </a>
            </div>
        </div>
    </div>

    {{-- Deposit Queue --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="p-4 flex items-center justify-between">
            <div>
                <div class="font-semibold text-slate-800">Antrian Deposit (Pending)</div>
                <div class="text-xs text-slate-500 mt-1">5 data terbaru yang butuh approval.</div>
            </div>
            <a href="{{ url('/coa/deposits') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($depositQueue as $d)
                <div class="p-4 space-y-2">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $d->no_bilyet }}</div>
                            <div class="text-xs text-slate-500">
                                Nasabah: <span class="text-slate-700 font-medium">{{ $d->nasabah->nama ?? '-' }}</span>
                                · Agen: <span class="text-slate-700 font-medium">{{ $d->agent->nama ?? '-' }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px]">PENDING</span>
                    </div>

                    <div class="text-xs text-slate-600">
                        Nominal: <span class="font-semibold text-slate-800">{{ $fmt($d->nominal) }}</span><br>
                        Tgl transaksi: <span class="font-medium">{{ optional($d->tanggal_transaksi)->format('d-m-Y') ?? $d->tanggal_transaksi }}</span>
                    </div>

                    <a href="{{ url('/coa/deposits/'.$d->id) }}"
                       class="inline-flex w-full justify-center px-3 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                        Lihat & Approve
                    </a>
                </div>
            @empty
                <div class="p-4 text-xs text-slate-500">Tidak ada deposit pending.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left px-4 py-3">No Bilyet</th>
                        <th class="text-left px-4 py-3">Nasabah</th>
                        <th class="text-left px-4 py-3">Agen</th>
                        <th class="text-right px-4 py-3">Nominal</th>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($depositQueue as $d)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $d->no_bilyet }}</td>
                            <td class="px-4 py-3">{{ $d->nasabah->nama ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $d->agent->nama ?? '-' }}</td>
                            <td class="px-4 py-3 text-right font-semibold">{{ $fmt($d->nominal) }}</td>
                            <td class="px-4 py-3">{{ $d->tanggal_transaksi }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ url('/coa/deposits/'.$d->id) }}"
                                   class="inline-flex px-3 py-1.5 rounded-lg bg-blue-600 text-white text-[11px] hover:bg-blue-700">
                                    Lihat & Approve
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Tidak ada deposit pending.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Promosi Queue --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="p-4 flex items-center justify-between">
            <div>
                <div class="font-semibold text-slate-800">Antrian Promosi Agent (Pending)</div>
                <div class="text-xs text-slate-500 mt-1">Pengajuan RM → BDP yang menunggu keputusan.</div>
            </div>
            <a href="{{ url('/coa/promosi-agent') }}" class="text-xs text-blue-600 hover:underline">Lihat semua</a>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($promosiQueue as $p)
                <div class="p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold text-slate-800">
                            {{ $p->agent->nama ?? '-' }} <span class="text-xs text-slate-500">(RM → BDP)</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">
                            Atasan BDP: <span class="text-slate-700 font-medium">{{ $p->atasanBdp->nama ?? '-' }}</span>
                            · Status: <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[11px]">PENDING</span>
                        </div>
                    </div>

                    <a href="{{ url('/coa/promosi-agent/'.$p->id) }}"
                       class="inline-flex md:w-auto w-full justify-center px-3 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                        Review
                    </a>
                </div>
            @empty
                <div class="p-4 text-xs text-slate-500">Tidak ada promosi pending.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
