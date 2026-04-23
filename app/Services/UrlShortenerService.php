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
     */
    public function shorten(string $url, ?string $alias = null): Link
    {
        // 1. Normalize and validate
        $url = strtolower(rtrim($url, '/'));

        if (!preg_match('#^https?://#', $url)) {
            throw new InvalidArgumentException(
                'URL harus dimulai dengan http:// atau https://'
            );
        }

        // 2. Check blacklist
        if ($this->blacklistFilter->isDomainBlocked($url)) {
            throw new InvalidArgumentException(
                'Domain URL tersebut termasuk dalam daftar hitam dan tidak dapat dipendekkan.'
            );
        }

        // 3. Resolve slug
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

        // 4. Generate QR Code
        $shortLink = config('app.url') . '/' . $slug;
        $qrCodeSvg = $this->qrCodeGenerator->generateSvg($shortLink);

        return Link::create([
            'original_url' => $url,
            'slug'         => $slug,
            'qr_code_svg'  => $qrCodeSvg,
            'total_clicks' => 0,
        ]);
    }

    /**
     * Update the destination URL of an existing link.
     */
    public function update(Link $link, string $newUrl): Link
    {
        $newUrl = strtolower(rtrim($newUrl, '/'));

        if (!preg_match('#^https?://#', $newUrl)) {
            throw new InvalidArgumentException(
                'URL harus dimulai dengan http:// atau https://'
            );
        }

        if ($this->blacklistFilter->isDomainBlocked($newUrl)) {
            throw new InvalidArgumentException(
                'Domain URL tersebut termasuk dalam daftar hitam.'
            );
        }

        $link->original_url = $newUrl;
        $link->save();

        return $link;
    }

    /**
     * Delete a link.
     */
    public function delete(Link $link): void
    {
        $link->delete();
    }
}