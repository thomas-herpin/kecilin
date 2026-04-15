<?php

// 11.2 Integration test: Persistensi data analitik
// Buat tautan, akses beberapa kali, verifikasi data klik tersimpan dan terbaca kembali
// Property 10: Agregasi klik harian akurat
// Validates: Requirements 4.3, 7.2
// # Feature: kecilin-url-shortener, Property 10: daily click aggregation

use App\Models\Click;
use App\Models\Link;
use Illuminate\Support\Facades\DB;

test('clicks are persisted and readable after multiple accesses', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'persist1',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    // Simulate 3 human accesses
    for ($i = 0; $i < 3; $i++) {
        $this->get('/persist1');
    }

    expect($link->fresh()->total_clicks)->toBe(3);
    expect(Click::where('link_id', $link->id)->count())->toBe(3);
});

// Property 10: Agregasi klik harian akurat
// # Feature: kecilin-url-shortener, Property 10: daily click aggregation
test('daily click aggregation returns accurate count for clicks on the same day', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'agg001',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    $today = now()->startOfDay();
    $clickCount = 5;

    for ($i = 0; $i < $clickCount; $i++) {
        Click::create([
            'link_id'    => $link->id,
            'clicked_at' => $today->copy()->addMinutes($i),
        ]);
    }

    // Aggregate by day using SQLite-compatible query
    $result = DB::select(
        "SELECT DATE(clicked_at) as date, COUNT(*) as count
         FROM clicks
         WHERE link_id = ?
         GROUP BY DATE(clicked_at)",
        [$link->id]
    );

    expect($result)->toHaveCount(1);
    expect((int) $result[0]->count)->toBe($clickCount);
});

test('clicks on different days are aggregated into separate buckets', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'agg002',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    // 2 clicks yesterday, 3 clicks today
    Click::create(['link_id' => $link->id, 'clicked_at' => now()->subDay()->startOfDay()]);
    Click::create(['link_id' => $link->id, 'clicked_at' => now()->subDay()->startOfDay()->addHour()]);
    Click::create(['link_id' => $link->id, 'clicked_at' => now()->startOfDay()]);
    Click::create(['link_id' => $link->id, 'clicked_at' => now()->startOfDay()->addHour()]);
    Click::create(['link_id' => $link->id, 'clicked_at' => now()->startOfDay()->addHours(2)]);

    $result = DB::select(
        "SELECT DATE(clicked_at) as date, COUNT(*) as count
         FROM clicks
         WHERE link_id = ?
         GROUP BY DATE(clicked_at)
         ORDER BY date ASC",
        [$link->id]
    );

    expect($result)->toHaveCount(2);
    expect((int) $result[0]->count)->toBe(2); // yesterday
    expect((int) $result[1]->count)->toBe(3); // today
});
