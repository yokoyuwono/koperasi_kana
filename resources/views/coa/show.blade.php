@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Detail Deposito untuk Approval</h2>
    <p class="text-xs text-slate-500 mt-1">
        COA memeriksa kebenaran data dan komisi sebelum menyetujui.
    </p>
</div>

<div class="space-y-4">
    {{-- Info utama --}}
    <div class="bg-white border border-slate-100 rounded-xl p-4 space-y-2 text-xs">
        <div class="flex flex-col md:flex-row md:justify-between gap-2">
            <div>
                <div class="text-[11px] text-slate-500">Nasabah</div>
                <div class="font-semibold text-slate-800">
                    {{ $deposit->nasabah->nama ?? '-' }}
                </div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Agent</div>
                <div class="font-semibold text-slate-800">
                    {{ $deposit->agent->nama ?? '-' }} ({{ $deposit->agent->jabatan ?? '-' }})
                </div>
                @if($deposit->agent && $deposit->agent->atasan)
                    <div class="text-[11px] text-slate-500">
                        Atasan BDP: {{ $deposit->agent->atasan->nama }}
                    </div>
                @endif
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Nominal</div>
                <div class="font-mono text-slate-800">
                    Rp {{ number_format($deposit->nominal, 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Panel komisi ringkas --}}
    <div class="grid md:grid-cols-2 gap-4">
        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs">
            <h3 class="text-sm font-semibold text-slate-800 mb-2">Komisi Sistem</h3>
            <ul class="space-y-1">
                <li>
                    RM: {{ number_format($rmSystem, 1) }}%
                    (≈ Rp {{ number_format($deposit->nominal * $rmSystem / 100, 0, ',', '.') }})
                </li>
                <li>
                    BDP: {{ number_format($bdpSystem, 1) }}%
                    (≈ Rp {{ number_format($deposit->nominal * $bdpSystem / 100, 0, ',', '.') }})
                </li>
            </ul>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-4 text-xs">
            <h3 class="text-sm font-semibold text-slate-800 mb-2">Komisi Final (Draft Admin)</h3>
            <ul class="space-y-1">
                @forelse($komisi as $k)
                    <li>
                        <span class="font-semibold">{{ $k->agent->nama ?? '-' }}</span>
                        ({{ $k->agent->jabatan ?? '-' }}) :
                        {{ number_format($k->persen_komisi, 1) }}%
                        (Rp {{ number_format($k->nominal, 0, ',', '.') }})
                    </li>
                @empty
                    <li class="text-slate-500 text-[11px]">
                        Belum ada draft komisi. (Cek kembali pengisian oleh admin.)
                    </li>
                @endforelse
            </ul>

            @php
                $beda = false;
                foreach ($komisi as $k) {
                    if ($k->agent && $k->agent->jabatan === 'RM' && (float)$k->persen_komisi !== (float)$rmSystem) {
                        $beda = true;
                    }
                    if ($k->agent && $k->agent->jabatan === 'BDP' && (float)$k->persen_komisi !== (float)$bdpSystem) {
                        $beda = true;
                    }
                }
            @endphp

            @if($beda)
                <div class="mt-3 rounded bg-amber-50 border border-amber-200 px-3 py-2 text-[11px] text-amber-800">
                    Rate komisi yang diusulkan admin berbeda dengan perhitungan sistem.
                    Pastikan dokumen & catatan pendukung sudah sesuai.
                </div>
            @endif
        </div>
    </div>

    {{-- Dokumen & catatan --}}
    <div class="bg-white border border-slate-100 rounded-xl p-4 text-xs space-y-3">
        <div class="grid md:grid-cols-2 gap-4">
            @if($deposit->bukti_transfer)
                <div>
                    <div class="text-[11px] text-slate-500 mb-1">Bukti Transfer</div>
                    <a href="{{ asset('storage/'.$deposit->bukti_transfer) }}" target="_blank"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200">
                        Lihat Bukti Transfer
                    </a>
                </div>
            @endif
            @if($deposit->dokumen_pendukung)
                <div>
                    <div class="text-[11px] text-slate-500 mb-1">Dokumen Pendukung</div>
                    <a href="{{ asset('storage/'.$deposit->dokumen_pendukung) }}" target="_blank"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] bg-slate-100 text-slate-700 hover:bg-slate-200">
                        Lihat Dokumen Pendukung
                    </a>
                </div>
            @endif
        </div>

        {{-- Form approve / reject --}}
        <form method="POST" action="{{ route('coa.deposits.approve', $deposit) }}" class="mt-3 space-y-3">
            @csrf
            <div>
                <label class="block text-[11px] font-medium text-slate-700 mb-1">
                    Catatan COA (wajib diisi jika menolak)
                </label>
                <textarea name="catatan" rows="3"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ old('catatan') }}</textarea>
            </div>

            <div class="flex justify-between items-center">
                <a href="{{ route('coa.deposits.index') }}"
                   class="px-3 py-1.5 rounded-lg border border-slate-200 text-[11px] text-slate-600 hover:bg-slate-50">
                    Kembali
                </a>

                <div class="flex gap-2">
                    {{-- tombol tolak --}}
                    <button formaction="{{ route('coa.deposits.reject', $deposit) }}"
                            class="px-3 py-1.5 rounded-lg bg-red-50 text-[11px] text-red-700 border border-red-200 hover:bg-red-100">
                        Tolak
                    </button>
                    {{-- tombol setujui --}}
                    <button type="submit"
                            class="px-3 py-1.5 rounded-lg bg-emerald-600 text-[11px] text-white hover:bg-emerald-700">
                        Setujui
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
