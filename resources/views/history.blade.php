@extends('layouts.app')

@section('title', 'Kecilin - Riwayat Tautan')

@section('content')

<div class="max-w-5xl mx-auto px-4 py-12">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-extrabold text-zinc-900">Riwayat Tautan</h1>
            <p class="text-sm text-zinc-500 mt-1">Semua tautan yang telah kamu buat, diurutkan terbaru.</p>
        </div>
        <a href="/" class="text-sm bg-violet-600 hover:bg-violet-700 text-white font-semibold px-4 py-2 rounded-lg transition-colors">
            + Buat Tautan
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse($links as $link)
        <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-5 mb-4 hover:shadow-md transition-shadow">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">

                {{-- Slug & URL --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="inline-block bg-violet-100 text-violet-700 text-xs font-bold px-2.5 py-1 rounded-full">
                            /{{ $link->slug }}
                        </span>
                        <span class="text-xs text-zinc-400">{{ $link->created_at->format('d M Y') }}</span>
                    </div>
                    <p class="text-sm text-zinc-600 truncate" title="{{ $link->original_url }}">
                        {{ Str::limit($link->original_url, 70) }}
                    </p>
                </div>

                {{-- Click Count --}}
                <div class="flex items-center gap-1 text-sm text-zinc-500 sm:flex-col sm:items-end sm:gap-0">
                    <span class="text-lg font-bold text-fuchsia-500">{{ number_format($link->total_clicks) }}</span>
                    <span class="text-xs text-zinc-400">klik</span>
                </div>

            </div>

            {{-- Actions --}}
            <div class="mt-4 flex flex-wrap items-center gap-3 border-t border-zinc-50 pt-4">

                {{-- Edit Form --}}
                <form method="POST" action="/links/{{ $link->id }}" class="flex items-center gap-2 flex-1 min-w-0">
                    @csrf
                    @method('PUT')
                    <input
                        type="text"
                        name="url"
                        placeholder="URL baru..."
                        class="flex-1 min-w-0 border border-zinc-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-violet-400 transition"
                    >
                    <button
                        type="submit"
                        class="text-xs bg-zinc-100 hover:bg-zinc-200 text-zinc-700 font-medium px-3 py-1.5 rounded-lg transition-colors whitespace-nowrap"
                    >
                        Simpan
                    </button>
                </form>

                {{-- Delete Form --}}
                <form
                    method="POST"
                    action="/links/{{ $link->id }}"
                    onsubmit="return confirm('Hapus tautan /{{ $link->slug }}? Semua data klik akan ikut terhapus.')"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="text-xs bg-red-50 hover:bg-red-100 text-red-600 font-medium px-3 py-1.5 rounded-lg transition-colors"
                    >
                        Hapus
                    </button>
                </form>

            </div>
        </div>
    @empty
        <div class="text-center py-20 text-zinc-400">
            <div class="text-5xl mb-4">🔗</div>
            <p class="text-lg font-semibold text-zinc-500">Belum ada tautan.</p>
            <p class="text-sm mt-1">Mulai dengan memendekkan URL pertamamu!</p>
            <a href="/" class="inline-block mt-5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                Buat Tautan Pertama
            </a>
        </div>
    @endforelse
</div>

@endsection
