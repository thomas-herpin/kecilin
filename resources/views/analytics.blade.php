@extends('layouts.app')

@section('title', 'Kecilin - Analitik Klik')

@section('content')

<div class="max-w-5xl mx-auto px-4 py-12">
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-zinc-900">Analitik Klik</h1>
        <p class="text-sm text-zinc-500 mt-1">Tren klik harian untuk 30 hari terakhir.</p>
    </div>

    <div class="bg-white rounded-2xl border border-zinc-100 shadow-sm p-6">

        @if(empty($chartData))
            <div class="flex flex-col items-center justify-center py-16 text-zinc-400">
                <div class="text-5xl mb-4">📉</div>
                <p class="text-lg font-semibold text-zinc-500">Belum ada data klik.</p>
                <p class="text-sm mt-1">Data akan muncul setelah tautan kamu mulai dikunjungi.</p>
                <a href="/" class="inline-block mt-5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition-colors">
                    Buat Tautan Sekarang
                </a>
            </div>
        @else
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-zinc-600">Klik Harian</h2>
                <span class="text-xs text-zinc-400">30 hari terakhir</span>
            </div>
            <div class="relative" style="height: 320px;">
                <canvas id="clickChart"></canvas>
            </div>
        @endif

    </div>
</div>

@if(!empty($chartData))
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const chartData = @json($chartData);

    const labels = chartData.map(d => d.date);
    const counts = chartData.map(d => d.count);

    const ctx = document.getElementById('clickChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Klik',
                data: counts,
                backgroundColor: 'rgba(217, 70, 239, 0.2)',
                borderColor: '#D946EF',
                borderWidth: 2,
                borderRadius: 6,
                hoverBackgroundColor: 'rgba(217, 70, 239, 0.4)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.parsed.y} klik`
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#71717a', font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#71717a',
                        font: { size: 11 },
                        precision: 0
                    },
                    grid: { color: '#f4f4f5' }
                }
            }
        }
    });
</script>
@endif

@endsection
