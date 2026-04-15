@extends('layouts.app')

@section('title', 'Kecilin - Perpendek URL')

@section('content')

{{-- Hero Section --}}
<section class="max-w-5xl mx-auto px-4 pt-16 pb-10 text-center">
    <h1 class="text-4xl sm:text-5xl font-extrabold text-zinc-900 leading-tight mb-3">
        Link panjang? <span class="text-violet-600">Kecilin</span> aja.
    </h1>
    <p class="text-zinc-500 text-lg mb-10">Buat tautan pendek bermerek, lacak klik, dan dapatkan QR Code — gratis.</p>

    {{-- Error Messages --}}
    @if($errors->any())
        <div class="mb-6 max-w-xl mx-auto bg-red-50 border border-red-200 rounded-xl p-4 text-left">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Shorten Form --}}
    <form method="POST" action="/shorten" class="max-w-xl mx-auto bg-white rounded-2xl shadow-md border border-zinc-100 p-6 text-left space-y-4">
        @csrf
        <div>
            <label for="url" class="block text-sm font-semibold text-zinc-700 mb-1">URL Panjang</label>
            <input
                type="text"
                id="url"
                name="url"
                placeholder="https://example.com/url-yang-sangat-panjang"
                value="{{ old('url') }}"
                class="w-full border border-zinc-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-transparent transition"
                required
            >
        </div>
        <div>
            <label for="alias" class="block text-sm font-semibold text-zinc-700 mb-1">
                Alias Kustom <span class="text-zinc-400 font-normal">(opsional)</span>
            </label>
            <div class="flex items-center gap-2">
                <span class="text-sm text-zinc-400 whitespace-nowrap">kecilin.app/</span>
                <input
                    type="text"
                    id="alias"
                    name="alias"
                    placeholder="nama-brandmu"
                    value="{{ old('alias') }}"
                    class="flex-1 border border-zinc-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-fuchsia-400 focus:border-transparent transition"
                >
            </div>
            <p class="text-xs text-zinc-400 mt-1">3–50 karakter: huruf, angka, tanda hubung, garis bawah.</p>
        </div>
        <button
            type="submit"
            class="w-full bg-violet-600 hover:bg-violet-700 text-white font-semibold py-2.5 rounded-lg transition-colors text-sm"
        >
            Kecilin Sekarang →
        </button>
    </form>
</section>

{{-- Result Section --}}
@if(isset($link))
<section class="max-w-xl mx-auto px-4 pb-10">
    <div class="bg-white rounded-2xl shadow-md border border-violet-100 p-6">
        <h2 class="text-base font-semibold text-zinc-700 mb-4">🎉 Tautan berhasil dipendekkan!</h2>
        <div class="flex items-center gap-3 bg-violet-50 rounded-lg px-4 py-3 mb-4">
            <a href="{{ $shortUrl }}" target="_blank" class="text-violet-600 font-semibold text-sm break-all hover:underline">
                {{ $shortUrl }}
            </a>
            <button
                onclick="navigator.clipboard.writeText('{{ $shortUrl }}')"
                class="ml-auto text-xs bg-violet-600 hover:bg-violet-700 text-white px-3 py-1.5 rounded-md transition-colors whitespace-nowrap"
            >
                Salin
            </button>
        </div>
        @if(isset($qrCode))
        <div class="flex flex-col items-center gap-2">
            <p class="text-xs text-zinc-400 font-medium">QR Code</p>
            <div class="p-3 bg-white border border-zinc-100 rounded-xl shadow-sm">
                {!! $qrCode !!}
            </div>
        </div>
        @endif
    </div>
</section>
@endif

{{-- Bento Grid Features --}}
<section class="max-w-5xl mx-auto px-4 pb-16">
    <h2 class="text-xl font-bold text-zinc-800 mb-6 text-center">Semua yang kamu butuhkan</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        {{-- Feature 1: Insight Performa --}}
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 flex gap-4 items-start hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600 text-xl flex-shrink-0">📊</div>
            <div>
                <h3 class="font-semibold text-zinc-800 mb-1">Insight Performa Tautan</h3>
                <p class="text-sm text-zinc-500">Pantau jumlah klik dan tren harian secara real-time di dashboard analitik.</p>
            </div>
        </div>

        {{-- Feature 2: QR Code --}}
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 flex gap-4 items-start hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-xl bg-fuchsia-100 flex items-center justify-center text-fuchsia-500 text-xl flex-shrink-0">📱</div>
            <div>
                <h3 class="font-semibold text-zinc-800 mb-1">QR Code Instan</h3>
                <p class="text-sm text-zinc-500">Setiap tautan pendek otomatis dilengkapi QR Code SVG yang siap diunduh dan dibagikan.</p>
            </div>
        </div>

        {{-- Feature 3: Blacklist --}}
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 flex gap-4 items-start hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center text-red-500 text-xl flex-shrink-0">🛡️</div>
            <div>
                <h3 class="font-semibold text-zinc-800 mb-1">Saring Tautan Berbahaya</h3>
                <p class="text-sm text-zinc-500">Domain berbahaya diblokir otomatis sebelum tautan dibuat, menjaga platform tetap aman.</p>
            </div>
        </div>

        {{-- Feature 4: Custom Alias --}}
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6 flex gap-4 items-start hover:shadow-md transition-shadow">
            <div class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center text-violet-600 text-xl flex-shrink-0">✨</div>
            <div>
                <h3 class="font-semibold text-zinc-800 mb-1">Personalisasi Identitas</h3>
                <p class="text-sm text-zinc-500">Gunakan alias kustom untuk tautan yang mencerminkan identitas merek atau kontenmu.</p>
            </div>
        </div>

    </div>
</section>

@endsection
