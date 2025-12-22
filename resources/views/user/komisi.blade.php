@extends('layouts.app')

@section('content')
@php
  $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp

<div class="space-y-4">
  <div class="flex items-start justify-between gap-3">
    <div>
      <h1 class="text-xl font-semibold text-slate-800">Komisi</h1>
      <p class="text-xs text-slate-500 mt-1">
        @if(strtoupper($agent->jabatan) === 'bdp')
          Menampilkan komisi BDP + komisi RM bawahan.
        @else
          Menampilkan komisi Anda (RM).
        @endif
      </p>
    </div>
    <a href="{{ route('user.dashboard') }}" class="text-xs text-slate-600 hover:underline">← Dashboard</a>
  </div>

  {{-- Filter --}}
  <form method="GET" class="bg-white border border-slate-100 rounded-xl p-4 space-y-3">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Status</label>
        <select name="status" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
          <option value="all" {{ $qStatus==='all' ? 'selected' : '' }}>Semua</option>
          <option value="approved" {{ $qStatus==='approved' ? 'selected' : '' }}>Approved</option>
          <option value="paid" {{ $qStatus==='paid' ? 'selected' : '' }}>Paid</option>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Bulan (opsional)</label>
        <input type="month" name="bulan" value="{{ $qBulan }}"
               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
      </div>

      <div class="flex items-end gap-2">
        <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs hover:bg-slate-900">
          Terapkan
        </button>
        <a href="{{ route('user.komisi') }}"
           class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-xs hover:bg-slate-50">
          Reset
        </a>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
      <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
        <div class="text-slate-500">Total Approved</div>
        <div class="font-semibold text-slate-800 mt-1">{{ $fmt($sumApproved) }}</div>
      </div>
      <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
        <div class="text-slate-500">Total Paid</div>
        <div class="font-semibold text-slate-800 mt-1">{{ $fmt($sumPaid) }}</div>
      </div>
    </div>
  </form>

  {{-- List (mobile-friendly cards) --}}
  <div class="space-y-3">
    @forelse($rows as $k)
      @php
        $agentName = $k->agent->nama ?? ('Agent #'.$k->id_agent);
        $jabatan = $k->agent->jabatan ?? '-';
      @endphp

      <div class="bg-white border border-slate-100 rounded-xl p-4">
        <div class="flex items-start justify-between gap-3">
          <div>
            <div class="font-semibold text-slate-800">{{ $agentName }}</div>
            <div class="text-[11px] text-slate-500 mt-1">
              Jabatan: {{ $jabatan }} · Deposit: {{ $k->id_deposit }} · Periode: {{ $k->tanggal_periode }}
            </div>
          </div>
          <div class="text-right">
            <div class="text-[11px] text-slate-500">{{ (float)$k->persen_komisi }}%</div>
            <div class="font-semibold text-slate-800">{{ $fmt($k->nominal) }}</div>
          </div>
        </div>

        <div class="mt-3 flex items-center justify-between text-xs">
          <div class="text-slate-600">
            Status:
            @if($k->status === 'paid')
              <span class="ml-1 px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px]">PAID</span>
            @elseif($k->status === 'approved')
              <span class="ml-1 px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-[11px]">APPROVED</span>
            @else
              <span class="ml-1 px-2 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px]">{{ strtoupper($k->status) }}</span>
            @endif
          </div>

          @if($k->tanggal_pembayaran)
            <div class="text-[11px] text-slate-500">
              Dibayar: {{ $k->tanggal_pembayaran }}
            </div>
          @endif
        </div>
      </div>
    @empty
      <div class="bg-white border border-slate-100 rounded-xl p-6 text-sm text-slate-500">
        Tidak ada data komisi.
      </div>
    @endforelse
  </div>

  <div>
    {{ $rows->links() }}
  </div>
</div>
@endsection
