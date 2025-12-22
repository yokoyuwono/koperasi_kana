@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Tambah Agen Baru</h2>
    <p class="text-xs text-slate-500 mt-1">
        Lengkapi data agen sesuai dokumen KTP dan rekening.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-6">
    <form method="POST" action="{{ route('agents.store') }}" class="space-y-6">
        @csrf

        {{-- Data Utama --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Data Utama</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Kode Agent <span class="text-red-500">*</span></label>
                    <input name="kode_agent" value="{{ old('kode_agent') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('kode_agent') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input name="nama" value="{{ old('nama') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('nama') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4 mt-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('email') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Jabatan <span class="text-red-500">*</span>
                    </label>
                    <select id="jabatan" name="jabatan"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Jabatan -</option>
                        <option value="BDP" {{ old('jabatan') == 'BDP' ? 'selected' : '' }}>BDP</option>
                        <option value="RM"  {{ old('jabatan') == 'RM'  ? 'selected' : '' }}>RM</option>
                    </select>
                    @error('jabatan') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Data Pribadi --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Data Pribadi</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tempat Lahir</label>
                    <input name="tempat_lahir" value="{{ old('tempat_lahir') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih -</option>
                        <option value="Laki-laki" {{ old('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="Perempuan" {{ old('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">NIK</label>
                    <input name="NIK" value="{{ old('NIK') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Alamat KTP</label>
                    <textarea name="alamat_KTP" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ old('alamat_KTP') }}</textarea>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">No Telepon</label>
                    <input name="telepon" value="{{ old('telepon') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Alamat Sekarang</label>
                    <textarea name="alamat_sekarang" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ old('alamat_sekarang') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Data Rekening --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Data Rekening</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Rekening Bank</label>
                    <input name="rekening_bank" value="{{ old('rekening_bank') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tanggal Daftar</label>
                    <input type="date" name="tanggal_daftar" value="{{ old('tanggal_daftar') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Jika dikosongkan, akan otomatis terisi tanggal hari ini.
                    </p>
                </div>
            </div>
        </div>

        {{-- Data Tambahan: Agent Referal / Atasan --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Relasi Agent</h3>
            <div class="grid md:grid-cols-2 gap-4">
                {{-- Agent Referal (khusus BDP) --}}
                <div id="field_referral" class="hidden">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Agent Referal</label>
                    <select name="refferred_by_agent_id"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tidak ada</option>
                        @foreach($refferralAgents as $a)
                            <option value="{{ $a->id }}" {{ old('refferred_by_agent_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->kode_agent }} - {{ $a->nama }} ({{ $a->jabatan }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Pilih agent BDP yang mereferensikan agen ini. Jika tidak ada, biarkan "Tidak ada".
                    </p>
                    @error('refferred_by_agent_id') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Atasan (khusus RM, BDP saja) --}}
                <div id="field_atasan" class="hidden">
                    <label class="block text-xs font-medium text-slate-700 mb-1">Atasan BDP (wajib untuk RM)</label>
                    <select name="atasan_id"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih BDP Atasan -</option>
                        @foreach($refferralAgents as $a)
                            @if($a->jabatan === 'BDP')
                                <option value="{{ $a->id }}" {{ old('atasan_id') == $a->id ? 'selected' : '' }}>
                                    {{ $a->kode_agent }} - {{ $a->nama }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <p class="text-[11px] text-slate-400 mt-1">
                        Untuk RM, wajib memilih BDP sebagai atasan langsung.
                    </p>
                    @error('atasan_id') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
            <a href="{{ route('agents.index') }}"
               class="px-4 py-2 text-xs border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50">
                Batal
            </a>
            <button type="submit"
                    class="px-4 py-2 text-xs rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">
                Simpan Agent
            </button>
        </div>
    </form>
</div>

<script>
    function toggleAgentExtraFields() {
        const jabatan = document.getElementById('jabatan') ? document.getElementById('jabatan').value : '';
        const fieldReferral = document.getElementById('field_referral');
        const fieldAtasan   = document.getElementById('field_atasan');

        if (!fieldReferral || !fieldAtasan) return;

        if (jabatan === 'BDP') {
            fieldReferral.classList.remove('hidden');
            fieldAtasan.classList.add('hidden');
        } else if (jabatan === 'RM') {
            fieldReferral.classList.add('hidden');
            fieldAtasan.classList.remove('hidden');
        } else {
            fieldReferral.classList.add('hidden');
            fieldAtasan.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleAgentExtraFields();
        const jabatanSelect = document.getElementById('jabatan');
        if (jabatanSelect) {
            jabatanSelect.addEventListener('change', toggleAgentExtraFields);
        }
    });
</script>
@endsection
