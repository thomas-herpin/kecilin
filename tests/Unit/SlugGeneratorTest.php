<?php

use App\Services\SlugGenerator;

beforeEach(function () {
    $this->generator = new SlugGenerator();
});

// --- generate() ---

test('generate returns a string of exactly 6 characters', function () {
    $slug = $this->generator->generate();
    expect(strlen($slug))->toBe(6);
});

test('generate returns only alphanumeric characters', function () {
    $slug = $this->generator->generate();
    expect($slug)->toMatch('/^[a-zA-Z0-9]{6}$/');
});

test('generate produces different slugs on repeated calls', function () {
    $slugs = array_map(fn() => $this->generator->generate(), range(1, 20));
    // With 62^6 combinations, duplicates in 20 calls are astronomically unlikely
    expect(count(array_unique($slugs)))->toBeGreaterThan(1);
});

// --- validateAlias() ---

test('validateAlias accepts valid alias with only letters', function () {
    expect($this->generator->validateAlias('MyBrand'))->toBeTrue();
});

test('validateAlias accepts valid alias with letters and numbers', function () {
    expect($this->generator->validateAlias('Brand123'))->toBeTrue();
});

test('validateAlias accepts valid alias with hyphens and underscores', function () {
    expect($this->generator->validateAlias('my-brand_link'))->toBeTrue();
});

test('validateAlias accepts alias at minimum length of 3', function () {
    expect($this->generator->validateAlias('abc'))->toBeTrue();
});

test('validateAlias accepts alias at maximum length of 50', function () {
    $alias = str_repeat('a', 50);
    expect($this->generator->validateAlias($alias))->toBeTrue();
});

test('validateAlias rejects alias shorter than 3 characters', function () {
    expect($this->generator->validateAlias('ab'))->toBeFalse();
    expect($this->generator->validateAlias('a'))->toBeFalse();
    expect($this->generator->validateAlias(''))->toBeFalse();
});

test('validateAlias rejects alias longer than 50 characters', function () {
    $alias = str_repeat('a', 51);
    expect($this->generator->validateAlias($alias))->toBeFalse();
});

test('validateAlias rejects alias with spaces', function () {
    expect($this->generator->validateAlias('my brand'))->toBeFalse();
    expect($this->generator->validateAlias('my brand link'))->toBeFalse();
});

test('validateAlias rejects alias with special characters', function () {
    expect($this->generator->validateAlias('my@brand'))->toBeFalse();
    expect($this->generator->validateAlias('my.brand'))->toBeFalse();
    expect($this->generator->validateAlias('my/brand'))->toBeFalse();
    expect($this->generator->validateAlias('my#brand'))->toBeFalse();
});

// --- generateUnique() ---

test('generateUnique returns a slug not in the existing set', function () {
    $existing = ['abc123', 'xyz789'];
    $slug = $this->generator->generateUnique(fn($s) => in_array($s, $existing));
    expect($slug)->not->toBeIn($existing);
    expect($slug)->toMatch('/^[a-zA-Z0-9]{6}$/');
});

test('generateUnique retries when slug already exists', function () {
    $attempts = 0;
    // First 3 calls return "exists", 4th is unique
    $slug = $this->generator->generateUnique(function ($s) use (&$attempts) {
        $attempts++;
        return $attempts < 4; // first 3 attempts "exist"
    });

    expect($attempts)->toBeGreaterThanOrEqual(4);
    expect($slug)->toMatch('/^[a-zA-Z0-9]{6}$/');
});

test('generateUnique throws RuntimeException when max attempts exceeded', function () {
    expect(fn() => $this->generator->generateUnique(
        fn($s) => true, // always exists
        maxAttempts: 10
    ))->toThrow(RuntimeException::class);
});

test('generateUnique respects custom maxAttempts', function () {
    $attempts = 0;
    $existsCheck = function ($s) use (&$attempts) {
        $attempts++;
        return true; // always exists
    };

    try {
        $this->generator->generateUnique($existsCheck, maxAttempts: 3);
    } catch (RuntimeException $e) {
        // expected
    }

    expect($attempts)->toBe(3);
});

test('generateUnique succeeds on first attempt when no collision', function () {
    $attempts = 0;
    $slug = $this->generator->generateUnique(function ($s) use (&$attempts) {
        $attempts++;
        return false; // never exists
    });

    expect($attempts)->toBe(1);
    expect($slug)->toMatch('/^[a-zA-Z0-9]{6}$/');
});
