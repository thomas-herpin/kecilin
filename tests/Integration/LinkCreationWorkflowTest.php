<?php

// 11.4 Integration test: Workflow Pembuatan Lengkap
// POST /shorten → verifikasi response mengandung tautan pendek dan SVG QR Code
// Verifikasi data tersimpan di database
// Requirements: 1.2, 1.3, 5.1

use App\Models\Link;

test('POST /shorten stores link in database and returns short URL with QR Code', function () {
    $response = $this->post('/shorten', [
        'url' => 'https://example.com/long/path',
    ]);

    $response->assertStatus(200);

    // Verify persisted in DB
    $link = Link::latest()->first();
    expect($link)->not->toBeNull();
    expect($link->original_url)->toBe('https://example.com/long/path');
    expect($link->slug)->toMatch('/^[a-zA-Z0-9]{6}$/');
    expect($link->qr_code_svg)->toContain('<svg');

    // Verify view contains the short URL and QR Code SVG
    $response->assertViewHas('shortUrl');
    $response->assertViewHas('qrCode');

    $shortUrl = $response->viewData('shortUrl');
    expect($shortUrl)->toContain('/' . $link->slug);

    $qrCode = $response->viewData('qrCode');
    expect($qrCode)->toContain('<svg');
});

test('POST /shorten with custom alias stores link with that alias', function () {
    $response = $this->post('/shorten', [
        'url'   => 'https://example.com',
        'alias' => 'my-brand',
    ]);

    $response->assertStatus(200);

    $link = Link::where('slug', 'my-brand')->first();
    expect($link)->not->toBeNull();
    expect($link->original_url)->toBe('https://example.com');
});

test('POST /shorten with invalid URL returns validation error', function () {
    $response = $this->post('/shorten', [
        'url' => 'not-a-valid-url',
    ]);

    $response->assertSessionHasErrors(['url']);
});

test('POST /shorten with blacklisted domain returns validation error', function () {
    $response = $this->post('/shorten', [
        'url' => 'http://malware.com/page',
    ]);

    $response->assertSessionHasErrors(['url']);
});
