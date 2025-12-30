@extends('layouts.app') {{-- sesuaikan dengan layout utama kamu --}}

@section('content')
@php
  use Illuminate\Support\Facades\Storage;

  $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
  $depositNominal = (float)($deposit->nominal ?? 0);
@endphp
<div class="space-y-4">

    {{-- Header --}}
    <div>
        <h2 class="text-xl font-semibold text-slate-800">Detail Pengajuan Komisi (COA)</h2>
        <p class="text-xs text-slate-500 mt-1">
            Periksa data dan komisi sebelum memberikan persetujuan.
        </p>
    </div>

    

    
        {{-- HANYA jika pending: tampilkan form Approve/Reject --}}
        <form method="POST" action="{{ route('coa.deposits.approve', $deposit) }}" class="space-y-4">
            @csrf

            {{-- ====== BLOCK FORM DEPOSIT (MIRIP ADMIN EDIT, READONLY) ====== --}}
            <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <div class="font-semibold text-slate-800">Data Deposit</div>
                <div class="text-xs text-slate-500 mt-1">Data ini berasal dari input Admin (read-only).</div>
            </div>

            <div class="p-4 space-y-4">
                {{-- Info Pihak Terkait (Nasabah & Agent) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 border-b border-slate-100 pb-4">
                    {{-- Nasabah --}}
                    <div>
                        <div class="text-sm font-semibold text-slate-800 mb-2 flex items-center gap-2">
                            <span class="p-1.5 bg-blue-50 text-blue-600 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </span>
                            Informasi Nasabah
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="text-slate-500">Nama Nasabah</div>
                            <div class="font-medium text-slate-800">{{ $deposit->nasabah->nama ?? '-' }}</div>
                            
                            <div class="text-slate-500">NIK</div>
                            <div class="font-medium text-slate-800">{{ $deposit->nasabah->nik ?? '-' }}</div>

                            <div class="text-slate-500">No. Telepon</div>
                            <div class="font-medium text-slate-800">{{ $deposit->nasabah->no_hp ?? '-' }}</div>
                        </div>
                    </div>

                    {{-- Agent --}}
                    <div>
                        <div class="text-sm font-semibold text-slate-800 mb-2 flex items-center gap-2">
                             <span class="p-1.5 bg-emerald-50 text-emerald-600 rounded-lg">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            Informasi Marketing (Agent)
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="text-slate-500">Nama Agent</div>
                            <div class="font-medium text-slate-800">{{ $deposit->agent->nama ?? '-' }}</div>
                            
                            <div class="text-slate-500">Jabatan</div>
                            <div class="font-medium text-slate-800">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 border border-slate-200 text-[10px] font-semibold text-slate-600">
                                    {{ $deposit->agent->jabatan ?? '-' }}
                                </span>
                            </div>

                             <div class="text-slate-500">Kode Agent</div>
                            <div class="font-medium text-slate-800">{{ $deposit->agent->kode_agent ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                {{-- Info Bilyet & Tanggal --}}
                <div>
                <div class="text-sm font-semibold text-slate-800 mb-2">Info Bilyet & Tanggal</div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">No Bilyet</label>
                    <input value="{{ $deposit->no_bilyet }}" readonly
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50">
                    </div>

                    <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Transaksi</label>
                    <input value="{{ $deposit->tanggal_transaksi->format('d-m-Y') }}" readonly
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50">
                    </div>

                    <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tenor (bulan)</label>
                    <input value="{{ $deposit->tenor }}" readonly
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50">
                    </div>

                    <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                    <input value="{{ $deposit->tanggal_mulai->format('d-m-Y') }}" readonly
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50">
                    </div>

                    <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Jatuh Tempo</label>
                    <input value="{{ $deposit->tanggal_tempo->format('d-m-Y') }}" readonly
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50">
                    </div>

                    <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nominal</label>
                    <input value="{{ $fmt($depositNominal) }}" readonly
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50 font-semibold">
                    </div>
                </div>
                </div>

                {{-- Dokumen --}}
                <div>
                <div class="text-sm font-semibold text-slate-800 mb-2">Dokumen</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="border border-slate-200 rounded-lg p-3 bg-slate-50">
                    <div class="text-xs text-slate-600">Bukti Transfer</div>
                    @if(!empty($deposit->bukti_transfer))
                        <a href="{{ asset('storage/'.$deposit->bukti_transfer) }}" target="_blank"
                        class="text-xs text-blue-600 hover:underline">Lihat Bukti Transfer</a>
                        <img src="{{ Storage::url($deposit->bukti_transfer) }}" alt="Bukti Transfer" class="mt-2 max-h-48 object-contain border border-slate-200 rounded-lg">
                    @else
                        <div class="text-xs text-slate-400">Tidak ada file</div>
                    @endif
                    </div>

                    <div class="border border-slate-200 rounded-lg p-3 bg-slate-50">
                    <div class="text-xs text-slate-600">Dokumen Bukti Special Rate</div>
                    @if(!empty($deposit->dokumen_pendukung))
                        <a href="{{ asset('storage/'.$deposit->dokumen_pendukung) }}" target="_blank"
                        class="text-xs text-blue-600 hover:underline">Lihat Dokumen</a>
                        <img src="{{ Storage::url($deposit->dokumen_pendukung) }}" alt="Bukti Transfer" class="mt-2 max-h-48 object-contain border border-slate-200 rounded-lg">
                    @else
                        <div class="text-xs text-slate-400">Tidak ada file</div>
                    @endif
                    </div>
                </div>

                @if(!empty($deposit->catatan_admin))
                    <div class="mt-3 text-xs text-slate-700">
                    <h4 class="font-semibold">Catatan Admin:</h4> 
                    <p>{{ $deposit->catatan_admin }}</p>
                    </div>
                @endif
                </div>
            </div>
            </div>

            {{-- ====== KOMISI: Perhitungan Sistem (read-only) + Final COA (editable) ====== --}}
            <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
            <div class="p-4 border-b border-slate-100">
                <div class="font-semibold text-slate-800">Komisi</div>
                <div class="text-xs text-slate-500 mt-1">COA dapat mengubah persen komisi (final).</div>
            </div>

            <div class="p-4 space-y-4">
                
        {{-- Perhitungan komisi (skema baru) --}}
        @php
            $depositNominal = (float) ($deposit->nominal ?? 0);
            $isPengajuRm = strtoupper($jabatanPengaju ?? '') === 'RM';
            $rmVal  = old('rm_percent', $rmPercentCurrent);
            $bdpVal = old('bdp_percent', $bdpPercentCurrent);
            $refEnabledOld = old('bdp_ref_enabled', $bdpRefEnabled ? 1 : 0);
            $refVal = old('bdp_ref_percent', $bdpRefPercentCurrent);
        @endphp

        <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-sm font-semibold text-slate-800">Perhitungan Komisi Sistem</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">
                        Nilai default dari sistem. COA boleh ubah persen sebelum approve.
                    </div>
                </div>

                @if($refMode === 'forced')
                    <span class="text-[11px] px-2 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700">
                        BDP Referral Wajib
                    </span>
                @elseif($refMode === 'optional')
                    <span class="text-[11px] px-2 py-1 rounded-lg bg-slate-50 border border-slate-200 text-slate-700">
                        BDP Referral Opsional
                    </span>
                @endif
            </div>

            <div class="p-4 space-y-4">
                {{-- RM --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                    <div class="md:col-span-4">
                        <div class="text-xs font-semibold text-slate-800">Komisi RM</div>
                        <div class="text-[11px] text-slate-500">Default: {{ $rmSystem === null ? '-' : number_format($rmSystem, 2, ',', '.') . '%' }}</div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Persen Final RM</label>
                        <input id="rm_percent" name="rm_percent" type="number" step="0.01" max="100"
                               value="{{ $rmVal }}"
                               @if(!$isPengajuRm) disabled @endif
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-slate-50 disabled:text-slate-400">
                        @error('rm_percent') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                        @if(!$isPengajuRm)
                            <p class="text-[11px] text-slate-500 mt-1">Pengaju BDP → RM tidak dapat komisi.</p>
                        @endif
                    </div>

                    <div class="md:col-span-4">
                        <div class="text-xs font-medium text-slate-700 mb-1">Nominal RM (preview)</div>
                        <div id="rm_nominal_preview" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50 text-slate-700">-</div>
                    </div>
                </div>

                {{-- BDP --}}
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                    <div class="md:col-span-4">
                        <div class="text-xs font-semibold text-slate-800">Komisi BDP</div>
                        <div class="text-[11px] text-slate-500">Default: {{ number_format((float)$bdpSystem, 2, ',', '.') }}%</div>
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-xs font-medium text-slate-700 mb-1">Persen Final BDP <span class="text-red-500">*</span></label>
                        <input id="bdp_percent" name="bdp_percent" type="number" step="0.01"  max="100"
                               value="{{ $bdpVal }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        @error('bdp_percent') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-4">
                        <div class="text-xs font-medium text-slate-700 mb-1">Nominal BDP (preview)</div>
                        <div id="bdp_nominal_preview" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50 text-slate-700">-</div>
                    </div>
                </div>

                {{-- BDP Referral --}}
                <div class="border border-slate-200 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs font-semibold text-slate-800">BDP Referral (BDP get BDP)</div>
                            <div class="text-[11px] text-slate-500">Default: {{ number_format((float)$bdpRefSystem, 2, ',', '.') }}%</div>
                        </div>

                        <label class="flex items-center gap-2 text-xs text-slate-700 select-none">
                            <input id="bdp_ref_enabled" type="checkbox" name="bdp_ref_enabled" value="1"
                                   @checked((int)$refEnabledOld === 1)
                                   @if($refMode === 'forced') checked disabled @endif
                                   class="rounded border-slate-300">
                            Aktifkan BDP Ref
                        </label>
                    </div>

                    <div id="bdp_ref_section" class="mt-3 grid grid-cols-1 md:grid-cols-12 gap-3 items-start">
                        <div class="md:col-span-4">
                            <label class="block text-xs font-medium text-slate-700 mb-1">Persen Final BDP Ref</label>
                            <input id="bdp_ref_percent" name="bdp_ref_percent" type="number" step="0.01"  max="100"
                                   value="{{ $bdpRefSystem }}"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            @error('bdp_ref_percent') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-4">
                            <div class="text-xs font-medium text-slate-700 mb-1">Nominal BDP Ref (preview)</div>
                            <div id="bdp_ref_nominal_preview" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50 text-slate-700">-</div>
                        </div>

                        <div class="md:col-span-4">
                            <div class="text-[11px] text-slate-500 mt-6">
                                Jika tidak aktif, komisi referral tidak dibuat.
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Catatan COA --}}
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Catatan COA (opsional)</label>
                    <textarea name="catatan" rows="3"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs"
                              placeholder="Catatan / alasan penyesuaian komisi...">{{ old('catatan', $deposit->catatan) }}</textarea>
                    @error('catatan') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-3">
                    
                </div>
            </div>
        </div>


{{-- ACTIONS --}}
        @if($deposit->status === 'pending')
            <div class="flex flex-col md:flex-row gap-2">
                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700">
                    Approve Deposit
                </button>
                
                
                <a href="{{ url()->previous() }}"
                    class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-xs hover:bg-slate-50 text-center">
                    Kembali
                </a>
            </div>
        @endif
        
        </form>
        {{-- REJECT --}}
        @if($deposit->status === 'pending')
        <form method="POST" action="{{ route('coa.deposits.reject', $deposit) }}"
                class="bg-white border border-slate-100 rounded-xl p-4 space-y-3">
            @csrf
            <div class="font-semibold text-slate-800">Reject Deposit</div>

            <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Alasan Reject</label>
            <textarea name="catatan" rows="3"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs"
                        placeholder="Masukkan alasan penolakan...">{{ old('catatan') }}</textarea>
            </div>

            <button onclick="return confirm('Yakin tolak deposit ini?')"
                    class="px-4 py-2 rounded-lg bg-red-600 text-white text-xs hover:bg-red-700">
            Reject
            </button>
        </form>
         @else
         <div class="flex flex-col md:flex-row gap-2">
            <a href="{{ url()->previous() }}"
                class="px-4 py-2 rounded-lg border border-slate-200 text-slate-700 text-xs hover:bg-slate-50 text-center">
                Kembali
            </a>
        </div>
        @endif
    </div>
    </div>
</div>

<script>
  const depositNominal = {{ (float)($deposit->nominal ?? 0) }};

  function rupiah(n){
    n = Number(n || 0);
    return 'Rp ' + n.toLocaleString('id-ID', {maximumFractionDigits: 0});
  }

  function toNum(v){
    const x = parseFloat(v);
    return Number.isFinite(x) ? x : 0;
  }

  const isPengajuRm = {{ strtoupper($jabatanPengaju ?? '') === 'RM' ? 'true' : 'false' }};
  const refMode = "{{ $refMode ?? 'none' }}"; // optional|forced|none

  const rmEl = document.getElementById('rm_percent');
  const bdpEl = document.getElementById('bdp_percent');
  const refCb = document.getElementById('bdp_ref_enabled');
  const refEl = document.getElementById('bdp_ref_percent');
  const refSection = document.getElementById('bdp_ref_section');

  const rmPrev  = document.getElementById('rm_nominal_preview');
  const bdpPrev = document.getElementById('bdp_nominal_preview');
  const refPrev = document.getElementById('bdp_ref_nominal_preview');

  function setRefVisibility() {
    const enabled = refCb && refCb.checked;
    if (refSection) refSection.style.display = enabled ? '' : 'none';
  }

  function recalcPreview() {
    const rmP  = isPengajuRm && rmEl ? toNum(rmEl.value) : 0;
    const bdpP = bdpEl ? toNum(bdpEl.value) : 0;
    const refP = (refCb && refCb.checked && refEl) ? toNum(refEl.value) : 0;

    if (rmPrev)  rmPrev.textContent  = isPengajuRm ? rupiah(depositNominal * rmP / 100) : '-';
    if (bdpPrev) bdpPrev.textContent = rupiah(depositNominal * bdpP / 100);
    if (refPrev) refPrev.textContent = (refCb && refCb.checked) ? rupiah(depositNominal * refP / 100) : '-';
  }

  // init
  if (refMode === 'forced' && refCb) {
    refCb.checked = true;
    refCb.disabled = true;
  }
  if (refMode === 'none' && refCb) {
    refCb.checked = false;
  }

  setRefVisibility();
  recalcPreview();

  [rmEl, bdpEl, refEl].forEach(el => {
    if (!el) return;
    el.addEventListener('input', recalcPreview);
  });

  if (refCb) {
    refCb.addEventListener('change', () => {
      setRefVisibility();
      recalcPreview();
    });
  }
</script>
@endsection
