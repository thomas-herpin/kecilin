<?php

use App\Models\Link;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view analytics page with chart data', function () {
    $link = Link::create([
        'original_url' => 'https://google.com',
        'slug' => 'googl',
        'qr_code_svg' => '<svg>dummy</svg>'
    ]);

    Click::create(['link_id' => $link->id, 'clicked_at' => now()]);

    $response = $this->get('/analytics');

    $response->assertStatus(200);
    $response->assertViewHas('chartData');
});