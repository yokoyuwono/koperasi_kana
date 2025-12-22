@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Buat Pengajuan Promosi</h2>
            <p class="text-xs text-slate-500 mt-1">Admin mengajukan RM naik jabatan menjadi BDP.</p>
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

    <form method="POST" action="{{ route('promosi.store') }}"
          class="bg-white border border-slate-100 rounded-xl p-4 space-y-4">
        @csrf

        <div class="grid md:grid-cols-2 gap-4">
            {{-- Agent RM --}}
            <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Agent (RM)</label>
            <select id="id_agent" name="id_agent"
                    class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">
                <option value="">-- Pilih Agent RM --</option>
                @foreach($rmAgents as $a)
                <option value="{{ $a->id }}"
                        data-atasan-id="{{ $a->atasan_id ?? '' }}"
                        data-atasan-nama="{{ $a->atasan?->nama ?? '' }}">
                    {{ $a->nama }}
                </option>
                @endforeach
            </select>
            @error('id_agent') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>
            {{-- Referral otomatis --}}
            <input type="hidden" name="bdp_id" id="bdp_id">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">Atasan (BDP) Otomatis</label>
                <input id="bdp_label" type="text" readonly
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-slate-50"
                        placeholder="Otomatis terisi dari atasan RM">
                @error('bdp_label') <div class="text-xs text-red-600 mt-1">{{ $message }}</div> @enderror
            </div>
                    
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">Catatan Admin (opsional)</label>
            <textarea name="catatan_admin" rows="3"
                      class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs">{{ old('catatan_admin') }}</textarea>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
                Kirim Pengajuan
            </button>
        </div>
    </form>
    <script>
        const agentSelect = document.getElementById('agent_id');
        const bdpId = document.getElementById('bdp_id');
        const bdpLabel = document.getElementById('bdp_label');

        agentSelect.addEventListener('change', () => {
            const opt = agentSelect.options[agentSelect.selectedIndex];
            bdpId.value = opt.dataset.atasanId || '';
            bdpLabel.value = opt.dataset.atasanNama || '';
        });
    </script>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rmSelect  = document.getElementById('id_agent');
    const bdpIdEl   = document.getElementById('bdp_id');
    const bdpLabel  = document.getElementById('bdp_label');

    if (!rmSelect || !bdpIdEl || !bdpLabel) return;

    function updateBdp() {
        const opt = rmSelect.options[rmSelect.selectedIndex];
        if (!opt) return;

        const atasanId = opt.getAttribute('data-atasan-id') || '';
        const atasanNama = opt.getAttribute('data-atasan-nama') || '';

        if (atasanId && atasanNama) {
            bdpIdEl.value = atasanId;
            bdpLabel.value = `${atasanId} - ${atasanNama}`;
        } else if (atasanId) {
            bdpIdEl.value = atasanId;
            bdpLabel.value = `${atasanId}`;
        } else {
            bdpIdEl.value = '';
            bdpLabel.value = '';
            bdpLabel.placeholder = 'RM belum punya atasan BDP';
        }
    }

    rmSelect.addEventListener('change', updateBdp);

    // initial (kalau old() / edit)
    updateBdp();
});
</script>