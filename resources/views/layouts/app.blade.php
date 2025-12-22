{{-- ====== SIDEBAR MENU (contoh patch) ====== --}}
@php
  $u = auth()->user();
  $role = $u->role ?? null; // sesuaikan kalau kamu pakai role field lain / spatie
@endphp
<!DOCTYPE html>
<html lang="en">
    
<head>
    <meta charset="UTF-8">
    <title>Koperasi KANA - Admin</title>

    {{-- Viewport untuk responsive --}}
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind via CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

</head>
<body class="bg-slate-100 min-h-screen text-slate-800">

    <div class="min-h-screen flex">
        {{-- SIDEBAR DESKTOP --}}
        <aside class="hidden md:block w-64 bg-slate-900 text-slate-100">
            <div class="px-4 py-4 border-b border-slate-800">
                <div class="text-lg font-semibold tracking-wide">
                    KANA Admin
                </div>
                <div class="text-xs text-slate-400 mt-1">
                    Deposito & Agen Management
                </div>
            </div>

            <nav class="mt-4 px-2 space-y-1 text-sm">
                {{-- USER (RM/BDP) --}}
                @if(in_array($role, ['rm','bdp']))
                    <a href="{{ route('user.dashboard') }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->routeIs('user.dashboard') ? 'bg-slate-800' : '' }}">
                        <span>🏠</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('user.komisi') }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->routeIs('user.komisi') ? 'bg-slate-800' : '' }}">
                        <span>💰</span>
                        <span>Komisi</span>
                    </a>
                @else
                    @if(auth()->user()->role === 'coa')
                        <a href="{{ route('coa.dashboard') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800
                                {{ request()->routeIs('coa.dashboard')  ? 'bg-slate-800' : '' }}">
                            <span>🏠</span>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('coa.agents.index') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->is('agents*') ? 'bg-slate-800' : '' }}">
                            <span>👤</span>
                            <span>Agents</span>
                        </a>                    
                        <a a href="{{ route('coa.nasabah.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->is('nasabah*') ? 'bg-slate-800' : '' }}">
                            <span>👥</span>
                            <span>Nasabah</span>
                        </a>
                        <a href="{{ route('coa.deposits.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->is('deposits*') ? 'bg-slate-800' : '' }}">
                            <span>💰</span>
                            <span>Deposito</span>
                        </a>
                        <a href="{{ route('coa.promosi.index') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800
                                {{ request()->routeIs('coa.promosi.*') ? 'bg-slate-800' : ''  }}">
                            <span>🎖</span>
                            <span>Promosi Agent</span>
                        </a>
                    @endif
                    @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800
                                {{ request()->routeIs('admin.dashboard')  ? 'bg-slate-800' : '' }}">
                            <span>🏠</span>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('agents.index') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->is('agents*') ? 'bg-slate-800' : '' }}">
                            <span>👤</span>
                            <span>Agents</span>
                        </a>                    
                        <a a href="{{ route('nasabah.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->is('nasabah*') ? 'bg-slate-800' : '' }}">
                            <span>👥</span>
                            <span>Nasabah</span>
                        </a>
                        <a href="{{ route('deposits.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->is('deposits*') ? 'bg-slate-800' : '' }}">
                            <span>💰</span>
                            <span>Pengajuan Komisi</span>
                        </a>
                         <a href="{{ route('promosi.index') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->routeIs('promosi.*') ? 'bg-slate-800' : ''  }}">
                            <span>🎖</span>     
                            <span>Promosi Agent</span>
                        </a>
                        <a href="{{ route('komisi.report') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800
                                {{ request()->routeIs('komisi.report*') ? 'bg-slate-800' : '' }}">
                            <span>📊</span>
                            <span>Laporan Komisi</span>
                        </a>
                    @endif
                @endif
            </nav>
        </aside>

        {{-- MOBILE SIDEBAR (OVERLAY) --}}
        <div id="mobileSidebar"
             class="fixed inset-0 z-40 md:hidden hidden">
            {{-- backdrop --}}
            <div class="absolute inset-0 bg-black/40" onclick="toggleSidebar()"></div>

            {{-- panel --}}
            <div class="relative h-full w-72 max-w-[80%] bg-slate-900 text-slate-100 shadow-xl">
                <div class="px-4 py-4 border-b border-slate-800 flex items-center justify-between">
                    <div>
                        <div class="text-lg font-semibold tracking-wide">
                            KANA Admin
                        </div>
                        <div class="text-xs text-slate-400 mt-1">
                            Menu navigasi
                        </div>
                    </div>
                    <button type="button"
                            class="text-slate-300 hover:text-white"
                            onclick="toggleSidebar()">
                        ✕
                    </button>
                </div>

                <nav class="mt-4 px-2 space-y-1 text-sm">
                    @if(auth()->user()->role === 'coa')
                        <a href="{{ route('coa.dashboard') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800
                                {{ request()->routeIs('coa.dashboard')  ? 'bg-slate-800' : '' }}">
                            <span>🏠</span>
                            <span>Dashboard</span>
                        </a>
                        
                    @endif
                     @if(auth()->user()->role === 'admin')
                        <a href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800
                                {{ request()->routeIs('admin.dashboard')  ? 'bg-slate-800' : '' }}">
                            <span>🏠</span>
                            <span>Dashboard</span>
                        </a>
                    @endif
                    <a href="{{ route('agents.index') }}"
                       class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->is('agents*') ? 'bg-slate-800' : '' }}"
                       onclick="toggleSidebar()">
                        <span>👤</span>
                        <span>Agents</span>
                    </a>
                    <div
                       class="flex items-center gap-2 px-3 py-2 rounded-md opacity-60 cursor-not-allowed">
                        <span>👥</span>
                        <span>Nasabah </span>
                    </div>
                    <div>
                        <a href="{{ route('deposits.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->is('deposits*') ? 'bg-slate-800' : '' }}">
                        <span>💰</span>
                        <span>Deposito</span>
                    </a>
                    </div>
                    <div>
                        {{-- PROMOSI AGENT --}}
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('promosi.index') }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800 {{ request()->routeIs('promosi.*') ? 'bg-slate-800' : ''  }}"
                            onclick="toggleSidebar()">
                                <span>🎖</span>     
                                <span>Promosi Agent</span>
                            </a>
                        @endif

                        @if(auth()->user()->role === 'coa')
                            <a href="{{ route('coa.promosi.index') }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-md hover:bg-slate-800
                                    {{ request()->routeIs('coa.promosi.*') ? 'bg-slate-800' : ''  }}"
                                    onclick="toggleSidebar()">
                                <span>🎖</span>
                                <span>Promosi Agent</span>
                            </a>
                        @endif

                    </div>
                    <div>
                        @if(auth()->user()->role === 'admin')
                            {{-- <a href="{{ route('komisi.report') }}"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm
                                    {{ request()->routeIs('komisi.report*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50' }}"
                                    onclick="toggleSidebar()">
                                <span>Laporan Komisi</span>
                            </a> --}}
                        @endif  
                </nav>
            </div>
        </div>

        {{-- AREA UTAMA (TOPBAR + CONTENT) --}}
        <div class="flex-1 flex flex-col">

            {{-- TOPBAR --}}
            <header class="bg-white border-b border-slate-200">
                <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        {{-- tombol menu hanya di mobile --}}
                        <button type="button"
                                class="md:hidden inline-flex items-center justify-center h-9 w-9 rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50"
                                onclick="toggleSidebar()">
                            ☰
                        </button>
                        <div>
                            <h1 class="text-base md:text-lg font-semibold">
                                Koperasi KANA Admin
                            </h1>
                            <p class="text-xs text-slate-500">
                                Administrasi untuk mengelola agen & nasabah.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        @auth
                            <div class="hidden md:block text-right">
                                <div class="text-sm font-medium">
                                    {{ auth()->user()->nama ?? auth()->user()->email }}
                                </div>
                                <div class="text-xs text-slate-500 uppercase">
                                    Role: {{ auth()->user()->role }}
                                </div>
                            </div>

                            <div class="relative">
                                {{-- avatar + dropdown logout sederhana --}}
                                <button type="button"
                                        class="h-9 w-9 rounded-full bg-slate-200 flex items-center justify-center text-[11px] font-semibold text-slate-700"
                                        onclick="document.getElementById('userMenu').classList.toggle('hidden')">
                                    {{ strtoupper(substr(auth()->user()->nama ?? auth()->user()->email, 0, 2)) }}
                                </button>

                                <div id="userMenu"
                                    class="hidden absolute right-0 mt-2 w-40 bg-white border border-slate-200 rounded-lg shadow-lg text-xs z-30">
                                    <div class="px-3 py-2 border-b border-slate-100">
                                        <div class="font-medium text-slate-800 truncate">
                                            {{ auth()->user()->nama ?? auth()->user()->email }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 uppercase">
                                            {{ auth()->user()->role }}
                                        </div>
                                    </div>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button
                                            class="w-full text-left px-3 py-2 hover:bg-slate-50 text-red-600">
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endauth
                    </div>
                </div>
            </header>

            {{-- CONTENT --}}
            <main class="flex-1">
                <div class="max-w-6xl mx-auto px-4 py-6">
                    @yield('content')
                </div>
            </main>

            {{-- FOOTER --}}
            <footer class="border-t border-slate-200 bg-white">
                <div class="max-w-6xl mx-auto px-4 py-3 text-[11px] text-slate-400 flex flex-col sm:flex-row justify-between gap-1">
                    <span>© {{ date('Y') }} Koperasi KANA</span>
                </div>
            </footer>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const el = document.getElementById('mobileSidebar');
            if (!el) return;
            el.classList.toggle('hidden');
        }
        // tutup menu user jika klik di luar
        document.addEventListener('click', function (e) {
            const menu = document.getElementById('userMenu');
            if (!menu) return;

            const avatarBtn = document.querySelector('button[onclick*="userMenu"]');
            if (avatarBtn && (avatarBtn.contains(e.target) || menu.contains(e.target))) {
                return;
            }
            menu.classList.add('hidden');
        });
        
    </script>
</body>
</html>
