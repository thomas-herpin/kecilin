<?php

namespace App\Services;

use RuntimeException;

class SlugGenerator
{
    private const CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    private const SLUG_LENGTH = 6;
    private const ALIAS_PATTERN = '/^[a-zA-Z0-9_-]{3,50}$/';

    /**
     * Generate a random 6-character alphanumeric slug.
     */
    public function generate(): string
    {
        $chars = self::CHARACTERS;
        $length = strlen($chars);
        $slug = '';

        for ($i = 0; $i < self::SLUG_LENGTH; $i++) {
            $slug .= $chars[random_int(0, $length - 1)];
        }

        return $slug;
    }

    /**
     * Validate a custom alias against the allowed pattern.
     * Must match ^[a-zA-Z0-9_-]{3,50}$
     */
    public function validateAlias(string $alias): bool
    {
        return (bool) preg_match(self::ALIAS_PATTERN, $alias);
    }

    /**
     * Generate a unique slug by checking against existing slugs.
     *
     * @param callable $existsCheck Callable that returns true if the slug already exists
     * @param int $maxAttempts Maximum number of generation attempts
     * @throws RuntimeException if a unique slug cannot be found within maxAttempts
     */
    public function generateUnique(callable $existsCheck, int $maxAttempts = 10): string
    {
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $slug = $this->generate();

            if (!$existsCheck($slug)) {
                return $slug;
            }
        }

        throw new RuntimeException(
            "Unable to generate a unique slug after {$maxAttempts} attempts."
        );
    }
}
