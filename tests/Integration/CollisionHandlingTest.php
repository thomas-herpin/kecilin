<?php

// 11.3 Integration test: Collision Handling
// Verifikasi sistem mencoba lagi dan berhasil jika slug sudah ada
// Requirements: 10.1

use App\Models\Link;
use App\Services\BlacklistFilter;
use App\Services\QrCodeGenerator;
use App\Services\SlugGenerator;
use App\Services\UrlShortenerService;

test('slug generator retries and succeeds when first slug already exists', function () {
    // Pre-create a link with a known slug
    $existingSlug = 'aaaaaa';
    Link::create([
        'original_url' => 'https://existing.com',
        'slug'         => $existingSlug,
        'qr_code_svg'  => '<svg></svg>',
        'total_clicks' => 0,
    ]);

    // Use a custom SlugGenerator that returns the existing slug on first call,
    // then a unique one on the second call
    $callCount = 0;
    $slugGenerator = new class($existingSlug) extends SlugGenerator {
        public function __construct(private string $firstSlug) {}

        public function generate(): string
        {
            static $count = 0;
            $count++;
            return $count === 1 ? $this->firstSlug : 'bbbbbb';
        }
    };

    $service = new UrlShortenerService(
        new BlacklistFilter(),
        $slugGenerator,
        new QrCodeGenerator(),
    );

    $link = $service->shorten('https://new-url.com');

    // Should have used the second generated slug
    expect($link->slug)->toBe('bbbbbb');
    expect(Link::where('slug', 'bbbbbb')->exists())->toBeTrue();
});

test('generateUnique throws RuntimeException after max attempts exceeded', function () {
    $slugGenerator = new SlugGenerator();

    // Fill the "database" with all possible slugs by making existsCheck always return true
    expect(fn() => $slugGenerator->generateUnique(fn(string $s) => true, 10))
        ->toThrow(RuntimeException::class);
});
