@extends('layouts.app')

@section('content')
@php
  $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp

<div class="space-y-4">
  <div>
    <h1 class="text-xl font-semibold text-slate-800">Dashboard {{ $stats['role'] }}</h1>
    <p class="text-xs text-slate-500 mt-1">Ringkasan data (read-only).</p>
  </div>

  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @if($stats['role'] === 'bdp')
      <div class="bg-white border border-slate-100 rounded-xl p-4">
        <div class="text-xs text-slate-500">RM Bawahan</div>
        <div class="text-lg font-semibold text-slate-800">{{ $stats['rm_count'] }}</div>
      </div>
    @endif

    <div class="bg-white border border-slate-100 rounded-xl p-4">
      <div class="text-xs text-slate-500">Nasabah</div>
      <div class="text-lg font-semibold text-slate-800">{{ $stats['nasabah_count'] }}</div>
    </div>

    <div class="bg-white border border-slate-100 rounded-xl p-4">
      <div class="text-xs text-slate-500">Deposit Pending</div>
      <div class="text-lg font-semibold text-slate-800">{{ $stats['deposit_pending'] }}</div>
    </div>

    <div class="bg-white border border-slate-100 rounded-xl p-4">
      <div class="text-xs text-slate-500">Deposit Approved</div>
      <div class="text-lg font-semibold text-slate-800">{{ $stats['deposit_approved'] }}</div>
    </div>

    <div class="bg-white border border-slate-100 rounded-xl p-4 col-span-2 md:col-span-2">
      <div class="text-xs text-slate-500">Komisi Approved</div>
      <div class="text-lg font-semibold text-slate-800">{{ $fmt($stats['komisi_approved']) }}</div>
    </div>

    <div class="bg-white border border-slate-100 rounded-xl p-4 col-span-2 md:col-span-2">
      <div class="text-xs text-slate-500">Komisi Paid</div>
      <div class="text-lg font-semibold text-slate-800">{{ $fmt($stats['komisi_paid']) }}</div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
    @if($stats['role'] === 'rm')
      <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="p-4 border-b border-slate-100 font-semibold text-slate-800 text-sm">Nasabah Terbaru</div>
        <div class="divide-y divide-slate-100">
          @forelse($latestNasabah as $n)
            <div class="p-4 text-sm">
              <div class="font-semibold text-slate-800">{{ $n->nama ?? '-' }}</div>
              <div class="text-xs text-slate-500 mt-1">ID: {{ $n->id }}</div>
            </div>
          @empty
            <div class="p-4 text-sm text-slate-500">Belum ada data.</div>
          @endforelse
        </div>
      </div>
    @endif

    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
      <div class="p-4 border-b border-slate-100 font-semibold text-slate-800 text-sm">
        Deposit Terbaru {{ $stats['role'] === 'bdp' ? '(Team)' : '' }}
      </div>
      <div class="divide-y divide-slate-100">
        @forelse($latestDeposit as $d)
          <div class="p-4 text-sm">
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="font-semibold text-slate-800">{{ $d->no_bilyet ?? ('Deposit #'.$d->id) }}</div>
                <div class="text-xs text-slate-500 mt-1">Status: {{ strtoupper($d->status) }}</div>
              </div>
              <div class="text-right">
                <div class="text-xs text-slate-500">Nominal</div>
                <div class="font-semibold text-slate-800">{{ $fmt($d->nominal ?? 0) }}</div>
              </div>
            </div>
          </div>
        @empty
          <div class="p-4 text-sm text-slate-500">Belum ada data.</div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="pt-2">
    <a href="{{ route('user.komisi') }}"
       class="inline-flex items-center justify-center px-4 py-2 rounded-lg bg-slate-800 text-white text-xs hover:bg-slate-900">
      Lihat Komisi
    </a>
  </div>
</div>
@endsection
