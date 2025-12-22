@extends('layouts.app')

@section('content')
<div class="mb-4">
    <h2 class="text-xl font-semibold">Approval Deposito (COA)</h2>
    <p class="text-xs text-slate-500 mt-1">
        Daftar deposito yang menunggu persetujuan COA.
    </p>
</div>

<div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full text-xs">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">No</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Nasabah</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Agent</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Nominal</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Tgl Transaksi</th>
                    <th class="px-3 py-2 text-left font-semibold text-slate-600">Status</th>
                    <th class="px-3 py-2 text-right font-semibold text-slate-600">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $index => $deposit)
                    <tr class="border-b last:border-b-0">
                        <td class="px-3 py-2 align-top">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2 align-top">
                            <div class="font-semibold text-slate-800">
                                {{ $deposit->nasabah->nama ?? '-' }}
                            </div>
                            <div class="text-[11px] text-slate-500">
                                No Bilyet: {{ $deposit->no_bilyet }}
                            </div>
                        </td>
                        <td class="px-3 py-2 align-top">
                            <div class="font-semibold text-slate-800">
                                {{ $deposit->agent->nama ?? '-' }}
                            </div>
                            <div class="text-[11px] text-slate-500">
                                {{ $deposit->agent->jabatan ?? '-' }}
                            </div>
                        </td>
                        <td class="px-3 py-2 align-top">
                            <div class="font-mono text-slate-800">
                                Rp {{ number_format($deposit->nominal, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-3 py-2 align-top text-slate-700">
                            {{ optional($deposit->tanggal_transaksi)->format('d/m/Y') }}
                        </td>
                        <td class="px-3 py-2 align-top">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] bg-amber-50 text-amber-700 border border-amber-200">
                                Pending COA
                            </span>
                        </td>
                        <td class="px-3 py-2 align-top text-right">
                            <a href="{{ route('coa.deposits.show', $deposit) }}"
                               class="inline-flex items-center px-3 py-1.5 rounded-lg text-[11px] bg-blue-600 text-white hover:bg-blue-700">
                                Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-4 text-center text-xs text-slate-500">
                            Belum ada deposito dengan status pending.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-3 py-2 border-t border-slate-100">
        {{ $deposits->links() }}
    </div>
</div>
@endsection
