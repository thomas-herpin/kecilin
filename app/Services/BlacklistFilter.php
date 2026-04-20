<?php

namespace App\Services;

class BlacklistFilter
{
    /**
     * Check if the domain of the given URL is in the blacklist (including sub-domain).
     */
    public function isDomainBlocked(string $url): bool
{
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) return false;

    $host = strtolower($host);
    $blocked = array_map('strtolower', $this->getBlockedDomains());

    foreach ($blocked as $domain) {
        if ($host === $domain || str_ends_with($host, '.' . $domain)) {
            return true;
        }
    }

    return false;
}

    /**
     * Return the list of blocked domains from configuration.
     */
    public function getBlockedDomains(): array
    {
        return config('blacklist.domains', []);
    }
}
