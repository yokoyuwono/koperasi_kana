@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Tambah Deposit</h2>
    <p class="text-xs text-slate-500 mt-1">
        Isi data deposito dengan teliti. Anda bisa menyimpan sebagai draft atau langsung kirim ke COA.
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
                    <!-- <select id="select_agent" name="id_agent"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white">
                        <option value="">- Pilih Agen -</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" data-jabatan="{{ $a->jabatan }}" {{ old(key: 'id_agent') == $a->id ? 'selected' : '' }}>
                                {{ $a->kode_agent }} - {{ $a->nama }} ({{ $a->jabatan }})
                            </option>
                        @endforeach
                    </select> -->
                    <select id="id_agent" id="select_agent" name="id_agent"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-xs bg-white">
                    <option value="">- Pilih Agen -</option>
                    @foreach($agents as $a)
                        <option value="{{ $a->id }}"
                            data-jabatan="{{ $a->jabatan }}"
                            {{ old('id_agent') == $a->id ? 'selected' : '' }}>
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
                {{-- <div class="md:col-span-2 flex items-start gap-2">
                    <input type="hidden" name="BDP_ref" value="0">
                    <label class="inline-flex items-start gap-2 cursor-pointer select-none mt-5 md:mt-6">
                        <input type="checkbox" name="BDP_ref" value="1"
                            {{ old('BDP_ref') ? 'checked' : '' }}
                            class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-xs text-slate-700">
                            Ada BDP Referral (tambahan komisi <span class="font-semibold">0,5%</span> untuk BDP yang mereferensikan).
                            <span class="block text-[11px] text-slate-400">
                                Centang jika deposito ini berasal dari referensi BDP lain.
                            </span>
                        </span>
                    </label>
                </div> --}}
            </div>
        </div>

        {{-- Panel Komisi --}}
        <div class="grid md:grid-cols-2 gap-4 mt-6">
            {{-- Komisi Sistem (read-only) --}}
            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50">
                <h3 class="text-sm font-semibold text-slate-800 mb-2">Perhitungan Komisi Sistem</h3>
                <p class="text-[11px] text-slate-500 mb-3">
                    Sistem menghitung otomatis berdasarkan nominal deposito dan aturan standar.
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

                <div  class="space-y-3">
                    {{-- Komisi RM Final --}}
                    <div id="komisi-rm-section">
                        <label class="block text-xs font-medium text-slate-700 mb-1">
                            % Komisi RM Final
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.1" min="0"
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
                            <input type="number" step="0.1" min="0"
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
                    <div class="pt-2 border-t border-slate-100 mt-2">
                        <label class="inline-flex items-start gap-2 cursor-pointer select-none">
                            <input type="hidden" name="BDP_ref" value="0">
                            <input id="checkbox_bdp_ref" type="checkbox" name="BDP_ref" value="1"
                                {{ old('BDP_ref') ? 'checked' : '' }}
                                class="mt-0.5 w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-[11px] text-slate-700">
                                Aktifkan komisi BDP referral (tambahan 0,5% untuk BDP yang mereferensikan).
                            </span>
                        </label>
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
                        Format: JPG/PNG, maks 2MB. Disimpan sebagai path file di server.
                    </p>
                    @error('bukti_transfer') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Dokumen Pendukung (gambar)</label>
                    <input type="file" name="dokumen_pendukung" accept="image/*"
                        class="block w-full text-xs text-slate-700 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-lg cursor-pointer">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Misalnya foto formulir, KTP, atau dokumen lain (opsional).
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
    const agentSelect = document.getElementById('id_agent');
    const rmSection   = document.getElementById('komisi-rm-section');
    const checkBox = document.getElementById('checkbox_bdp_ref');


    if (!agentSelect || !rmSection) return;

    function toggleKomisiRM() {
        const opt = agentSelect.options[agentSelect.selectedIndex];
        if (!opt) return;

        const jabatan = opt.getAttribute('data-jabatan');

        if (jabatan === 'BDP') {
            rmSection.classList.add('hidden');
            checkBox.checked = true;
            // optional: clear input RM biar tidak terkirim
            rmSection.querySelectorAll('input, select, textarea').forEach(el => {
                el.value = '';
            });
        } else {
            checkBox.checked = false;
            rmSection.classList.remove('hidden');
        }
    }

    agentSelect.addEventListener('change', toggleKomisiRM);

    // initial run (penting untuk edit)
    toggleKomisiRM();
});
</script>

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
    const agentEl   = document.getElementById('id_agent');

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
    
    function formatRupiah(number) {
        if (!number || isNaN(number)) return 'Rp 0';
        return 'Rp ' + Number(number).toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }

    function hitungPersenSystem(nominal) {
        // aturan: < 200jt → RM 3%, BDP 4% ; >= 200jt → RM 4%, BDP 5%
        if (nominal < 200000000) {
            return { rm: 3.0, bdp: 4.0 };
        }
        return { rm: 4.0, bdp: 5.0 };
    }

    function recalcKomisiPanel() {
        const nominalInput   = document.querySelector('input[name="nominal"]');
        const rmFinalInput   = document.querySelector('input[name="komisi_rm_persen_final"]');
        const bdpFinalInput  = document.querySelector('input[name="komisi_bdp_persen_final"]');

        if (!nominalInput || !rmFinalInput || !bdpFinalInput) return;

        const nominal = parseFloat(nominalInput.value || '0');

        // Hitung % sistem
        const sistem = hitungPersenSystem(nominal);
        const rmSystemPercent  = sistem.rm;
        const bdpSystemPercent = sistem.bdp;

        // Set tampilan komisi sistem
        document.getElementById('komisi-system-rm-percent').textContent  = rmSystemPercent.toFixed(1) + '%';
        document.getElementById('komisi-system-bdp-percent').textContent = bdpSystemPercent.toFixed(1) + '%';

        const rmSystemNominal  = nominal * rmSystemPercent / 100;
        const bdpSystemNominal = nominal * bdpSystemPercent / 100;

        document.getElementById('komisi-system-rm-nominal').textContent  = formatRupiah(rmSystemNominal);
        document.getElementById('komisi-system-bdp-nominal').textContent = formatRupiah(bdpSystemNominal);

        // Jika input final masih kosong, default-kan ke nilai sistem
        if (rmFinalInput.value === '' && nominal > 0) {
            rmFinalInput.value = rmSystemPercent.toFixed(1);
        }
        if (bdpFinalInput.value === '' && nominal > 0) {
            bdpFinalInput.value = bdpSystemPercent.toFixed(1);
        }

        const rmFinalPercent  = parseFloat(rmFinalInput.value || '0');
        const bdpFinalPercent = parseFloat(bdpFinalInput.value || '0');

        const rmFinalNominal  = nominal * rmFinalPercent / 100;
        const bdpFinalNominal = nominal * bdpFinalPercent / 100;

        document.getElementById('komisi-final-rm-nominal').textContent  = formatRupiah(rmFinalNominal);
        document.getElementById('komisi-final-bdp-nominal').textContent = formatRupiah(bdpFinalNominal);

        // Tampilkan warning jika % final ≠ % sistem
        const warningBox = document.getElementById('komisi-warning');
        if (
            nominal > 0 &&
            (rmFinalPercent.toFixed(1) !== rmSystemPercent.toFixed(1) ||
             bdpFinalPercent.toFixed(1) !== bdpSystemPercent.toFixed(1))
        ) {
            warningBox.classList.remove('hidden');
        } else {
            warningBox.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const nominalInput  = document.querySelector('input[name="nominal"]');
        const rmFinalInput  = document.querySelector('input[name="komisi_rm_persen_final"]');
        const bdpFinalInput = document.querySelector('input[name="komisi_bdp_persen_final"]');

        if (nominalInput) {
            nominalInput.addEventListener('input', recalcKomisiPanel);
        }
        if (rmFinalInput) {
            rmFinalInput.addEventListener('input', recalcKomisiPanel);
        }
        if (bdpFinalInput) {
            bdpFinalInput.addEventListener('input', recalcKomisiPanel);
        }

        // Hitung pertama kali saat halaman load
        recalcKomisiPanel();
    });
</script>