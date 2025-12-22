<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Koperasi KANA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center">

    <div class="w-full max-w-md px-4">
        <div class="bg-white rounded-2xl shadow-md border border-slate-100 p-6 md:p-8">
            <h1 class="text-xl font-semibold text-slate-800 mb-1 text-center">
                Koperasi KANA Admin
            </h1>
            <p class="text-xs text-slate-500 text-center mb-6">
                Masuk untuk mengelola agen & nasabah deposito.
            </p>

            @if($errors->any())
                <div class="mb-4 px-3 py-2 bg-red-50 border border-red-100 text-xs text-red-600 rounded-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           required autofocus>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">Password</label>
                    <input type="password" name="password"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                           required>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-600">
                    
                    <span class="text-[11px] text-slate-400">
                        Demo: admin@example.com / qwer@1234
                    </span>
                </div>

                <button
                    class="w-full mt-2 px-4 py-2 text-sm font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Masuk
                </button>
            </form>
        </div>

        <p class="mt-4 text-[11px] text-slate-400 text-center">
            © {{ date('Y') }} Koperasi KANA
        </p>
    </div>

</body>
</html>
