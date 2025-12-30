@extends('layouts.app')

@section('content')
@php
  $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
  $dt  = fn($d) => $d ? \Carbon\Carbon::parse($d)->format('d-m-Y') : '-';
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

  {{-- =========================
     LIST KOMISI
========================= --}}
<div class="bg-white border border-slate-100 rounded-xl p-4">
  <div class="flex items-start justify-between gap-3">
    <div>
      <h2 class="text-sm font-semibold text-slate-800">Komisi</h2>
      <p class="text-xs text-slate-500 mt-1">
        Menampilkan komisi berdasarkan agent yang Anda akses.
      </p>
    </div>

    {{-- Filter komisi (kalau kamu sudah punya, biarkan; kalau belum, ini aman) --}}
    <form method="GET" class="flex items-end gap-2">
      <input type="hidden" name="d_status" value="{{ $dStatus ?? 'all' }}">

      <div>
        <label class="block text-[11px] font-medium text-slate-700 mb-1">Status</label>
        <select name="status"
                class="border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
          <option value="all" {{ ($qStatus ?? 'all')==='all' ? 'selected' : '' }}>Semua</option>
          <option value="approved" {{ ($qStatus ?? '')==='approved' ? 'selected' : '' }}>Approved</option>
          <option value="paid" {{ ($qStatus ?? '')==='paid' ? 'selected' : '' }}>Paid</option>
        </select>
      </div>

      <div>
        <label class="block text-[11px] font-medium text-slate-700 mb-1">Bulan</label>
        <input type="month" name="bulan" value="{{ $qBulan }}"
               class="border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
      </div>

      <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs hover:bg-slate-900">
        Terapkan
      </button>
    </form>
  </div>

  {{-- ===== Desktop Table ===== --}}
  <div class="hidden md:block mt-4 overflow-x-auto rounded-lg border border-slate-200">
    <table class="w-full text-xs">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="px-3 py-2 text-left">ID Komisi</th>
          <th class="px-3 py-2 text-left">Agent</th>
          <th class="px-3 py-2 text-left">Jabatan</th>
          <th class="px-3 py-2 text-right">Persen</th>
          <th class="px-3 py-2 text-right">Nominal</th>
          <th class="px-3 py-2 text-left">Tanggal Periode</th>
          <th class="px-3 py-2 text-left">Status</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-slate-100">
        @forelse($rows as $k)
          @php
            $st = strtolower($k->status ?? '-');
          @endphp
          <tr class="hover:bg-slate-50/50">
            <td class="px-3 py-2 font-semibold text-slate-800">#{{ $k->id }}</td>

            <td class="px-3 py-2">
              <div class="font-semibold text-slate-800">
                {{ $k->agent?->nama ?? '-' }}
              </div>
              <div class="text-[11px] text-slate-500">
                ID Agent: {{ $k->id_agent }}
              </div>
            </td>

            <td class="px-3 py-2">
              {{ $k->agent?->jabatan ?? '-' }}
            </td>

            <td class="px-3 py-2 text-right">
              {{ $k->persen_komisi !== null ? rtrim(rtrim(number_format((float)$k->persen_komisi, 2, '.', ''), '0'), '.') . '%' : '-' }}
            </td>

            <td class="px-3 py-2 text-right font-semibold text-slate-800">
              {{ $fmt($k->nominal ?? 0) }}
            </td>

            <td class="px-3 py-2">
              {{ $dt($k->tanggal_periode) }}
            </td>

            <td class="px-3 py-2">
              @if($st === 'paid')
                <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-semibold">PAID</span>
              @elseif($st === 'approved')
                <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 text-[11px] font-semibold">APPROVED</span>
              @else
                <span class="px-2 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-semibold">{{ strtoupper($st) }}</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-3 py-6 text-center text-slate-500">
              Tidak ada data komisi.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- ===== Mobile Cards (tetap) ===== --}}
  <div class="md:hidden mt-4 divide-y divide-slate-100 border border-slate-200 rounded-lg">
    @forelse($rows as $k)
      @php $st = strtolower($k->status ?? '-'); @endphp
      <div class="p-3 space-y-2">
        <div class="flex items-start justify-between gap-2">
          <div>
            <div class="text-sm font-semibold text-slate-800">
              #{{ $k->id }} · {{ $k->agent?->nama ?? '-' }}
            </div>
            <div class="text-[11px] text-slate-500">
              {{ $k->agent?->jabatan ?? '-' }} · Periode: {{ $dt($k->tanggal_periode) }}
            </div>
          </div>
          <div class="text-right">
            <div class="text-[11px] text-slate-500">Nominal</div>
            <div class="text-sm font-semibold text-slate-800">{{ $fmt($k->nominal ?? 0) }}</div>
          </div>
        </div>

        <div class="flex items-center justify-between text-[11px]">
          <div class="text-slate-600">
            Persen: <span class="font-medium">
              {{ $k->persen_komisi !== null ? rtrim(rtrim(number_format((float)$k->persen_komisi, 2, '.', ''), '0'), '.') . '%' : '-' }}
            </span>
          </div>
          <div>
            @if($st === 'paid')
              <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold">PAID</span>
            @elseif($st === 'approved')
              <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-700 font-semibold">APPROVED</span>
            @else
              <span class="px-2 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold">{{ strtoupper($st) }}</span>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="p-4 text-sm text-slate-500">Tidak ada data.</div>
    @endforelse
  </div>

  <div class="mt-3">
    {{ $rows->links() }}
  </div>
</div>

{{-- =========================
     PENGAJUAN DEPOSIT
========================= --}}
<div class="bg-white border border-slate-100 rounded-xl p-4">
  <div class="flex items-start justify-between gap-3">
    <div>
      <h2 class="text-sm font-semibold text-slate-800">Pengajuan Deposit</h2>
      <p class="text-xs text-slate-500 mt-1">
        Menampilkan pengajuan deposit berdasarkan Agent yang Anda akses (RM: Anda sendiri, BDP: Anda + RM bawahan).
      </p>
    </div>

    <form method="GET" class="flex items-end gap-2">
      <input type="hidden" name="status" value="{{ $qStatus }}">
      <input type="hidden" name="bulan" value="{{ $qBulan }}">

      <div>
        <label class="block text-[11px] font-medium text-slate-700 mb-1">Status Deposit</label>
        <select name="d_status"
                class="border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
          <option value="all" {{ ($dStatus ?? 'all')==='all' ? 'selected' : '' }}>Semua</option>
          <option value="pending" {{ ($dStatus ?? '')==='pending' ? 'selected' : '' }}>Pending</option>
          <option value="approved" {{ ($dStatus ?? '')==='approved' ? 'selected' : '' }}>Approved</option>
          <option value="rejected" {{ ($dStatus ?? '')==='rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
      </div>

      <button class="px-4 py-2 rounded-lg bg-slate-800 text-white text-xs hover:bg-slate-900">
        Terapkan
      </button>
    </form>
  </div>

  {{-- Desktop table --}}
  <div class="hidden md:block mt-4 overflow-x-auto rounded-lg border border-slate-200">
    <table class="w-full text-xs">
      <thead class="bg-slate-50 text-slate-600">
        <tr>
          <th class="px-3 py-2 text-left">ID</th>
          <th class="px-3 py-2 text-left">Nasabah</th>
          <th class="px-3 py-2 text-left">Agent</th>
          <th class="px-3 py-2 text-right">Nominal</th>
          <th class="px-3 py-2 text-left">Tgl Mulai</th>
          <th class="px-3 py-2 text-left">Tenor</th>
          <th class="px-3 py-2 text-left">Jatuh Tempo</th>
          <th class="px-3 py-2 text-left">Status</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($depositRows as $d)
          @php
            $status = strtolower($d->status ?? 'pending');
          @endphp
          <tr class="hover:bg-slate-50/50">
            <td class="px-3 py-2 font-semibold text-slate-800">#{{ $d->id }}</td>

            <td class="px-3 py-2">
              <div class="font-semibold text-slate-800">
                {{ $d->nasabah?->nama ?? '-' }}
              </div>
              <div class="text-[11px] text-slate-500">
                {{ $d->nasabah?->kode_nasabah ?? '-' }}
              </div>
            </td>

            <td class="px-3 py-2">
              <div class="font-semibold text-slate-800">
                {{ $d->agent?->nama ?? '-' }}
              </div>
              <div class="text-[11px] text-slate-500">
                {{ $d->agent?->kode_agent ?? '-' }} · {{ $d->agent?->jabatan ?? '-' }}
              </div>
            </td>

            <td class="px-3 py-2 text-right font-semibold text-slate-800">
              {{ $fmt($d->nominal ?? 0) }}
            </td>

            <td class="px-3 py-2">{{ $dt($d->tanggal_mulai) }}</td>
            <td class="px-3 py-2">{{ $d->tenor ? $d->tenor.' bln' : '-' }}</td>
            <td class="px-3 py-2">{{ $dt($d->tanggal_tempo) }}</td>

            <td class="px-3 py-2">
              @if($status === 'approved')
                <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-semibold">APPROVED</span>
              @elseif($status === 'rejected')
                <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-[11px] font-semibold">REJECTED</span>
              @else
                <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 text-[11px] font-semibold">PENDING</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="px-3 py-6 text-center text-slate-500">
              Tidak ada pengajuan deposit.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Mobile cards --}}
  <div class="md:hidden mt-4 divide-y divide-slate-100 border border-slate-200 rounded-lg">
    @forelse($depositRows as $d)
      @php $status = strtolower($d->status ?? 'pending'); @endphp
      <div class="p-3 space-y-2">
        <div class="flex items-start justify-between gap-2">
          <div>
            <div class="text-sm font-semibold text-slate-800">#{{ $d->id }} · {{ $d->nasabah?->nama ?? '-' }}</div>
            <div class="text-[11px] text-slate-500">
              Nasabah: {{ $d->nasabah?->kode_nasabah ?? '-' }}
            </div>
            <div class="text-[11px] text-slate-500">
              Agent: {{ $d->agent?->nama ?? '-' }} ({{ $d->agent?->jabatan ?? '-' }})
            </div>
          </div>
          <div class="text-right">
            <div class="text-[11px] text-slate-500">Nominal</div>
            <div class="text-sm font-semibold text-slate-800">{{ $fmt($d->nominal ?? 0) }}</div>
          </div>
        </div>

        <div class="flex items-center justify-between text-[11px] text-slate-600">
          <div>Mulai: <span class="font-medium">{{ $dt($d->tanggal_mulai) }}</span></div>
          <div>Tenor: <span class="font-medium">{{ $d->tenor ? $d->tenor.' bln' : '-' }}</span></div>
        </div>

        <div class="flex items-center justify-between text-[11px]">
          <div class="text-slate-600">Tempo: <span class="font-medium">{{ $dt($d->tanggal_tempo) }}</span></div>
          <div>
            @if($status === 'approved')
              <span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 font-semibold">APPROVED</span>
            @elseif($status === 'rejected')
              <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 font-semibold">REJECTED</span>
            @else
              <span class="px-2 py-1 rounded-full bg-amber-100 text-amber-700 font-semibold">PENDING</span>
            @endif
          </div>
        </div>
      </div>
    @empty
      <div class="p-4 text-sm text-slate-500">Tidak ada data.</div>
    @endforelse
  </div>

  <div class="mt-3">
    {{ $depositRows->links() }}
  </div>
</div>

@endsection
