<?php

// 11.1 Integration test: End-to-End Redirection
// POST /shorten → GET /{slug} → verifikasi redirect 302 ke URL asli
// Requirements: 3.1

use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
uses(RefreshDatabase::class);

test('end-to-end: POST /shorten then GET /{slug} redirects 302 to original URL', function () {
    // Step 1: shorten a URL
    $response = $this->post('/shorten', [
        'url' => 'https://example.com/some/long/path',
    ]);

    // Should render the home view with the link data (not a redirect)
    $response->assertStatus(200);

    // Retrieve the created link from DB
    $link = Link::latest()->first();
    expect($link)->not->toBeNull();
    expect($link->original_url)->toBe('https://example.com/some/long/path');

    // Step 2: access the short link
    $redirect = $this->get('/' . $link->slug);

    $redirect->assertStatus(302);
    $redirect->assertRedirect('https://example.com/some/long/path');
});

test('redirect increments click count on valid access', function () {
    $link = Link::create([
        'original_url' => 'https://example.com',
        'slug'         => 'abc123',
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    $this->get('/abc123');

    expect($link->fresh()->total_clicks)->toBe(1);
});

test('user can update a link destination', function () {
    $link = Link::create([
        'original_url' => 'https://old.com',
        'slug' => 'oldie',
        'qr_code_svg' => '<svg>dummy</svg>'
    ]);

    $response = $this->put("/links/{$link->id}", [
        'url' => 'https://new-destination.com'
    ]);

    $response->assertRedirect('/history');
    expect($link->fresh()->original_url)->toBe('https://new-destination.com');
});

test('update fails and redirects back with error for invalid url', function () {
    $link = Link::create([
        'original_url' => 'https://old.com',
        'slug' => 'oldie',
        'qr_code_svg' => '<svg>dummy</svg>'
    ]);

    $response = $this->put("/links/{$link->id}", [
        'url' => 'bukan-url-valid' 
    ]);

    $response->assertStatus(302); // Redirect back
    $response->assertSessionHasErrors('url');
});