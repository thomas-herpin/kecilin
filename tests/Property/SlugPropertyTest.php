<?php

// # Feature: kecilin-url-shortener, Property 1: slug format validity
// # Feature: kecilin-url-shortener, Property 2: alias validation
// # Feature: kecilin-url-shortener, Property 3: slug uniqueness

use App\Services\SlugGenerator;

beforeEach(function () {
    $this->generator = new SlugGenerator();
});

/**
 * Property 1: Slug yang dihasilkan selalu valid secara format
 * Validates: Requirements 1.1
 *
 * For every call to generate(), the result must be exactly 6 alphanumeric characters [a-zA-Z0-9].
 */
test('Property 1: slug format validity — generate() always produces a valid 6-char alphanumeric slug', function () {
    // # Feature: kecilin-url-shortener, Property 1: slug format validity
    // Validates: Requirements 1.1

    for ($i = 0; $i < 100; $i++) {
        $slug = $this->generator->generate();

        expect(strlen($slug))->toBe(6, "Iteration {$i}: slug length must be exactly 6, got: {$slug}");
        expect($slug)->toMatch('/^[a-zA-Z0-9]{6}$/', "Iteration {$i}: slug must only contain [a-zA-Z0-9], got: {$slug}");
    }
})->group('property');

/**
 * Property 2: Validasi alias kustom mencakup karakter dan panjang
 * Validates: Requirements 2.2, 2.4
 *
 * For every string containing only [a-zA-Z0-9_-] with length 3–50, validateAlias() must return true.
 * For every string with spaces, illegal characters, or length outside 3–50, must return false.
 */
test('Property 2: alias validation — valid aliases are accepted', function () {
    // # Feature: kecilin-url-shortener, Property 2: alias validation
    // Validates: Requirements 2.2, 2.4

    $validChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_-';
    $charLen = strlen($validChars);

    for ($i = 0; $i < 100; $i++) {
        // Generate a random valid alias: length between 3 and 50, only valid chars
        $length = random_int(3, 50);
        $alias = '';
        for ($j = 0; $j < $length; $j++) {
            $alias .= $validChars[random_int(0, $charLen - 1)];
        }

        expect($this->generator->validateAlias($alias))->toBeTrue(
            "Iteration {$i}: valid alias '{$alias}' (len={$length}) should be accepted"
        );
    }
})->group('property');

test('Property 2: alias validation — aliases that are too short are rejected', function () {
    // # Feature: kecilin-url-shortener, Property 2: alias validation
    // Validates: Requirements 2.4

    $validChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charLen = strlen($validChars);

    for ($i = 0; $i < 100; $i++) {
        // Generate alias shorter than 3 chars (0, 1, or 2 chars)
        $length = random_int(0, 2);
        $alias = '';
        for ($j = 0; $j < $length; $j++) {
            $alias .= $validChars[random_int(0, $charLen - 1)];
        }

        expect($this->generator->validateAlias($alias))->toBeFalse(
            "Iteration {$i}: alias '{$alias}' (len={$length}) is too short and should be rejected"
        );
    }
})->group('property');

test('Property 2: alias validation — aliases that are too long are rejected', function () {
    // # Feature: kecilin-url-shortener, Property 2: alias validation
    // Validates: Requirements 2.4

    $validChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $charLen = strlen($validChars);

    for ($i = 0; $i < 100; $i++) {
        // Generate alias longer than 50 chars (51 to 100)
        $length = random_int(51, 100);
        $alias = '';
        for ($j = 0; $j < $length; $j++) {
            $alias .= $validChars[random_int(0, $charLen - 1)];
        }

        expect($this->generator->validateAlias($alias))->toBeFalse(
            "Iteration {$i}: alias '{$alias}' (len={$length}) is too long and should be rejected"
        );
    }
})->group('property');

test('Property 2: alias validation — aliases with illegal characters are rejected', function () {
    // # Feature: kecilin-url-shortener, Property 2: alias validation
    // Validates: Requirements 2.2

    // Characters that are NOT allowed in aliases
    $illegalChars = [' ', '@', '#', '$', '%', '!', '.', '/', '\\', '?', '=', '+', '*', '&', '^', '(', ')', '[', ']', '{', '}', '|', '~', '`', '"', "'", '<', '>', ',', ';', ':'];

    for ($i = 0; $i < 100; $i++) {
        // Pick a random illegal character and embed it in an otherwise valid alias
        $illegalChar = $illegalChars[array_rand($illegalChars)];
        $prefix = 'valid';
        $alias = $prefix . $illegalChar . 'alias';

        expect($this->generator->validateAlias($alias))->toBeFalse(
            "Iteration {$i}: alias '{$alias}' contains illegal char '{$illegalChar}' and should be rejected"
        );
    }
})->group('property');

/**
 * Property 3: Slug yang dihasilkan selalu unik dalam konteks database
 * Validates: Requirements 10.1, 10.2
 *
 * For any set of existing slugs, generateUnique() must produce a slug not in that set,
 * as long as the number of attempts does not exceed the maximum (10).
 */
test('Property 3: slug uniqueness — generateUnique() always returns a slug not in the existing set', function () {
    // # Feature: kecilin-url-shortener, Property 3: slug uniqueness
    // Validates: Requirements 10.1, 10.2

    for ($i = 0; $i < 100; $i++) {
        // Build a set of 0–5 random existing slugs
        $existingCount = random_int(0, 5);
        $existing = [];
        for ($j = 0; $j < $existingCount; $j++) {
            $existing[] = $this->generator->generate();
        }
        $existingSet = array_unique($existing);

        $slug = $this->generator->generateUnique(fn($s) => in_array($s, $existingSet));

        expect(in_array($slug, $existingSet))->toBeFalse(
            "Iteration {$i}: generated slug '{$slug}' must not be in the existing set"
        );
        expect($slug)->toMatch('/^[a-zA-Z0-9]{6}$/',
            "Iteration {$i}: generated unique slug '{$slug}' must be valid format"
        );
    }
})->group('property');

test('Property 3: slug uniqueness — generateUnique() throws RuntimeException after maxAttempts', function () {
    // # Feature: kecilin-url-shortener, Property 3: slug uniqueness
    // Validates: Requirements 10.2, 10.3

    for ($i = 0; $i < 100; $i++) {
        $maxAttempts = random_int(1, 10);

        expect(fn() => $this->generator->generateUnique(
            fn($s) => true, // every slug "already exists"
            maxAttempts: $maxAttempts
        ))->toThrow(RuntimeException::class);
    }
})->group('property');
