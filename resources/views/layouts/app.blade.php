<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kecilin - Perpendek URL')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'deep-bg': '#FAFAFA',
                        'primary': '#7C3AED',
                        'highlight': '#D946EF',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-zinc-50 min-h-screen flex flex-col text-zinc-800">

    {{-- Navbar --}}
    <nav class="bg-white border-b border-zinc-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="/" class="flex items-center gap-2">
                <span class="text-2xl font-extrabold text-violet-600 tracking-tight">Kecilin</span>
            </a>
            <div class="flex items-center gap-6 text-sm font-medium">
                <a href="/" class="text-zinc-600 hover:text-violet-600 transition-colors {{ request()->is('/') ? 'text-violet-600 font-semibold' : '' }}">Beranda</a>
                <a href="/analytics" class="text-zinc-600 hover:text-violet-600 transition-colors {{ request()->is('analytics') ? 'text-violet-600 font-semibold' : '' }}">Analitik</a>
                <a href="/history" class="text-zinc-600 hover:text-violet-600 transition-colors {{ request()->is('history') ? 'text-violet-600 font-semibold' : '' }}">Riwayat</a>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-white border-t border-zinc-200 mt-auto">
        <div class="max-w-5xl mx-auto px-4 py-5 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-zinc-400">
            <span>&copy; {{ date('Y') }} <span class="font-semibold text-violet-600">Kecilin</span>.
        </div>
    </footer>

</body>
</html>
