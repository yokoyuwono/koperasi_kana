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
        <h2 class="text-xl font-semibold text-slate-800">Detail Deposito (COA)</h2>
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
                    <input value="{{ $deposit->tanggal_transaksi }}" readonly
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50">
                    </div>

                    <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tenor (bulan)</label>
                    <input value="{{ $deposit->tenor }}" readonly
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50">
                    </div>

                    <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Mulai</label>
                    <input value="{{ $deposit->tanggal_mulai }}" readonly
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50">
                    </div>

                    <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Jatuh Tempo</label>
                    <input value="{{ $deposit->tanggal_tempo }}" readonly
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
                    <div class="text-xs text-slate-600">Dokumen Pendukung</div>
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
                    <span class="font-semibold">Catatan Admin:</span> {{ $deposit->catatan_admin }}
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
                {{-- Perhitungan komisi sistem (ringkas) --}}
                <div class="border border-slate-200 rounded-xl p-4 bg-slate-50">
                <div class="text-sm font-semibold text-slate-800">Perhitungan Komisi Sistem</div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 text-sm">
                    <div>
                    <div class="text-xs text-slate-500">Komisi RM (sistem)</div>
                    <div class="font-semibold text-slate-800">
                        @isset($rmSystem) {{ $rmSystem }}% @else - @endisset
                    </div>
                    </div>
                    <div>
                    <div class="text-xs text-slate-500">Komisi BDP (sistem)</div>
                    <div class="font-semibold text-slate-800">
                        @isset($bdpSystem) {{ $bdpSystem }}% @else - @endisset
                    </div>
                    </div>
                </div>
                </div>

                {{-- Komisi Final COA --}}
                <div class="border border-blue-200 rounded-xl p-4 bg-blue-50">
                <div class="flex items-start justify-between gap-3">
                    <div>
                    <div class="text-sm font-semibold text-slate-800">Komisi Final (COA)</div>
                    <div class="text-xs text-slate-600 mt-1">
                        Ubah persen komisi. Nominal akan dihitung otomatis dari nominal deposit.
                    </div>
                    </div>

                    @if(!empty($deposit->BDP_ref))
                    <div class="text-[11px] px-2 py-1 rounded-lg bg-white border border-blue-200 text-blue-700">
                        BDP Referral Aktif
                    </div>
                    @endif
                </div>

                {{-- daftar komisi --}}
                <div class="mt-4 space-y-3">
                    @foreach($komisiList as $k)
                    @php
                        $agentName = $k->agent->nama ?? ('Agent #' . $k->id_agent);
                        $jabatan = $k->agent->jabatan ?? '-';
                        $persen = (float)($k->persen_komisi ?? 0);
                        $nominalPreview = $depositNominal * ($persen/100);
                    @endphp

                    <div class="bg-white border border-slate-200 rounded-xl p-3">
                        <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-slate-800">{{ $agentName }}</div>
                            <div class="text-[11px] text-slate-500">Jabatan: {{ $jabatan }} · ID: {{ $k->id_agent }}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-[11px] text-slate-500">Nominal</div>
                            <div class="font-semibold text-slate-800" id="nominal-{{ $k->id }}">
                            {{ $fmt($nominalPreview) }}
                            </div>
                        </div>
                        </div>

                        <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-700 mb-1">% Komisi</label>
                            <input
                            type="number"
                            step="0.1"
                            min="0"
                            max="100"
                            name="komisi[{{ $k->id }}]"
                            value="{{ old('komisi.'.$k->id, $k->persen_komisi) }}"
                            data-komisi-id="{{ $k->id }}"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs komisi-input">
                        </div>

                        <div class="md:col-span-2">
                            <div class="text-xs text-slate-500">Status komisi</div>
                            <div class="text-xs font-semibold text-slate-700 uppercase mt-1">{{ $k->status }}</div>

                            {{-- info kecil untuk referral (opsional) --}}
                            @if(!empty($deposit->BDP_ref) && $k->persen_komisi == 0.5)
                            <div class="text-[11px] text-slate-500 mt-2">
                                Komisi ini kemungkinan referral BDP (default 0.5%).
                            </div>
                            @endif
                        </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- warning jika COA ubah dari referensi sistem (opsional, purely UI) --}}
                <div class="mt-4 text-[11px] text-amber-700 bg-amber-50 border border-amber-200 rounded-xl p-3">
                    Jika komisi final berbeda dari referensi sistem, pastikan ada dokumen pendukung/catatan yang cukup.
                </div>
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
  const depositNominal = {{ (float)$depositNominal }};

  function rupiah(n){
    n = Number(n || 0);
    return 'Rp ' + n.toLocaleString('id-ID', {maximumFractionDigits: 0});
  }

  document.querySelectorAll('.komisi-input').forEach(inp => {
    inp.addEventListener('input', (e) => {
      const persen = Number(e.target.value || 0);
      const kid = e.target.dataset.komisiId;
      const nominal = depositNominal * (persen / 100);
      const target = document.getElementById('nominal-' + kid);
      if (target) target.textContent = rupiah(nominal);
    });
  });
</script>
@endsection
