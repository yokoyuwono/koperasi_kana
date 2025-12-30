@extends('layouts.app')

@section('content')
<div class="space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Riwayat Aktifitas</h1>
            <p class="text-xs text-slate-500 mt-1">
                Log sistem yang mencatat setiap perubahan data oleh admin dan COA.
            </p>
        </div>
    </div>

    {{-- Unified Activity Table --}}
    <div class="bg-white border border-slate-100 rounded-xl overflow-hidden shadow-sm">
        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100">
                    <tr>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Waktu</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Admin</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Entitas</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Aksi</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Perubahan</th>
                        <th class="text-left px-6 py-4 font-semibold uppercase tracking-wider text-[11px]">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4 text-xs text-slate-500 font-mono whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-900">{{ $log->admin_nama }}</div>
                                <div class="text-[10px] text-slate-400 uppercase tracking-tighter">{{ $log->admin_role }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide
                                    @switch($log->source)
                                        @case('Agent') bg-blue-50 text-blue-700 @break
                                        @case('Nasabah') bg-emerald-50 text-emerald-700 @break
                                        @case('Deposit') bg-amber-50 text-amber-700 @break
                                        @case('Komisi') bg-purple-50 text-purple-700 @break
                                        @default bg-slate-100 text-slate-700
                                    @endswitch">
                                    {{ $log->source }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full
                                    @if(Str::contains(strtolower($log->action), 'create')) bg-green-50 text-green-700
                                    @elseif(Str::contains(strtolower($log->action), 'update')) bg-blue-50 text-blue-700
                                    @elseif(Str::contains(strtolower($log->action), 'delete')) bg-red-50 text-red-700
                                    @elseif(Str::contains(strtolower($log->action), 'approve')) bg-emerald-50 text-emerald-700
                                    @elseif(Str::contains(strtolower($log->action), 'reject')) bg-rose-50 text-rose-700
                                    @else bg-slate-50 text-slate-600 @endif">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-mono text-slate-500 max-w-sm overflow-hidden truncate">
                                @if($log->new_data)
                                    <div class="hover:text-blue-600 cursor-help" title="{{ $log->new_data }}">
                                        {{ Str::limit($log->new_data, 50) }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-600 text-xs text-nowrap">
                                {{ Str::limit($log->note, 30) ?: '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">
                                Belum ada riwayat aktifitas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="md:hidden divide-y divide-slate-100">
            @forelse($logs as $log)
                <div class="p-4 space-y-2">
                    <div class="flex justify-between items-start">
                        <div class="text-[10px] text-slate-400 font-mono">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i:s') }}
                        </div>
                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase
                            @switch($log->source)
                                @case('Agent') bg-blue-50 text-blue-700 @break
                                @case('Nasabah') bg-emerald-50 text-emerald-700 @break
                                @case('Deposit') bg-amber-50 text-amber-700 @break
                                @case('Komisi') bg-purple-50 text-purple-700 @break
                                @default bg-slate-100 text-slate-700
                            @endswitch">
                            {{ $log->source }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <div class="font-bold text-slate-900">{{ $log->admin_nama }}</div>
                        <div class="text-xs font-bold
                            @if(Str::contains(strtolower($log->action), 'create')) text-green-600
                            @elseif(Str::contains(strtolower($log->action), 'update')) text-blue-600
                            @elseif(Str::contains(strtolower($log->action), 'delete')) text-red-600
                            @else text-slate-600 @endif">
                            {{ strtoupper($log->action) }}
                        </div>
                    </div>
                    <div class="text-[11px] text-slate-500 bg-slate-50 p-2 rounded italic space-y-1">
                        <div><strong>Note:</strong> {{ $log->note ?: '-' }}</div>
                        @if($log->new_data)
                        <div class="pt-1 border-t border-slate-100 font-mono text-[10px] break-all">
                            <strong>Data:</strong> {{ $log->new_data }}
                        </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-slate-400 text-sm">
                    Belum ada riwayat aktifitas.
                </div>
            @endforelse
        </div>
    </div>

    {{-- Pagination --}}
    <div class="pt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
