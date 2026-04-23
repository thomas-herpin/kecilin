<?php

use App\Models\Link;
use App\Services\BlacklistFilter;
use App\Services\QrCodeGenerator;
use App\Services\SlugGenerator;
use App\Services\UrlShortenerService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new UrlShortenerService(
        new BlacklistFilter(),
        new SlugGenerator(),
        new QrCodeGenerator(),
    );
});

// --- shorten(): URL scheme validation ---

test('shorten throws InvalidArgumentException for URL without scheme', function () {
    expect(fn() => $this->service->shorten('example.com/page'))
        ->toThrow(InvalidArgumentException::class);
});

test('shorten throws InvalidArgumentException for empty string', function () {
    expect(fn() => $this->service->shorten(''))
        ->toThrow(InvalidArgumentException::class);
});

test('shorten throws InvalidArgumentException for ftp:// scheme', function () {
    expect(fn() => $this->service->shorten('ftp://example.com'))
        ->toThrow(InvalidArgumentException::class);
});

test('shorten accepts http:// URL', function () {
    $link = $this->service->shorten('http://example.com');
    expect($link)->toBeInstanceOf(Link::class);
    expect($link->original_url)->toBe('http://example.com');
});

test('shorten accepts https:// URL', function () {
    $link = $this->service->shorten('https://example.com');
    expect($link)->toBeInstanceOf(Link::class);
    expect($link->original_url)->toBe('https://example.com');
});

// --- shorten(): URL normalization ---

test('shorten removes trailing slash from URL', function () {
    $link = $this->service->shorten('https://example.com/page/');
    expect($link->original_url)->toBe('https://example.com/page');
});

test('shorten removes multiple trailing slashes', function () {
    $link = $this->service->shorten('https://example.com///');
    expect($link->original_url)->toBe('https://example.com');
});

test('shorten does not alter URL without trailing slash', function () {
    $link = $this->service->shorten('https://example.com/page');
    expect($link->original_url)->toBe('https://example.com/page');
});

test('shorten normalizes URL with multiple slashes and uppercase scheme', function () {
    $link = $this->service->shorten('HTTPS://EXAMPLE.COM/path///');
    expect($link->original_url)->toBe('https://example.com/path');
});

// --- shorten(): blacklist ---

test('shorten throws InvalidArgumentException for blacklisted domain', function () {
    expect(fn() => $this->service->shorten('http://malware.com/page'))
        ->toThrow(InvalidArgumentException::class);
});

test('update throws InvalidArgumentException for blacklisted new URL', function () {
    $link = $this->service->shorten('https://safe-url.com');
    
    expect(fn() => $this->service->update($link, 'http://malware.com'))
        ->toThrow(InvalidArgumentException::class);
});

// --- shorten(): alias ---

test('shorten uses provided alias as slug', function () {
    $link = $this->service->shorten('https://example.com', 'MyAlias');
    expect($link->slug)->toBe('MyAlias');
});

test('shorten throws InvalidArgumentException for invalid alias format', function () {
    expect(fn() => $this->service->shorten('https://example.com', 'bad alias!'))
        ->toThrow(InvalidArgumentException::class);
});

test('shorten throws InvalidArgumentException for alias too short', function () {
    expect(fn() => $this->service->shorten('https://example.com', 'ab'))
        ->toThrow(InvalidArgumentException::class);
});

test('shorten throws InvalidArgumentException for duplicate alias', function () {
    $this->service->shorten('https://example.com', 'taken');

    expect(fn() => $this->service->shorten('https://other.com', 'taken'))
        ->toThrow(InvalidArgumentException::class);
});

test('shorten throws InvalidArgumentException for alias too long (51 chars)', function () {
    $longAlias = str_repeat('a', 51);
    expect(fn() => $this->service->shorten('https://example.com', $longAlias))
        ->toThrow(InvalidArgumentException::class);
});

test('shorten throws RuntimeException when auto-generated slug always collides', function () {
    $mockSlugGen = Mockery::mock(App\Services\SlugGenerator::class);
    $mockSlugGen->shouldReceive('generateUnique')->andThrow(new RuntimeException("Max attempts reached"));

    $serviceWithMock = new App\Services\UrlShortenerService(
        new App\Services\BlacklistFilter(),
        $mockSlugGen,
        new App\Services\QrCodeGenerator()
    );

    expect(fn() => $serviceWithMock->shorten('https://example.com'))
        ->toThrow(RuntimeException::class);
});

// --- shorten(): auto slug generation ---

test('shorten generates a 6-char alphanumeric slug when no alias given', function () {
    $link = $this->service->shorten('https://example.com');
    expect($link->slug)->toMatch('/^[a-zA-Z0-9]{6}$/');
});

// --- shorten(): QR Code and persistence ---

test('shorten saves link to database', function () {
    $link = $this->service->shorten('https://example.com');
    expect(Link::find($link->id))->not->toBeNull();
});

test('shorten generates QR Code SVG containing the short link', function () {
    $link = $this->service->shorten('https://example.com');
    expect($link->qr_code_svg)->toContain('<svg');
});

// --- update() ---

test('update changes the original_url of a link', function () {
    $link = $this->service->shorten('https://example.com');
    $updated = $this->service->update($link, 'https://new-url.com');
    expect($updated->original_url)->toBe('https://new-url.com');
});

test('update throws InvalidArgumentException for URL without scheme', function () {
    $link = $this->service->shorten('https://example.com');
    expect(fn() => $this->service->update($link, 'no-scheme.com'))
        ->toThrow(InvalidArgumentException::class);
});

test('update normalizes trailing slash on new URL', function () {
    $link = $this->service->shorten('https://example.com');
    $updated = $this->service->update($link, 'https://new-url.com/path/');
    expect($updated->original_url)->toBe('https://new-url.com/path');
});

test('update persists the change to database', function () {
    $link = $this->service->shorten('https://example.com');
    $this->service->update($link, 'https://updated.com');
    expect(Link::find($link->id)->original_url)->toBe('https://updated.com');
});

// --- delete() ---

test('delete removes the link from database', function () {
    $link = $this->service->shorten('https://example.com');
    $id = $link->id;
    $this->service->delete($link);
    expect(Link::find($id))->toBeNull();
});