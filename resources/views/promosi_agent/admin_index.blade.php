@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Pengajuan Naik Jabatan</h2>
            <p class="text-xs text-slate-500 mt-1">Daftar pengajuan promosi RM → BDP yang dibuat admin.</p>
        </div>

        <a href="{{ route('promosi.create') }}"
           class="inline-flex items-center px-3 py-2 rounded-lg bg-blue-600 text-white text-xs hover:bg-blue-700">
            + Buat Pengajuan
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="text-left px-4 py-3">Agent (RM)</th>
                        <th class="text-left px-4 py-3">Atasan BDP</th>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($items as $it)
                        @php $status = strtolower($it->status); @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3 align-top">
                                <div class="font-semibold text-slate-800">{{ $it->agent->nama ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $it->agent->kode_agent ?? '' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="text-slate-800">{{ $it->atasanBdp->nama ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $it->atasanBdp->kode_agent ?? '' }}
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="text-slate-800">{{ $it->tanggal_apply ? \Carbon\Carbon::parse($it->tanggal_apply)->format('d-m-Y') : '-' }}</div>
                                <div class="text-[11px] text-slate-500">
                                    Update: {{ $it->updated_at?->format('d-m-Y H:i') }}
                                </div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @if($status === 'pending')
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">PENDING</span>
                                @elseif($status === 'approved')
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">APPROVED</span>
                                @elseif($status === 'rejected')
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200">REJECTED</span>
                                @else
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">{{ strtoupper($it->status) }}</span>
                                @endif

                                @if($status === 'rejected' && $it->alasan_reject)
                                    <div class="mt-2 text-[11px] text-red-700 bg-red-50 border border-red-100 rounded-lg p-2">
                                        <div class="font-semibold">Alasan COA:</div>
                                        <div class="whitespace-pre-line">{{ $it->alasan_reject }}</div>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-right">
                                @if(in_array($status, ['pending','rejected']))
                                    <a href="{{ route('promosi.edit', $it) }}"
                                       class="inline-flex px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 text-[11px] hover:bg-slate-50">
                                        Edit
                                    </a>
                                @else
                                    <span class="text-[11px] text-slate-400">—</span>
                                @endif
                            </td>
                        </tr>

                        {{-- mobile row (optional) --}}
                        <tr class="md:hidden">
                            <td colspan="5" class="px-4 pb-3">
                                <div class="text-[11px] text-slate-500">
                                    Catatan Admin:
                                    <span class="text-slate-700">{{ $it->catatan_admin ?? '-' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                Belum ada pengajuan promosi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3">
            {{ $items->links() }}
        </div>
    </div>
</div>
@endsection
