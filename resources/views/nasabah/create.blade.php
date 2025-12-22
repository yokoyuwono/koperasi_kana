@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Tambah Nasabah Baru</h2>
    <p class="text-xs text-slate-500 mt-1">
        Lengkapi data nasabah sesuai dokumen yang dimiliki.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5 space-y-6">
    <form method="POST" action="{{ route('nasabah.store') }}" class="space-y-6">
        @csrf

        {{-- Data Utama --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Data Utama</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Kode Nasabah <span class="text-red-500">*</span>
                    </label>
                    <input name="kode_nasabah" value="{{ old('kode_nasabah') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('kode_nasabah') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input name="nama" value="{{ old('nama') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    @error('nama') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Agen Penanggung Jawab <span class="text-red-500">*</span>
                    </label>
                    <select name="id_agent"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Agen -</option>
                        @foreach($agents as $agent)
                            <option value="{{ $agent->id }}" {{ old('id_agent') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->kode_agent }} - {{ $agent->nama }} ({{ $agent->jabatan }})
                            </option>
                        @endforeach
                    </select>
                    @error('id_agent') <p class="text-[11px] text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Telepon</label>
                        <input name="telepon" value="{{ old('telepon') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    </div>
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
                        <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">NIK</label>
                    <input name="NIK" value="{{ old('NIK') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">NPWP</label>
                    <input name="NPWP" value="{{ old('NPWP') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Agama</label>
                    <input name="agama" value="{{ old('agama') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Alamat KTP</label>
                    <textarea name="alamat_KTP" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ old('alamat_KTP') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Alamat Sekarang</label>
                    <textarea name="alamat_sekarang" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ old('alamat_sekarang') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Data Orang Tua / Wali --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Data Orang Tua / Wali</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nama Ibu</label>
                    <input name="nama_ibu" value="{{ old('nama_ibu') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nama Wali</label>
                    <input name="nama_wali" value="{{ old('nama_wali') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">NIK Wali</label>
                    <input name="NIK_wali" value="{{ old('NIK_wali') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
        </div>

        {{-- Pekerjaan & Usaha --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Pekerjaan & Usaha</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Jenis Pekerjaan</label>
                    <input name="jenis_pekerjaan" value="{{ old('jenis_pekerjaan') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Bidang Usaha</label>
                    <input name="bidang_usaha" value="{{ old('bidang_usaha') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Penghasilan (per bulan)</label>
                    <input type="number" step="0.01" name="penghasilan" value="{{ old('penghasilan') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Alamat Perusahaan</label>
                    <textarea name="alamat_perusahaan" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ old('alamat_perusahaan') }}</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Aktivitas Transaksi</label>
                    <textarea name="aktivitas_transaksi" rows="2"
                              class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">{{ old('aktivitas_transaksi') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Rekening & Sumber Dana --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-700 mb-3">Rekening & Sumber Dana</h3>
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Bank</label>
                    <input name="rekening_bank" value="{{ old('rekening_bank') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nomor Rekening</label>
                    <input name="nomor_rekening" value="{{ old('nomor_rekening') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Nama di Rekening</label>
                    <input name="nama_rekening" value="{{ old('nama_rekening') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-4 mt-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Tujuan Rekening</label>
                    <input name="tujuan_rekening" value="{{ old('tujuan_rekening') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Sumber Dana</label>
                    <input name="sumber_dana" value="{{ old('sumber_dana') }}"
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

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
            <a href="{{ route('nasabah.index') }}"
               class="px-4 py-2 text-xs border border-slate-200 rounded-lg text-slate-600 hover:bg-slate-50">
                Batal
            </a>
            <button
                class="px-4 py-2 text-xs rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700">
                Simpan Nasabah
            </button>
        </div>
    </form>
</div>
@endsection
