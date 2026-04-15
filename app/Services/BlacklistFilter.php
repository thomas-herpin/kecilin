<?php

namespace App\Services;

class BlacklistFilter
{
    /**
     * Check if the domain of the given URL is in the blacklist.
     * Matching is case-insensitive on both sides.
     */
    public function isDomainBlocked(string $url): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null || $host === false) {
            return false;
        }

        $host = strtolower($host);
        $blocked = array_map('strtolower', $this->getBlockedDomains());

        return in_array($host, $blocked, true);
    }

    /**
     * Return the list of blocked domains from configuration.
     */
    public function getBlockedDomains(): array
    {
        return config('blacklist.domains', []);
    }
}
