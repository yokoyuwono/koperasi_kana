@extends('layouts.app')

@section('content')
@php
    $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp

<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Laporan Komisi Agent</h1>
            <p class="text-xs text-slate-500 mt-1">
                Hanya komisi dari deposit yang sudah <span class="font-semibold">APPROVED oleh COA</span>.
            </p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('komisi.report') }}"
          class="bg-white border border-slate-100 rounded-xl p-4 space-y-3">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Bulan</label>
                <input type="month" name="month" value="{{ $month }}"
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Periode Approval COA</label>
                <select name="periode" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    <option value="all" {{ $periode==='all' ? 'selected' : '' }}>Semua (Periode 1 + 2)</option>
                    <option value="1" {{ $periode==='1' ? 'selected' : '' }}>Periode 1 (H0 akhir bulan lalu s/d tgl 14)</option>
                    <option value="2" {{ $periode==='2' ? 'selected' : '' }}>Periode 2 (tgl 15 s/d H-1 akhir bulan)</option>
                </select>
                <div class="text-[11px] text-slate-500 mt-1">
                    Range terpilih: <span class="font-semibold">{{ $start }}</span> s/d <span class="font-semibold">{{ $end }}</span>
                </div>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit"
                        class="w-full md:w-auto inline-flex justify-center px-4 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                    Terapkan
                </button>
                <button type="button" onclick="openPayModal()" class="w-full md:w-auto inline-flex justify-center px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-xs hover:bg-slate-50">
                    Pembayaran
                </button>
                <!-- <button type="button" class="w-full md:w-auto inline-flex justify-center px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-xs hover:bg-slate-50">
                    Pembayaran
                </button> -->
                <a href="{{ route('komisi.report.export', parameters: ['month'=>$month, 'periode'=>$periode]) }}"
                   class="w-full md:w-auto inline-flex justify-center px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-xs hover:bg-slate-50">
                    Export Excel
                </a>
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="overflow-x-auto hidden md:block">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 text-slate-600">
                  <tr>
                    <th class="text-left px-4 py-3">ID Komisi</th>
                    <th class="text-left px-4 py-3">Kode Agent</th>
                    <th class="text-left px-4 py-3">Nama</th>
                    <th class="text-left px-4 py-3">Jabatan</th>
                    <th class="text-left px-4 py-3">Jenis Komisi</th>
                    <th class="text-left px-4 py-3">Kode Bliyet</th>
                    <th class="text-right px-4 py-3">Nominal</th>
                    <th class="text-left px-4 py-3">Tanggal Pembayaran</th>
                    <th class="text-left px-4 py-3">Status Pembayaran</th>
                  </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                  @forelse($rows as $r)
                    @php
                      $isPaid = !is_null($r->tanggal_pembayaran);
                    @endphp
                    <tr class="hover:bg-slate-50/50">
                      <td class="px-4 py-3 font-semibold text-slate-800">#{{ $r->komisi_id }}</td>
                      <td class="px-4 py-3">{{ $r->kode_agent }}</td>
                      <td class="px-4 py-3">
                        <div class="font-semibold text-slate-800">{{ $r->agent_nama }}</div>
                      </td>
                      <td class="px-4 py-3">{{ $r->jabatan }}</td>
                      <td class="px-4 py-3">{{ $r->jenis_komisi }}</td>
                      <td class="px-4 py-3">{{ $r->no_bilyet }}</td>
                      <td class="px-4 py-3 text-right font-semibold">{{ $fmt($r->nominal) }}</td>
                      <td class="px-4 py-3">
                        {{ $r->tanggal_pembayaran ? \Carbon\Carbon::parse($r->tanggal_pembayaran)->format('d-m-Y') : '-' }}
                      </td>
                      <td class="px-4 py-3">
                        @if($isPaid)
                          <span class="inline-flex items-center px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold">
                            PAID
                          </span>
                        @else
                          <span class="inline-flex items-center px-2 py-1 rounded-full bg-amber-50 text-amber-700 text-[11px] font-semibold">
                            UNPAID
                          </span>
                        @endif
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="9" class="px-4 py-6 text-center text-slate-500">
                        Tidak ada data pada filter ini.
                      </td>
                    </tr>
                  @endforelse
                </tbody>
            </table>
            <div class="mt-3 px-4 pb-4">
              {{ $rows->links() }}
            </div>

        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-slate-100">
        @forelse($rows as $r)
          @php $isPaid = !is_null($r->tanggal_pembayaran); @endphp
          <div class="p-4 space-y-2">
            <div class="flex items-start justify-between gap-2">
              <div>
                <div class="text-sm font-semibold text-slate-800">#{{ $r->komisi_id }} · {{ $r->agent_nama }}</div>
                <div class="text-[11px] text-slate-500">{{ $r->kode_agent }} · {{ $r->jabatan }} · {{ $r->jenis_komisi }}</div>
                <div class="text-[11px] text-slate-500">Bliyet: {{ $r->kode_bliyet ?? '-' }}</div>
              </div>
              <div class="text-right">
                <div class="text-[11px] text-slate-500">Nominal</div>
                <div class="text-sm font-semibold text-slate-800">{{ $fmt($r->nominal) }}</div>
              </div>
            </div>

            <div class="flex items-center justify-between text-xs">
              <div class="text-slate-600">Tgl bayar: <span class="font-medium">{{ $r->tanggal_pembayaran ? \Carbon\Carbon::parse($r->tanggal_pembayaran)->format('d-m-Y')  : '-' }}</span></div>
              @if($isPaid)
                <span class="px-2 py-1 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold">PAID</span>
              @else
                <span class="px-2 py-1 rounded-full bg-amber-50 text-amber-700 text-[11px] font-semibold">UNPAID</span>
              @endif
            </div>
          </div>
        @empty
          <div class="p-4 text-xs text-slate-500">Tidak ada data.</div>
        @endforelse
      </div>

    </div>
</div>

{{-- Modal Pembayaran --}}
<div id="payModal" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/40" onclick="closePayModal()"></div>

  <div class="relative mx-auto mt-20 w-[92%] max-w-xl bg-white rounded-xl shadow-lg border border-slate-100">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
      <div>
        <div class="text-sm font-semibold text-slate-800">Pembayaran Komisi</div>
        <div class="text-[11px] text-slate-500">
          Filter aktif: <span class="font-semibold">{{ $start }}</span> s/d <span class="font-semibold">{{ $end }}</span>
        </div>
      </div>
      <button type="button" class="text-slate-500 hover:text-slate-700" onclick="closePayModal()">✕</button>
    </div>

    <form method="POST" action="{{ route('komisi.report.pay') }}" class="p-4 space-y-3">
      @csrf

      {{-- kirim filter agar controller update sesuai range yang sama --}}
      <input type="hidden" name="month" value="{{ $month }}">
      <input type="hidden" name="periode" value="{{ $periode }}">
  
      <div>
        <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Pembayaran</label>
        <input type="date" name="tanggal_pembayaran" value="{{ now()->toDateString() }}"
               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
        @error('tanggal_pembayaran') <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div class="flex items-center justify-between">
        <div class="text-xs font-semibold text-slate-700">Pilih Komisi</div>
        <div class="flex gap-3">
          <button type="button" class="text-xs text-blue-600 hover:underline" onclick="toggleCheckAll(true)">Check all</button>
          <button type="button" class="text-xs text-slate-600 hover:underline" onclick="toggleCheckAll(false)">Uncheck all</button>
        </div>
      </div>

      <div class="max-h-72 overflow-auto border border-slate-100 rounded-lg divide-y divide-slate-100">
      @foreach($rows as $r)
        @php $isPaid = !is_null($r->tanggal_pembayaran); @endphp

        <label class="flex items-start gap-3 p-3">
          <input type="checkbox"
                class="mt-1 pay-check"
                name="komisi_ids[]"
                value="{{ $r->komisi_id }}"
                {{ $isPaid ? '' : 'checked' }}>
          <div class="min-w-0">
            <div class="text-xs font-semibold text-slate-800 truncate">
              #{{ $r->komisi_id }} · {{ $r->agent_nama }}
              <span class="text-slate-500 font-normal">({{ $r->kode_agent }})</span>
            </div>
            <div class="text-[11px] text-slate-500">
              {{ $r->jabatan }} · {{ $r->jenis_komisi }} · Bliyet: {{ $r->kode_bliyet ?? '-' }}
              · Nominal: <span class="font-semibold">{{ $fmt($r->nominal) }}</span>
              · Status:
              @if($isPaid)
                <span class="font-semibold text-emerald-700">PAID</span>
              @else
                <span class="font-semibold text-amber-700">UNPAID</span>
              @endif
            </div>
          </div>
        </label>
      @endforeach
    </div>

    @error('komisi_ids') <p class="text-[11px] text-red-600">{{ $message }}</p> @enderror

      <div class="flex gap-2 pt-2">
        <button type="button"
                class="flex-1 px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-xs hover:bg-slate-50"
                onclick="closePayModal()">
          Batal
        </button>

        <button type="submit"
                onclick="return confirm('Yakin set komisi terpilih menjadi PAID?')"
                class="flex-1 px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700">
          Submit Pembayaran
        </button>
      </div>
    </form>
  </div>
</div>

@endsection

<script>
function openPayModal() {
  document.getElementById('payModal')?.classList.remove('hidden');
}
function closePayModal() {
  document.getElementById('payModal')?.classList.add('hidden');
}
function toggleCheckAll(state) {
  document.querySelectorAll('.pay-check').forEach(cb => cb.checked = state);
}
</script>
