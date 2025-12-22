@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Edit Pengajuan Promosi</h2>
            <p class="text-xs text-slate-500 mt-1">Bisa diedit jika status masih pending / rejected.</p>
        </div>
        <a href="{{ route('promosi.index') }}"
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

    <form method="POST" action="{{ route('promosi.update', $promosi) }}"
          class="bg-white border border-slate-100 rounded-xl p-4 space-y-4">
        @csrf
        @method('PUT')

        @if($promosi->status === 'rejected' && $promosi->alasan_reject)
            <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-xs text-red-800">
                <div class="font-semibold">Alasan reject dari COA:</div>
                <div class="whitespace-pre-line mt-1">{{ $promosi->alasan_reject }}</div>
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            {{-- RM select --}}
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Pilih Agent (RM) <span class="text-red-500">*</span>
                </label>

                <select id="id_agent" name="id_agent"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                    <option value="">— Pilih RM —</option>
                    @foreach($rmAgents as $a)
                        <option value="{{ $a->id }}"
                            data-atasan-id="{{ $a->atasan_id ?? '' }}"
                            data-atasan-nama="{{ $a->atasan?->nama ?? '' }}"
                            data-atasan-kode="{{ $a->atasan?->kode_agent ?? '' }}"
                            {{ old('id_agent', $promosi->id_agent) == $a->id ? 'selected' : '' }}>
                            {{ $a->nama }} ({{ $a->kode_agent }})
                        </option>
                    @endforeach
                </select>

                @error('id_agent')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Atasan BDP otomatis --}}
            <input type="hidden" name="atasan_bdp_id" id="atasan_bdp_id"
                value="{{ old('atasan_bdp_id', $promosi->atasan_bdp_id) }}">

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Atasan BDP (otomatis) <span class="text-red-500">*</span>
                </label>

                <input id="atasan_bdp_label" type="text" readonly
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50"
                    placeholder="Otomatis terisi dari atasan RM">

                @error('atasan_bdp_id')
                    <p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Catatan Admin (opsional)</label>
            <textarea name="catatan_admin" rows="3"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">{{ old('catatan_admin', $promosi->catatan_admin) }}</textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                Simpan & Kirim Ulang
            </button>
        </div>
    </form>
</div>
@endsection
{{-- Script auto-fill --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rmSelect = document.getElementById('id_agent');
    const bdpIdEl  = document.getElementById('atasan_bdp_id');
    const bdpLabel = document.getElementById('atasan_bdp_label');

    if (!rmSelect || !bdpIdEl || !bdpLabel) return;

    function updateBdp() {
        const opt = rmSelect.options[rmSelect.selectedIndex];
        if (!opt) return;

        const id   = opt.getAttribute('data-atasan-id') || '';
        const nama = opt.getAttribute('data-atasan-nama') || '';
        const kode = opt.getAttribute('data-atasan-kode') || '';

        if (!id) {
            bdpIdEl.value = '';
            bdpLabel.value = '';
            bdpLabel.placeholder = 'RM belum punya atasan BDP';
            return;
        }

        bdpIdEl.value = id;

        // format label: "Nama (KODE) [ID]"
        let label = '';
        if (nama) label += nama;
        if (kode) label += (label ? ' ' : '') + `(${kode})`;
        label += (label ? ' ' : '');

        bdpLabel.value = label.trim();
    }

    rmSelect.addEventListener('change', updateBdp);

    // initial run (penting untuk halaman edit)
    updateBdp();
});
</script>