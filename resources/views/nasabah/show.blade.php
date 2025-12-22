@extends('layouts.app')

@section('content')
<div class="space-y-4">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Detail Nasabah</h2>
            <p class="text-xs text-slate-500 mt-1">
                Informasi lengkap nasabah untuk keperluan verifikasi dan approval.
            </p>
        </div>

        <a href="{{ url()->previous() }}"
           class="inline-flex items-center px-3 py-1.5 rounded-lg border border-slate-200 text-[11px] text-slate-700 hover:bg-slate-50">
            Kembali
        </a>
    </div>

    {{-- Kartu utama --}}
    <div class="bg-white border border-slate-100 rounded-xl p-4 text-xs space-y-4">

        {{-- Data utama --}}
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <div class="text-[11px] text-slate-500">Kode Nasabah</div>
                <div class="font-mono text-slate-800">
                    {{ $nasabah->kode_nasabah }}
                </div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Nama Lengkap</div>
                <div class="font-semibold text-slate-800">
                    {{ $nasabah->nama }}
                </div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Tanggal Daftar</div>
                <div>
                    {{ optional($nasabah->tanggal_daftar)->format('d-m-Y') }}
                </div>
            </div>
        </div>

        {{-- Data pribadi --}}
        <div class="border-t border-slate-100 pt-3 grid md:grid-cols-3 gap-4">
            <div>
                <div class="text-[11px] text-slate-500">Tempat / Tanggal Lahir</div>
                <div>
                    {{ $nasabah->tempat_lahir }},
                    {{ optional($nasabah->tanggal_lahir)->format('d-m-Y') }}
                </div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Jenis Kelamin</div>
                <div>{{ $nasabah->jenis_kelamin }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Agama</div>
                <div>{{ $nasabah->agama }}</div>
            </div>
        </div>

        {{-- Identitas --}}
        <div class="border-t border-slate-100 pt-3 grid md:grid-cols-3 gap-4">
            <div>
                <div class="text-[11px] text-slate-500">NIK</div>
                <div class="font-mono">{{ $nasabah->NIK }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">NPWP</div>
                <div class="font-mono">{{ $nasabah->NPWP }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Telepon</div>
                <div>{{ $nasabah->telepon }}</div>
            </div>
        </div>

        {{-- Alamat --}}
        <div class="border-t border-slate-100 pt-3 grid md:grid-cols-2 gap-4">
            <div>
                <div class="text-[11px] text-slate-500">Alamat KTP</div>
                <div class="whitespace-pre-line">
                    {{ $nasabah->alamat_KTP }}
                </div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Alamat Sekarang</div>
                <div class="whitespace-pre-line">
                    {{ $nasabah->alamat_sekarang }}
                </div>
            </div>
        </div>

        {{-- Data pekerjaan & keuangan --}}
        <div class="border-t border-slate-100 pt-3 grid md:grid-cols-3 gap-4">
            <div>
                <div class="text-[11px] text-slate-500">Jenis Pekerjaan</div>
                <div>{{ $nasabah->jenis_pekerjaan }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Bidang Usaha</div>
                <div>{{ $nasabah->bidang_usaha }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Penghasilan</div>
                <div>
                    Rp {{ number_format($nasabah->penghasilan, 0, ',', '.') }}
                </div>
            </div>
        </div>

        {{-- Rekening --}}
        <div class="border-t border-slate-100 pt-3 grid md:grid-cols-3 gap-4">
            <div>
                <div class="text-[11px] text-slate-500">Bank</div>
                <div>{{ $nasabah->rekening_bank }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Nomor Rekening</div>
                <div class="font-mono">{{ $nasabah->nomor_rekening }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Nama Rekening</div>
                <div>{{ $nasabah->nama_rekening }}</div>
            </div>
        </div>

        {{-- Sumber dana & aktivitas --}}
        <div class="border-t border-slate-100 pt-3 grid md:grid-cols-2 gap-4">
            <div>
                <div class="text-[11px] text-slate-500">Sumber Dana</div>
                <div>{{ $nasabah->sumber_dana }}</div>
            </div>
            <div>
                <div class="text-[11px] text-slate-500">Aktivitas Transaksi</div>
                <div class="whitespace-pre-line">
                    {{ $nasabah->aktivitas_transaksi }}
                </div>
            </div>
        </div>

        {{-- Relasi agent (kalau ada) --}}
        @if($nasabah->agent ?? false)
            <div class="border-t border-slate-100 pt-3 grid md:grid-cols-2 gap-4">
                <div>
                    <div class="text-[11px] text-slate-500">Agent Pengelola</div>
                    <div class="font-semibold">
                        {{ $nasabah->agent->nama }} ({{ $nasabah->agent->jabatan }})
                    </div>
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
