<?php

use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can view history page with list of links', function () {
    Link::create([
        'original_url' => 'https://google.com',
        'slug' => 'googl',
        'qr_code_svg' => '<svg>dummy</svg>'
    ]);

    $response = $this->get('/history');

    $response->assertStatus(200);
    $response->assertViewHas('links');
    $response->assertSee('googl');
});