<?php

namespace App\Services;

use App\Models\Link;
use InvalidArgumentException;

class UrlShortenerService
{
    public function __construct(
        private BlacklistFilter $blacklistFilter,
        private SlugGenerator $slugGenerator,
        private QrCodeGenerator $qrCodeGenerator,
    ) {}

    /**
     * Shorten a URL, optionally using a custom alias as the slug.
     *
     * @throws InvalidArgumentException if the URL scheme is invalid, domain is blocked,
     *                                  or alias is invalid/already taken
     */
    public function shorten(string $url, ?string $alias = null): Link
    {
        // Validate URL scheme (must be http:// or https://)
        if (!preg_match('#^https?://#i', $url)) {
            throw new InvalidArgumentException(
                'URL harus dimulai dengan http:// atau https://'
            );
        }

        // Normalize: remove trailing slash
        $url = rtrim($url, '/');

        // Check blacklist
        if ($this->blacklistFilter->isDomainBlocked($url)) {
            throw new InvalidArgumentException(
                'Domain URL tersebut termasuk dalam daftar hitam dan tidak dapat dipendekkan.'
            );
        }

        // Resolve slug
        if ($alias !== null) {
            if (!$this->slugGenerator->validateAlias($alias)) {
                throw new InvalidArgumentException(
                    'Alias tidak valid. Gunakan 3–50 karakter huruf, angka, tanda hubung, atau garis bawah.'
                );
            }

            if (Link::where('slug', $alias)->exists()) {
                throw new InvalidArgumentException(
                    "Alias '{$alias}' sudah digunakan. Silakan pilih alias lain."
                );
            }

            $slug = $alias;
        } else {
            $slug = $this->slugGenerator->generateUnique(
                fn(string $s) => Link::where('slug', $s)->exists()
            );
        }

        // Generate QR Code for the full short link
        $shortLink = config('app.url') . '/' . $slug;
        $qrCodeSvg = $this->qrCodeGenerator->generateSvg($shortLink);

        // Persist and return
        return Link::create([
            'original_url' => $url,
            'slug'         => $slug,
            'qr_code_svg'  => $qrCodeSvg,
            'total_clicks' => 0,
        ]);
    }

    /**
     * Update the destination URL of an existing link.
     *
     * @throws InvalidArgumentException if the new URL scheme is invalid
     */
    public function update(Link $link, string $newUrl): Link
    {
        if (!preg_match('#^https?://#i', $newUrl)) {
            throw new InvalidArgumentException(
                'URL harus dimulai dengan http:// atau https://'
            );
        }

        $newUrl = rtrim($newUrl, '/');

        $link->original_url = $newUrl;
        $link->save();

        return $link;
    }

    /**
     * Delete a link (CASCADE will remove related clicks).
     */
    public function delete(Link $link): void
    {
        $link->delete();
    }
}
