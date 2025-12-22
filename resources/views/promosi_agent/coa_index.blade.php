@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Approval Promosi Agent</h2>
            <p class="text-xs text-slate-500 mt-1">COA memeriksa pengajuan RM → BDP.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" class="flex items-center gap-2 text-xs">
        <label class="text-slate-600">Filter status:</label>
        <select name="status" onchange="this.form.submit()"
                class="border border-slate-200 rounded-lg px-2 py-1 text-xs">
            @php
                $opts = ['all'=>'Semua','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'];
            @endphp
            @foreach($opts as $k => $v)
                <option value="{{ $k }}" {{ ($status ?? 'all') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </form>

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
                        @php $st = strtolower($it->status); @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-3">
                                <div class="font-semibold">{{ $it->agent->nama ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $it->agent->kode_agent ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div>{{ $it->atasanBdp->nama ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $it->atasanBdp->kode_agent ?? '' }}</div>
                            </td>
                            <td class="px-4 py-3">
                                {{ $it->tanggal_apply ? \Carbon\Carbon::parse($it->tanggal_apply)->format('d-m-Y') : '-' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($st === 'pending')
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">PENDING</span>
                                @elseif($st === 'approved')
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">APPROVED</span>
                                @elseif($st === 'rejected')
                                    <span class="inline-flex px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200">REJECTED</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('coa.promosi.show', $it) }}"
                                   class="inline-flex px-3 py-1.5 rounded-lg bg-blue-600 text-white text-[11px] hover:bg-blue-700">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-slate-500">
                                Belum ada pengajuan.
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
