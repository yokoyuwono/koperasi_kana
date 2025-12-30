@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Pengajuan Komisi</h2>
    <p class="text-xs text-slate-500 mt-1">
        Isi data pengajuan dengan teliti. Anda bisa menyimpan sebagai draft atau langsung kirim ke COA.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-6 max-w-5xl">
    <form method="POST" action="{{ route('deposits.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- Relasi Nasabah & Agen --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Relasi Nasabah & Agen</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nasabah <span class="text-red-500">*</span></label>

                    <select id="select_nasabah" name="id_nasabah"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white">
                        <option value="">- Pilih Nasabah -</option>
                        @foreach($nasabah as $n)
                            <option value="{{ $n->id }}" {{ old('id_nasabah') == $n->id ? 'selected' : '' }}>
                                {{ $n->kode_nasabah }} - {{ $n->nama }}
                            </option>
                        @endforeach
                    </select>

                    @error('id_nasabah') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Agen Penanggung Jawab <span class="text-red-500">*</span></label>

                    <select id="select_agent" name="id_agent"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white">
                        <option value="">- Pilih Agen -</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" data-jabatan="{{ $a->jabatan }}" {{ old('id_agent') == $a->id ? 'selected' : '' }}>
                                {{ $a->kode_agent }} - {{ $a->nama }} ({{ $a->jabatan }})
                            </option>
                        @endforeach
                    </select>

                    @error('id_agent') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Info Bilyet & Tanggal --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Info Bilyet & Tanggal</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">No Bilyet <span class="text-red-500">*</span></label>
                    <input name="no_bilyet" value="{{ old('no_bilyet') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('no_bilyet') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Transaksi <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_transaksi" value="{{ old('tanggal_transaksi') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('tanggal_transaksi') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <!-- <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tenor (bulan) <span class="text-red-500">*</span></label>
                    <input type="number" min="1" name="tenor" value="{{ old(key: 'tenor') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('tenor') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div> -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Tenor <span class="text-red-500">*</span>
                    </label>

                    <select id="tenor" name="tenor"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm
                            focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" >

                        <option value="">-- Pilih Tenor --</option>

                        <option value="1"  {{ old('tenor') == 1  ? 'selected' : '' }}>1 Bulan</option>
                        <option value="3"  {{ old('tenor') == 3  ? 'selected' : '' }}>3 Bulan</option>
                        <option value="6"  {{ old('tenor') == 6  ? 'selected' : '' }}>6 Bulan</option>
                        <option value="12" {{ old('tenor') == 12 ? 'selected' : '' }}>12 Bulan</option>
                    </select>

                    @error('tenor')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" 
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('tanggal_mulai') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <!-- <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Jatuh Tempo <span class="text-red-500">*</span></label>
                    <input type="date" name="tanggal_tempo" value="{{ old('tanggal_tempo') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('tanggal_tempo') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div> -->
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                    </label>

                    <input
                        type="date"
                        id="tanggal_tempo"
                        name="tanggal_tempo"
                        value="{{ old('tanggal_tempo') }}"
                        readonly
                        class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm
                            focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-[11px] text-slate-500 mt-1">Otomatis dihitung dari Tanggal Mulai + Tenor.</p>

                    @error('tanggal_tempo')
                        <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Nominal & BDP --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Nominal & Referensi</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nominal (rupiah) <span class="text-red-500">*</span></label>
                    <input type="number" min="0" name="nominal" value="{{ old('nominal') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('nominal') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

            </div>
        </div>

        {{-- Panel Komisi --}}
        <div class="grid md:grid-cols-2 gap-4 mt-6">
            {{-- Komisi Sistem (read-only) --}}
            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50">
                <h3 class="text-sm font-semibold text-slate-800 mb-2">Perhitungan Komisi Sistem</h3>
                <p class="text-[11px] text-slate-500 mb-3">
                    Sistem menghitung otomatis berdasarkan nominal dan aturan standar.
                </p>

                <dl class="space-y-2 text-xs">
                    <div class="flex justify-between">
                        <dt class="text-slate-600">Komisi RM (Sistem)</dt>
                        <dd class="text-right">
                            <span id="komisi-system-rm-percent" class="font-semibold">0%</span>
                            <span class="text-slate-400"> · </span>
                            <span id="komisi-system-rm-nominal" class="font-mono text-slate-700">Rp 0</span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-600">Komisi BDP (Sistem)</dt>
                        <dd class="text-right">
                            <span id="komisi-system-bdp-percent" class="font-semibold">0%</span>
                            <span class="text-slate-400"> · </span>
                            <span id="komisi-system-bdp-nominal" class="font-mono text-slate-700">Rp 0</span>
                        </dd>
                    </div>
                </dl>

                <p class="mt-3 text-[11px] text-slate-400">
                    Aturan: RM 3–4%, BDP 4–5% tergantung nominal (&lt; / ≥ 200 juta).
                </p>
            </div>

            {{-- Komisi Final Admin (editable) --}}
            <div class="border border-slate-200 rounded-xl p-4 bg-white">
                <h3 class="text-sm font-semibold text-slate-800 mb-2">Komisi Final Admin</h3>
                <p class="text-[11px] text-slate-500 mb-3">
                    Admin dapat menyesuaikan % komisi sesuai kebijakan. Default mengikuti komisi sistem.
                </p>

                <div class="space-y-3">
                    {{-- Komisi RM Final --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">
                            % Komisi RM Final
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.1" 
                                name="komisi_rm_persen_final"
                                value="{{ old('komisi_rm_persen_final') }}"
                                class="w-24 border border-slate-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <span class="text-xs text-slate-500">%</span>
                            <span class="text-[11px] text-slate-500 ml-auto">
                                ≈ <span id="komisi-final-rm-nominal" class="font-mono text-slate-700">Rp 0</span>
                            </span>
                        </div>
                    </div>

                    {{-- Komisi BDP Final --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">
                            % Komisi BDP Final
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.1"
                                name="komisi_bdp_persen_final"
                                value="{{ old('komisi_bdp_persen_final') }}"
                                class="w-24 border border-slate-200 rounded-lg px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                            <span class="text-xs text-slate-500">%</span>
                            <span class="text-[11px] text-slate-500 ml-auto">
                                ≈ <span id="komisi-final-bdp-nominal" class="font-mono text-slate-700">Rp 0</span>
                            </span>
                        </div>
                    </div>

                    {{-- Toggle BDP Referral (pakai checkbox BDP_ref yang sudah ada) --}}
                    <!-- <div class="pt-2 border-t border-slate-100 mt-2">
                        <label class="inline-flex items-start gap-2 cursor-pointer select-none">
                            <input type="hidden" name="BDP_ref" value="0">
                            <input type="checkbox" name="BDP_ref" value="1"
                                {{ old('BDP_ref') ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] text-slate-700">
                                Aktifkan komisi BDP referral (tambahan 0,5% untuk BDP yang mereferensikan).
                            </span>
                        </label>
                    </div> -->
                    <div class="md:col-span-2" id="bdpRefWrapper">
                        <input type="hidden" name="BDP_ref" value="0">
                        <label class="inline-flex items-start gap-2 cursor-pointer select-none">
                            <input id="bdp_ref_toggle" type="checkbox" name="BDP_ref" value="1"
                                {{ old('BDP_ref', $deposit->BDP_ref ?? false) ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <div class="text-xs">
                                <div class="font-medium text-slate-700">BDP Ref</div>
                                <div class="text-[11px] text-slate-500">Aktifkan komisi referral BDP (sesuai aturan default). Bisa diubah.</div>
                            </div>
                        </label>

                        <div id="bdpRefSection" class="mt-3 hidden rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">% Komisi BDP Ref</label>
                                    <input id="bdp_ref_percent" type="number" step="0.01" min="0" name="komisi_bdp_ref_persen_final"
                                        value="{{ old('komisi_bdp_ref_persen_final', $bdpRefPercent ?? '') }}"
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    @error('komisi_bdp_ref_persen_final') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-slate-700 mb-1">Nominal Komisi BDP Ref</label>
                                    <input id="bdp_ref_nominal" type="text" readonly
                                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-slate-50">
                                </div>

                                <div class="text-[11px] text-slate-500">
                                    Sistem menghitung otomatis berdasarkan nominal & tenor (prorata).
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Warning jika % final ≠ sistem --}}
                <div id="komisi-warning" class="hidden mt-3 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2">
                    <p class="text-[11px] text-amber-800">
                        Perbedaan rate komisi terdeteksi. Pastikan ada <strong>catatan</strong> atau
                        <strong>dokumen pendukung</strong> sebagai justifikasi perubahan komisi.
                    </p>
                </div>
            </div>
        </div>

        {{-- Dokumen & Catatan --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Dokumen & Catatan</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Bukti Transfer (gambar)</label>
                    <input type="file" name="bukti_transfer" accept="image/*"
                        class="block w-full text-xs text-slate-700 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-lg cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Format: JPG/PNG, maks 2MB
                    </p>
                    @error('bukti_transfer') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Dokumen Bukti Special Rate</label>
                    <input type="file" name="dokumen_pendukung" accept="image/*"
                        class="block w-full text-xs text-slate-700 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-lg cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Format: JPG/PNG, maks 2MB
                    </p>
                    @error('dokumen_pendukung') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-3">
                <label class="block text-xs font-medium text-slate-700 mb-1">Catatan Admin</label>
                <textarea name="catatan_admin" rows="3"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ old('catatan_admin') }}</textarea>
            </div>
        </div>

        {{-- Tombol --}}
        <div class="flex justify-between items-center pt-3 border-t border-slate-100">
            <a href="{{ route('deposits.index') }}"
               class="px-4 py-2 text-xs border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50">
                Batal
            </a>
            <div class="flex gap-2">
                
                <button type="submit" name="submit_to_coa" value="1"
                        class="px-4 py-2 text-xs rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">
                    Simpan & Kirim ke COA
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tenorEl = document.getElementById('tenor');
    const mulaiEl = document.getElementById('tanggal_mulai');
    const tempoEl = document.getElementById('tanggal_tempo');

    // Jika salah satu elemen tidak ada, stop tanpa error
    if (!tenorEl || !mulaiEl || !tempoEl) return;

    function pad2(n) { return String(n).padStart(2, '0'); }

    function addMonths(date, months) {
        const d = new Date(date.getTime());
        const day = d.getDate();

        d.setMonth(d.getMonth() + months);

        // fix end-of-month overflow
        if (d.getDate() < day) {
            d.setDate(0);
        }
        return d;
    }

    function formatDateInput(d) {
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }

    function recalc() {
        const tenorVal = tenorEl.value;
        const mulaiVal = mulaiEl.value;

        if (!tenorVal || !mulaiVal) {
            tempoEl.value = '';
            return;
        }

        const tenor = parseInt(tenorVal, 10);
        if (Number.isNaN(tenor)) {
            tempoEl.value = '';
            return;
        }

        const startDate = new Date(mulaiVal + 'T00:00:00');
        const dueDate = addMonths(startDate, tenor);

        tempoEl.value = formatDateInput(dueDate);
    }

    tenorEl.addEventListener('change', recalc);
    mulaiEl.addEventListener('change', recalc);

    // initial hitung (untuk kasus edit/old input)
    recalc();
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const nasabahEl = document.getElementById('select_nasabah');
    const agentEl   = document.getElementById('select_agent');

    if (nasabahEl) {
        new TomSelect(nasabahEl, {
            create: false,
            allowEmptyOption: true,
            placeholder: '- Pilih Nasabah -',
            sortField: { field: "text", direction: "asc" }
        });
    }

    if (agentEl) {
        new TomSelect(agentEl, {
            create: false,
            allowEmptyOption: true,
            placeholder: '- Pilih Agen -',
            sortField: { field: "text", direction: "asc" }
        });
    }
});
</script>
<script>

window.KOMISI_DEFAULTS = @json($komisiDefaults);

function formatRupiah(number) {
    if (!number || isNaN(number)) return 'Rp 0';
    return 'Rp ' + Number(number).toLocaleString('id-ID', { maximumFractionDigits: 0 });
}

function round2(n) {
    const x = Number(n);
    if (isNaN(x)) return 0;
    return Math.round(x * 100) / 100;
}

// prorata dari annual rate (basis 12 bulan)
function prorataPercent(annualRate, tenor) {
    const a = Number(annualRate) || 0;
    const t = Number(tenor) || 0;
    return round2((a / 12) * t);
}

function findDefault(pengaju, jenis, nominal) {
    const rows = window.KOMISI_DEFAULTS || [];
    const n = Number(nominal) || 0;

    let best = null;
    for (const r of rows) {
        if (!r || !r.aktif) continue;
        if ((String(r.pengaju_jabatan || '')).toUpperCase() !== pengaju) continue;
        if ((String(r.jenis_komisi || '')).toUpperCase() !== jenis) continue;

        const min = Number(r.nominal_min ?? 0);
        const max = (r.nominal_max === null || r.nominal_max === undefined) ? null : Number(r.nominal_max);

        if (n < min) continue;
        if (max !== null && n > max) continue;

        if (!best || min > Number(best.nominal_min ?? 0)) best = r;
    }
    return best;
}

function getSystemFromDb(jabatan, nominal, tenor) {
    const pengaju = (jabatan || '').toUpperCase();

    const rmRow  = findDefault(pengaju, 'RM', nominal);
    const bdpRow = findDefault(pengaju, 'BDP', nominal);
    const refRow = findDefault(pengaju, 'BDP_REF', nominal);

    const annualRm  = rmRow ? Number(rmRow.annual_rate || 0) : 0;
    const annualBdp = bdpRow ? Number(bdpRow.annual_rate || 0) : 0;
    const annualRef = refRow ? Number(refRow.annual_rate || 0) : 0;

    const rmEligible = annualRm > 0.0001;

    const refMode = refRow ? (refRow.ref_mode || 'none') : 'none'; // none|optional|mandatory

    return {
        rm: rmEligible ? prorataPercent(annualRm, tenor) : null,
        bdp: annualBdp > 0 ? prorataPercent(annualBdp, tenor) : null,
        bdp_ref: annualRef > 0 ? prorataPercent(annualRef, tenor) : null,
        ref_mode: refMode,
        rmEligible,
    };
}

document.addEventListener('DOMContentLoaded', function () {
    const nominalEl = document.querySelector('input[name="nominal"]');
    const agentEl   = document.getElementById('select_agent');
    const tenorSelectEl   = document.getElementById('tenor');

    const rmFinalInput  = document.querySelector('input[name="komisi_rm_persen_final"]');
    const bdpFinalInput = document.querySelector('input[name="komisi_bdp_persen_final"]');

    const bdpRefToggle  = document.getElementById('bdp_ref_toggle');
    const bdpRefSection = document.getElementById('bdpRefSection');
    const bdpRefWrapper = document.getElementById('bdpRefWrapper');
    const bdpRefInput   = document.querySelector('input[name="komisi_bdp_ref_persen_final"]');
    const bdpRefNominal = document.getElementById('bdp_ref_nominal');

    if (!nominalEl || !agentEl || !tenorSelectEl) return;

    function selectedJabatan() {
        const opt = agentEl.options[agentEl.selectedIndex];
        return (opt && opt.dataset && opt.dataset.jabatan) ? opt.dataset.jabatan : '';
    }

    function setWarning(show) {
        const el = document.getElementById('komisi-warning');
        if (!el) return;
        el.classList.toggle('hidden', !show);
    }

    function refresh() {
        const nominal = parseFloat(nominalEl.value || '0') || 0;
        const tenor   = parseInt(tenorSelectEl.value || '0', 10) || 0;
        const jabatan = selectedJabatan();

        const sys = getSystemFromDb(jabatan, nominal, tenor);

        // ====== SHOW/HIDE RM FINAL INPUT (BDP pengaju = tidak dapat RM) ======
        if (rmFinalInput) {
            const rmWrap = rmFinalInput.closest('div'); // aman untuk layout kamu
            if (rmWrap) rmWrap.classList.toggle('hidden', !sys.rmEligible);
            if (!sys.rmEligible) rmFinalInput.value = ''; // kirim null
        }

        // ====== System percent display ======
        const rmSysPct  = sys.rmEligible ? (sys.rm ?? 0) : 0;
        const bdpSysPct = sys.bdp ?? 0;

        const elRmSysPct = document.getElementById('komisi-system-rm-percent');
        const elBdpSysPct = document.getElementById('komisi-system-bdp-percent');
        const elRmSysNom = document.getElementById('komisi-system-rm-nominal');
        const elBdpSysNom = document.getElementById('komisi-system-bdp-nominal');

        if (elRmSysPct)  elRmSysPct.textContent  = sys.rmEligible ? (rmSysPct.toFixed(2) + '%') : '-';
        if (elBdpSysPct) elBdpSysPct.textContent = bdpSysPct.toFixed(2) + '%';

        if (elRmSysNom)  elRmSysNom.textContent  = sys.rmEligible ? formatRupiah(nominal * rmSysPct / 100) : '-';
        if (elBdpSysNom) elBdpSysNom.textContent = formatRupiah(nominal * bdpSysPct / 100);

        // ====== Default final percent if empty ======
        if (bdpFinalInput && bdpFinalInput.value === '' && nominal > 0 && tenor > 0) {
            bdpFinalInput.value = bdpSysPct.toFixed(2);
        }
        if (sys.rmEligible && rmFinalInput && rmFinalInput.value === '' && nominal > 0 && tenor > 0) {
            rmFinalInput.value = rmSysPct.toFixed(2);
        }

        // ====== Ref Mode UI ======
        if (bdpRefWrapper) {
            const mode = sys.ref_mode; // none|optional|mandatory
            const shouldShow = mode !== 'none' && (sys.bdp_ref ?? 0) > 0;

            bdpRefWrapper.classList.toggle('hidden', !shouldShow);

            if (shouldShow) {
                if (bdpRefToggle) {
                    if (mode === 'mandatory') {
                        bdpRefToggle.checked = true;
                        bdpRefToggle.disabled = true;
                    } else {
                        bdpRefToggle.disabled = false;

                        // default: RM -> unchecked, BDP -> checked (sesuai request kamu)
                        if (jabatan.toUpperCase() === 'BDP') bdpRefToggle.checked = true;
                        if (jabatan.toUpperCase() === 'RM' && !bdpRefToggle.checked) {
                            // keep as is (default unchecked)
                        }
                    }
                }

                const enabled = bdpRefToggle ? bdpRefToggle.checked : false;

                if (bdpRefSection) bdpRefSection.classList.toggle('hidden', !enabled);

                if (!enabled) {
                    if (bdpRefInput) bdpRefInput.value = '';
                    if (bdpRefNominal) bdpRefNominal.value = '';
                } else {
                    // set default percent if empty
                    const refPct = sys.bdp_ref ?? 0;
                    if (bdpRefInput && bdpRefInput.value === '' && nominal > 0 && tenor > 0) {
                        bdpRefInput.value = refPct.toFixed(2);
                    }
                    const refFinalPct = parseFloat(bdpRefInput ? (bdpRefInput.value || '0') : '0') || 0;
                    if (bdpRefNominal) bdpRefNominal.value = formatRupiah(nominal * refFinalPct / 100);
                }
            }
        }

        // ====== Final nominal display ======
        const rmFinalPct  = sys.rmEligible ? (parseFloat(rmFinalInput ? (rmFinalInput.value || '0') : '0') || 0) : 0;
        const bdpFinalPct = parseFloat(bdpFinalInput ? (bdpFinalInput.value || '0') : '0') || 0;

        const elRmFinalNom = document.getElementById('komisi-final-rm-nominal');
        const elBdpFinalNom = document.getElementById('komisi-final-bdp-nominal');

        if (elRmFinalNom)  elRmFinalNom.textContent  = sys.rmEligible ? formatRupiah(nominal * rmFinalPct / 100) : '-';
        if (elBdpFinalNom) elBdpFinalNom.textContent = formatRupiah(nominal * bdpFinalPct / 100);

        // ====== warning if final != system ======
        const warn = (sys.rmEligible && round2(rmFinalPct) !== round2(rmSysPct)) || (round2(bdpFinalPct) !== round2(bdpSysPct));
        setWarning(warn);
    }

    // listeners
    nominalEl.addEventListener('input', function() {
        // Reset final inputs to trigger auto-recalc from system default
        if (rmFinalInput) rmFinalInput.value = '';
        if (bdpFinalInput) bdpFinalInput.value = '';
        if (bdpRefInput) bdpRefInput.value = '';
        refresh();
    });

    tenorSelectEl.addEventListener('change', function() {
        // Reset final inputs on tenor change
        if (rmFinalInput) rmFinalInput.value = '';
        if (bdpFinalInput) bdpFinalInput.value = '';
        if (bdpRefInput) bdpRefInput.value = '';
        refresh();
    });

    agentEl.addEventListener('change', function() {
        // reset editable fields when ganti agent, biar mengikuti default baru
        if (rmFinalInput) rmFinalInput.value = '';
        if (bdpFinalInput) bdpFinalInput.value = '';
        if (bdpRefInput) bdpRefInput.value = '';
        if (bdpRefToggle) bdpRefToggle.checked = false;
        refresh();
    });

    if (rmFinalInput)  rmFinalInput.addEventListener('input', refresh);
    if (bdpFinalInput) bdpFinalInput.addEventListener('input', refresh);
    if (bdpRefToggle)  bdpRefToggle.addEventListener('change', refresh);
    if (bdpRefInput)   bdpRefInput.addEventListener('input', refresh);

    refresh();
});

</script>