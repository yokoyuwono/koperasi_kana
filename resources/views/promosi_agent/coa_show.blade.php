@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Detail Pengajuan Promosi</h2>
            <p class="text-xs text-slate-500 mt-1">COA dapat approve / reject jika status masih pending.</p>
        </div>

        <a href="{{ route('coa.promosi.index') }}"
           class="inline-flex items-center px-3 py-2 rounded-lg border border-slate-200 text-slate-700 text-xs hover:bg-slate-50">
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800">
            <div class="font-semibold mb-1">Ada error:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white border border-slate-100 rounded-xl p-4 text-xs space-y-4">
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <div class="text-[11px] text-slate-500">Agent yang diajukan</div>
                <div class="font-semibold text-slate-800">{{ $promosi->agent->nama ?? '-' }}</div>
                <div class="text-[11px] text-slate-500">{{ $promosi->agent->kode_agent ?? '' }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Atasan BDP (akan jadi referral)</div>
                <div class="font-semibold text-slate-800">{{ $promosi->atasanBdp->nama ?? '-' }}</div>
                <div class="text-[11px] text-slate-500">{{ $promosi->atasanBdp->kode_agent ?? '' }}</div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4 border-t border-slate-100 pt-3">
            <div>
                <div class="text-[11px] text-slate-500">Jabatan Lama</div>
                <div class="font-semibold">{{ $promosi->jabatan_lama }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Jabatan Baru</div>
                <div class="font-semibold">{{ $promosi->jabatan_baru }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Status</div>
                <div class="font-semibold">{{ strtoupper($promosi->status) }}</div>
            </div>
        </div>

        <div class="border-t border-slate-100 pt-3">
            <div class="text-[11px] text-slate-500">Catatan Admin</div>
            <div class="whitespace-pre-line text-slate-800">{{ $promosi->catatan_admin ?? '-' }}</div>
        </div>

        @if($promosi->status === 'rejected' && $promosi->alasan_reject)
            <div class="rounded-lg border border-red-200 bg-red-50 p-3">
                <div class="font-semibold text-red-800">Alasan Reject (COA)</div>
                <div class="whitespace-pre-line text-red-800 mt-1">{{ $promosi->alasan_reject }}</div>
            </div>
        @endif
    </div>

    {{-- Aksi COA --}}
    @if($promosi->status === 'pending')
        <div class="bg-white border border-slate-100 rounded-xl p-4 text-xs space-y-3">
            <div class="font-semibold text-slate-800">Keputusan COA</div>

            <form method="POST" action="{{ route('coa.promosi.approve', $promosi) }}" class="inline">
                @csrf
                <button type="submit"
                        class="w-full md:w-auto inline-flex justify-center px-4 py-2 rounded-lg bg-emerald-600 text-white text-xs hover:bg-emerald-700">
                    Setujui Promosi
                </button>
            </form>

            <form method="POST" action="{{ route('coa.promosi.reject', $promosi) }}" class="space-y-2">
                @csrf
                <label class="block text-xs font-medium text-slate-700">Alasan reject <span class="text-red-500">*</span></label>
                <textarea name="alasan_reject" rows="3"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs"
                          placeholder="Contoh: data atasan BDP tidak sesuai, mohon koreksi...">{{ old('alasan_reject') }}</textarea>

                <button type="submit"
                        class="w-full md:w-auto inline-flex justify-center px-4 py-2 rounded-lg bg-red-50 text-red-700 border border-red-200 text-xs hover:bg-red-100">
                    Tolak Promosi
                </button>
            </form>
        </div>
    @else
        <div class="text-[11px] text-slate-500">
            Pengajuan ini sudah diproses. COA hanya bisa melihat detailnya.
        </div>
    @endif
</div>
@endsection
