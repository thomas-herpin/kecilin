<?php

use App\Models\Click;
use App\Models\Link;
use App\Services\ClickTrackerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tracker = new ClickTrackerService();
});

// --- isBot() ---

test('isBot returns false for a normal browser user-agent', function () {
    $ua = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36';
    expect($this->tracker->isBot($ua))->toBeFalse();
});

test('isBot returns true for Googlebot', function () {
    expect($this->tracker->isBot('Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'))->toBeTrue();
});

test('isBot returns true for bingbot', function () {
    expect($this->tracker->isBot('Mozilla/5.0 (compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm)'))->toBeTrue();
});

test('isBot returns true for curl', function () {
    expect($this->tracker->isBot('curl/7.88.1'))->toBeTrue();
});

test('isBot returns true for wget', function () {
    expect($this->tracker->isBot('Wget/1.21.3'))->toBeTrue();
});

test('isBot returns true for YandexBot', function () {
    expect($this->tracker->isBot('Mozilla/5.0 (compatible; YandexBot/3.0; +http://yandex.com/bots)'))->toBeTrue();
});

test('isBot returns true for DuckDuckBot', function () {
    expect($this->tracker->isBot('DuckDuckBot/1.0; (+http://duckduckgo.com/duckduckbot.html)'))->toBeTrue();
});

test('isBot is case-insensitive for bot detection', function () {
    expect($this->tracker->isBot('GOOGLEBOT/2.1'))->toBeTrue();
    expect($this->tracker->isBot('CURL/7.88'))->toBeTrue();
});

test('isBot returns false for empty user-agent', function () {
    expect($this->tracker->isBot(''))->toBeFalse();
});

test('isBot returns false for Safari mobile user-agent', function () {
    $ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1';
    expect($this->tracker->isBot($ua))->toBeFalse();
});

// --- record() ---

test('record creates a Click record for a valid (non-bot) request', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'abc123',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    $request = Request::create('/abc123', 'GET');
    $request->headers->set('User-Agent', 'Mozilla/5.0 Chrome/120');

    $this->tracker->record($link, $request);

    expect(Click::where('link_id', $link->id)->count())->toBe(1);
    expect($link->fresh()->total_clicks)->toBe(1);
});

test('record increments total_clicks by exactly 1 for a valid click', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'def456',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 5,
    ]);

    $request = Request::create('/def456', 'GET');
    $request->headers->set('User-Agent', 'Mozilla/5.0 Firefox/120');

    $this->tracker->record($link, $request);

    expect($link->fresh()->total_clicks)->toBe(6);
});

test('record does NOT create a Click record for a bot request', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'ghi789',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    $request = Request::create('/ghi789', 'GET');
    $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; Googlebot/2.1)');

    $this->tracker->record($link, $request);

    expect(Click::where('link_id', $link->id)->count())->toBe(0);
    expect($link->fresh()->total_clicks)->toBe(0);
});

test('record does NOT increment total_clicks for a bot request', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'jkl012',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 3,
    ]);

    $request = Request::create('/jkl012', 'GET');
    $request->headers->set('User-Agent', 'curl/7.88.1');

    $this->tracker->record($link, $request);

    expect($link->fresh()->total_clicks)->toBe(3);
});

test('record stores clicked_at timestamp', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'mno345',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    $request = Request::create('/mno345', 'GET');
    $request->headers->set('User-Agent', 'Mozilla/5.0 Chrome/120');

    $before = now()->subSecond();
    $this->tracker->record($link, $request);
    $after = now()->addSecond();

    $click = Click::where('link_id', $link->id)->first();
    expect($click)->not->toBeNull();
    expect($click->clicked_at->between($before, $after))->toBeTrue();
});
