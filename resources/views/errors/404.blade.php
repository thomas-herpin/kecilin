@extends('layouts.app')

@section('title', 'Kecilin - Link Tidak Ditemukan')

@section('content')

<div class="max-w-5xl mx-auto px-4 py-24 flex flex-col items-center text-center">

    <div class="text-7xl mb-6">🔍</div>

    <h1 class="text-5xl font-extrabold text-violet-600 mb-2">404</h1>
    <h2 class="text-2xl font-bold text-zinc-800 mb-3">Link Tidak Ditemukan</h2>
    <p class="text-zinc-500 text-base max-w-md mb-8">
        Tautan yang kamu cari tidak ada atau sudah dihapus. Pastikan kamu menggunakan tautan yang benar.
    </p>

    <a
        href="/"
        class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-700 text-white font-semibold px-6 py-3 rounded-xl transition-colors text-sm"
    >
        ← Kembali ke Beranda
    </a>

</div>

@endsection
