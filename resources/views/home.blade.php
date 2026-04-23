@extends('layouts.app')

@section('title', 'Kecilin - Perpendek URL')

@section('content')

{{-- Hero Section --}}
<section class="max-w-5xl mx-auto px-6 pt-20 pb-12 text-center">
    
    <h1 class="text-4xl md:text-6xl font-black text-zinc-900 leading-tight mb-4 tracking-tight">
    Link panjang? 
        <span class="inline-block">
            <span class="text-transparent bg-clip-text bg-linear-to-r bg-violet-600">Kecilin</span> aja.
        </span>
    </h1>
    <p class="text-zinc-500 text-base md:text-lg max-w-2xl mx-auto mb-12 leading-relaxed px-4">
        Buat tautan pendek bermerek, lacak klik, dan dapatkan QR Code, gratis.
    </p>
    {{-- Error Messages --}}
    @if($errors->any())
        <div class="mb-8 max-w-xl mx-auto bg-red-50 border border-red-100 rounded-2xl p-4 text-left flex gap-3 items-center animate-shake">
            <div class="text-red-500 text-xl">⚠️</div>
            <ul class="text-sm text-red-600 font-medium">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Form --}}
    <div class="max-w-2xl mx-auto relative group">
        <div class="absolute -inset-1 bg-linear-to-r from-violet-600 to-fuchsia-500 rounded-3xl blur opacity-15 group-hover:opacity-25 transition duration-1000"></div>
        <form method="POST" action="/shorten" class="relative bg-white/80 backdrop-blur-xl rounded-2xl shadow-2xl border border-white p-6 md:p-8 text-left space-y-6">
            @csrf
            <div class="space-y-2">
                <label for="url" class="block text-sm font-bold text-zinc-800 uppercase tracking-wider">URL Panjang</label>
                <div class="relative">
                    <input
                        type="url"
                        id="url"
                        name="url"
                        placeholder="https://contoh.com/url-sangat-panjang"
                        value="{{ old('url') }}"
                        class="w-full bg-zinc-50 border border-zinc-200 rounded-xl px-5 py-4 text-base focus:outline-none focus:ring-2 focus:ring-violet-500 focus:bg-white transition-all"
                        required
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label for="alias" class="block text-sm font-bold text-zinc-800 uppercase tracking-wider">
                        Alias Kustom <span class="text-zinc-400 font-normal lowercase">(opsional)</span>
                    </label>
                    <div class="flex items-center group/input">
                        <span class="bg-zinc-100 border border-r-0 border-zinc-200 rounded-l-xl px-4 py-4 text-sm text-zinc-500 font-medium">kecilin.app/</span>
                        <input
                            type="text"
                            id="alias"
                            name="alias"
                            placeholder="brand-kamu"
                            value="{{ old('alias') }}"
                            class="w-full border border-zinc-200 rounded-r-xl px-4 py-4 text-sm focus:outline-none focus:ring-2 focus:ring-violet-500 transition-all"
                        >
                    </div>
                </div>
                
                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full bg-zinc-900 hover:bg-violet-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-zinc-200 hover:shadow-violet-200 transition-all duration-300 transform hover:-translate-y-1 active:scale-95"
                    >
                        Kecilin Sekarang →
                    </button>
                </div>
            </div>            
        </form>
    </div>
</section>

{{-- Result Section --}}
@if(isset($link))
    @if(!app()->environment('testing'))
        @vite(['resources/js/home.js'])
    @endif

    <section class="max-w-2xl mx-auto px-6 pb-16 animate-slide-up">
        <div class="bg-black rounded-[2.5rem] p-10 md:p-14 text-white relative overflow-hidden shadow-2xl border border-white/5">
            
            <div class="relative z-10 flex flex-col md:flex-row gap-10 md:gap-12 items-center justify-between">
                <div class="flex-1 space-y-6 text-center md:text-left">
                    <div>
                        <h2 class="text-3xl font-bold tracking-tight mb-3">Link berhasil dibuat!</h2>
                        <p class="text-zinc-400 text-sm leading-relaxed max-w-xs mx-auto md:mx-0">
                            Kamu bisa melihat daftar tautan yang kamu buat di halaman riwayat.
                        </p>
                    </div>

                    <div class="space-y-4">
                        <div class="text-xl md:text-xl font-bold text-violet-500 break-all leading-tight">
                            {{ $link->full_short_url }}
                        </div>

                        <button 
                            onclick="copyToClipboard('{{ $link->full_short_url }}')"
                            class="inline-flex items-center gap-2 text-violet-400 hover:text-violet-300 transition-colors group"
                        >
                            <span class="material-symbols-outlined text-lg group-active:scale-90 transition-transform">
                                content_copy
                            </span>
                            <span id="copy-text" class="text-sm font-medium">Salin Tautan</span>
                        </button>
                    </div>
                </div>

                <div class="flex flex-col items-center gap-6 shrink-0">
                    <div id="qr-container" class="bg-white p-4 rounded-4xl shadow-2xl w-50 h-50 md:w-50 md:h-50 flex items-center justify-center">
                        <div class="w-full h-full text-black flex items-center justify-center">
                            {!! $link->qr_code_svg !!}
                        </div>
                    </div>
                    
                    <button 
                        onclick="downloadQR(this)"
                        data-slug="{{ $link->slug }}"
                        class="flex items-center gap-2 px-6 py-3 bg-zinc-800 hover:bg-zinc-700 text-white rounded-full text-[10px] font-bold uppercase tracking-widest transition-all active:scale-95 shadow-lg border border-white/5"
                    >
                        <span class="material-symbols-outlined text-sm">download</span>
                        Unduh QR
                    </button>
                </div>

            </div>
        </div>
    </section>
@endif

{{-- Bento Grid Features --}}
<section class="max-w-5xl mx-auto px-6 pb-20 pt-5">
    <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
        
        {{-- Insight --}}
        <div class="md:col-span-4 bg-white rounded-[2.5rem] border border-zinc-100 shadow-sm p-10 hover:shadow-xl hover:shadow-violet-500/5 transition-all duration-500 group overflow-hidden relative">
            <div class="absolute -right-8 -bottom-8 text-zinc-50 group-hover:text-violet-100/50 group-hover:scale-110 transition-all duration-700">
                <svg class="w-64 h-64" fill="currentColor" viewBox="0 0 24 24"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg>
            </div>
            <div class="relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-violet-600 to-violet-400 text-white flex items-center justify-center text-2xl mb-8 shadow-lg shadow-violet-200">📊</div>
                <h3 class="text-xl font-bold text-zinc-900 mb-3">Insight Performa Kinetik</h3>
                <p class="text-zinc-500 max-w-sm leading-relaxed">Pantau lonjakan klik dan sumber trafik secara real-time dengan dashboard analitik kami.</p>
            </div>
        </div>

        {{-- QR Code --}}
        <div class="md:col-span-2 bg-zinc-900 rounded-[2.5rem] p-10 text-white hover:bg-zinc-800 transition-all duration-500 relative group overflow-hidden">
            <div class="w-14 h-14 rounded-2xl bg-violet-600 text-white flex items-center justify-center mb-8 shadow-lg shadow-fuchsia-900/20">
                <span class="material-symbols-outlined text-3xl">
                    qr_code_2
                </span>
            </div>
            <h3 class="text-xl font-bold mb-3">QR Code Instan</h3>
            <p class="text-zinc-400 text-sm leading-relaxed">Setiap link otomatis memiliki QR Code SVG siap cetak.</p>
        </div>

        {{-- Security --}}
        <div class="md:col-span-2 bg-white rounded-[2.5rem] border border-zinc-100 p-10 hover:shadow-xl hover:shadow-red-500/5 transition-all duration-500 group overflow-hidden relative">
            <div class="absolute -right-8 -bottom-8 text-zinc-50 group-hover:text-violet-100/50 group-hover:scale-110 transition-all duration-700">
                <svg class="w-40 h-40" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                </svg>
            </div>

            <div class="relative z-10">
                <div class="w-14 h-14 rounded-2xl bg-linear-to-br from-violet-600 to-violet-400 text-white flex items-center justify-center text-2xl mb-8 shadow-lg shadow-violet-200">🛡️</div>

                <h3 class="text-xl font-bold text-zinc-900 mb-3 tracking-tight">Saring Tautan</h3>
                <p class="text-zinc-500 text-sm leading-relaxed">
                    Proteksi blacklist domain berbahaya secara otomatis demi keamanan aset digital Anda.
                </p>
            </div>
        </div>

        {{-- Custom Alias --}}
        <div class="md:col-span-4 bg-zinc-900 rounded-[2.5rem] p-10 text-white hover:bg-zinc-800 transition-all duration-500 relative group overflow-hidden">
            <div class="relative z-10 flex flex-col items-start gap-8">
                
                <div class="text-left">
                    <h3 class="text-xl font-bold mb-3">Kustom Alias</h3>
                    <p class="text-zinc-400 max-w-md leading-relaxed text-sm md:text-base">
                        Gunakan identitas merekmu sendiri untuk meningkatkan rasio klik (CTR).
                    </p>
                </div>
        
                <div class="relative w-auto flex justify-start">
                    <div class="relative bg-white/10 backdrop-blur-xl rounded-2xl px-5 py-3 md:px-6 md:py-4 border border-white/20 shadow-2xl flex items-center shrink-0">
                        <span class="text-zinc-500 font-mono text-xs md:text-sm">kecilin.app/</span>
                        <span class="text-violet-600 font-mono font-bold text-xs md:text-sm">brand-kamu</span>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</section>

<style>
    @keyframes fade-in { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes slide-up { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
    .animate-fade-in { animation: fade-in 0.6s ease-out forwards; }
    .animate-slide-up { animation: slide-up 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    .animate-shake { animation: shake 0.4s ease-in-out; }
</style>

@endsection