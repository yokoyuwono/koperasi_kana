@extends('layouts.app')

@section('content')
@php
    $fmt = fn($n) => 'Rp ' . number_format((float)$n, 0, ',', '.');
@endphp

<div class="space-y-4">
    <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-indigo-900">Dashboard Superadmin</h1>
            <p class="text-xs text-indigo-700 mt-1">Akses penuh ke seluruh operasional sistem.</p>
        </div>
        <div class="text-3xl">🚀</div>
    </div>

    {{-- Quick actions --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-2">
        <a href="{{ route('users.index') }}" class="bg-white border border-slate-100 rounded-xl p-3 hover:bg-slate-50">
            <div class="text-xs text-slate-500">Manage</div>
            <div class="font-semibold text-slate-800 mt-1">Users</div>
        </a>
        <a href="{{ url('/agents/create') }}" class="bg-white border border-slate-100 rounded-xl p-3 hover:bg-slate-50">
            <div class="text-xs text-slate-500">Tambah</div>
            <div class="font-semibold text-slate-800 mt-1">Agent</div>
        </a>
        <a href="{{ url('/nasabah/create') }}" class="bg-white border border-slate-100 rounded-xl p-3 hover:bg-slate-50">
            <div class="text-xs text-slate-500">Tambah</div>
            <div class="font-semibold text-slate-800 mt-1">Nasabah</div>
        </a>
        <a href="{{ url('/deposits/create') }}" class="bg-white border border-slate-100 rounded-xl p-3 hover:bg-slate-50">
            <div class="text-xs text-slate-500">Input</div>
            <div class="font-semibold text-slate-800 mt-1">Deposit</div>
        </a>
        <a href="{{ url('/promosi-agent/create') }}" class="bg-white border border-slate-100 rounded-xl p-3 hover:bg-slate-50">
            <div class="text-xs text-slate-500">Ajukan</div>
            <div class="font-semibold text-slate-800 mt-1">Promosi</div>
        </a>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="bg-white border border-slate-100 rounded-xl p-4">
            <div class="text-xs text-slate-500">Master Data</div>
            <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                <div class="rounded-lg bg-slate-50 p-3 border border-slate-100">
                    <div class="text-slate-500 text-[11px]">Total Agent</div>
                    <div class="text-lg font-semibold text-slate-800">{{ $totalAgents }}</div>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 border border-slate-100">
                    <div class="text-slate-500 text-[11px]">Total Nasabah</div>
                    <div class="text-lg font-semibold text-slate-800">{{ $totalNasabah }}</div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-xl p-4">
            <div class="text-xs text-slate-500">Deposit Overview</div>
            <div class="mt-2 space-y-2 text-xs">
                <div class="flex items-center justify-between rounded-lg bg-slate-50 p-3 border border-slate-100">
                    <span class="text-slate-600">Draft</span>
                    <span class="font-semibold text-slate-800">{{ $depositDraftCount }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-amber-50 p-3 border border-amber-100">
                    <span class="text-amber-700">Pending COA</span>
                    <span class="font-semibold text-amber-800">{{ $depositPendingCount }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-red-50 p-3 border border-red-100">
                    <span class="text-red-700">Rejected</span>
                    <span class="font-semibold text-red-800">{{ $depositRejectedCount }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-100 rounded-xl p-4">
            <div class="text-xs text-slate-500">Promosi Overview</div>
            <div class="mt-2 space-y-2 text-xs">
                <div class="flex items-center justify-between rounded-lg bg-amber-50 p-3 border border-amber-100">
                    <span class="text-amber-700">Pending COA</span>
                    <span class="font-semibold text-amber-800">{{ $promosiPendingCount }}</span>
                </div>
                <div class="flex items-center justify-between rounded-lg bg-red-50 p-3 border border-red-100">
                    <span class="text-red-700">Rejected</span>
                    <span class="font-semibold text-red-800">{{ $promosiRejectedCount }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
