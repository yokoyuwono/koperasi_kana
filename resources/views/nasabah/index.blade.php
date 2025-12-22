@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Manajemen Nasabah</h2>
    <p class="text-xs text-slate-500 mt-1">
        Kelola data nasabah deposito dan relasinya dengan agen.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
    <div class="px-4 py-3 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-slate-700">Daftar Nasabah</h3>
            <p class="text-xs text-slate-500">Data nasabah aktif beserta agen pengelola.</p>
        </div>
        <div class="flex items-center gap-2 w-full md:w-auto">
            <input
                type="text"
                placeholder="Cari nama / kode nasabah..."
                class="flex-1 md:flex-none border border-slate-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
            >
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('nasabah.create') }}"
                   class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 whitespace-nowrap">
                    <span>+ Tambah Nasabah</span>
                </a>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="px-4 py-2 bg-green-50 text-green-700 text-xs border-b border-green-100">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-[820px] w-full text-sm">
            <thead>
                <tr class="bg-slate-50 text-xs uppercase text-slate-500 border-b border-slate-100">
                    <th class="px-4 py-2 text-left">Kode</th>
                    <th class="px-4 py-2 text-left">Nama & Kontak</th>
                    <th class="px-4 py-2 text-left">Agen</th>
                    <th class="px-4 py-2 text-left hidden sm:table-cell">Pekerjaan & Usaha</th>
                    <th class="px-4 py-2 text-left hidden sm:table-cell">Rekening</th>
                    <th class="px-4 py-2 text-left w-32">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($nasabah as $n)
                    <tr class="hover:bg-slate-50">
                        {{-- KODE --}}
                        <td class="px-4 py-2 align-top">
                            <div class="font-mono text-xs bg-slate-100 inline-block px-2 py-1 rounded">
                                {{ $n->kode_nasabah }}
                            </div>
                            @if($n->tanggal_daftar)
                                <div class="text-[11px] text-slate-400 mt-1">
                                    Daftar: {{ \Carbon\Carbon::parse($n->tanggal_daftar)->format('d-m-Y') }}
                                </div>
                            @endif
                        </td>

                        {{-- NAMA + KONTAK + LAHIR --}}
                        <td class="px-4 py-2 align-top">
                            <div class="font-medium text-slate-800 text-sm">
                                {{ $n->nama }}
                            </div>
                            <div class="mt-0.5 space-y-0.5">
                                @if($n->telepon)
                                    <div class="text-[11px] text-slate-500">
                                        📞 {{ $n->telepon }}
                                    </div>
                                @endif
                                @if($n->email)
                                    <div class="text-[11px] text-slate-500">
                                        ✉️ {{ $n->email }}
                                    </div>
                                @endif
                                @if($n->tanggal_lahir || $n->tempat_lahir)
                                    <div class="text-[11px] text-slate-400">
                                        Lahir: {{ $n->tempat_lahir }}{{ $n->tempat_lahir && $n->tanggal_lahir ? ', ' : '' }}
                                        @if($n->tanggal_lahir)
                                            {{ \Carbon\Carbon::parse($n->tanggal_lahir)->format('d-m-Y') }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>

                        {{-- AGEN --}}
                        <td class="px-4 py-2 align-top">
                            @if($n->agent)
                                <div class="text-sm font-medium text-slate-800">
                                    {{ $n->agent->nama }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    Kode: {{ $n->agent->kode_agent }} • {{ $n->agent->jabatan }}
                                </div>
                            @else
                                <div class="text-[11px] text-slate-400 italic">
                                    Tidak ada agen
                                </div>
                            @endif
                        </td>

                        {{-- PEKERJAAN & USAHA --}}
                        <td class="px-4 py-2 align-top hidden sm:table-cell">
                            @if($n->jenis_pekerjaan || $n->bidang_usaha)
                                <div class="text-xs text-slate-800">
                                    {{ $n->jenis_pekerjaan }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    Bidang: {{ $n->bidang_usaha }}
                                </div>
                                @if($n->penghasilan)
                                    <div class="text-[11px] text-slate-500">
                                        Penghasilan: {{ number_format($n->penghasilan, 0, ',', '.') }}
                                    </div>
                                @endif
                            @else
                                <div class="text-[11px] text-slate-400 italic">
                                    Belum diisi
                                </div>
                            @endif
                        </td>

                        {{-- REKENING --}}
                        <td class="px-4 py-2 align-top hidden sm:table-cell">
                            @if($n->rekening_bank || $n->nomor_rekening)
                                <div class="text-xs text-slate-800">
                                    {{ $n->rekening_bank }}
                                </div>
                                <div class="text-[11px] text-slate-500">
                                    No: {{ $n->nomor_rekening }}
                                </div>
                                @if($n->nama_rekening)
                                    <div class="text-[11px] text-slate-500">
                                        a.n {{ $n->nama_rekening }}
                                    </div>
                                @endif
                                @if($n->tujuan_rekening)
                                    <div class="text-[11px] text-slate-400 mt-1">
                                        Tujuan: {{ $n->tujuan_rekening }}
                                    </div>
                                @endif
                            @else
                                <div class="text-[11px] text-slate-400 italic">
                                    Belum diisi
                                </div>
                            @endif
                        </td>

                        {{-- AKSI --}}
                        {{-- <td class="px-4 py-2 align-top">
                            @if(auth()->user()->role === 'admin')
                                <div class="flex flex-wrap gap-1">
                                    <a href="{{ route('nasabah.edit', $n) }}"
                                       class="px-2 py-1 rounded-md bg-amber-400/90 text-white text-[11px] hover:bg-amber-500">
                                        Edit
                                    </a>
                                    <form action="{{ route('nasabah.destroy', $n) }}" method="POST"
                                          onsubmit="return confirm('Hapus nasabah ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="px-2 py-1 rounded-md bg-red-500/90 text-white text-[11px] hover:bg-red-600">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="text-[11px] text-slate-400">Hanya admin yang bisa mengubah</span>
                            @endif
                        </td> --}}
                        <td class="inline-flex px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('nasabah.edit', $n) }}" class="...">Edit</a>
                                {{-- tombol hapus kalau ada --}}
                            @elseif(auth()->user()->role === 'coa')
                                <a href="{{ route('coa.nasabah.show', $n) }}">
                                    Lihat
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-xs text-slate-500">
                            Belum ada data nasabah yang tersimpan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-4 py-3 border-t border-slate-100">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 text-[11px] text-slate-500">
            <div>
                Menampilkan {{ $nasabah->count() }} dari total {{ $nasabah->total() }} nasabah.
            </div>
            <div class="text-xs">
                {{ $nasabah->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
