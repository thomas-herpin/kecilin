<?php

use App\Models\Link;
use App\Models\Click;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

test('Link model has full_short_url accessor', function () {
    $link = new Link(['slug' => 'abc123']);
    $expected = config('app.url') . '/abc123';
    expect($link->full_short_url)->toBe($expected);
});

test('Link model has clicks relationship', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug' => 'abc123',
        'qr_code_svg' => '<svg>dummy</svg>'
    ]);

    Click::create(['link_id' => $link->id, 'clicked_at' => now()]);

    expect($link->clicks)->toHaveCount(1);
    expect($link->clicks->first())->toBeInstanceOf(Click::class);
});

test('Link model uses latest scope', function () {
    $oldTime = now()->subHour();
    Carbon::setTestNow($oldTime);
    Link::create([
        'original_url' => 'https://a.com', 
        'slug' => 'old', 
        'qr_code_svg' => '<svg>dummy</svg>',
        'created_at' => $oldTime
    ]);

    $newTime = now()->addHour();
    Carbon::setTestNow($newTime);
    Link::create([
        'original_url' => 'https://b.com', 
        'slug' => 'new', 
        'qr_code_svg' => '<svg>dummy</svg>',
        'created_at' => $newTime
    ]);

    $result = Link::latest()->first();
    
    Carbon::setTestNow();

    expect($result->slug)->toBe('new');
});