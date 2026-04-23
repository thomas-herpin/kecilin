<?php

use App\Models\Link;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('Click model belongs to a link', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug' => 'abc123',
        'qr_code_svg' => '<svg></svg>'
    ]);

    $click = Click::create(['link_id' => $link->id, 'clicked_at' => now()]);

    expect($click->link)->toBeInstanceOf(Link::class);
    expect($click->link->id)->toBe($link->id);
});