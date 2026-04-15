<?php

// # Feature: kecilin-url-shortener, Property 7: qr code svg validity

use App\Services\QrCodeGenerator;

beforeEach(function () {
    $this->generator = new QrCodeGenerator();
});

/**
 * Property 7: QR Code yang dihasilkan adalah SVG yang valid dan menyematkan tautan pendek
 * Validates: Requirements 5.1, 5.4
 *
 * For every valid URL, generateSvg() must produce a string that:
 * - is a non-empty string
 * - contains the <svg opening tag (valid SVG)
 * - contains the </svg> closing tag
 * - encodes the short link as QR data (verified by producing different SVGs for different URLs)
 *
 * Note: QR codes encode data as visual path patterns, not as literal text in the SVG output.
 * The URL is the input to the QR encoder; the SVG contains the encoded visual representation.
 */
test('Property 7: qr code svg validity — output is valid SVG for random short links', function () {
    // # Feature: kecilin-url-shortener, Property 7: qr code svg validity
    // Validates: Requirements 5.1, 5.4

    $slugChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charLen = strlen($slugChars);
    $schemes = ['http', 'https'];
    $domains = ['short.ly', 'kecilin.id', 's.example.com', 'go.link', 'tiny.url'];

    for ($i = 0; $i < 100; $i++) {
        // Generate a random valid short URL
        $scheme = $schemes[array_rand($schemes)];
        $domain = $domains[array_rand($domains)];

        // Random 6-char slug
        $slug = '';
        for ($j = 0; $j < 6; $j++) {
            $slug .= $slugChars[random_int(0, $charLen - 1)];
        }

        $shortUrl = "{$scheme}://{$domain}/{$slug}";

        $svg = $this->generator->generateSvg($shortUrl);

        // Must be a non-empty string
        expect($svg)->toBeString();
        expect(strlen($svg))->toBeGreaterThan(0);

        // Must be valid SVG: contains opening <svg tag
        expect(str_contains($svg, '<svg'))->toBeTrue(
            "Iteration {$i}: output for '{$shortUrl}' must contain <svg tag"
        );

        // Must be valid SVG: contains closing </svg> tag
        expect(str_contains($svg, '</svg>'))->toBeTrue(
            "Iteration {$i}: output for '{$shortUrl}' must contain </svg> closing tag"
        );
    }
})->group('property');

test('Property 7: qr code svg validity — different short links produce different SVGs', function () {
    // # Feature: kecilin-url-shortener, Property 7: qr code svg validity
    // Validates: Requirements 5.4
    // Verifies that the short link is actually used as the QR data (different inputs → different outputs)

    $slugChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charLen = strlen($slugChars);

    $differentOutputs = 0;

    for ($i = 0; $i < 100; $i++) {
        $slug1 = '';
        $slug2 = '';
        for ($j = 0; $j < 6; $j++) {
            $slug1 .= $slugChars[random_int(0, $charLen - 1)];
            $slug2 .= $slugChars[random_int(0, $charLen - 1)];
        }

        // Ensure slugs are different
        if ($slug1 === $slug2) {
            continue;
        }

        $url1 = "https://kecilin.id/{$slug1}";
        $url2 = "https://kecilin.id/{$slug2}";

        $svg1 = $this->generator->generateSvg($url1);
        $svg2 = $this->generator->generateSvg($url2);

        if ($svg1 !== $svg2) {
            $differentOutputs++;
        }
    }

    // Different URLs must produce different SVGs (QR data encodes the input URL)
    expect($differentOutputs)->toBeGreaterThan(0);
})->group('property');
