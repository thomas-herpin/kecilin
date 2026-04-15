<?php

// # Feature: kecilin-url-shortener, Property 4: url scheme validation
// # Feature: kecilin-url-shortener, Property 8: url normalization

use App\Models\Link;
use App\Services\BlacklistFilter;
use App\Services\QrCodeGenerator;
use App\Services\SlugGenerator;
use App\Services\UrlShortenerService;

beforeEach(function () {
    $this->service = new UrlShortenerService(
        new BlacklistFilter(),
        new SlugGenerator(),
        new QrCodeGenerator(),
    );
});

/**
 * Property 4: URL tanpa skema http/https selalu ditolak
 * Validates: Requirements 1.4, 1.5
 *
 * For every string that does not start with http:// or https://, shorten() must throw
 * an InvalidArgumentException. For every URL that starts with those schemes, it must succeed.
 */
test('Property 4: url scheme validation — URLs without http/https scheme are always rejected', function () {
    // # Feature: kecilin-url-shortener, Property 4: url scheme validation
    // Validates: Requirements 1.4, 1.5

    $invalidPrefixes = [
        '',
        'ftp://',
        'ftps://',
        'ssh://',
        'file://',
        'mailto:',
        'javascript:',
        'data:',
        '//',
        'www.',
        'example.com',
        'just-a-string',
        '//example.com',
    ];

    for ($i = 0; $i < 100; $i++) {
        // Pick a random invalid prefix and build a URL-like string
        $prefix = $invalidPrefixes[array_rand($invalidPrefixes)];
        $suffix = 'example-' . $i . '.com/path';
        $url = $prefix . $suffix;

        expect(fn() => $this->service->shorten($url))
            ->toThrow(
                InvalidArgumentException::class,
                null,
                "Iteration {$i}: URL '{$url}' without http/https scheme should be rejected"
            );
    }
})->group('property');

test('Property 4: url scheme validation — URLs with http:// or https:// are accepted', function () {
    // # Feature: kecilin-url-shortener, Property 4: url scheme validation
    // Validates: Requirements 1.4, 1.5

    $validSchemes = ['http://', 'https://'];

    for ($i = 0; $i < 100; $i++) {
        $scheme = $validSchemes[array_rand($validSchemes)];
        $url = $scheme . 'example-valid-' . $i . '.com';

        $link = $this->service->shorten($url);

        expect($link)->toBeInstanceOf(Link::class,
            "Iteration {$i}: URL '{$url}' with valid scheme should be accepted"
        );
    }
})->group('property');

/**
 * Property 8: Normalisasi URL menghapus trailing slash
 * Validates: Requirements 1.6
 *
 * For every valid URL with a trailing slash, normalization must produce the equivalent URL
 * without the trailing slash. URLs without trailing slash must not change.
 */
test('Property 8: url normalization — trailing slashes are always removed', function () {
    // # Feature: kecilin-url-shortener, Property 8: url normalization
    // Validates: Requirements 1.6

    $paths = [
        '',
        '/path',
        '/path/to/page',
        '/a/b/c',
        '/page?q=1',
    ];

    for ($i = 0; $i < 100; $i++) {
        $path = $paths[array_rand($paths)];
        $trailingSlashes = str_repeat('/', random_int(1, 5));
        $url = 'https://example-norm-' . $i . '.com' . $path . $trailingSlashes;

        $link = $this->service->shorten($url);

        expect(str_ends_with($link->original_url, '/'))
            ->toBeFalse(
                "Iteration {$i}: stored URL '{$link->original_url}' must not end with a slash (input: '{$url}')"
            );
    }
})->group('property');

test('Property 8: url normalization — URLs without trailing slash are stored unchanged', function () {
    // # Feature: kecilin-url-shortener, Property 8: url normalization
    // Validates: Requirements 1.6

    $paths = [
        '',
        '/path',
        '/path/to/page',
        '/page?q=1',
    ];

    for ($i = 0; $i < 100; $i++) {
        $path = $paths[array_rand($paths)];
        $url = 'https://example-notrail-' . $i . '.com' . $path;

        $link = $this->service->shorten($url);

        expect($link->original_url)->toBe(
            $url,
            "Iteration {$i}: URL '{$url}' without trailing slash should be stored as-is"
        );
    }
})->group('property');
