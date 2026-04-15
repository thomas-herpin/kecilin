<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Generator;

class QrCodeGenerator
{
    /**
     * Generate an SVG QR Code for the given URL.
     *
     * The input should be the full short link (not the original URL).
     */
    public function generateSvg(string $url): string
    {
        $qrCode = (new Generator())
            ->format('svg')
            ->generate($url);

        // HtmlString wraps the SVG; cast to string to get raw SVG content
        return (string) $qrCode;
    }
}
