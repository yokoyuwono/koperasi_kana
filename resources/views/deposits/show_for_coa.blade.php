@extends('layouts.app')

@section('content')
<div class="mb-4 flex flex-col gap-1">
    <div class="flex items-center gap-2">
        <h2 class="text-xl font-semibold">Detail Deposito untuk Approval</h2>
        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700">
            PENDING
        </span>
    </div>
    <p class="text-xs text-slate-500">
        Periksa kembali detail deposito berikut. Anda dapat menyetujui atau menolak dengan memberikan alasan.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-6 max-w-5xl">
    {{-- Info utama --}}
    <div class="grid md:grid-cols-2 gap-4 text-sm">
        <div class="space-y-2">
            <h3 class="text-xs font-semibold text-slate-500 uppercase">Nasabah</h3>
            <div class="text-slate-800 font-semibold">
                {{ $deposit->nasabah->nama ?? '-' }}
            </div>
            <div class="text-[11px] text-slate-500">
                Kode: {{ $deposit->nasabah->kode_nasabah ?? '-' }}
            </div>
            <div class="text-[11px] text-slate-500">
                Telp: {{ $deposit->nasabah->telepon ?? '-' }}
            </div>
            <div class="text-[11px] text-slate-500">
                Email: {{ $deposit->nasabah->email ?? '-' }}
            </div>
        </div>

        <div class="space-y-2">
            <h3 class="text-xs font-semibold text-slate-500 uppercase">Agen</h3>
            <div class="text-slate-800 font-semibold">
                {{ $deposit->agent->nama ?? '-' }}
            </div>
            <div class="text-[11px] text-slate-500">
                Kode: {{ $deposit->agent->kode_agent ?? '-' }} ({{ $deposit->agent->jabatan ?? '-' }})
            </div>
            @if($deposit->BDP_ref)
                <div class="text-[11px] text-slate-500">
                    BDP Ref: {{ $deposit->BDP_ref }}
                </div>
            @endif
        </div>
    </div>

    {{-- Info deposito --}}
    <div class="grid md:grid-cols-2 gap-4 text-sm">
        <div class="space-y-2">
            <h3 class="text-xs font-semibold text-slate-500 uppercase">Info Bilyet</h3>
            <div class="flex items-center gap-2">
                <span class="text-[11px] text-slate-500">No Bilyet:</span>
                <span class="font-mono bg-slate-100 px-2 py-1 rounded text-xs">
                    {{ $deposit->no_bilyet }}
                </span>
            </div>
            <div class="text-[11px] text-slate-600">
                Tanggal Transaksi:
                {{ $deposit->tanggal_transaksi ? \Carbon\Carbon::parse($deposit->tanggal_transaksi)->format('d-m-Y') : '-' }}
            </div>
            <div class="text-[11px] text-slate-600">
                Tanggal Mulai:
                {{ $deposit->tanggal_mulai ? \Carbon\Carbon::parse($deposit->tanggal_mulai)->format('d-m-Y') : '-' }}
            </div>
            <div class="text-[11px] text-slate-600">
                Jatuh Tempo:
                {{ $deposit->tanggal_tempo ? \Carbon\Carbon::parse($deposit->tanggal_tempo)->format('d-m-Y') : '-' }}
            </div>
            <div class="text-[11px] text-slate-600">
                Tenor: {{ $deposit->tenor }} bulan
            </div>
        </div>

        <div class="space-y-2">
            <h3 class="text-xs font-semibold text-slate-500 uppercase">Nominal & Dokumen</h3>
            <div class="text-sm font-semibold text-slate-800">
                Nominal: Rp {{ number_format($deposit->nominal, 0, ',', '.') }}
            </div>
            <div class="text-[11px] text-slate-600">
                Bukti Transfer: {{ $deposit->bukti_transfer ?: '-' }}
            </div>
            <div class="text-[11px] text-slate-600">
                Dokumen Pendukung: {{ $deposit->dokumen_pendukung ?: '-' }}
            </div>
        </div>
    </div>

    {{-- Catatan admin --}}
    <div>
        <h3 class="text-xs font-semibold text-slate-500 uppercase mb-1">Catatan Admin</h3>
        <div class="text-xs text-slate-700 bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 min-h-[48px]">
            {{ $deposit->catatan_admin ?: 'Tidak ada catatan dari admin.' }}
        </div>
    </div>

    {{-- Form approve / reject --}}
    <div class="pt-4 border-t border-slate-100">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <a href="{{ route('deposits.pending') }}"
               class="px-4 py-2 text-xs border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50">
                ‹ Kembali ke daftar pending
            </a>

            <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                {{-- Approve --}}
                <form method="POST" action="{{ route('deposits.approve', $deposit) }}"
                      onsubmit="return confirm('Setujui deposito ini?')"
                      class="w-full md:w-auto">
                    @csrf
                    <button
                        class="w-full md:w-auto px-4 py-2 text-xs rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700">
                        Setujui
                    </button>
                </form>

                {{-- Reject --}}
                <form method="POST" action="{{ route('deposits.reject', $deposit) }}" class="flex-1">
                    @csrf
                    <div class="flex flex-col gap-2">
                        <textarea name="catatan" rows="2" required
                                  placeholder="Alasan penolakan (wajib diisi jika menolak)"
                                  class="w-full border border-red-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-red-500 focus:border-red-500">{{ old('catatan') }}</textarea>
                        @error('catatan') <p class="text-[11px] text-red-500">{{ $message }}</p> @enderror

                        <button
                            class="w-full md:w-auto px-4 py-2 text-xs rounded-lg bg-red-600 text-white font-medium hover:bg-red-700">
                            Tolak dengan Alasan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
