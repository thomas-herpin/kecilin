<?php

// 11.5 Integration test: Error 404 Handling + Cascade Delete
// GET /slug-yang-tidak-ada → verifikasi response 404 dengan konten halaman kustom
// Property 9: Penghapusan tautan menghapus semua data klik terkait
// Validates: Requirements 8.2, 3.3
// # Feature: kecilin-url-shortener, Property 9: cascade delete

use App\Models\Click;
use App\Models\Link;

test('GET /{slug} with unknown slug returns 404 with custom page', function () {
    $response = $this->get('/slug-yang-tidak-ada');

    $response->assertStatus(404);
    $response->assertSee('404');
});

// Property 9: Penghapusan tautan menghapus semua data klik terkait (CASCADE)
// # Feature: kecilin-url-shortener, Property 9: cascade delete
test('deleting a link cascades and removes all related click records', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'cascade1',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    // Create some clicks
    Click::create(['link_id' => $link->id, 'clicked_at' => now()]);
    Click::create(['link_id' => $link->id, 'clicked_at' => now()]);
    Click::create(['link_id' => $link->id, 'clicked_at' => now()]);

    expect(Click::where('link_id', $link->id)->count())->toBe(3);

    // Delete the link via the route
    $response = $this->delete('/links/' . $link->id);
    $response->assertRedirect('/history');

    // Link should be gone
    expect(Link::find($link->id))->toBeNull();

    // All related clicks should be gone (CASCADE)
    expect(Click::where('link_id', $link->id)->count())->toBe(0);
});

test('no orphan clicks remain after link deletion', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'cascade2',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    for ($i = 0; $i < 5; $i++) {
        Click::create(['link_id' => $link->id, 'clicked_at' => now()]);
    }

    $linkId = $link->id;
    $link->delete();

    expect(Click::where('link_id', $linkId)->count())->toBe(0);
});
