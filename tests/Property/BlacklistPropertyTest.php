<?php

// # Feature: kecilin-url-shortener, Property 5: blacklist case-insensitive

use App\Services\BlacklistFilter;

beforeEach(function () {
    $this->filter = new BlacklistFilter();
});

/**
 * Property 5: Domain daftar hitam diblokir secara case-insensitive
 * Validates: Requirements 6.1, 6.2, 6.4
 *
 * For every URL whose domain is in the blacklist with any case variation,
 * isDomainBlocked() must return true.
 * For domains not in the blacklist, it must return false.
 */
test('Property 5: blacklist case-insensitive — blocked domains are rejected regardless of case', function () {
    // # Feature: kecilin-url-shortener, Property 5: blacklist case-insensitive
    // Validates: Requirements 6.1, 6.2, 6.4

    $blockedDomains = $this->filter->getBlockedDomains();

    for ($i = 0; $i < 100; $i++) {
        // Pick a random domain from the blacklist
        $domain = $blockedDomains[array_rand($blockedDomains)];

        // Generate a random case variation of the domain
        $variedDomain = '';
        for ($j = 0; $j < strlen($domain); $j++) {
            $char = $domain[$j];
            // Randomly uppercase or lowercase each letter
            $variedDomain .= random_int(0, 1) ? strtoupper($char) : strtolower($char);
        }

        $url = 'http://' . $variedDomain . '/some/path';

        expect($this->filter->isDomainBlocked($url))->toBeTrue(
            "Iteration {$i}: domain '{$variedDomain}' (variant of '{$domain}') should be blocked"
        );
    }
})->group('property');

test('Property 5: blacklist case-insensitive — non-blacklisted domains are allowed', function () {
    // # Feature: kecilin-url-shortener, Property 5: blacklist case-insensitive
    // Validates: Requirements 6.1, 6.2

    $safeDomains = [
        'example.com', 'google.com', 'github.com', 'laravel.com',
        'stackoverflow.com', 'wikipedia.org', 'openai.com', 'php.net',
        'safe-site.net', 'trusted.org',
    ];

    for ($i = 0; $i < 100; $i++) {
        $domain = $safeDomains[array_rand($safeDomains)];

        // Generate a random case variation
        $variedDomain = '';
        for ($j = 0; $j < strlen($domain); $j++) {
            $char = $domain[$j];
            $variedDomain .= random_int(0, 1) ? strtoupper($char) : strtolower($char);
        }

        $url = 'https://' . $variedDomain . '/page';

        expect($this->filter->isDomainBlocked($url))->toBeFalse(
            "Iteration {$i}: domain '{$variedDomain}' should not be blocked"
        );
    }
})->group('property');
