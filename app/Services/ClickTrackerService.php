<?php

namespace App\Services;

use App\Models\Click;
use App\Models\Link;
use Illuminate\Http\Request;

class ClickTrackerService
{
    /**
     * Known bot/crawler user-agent substrings (case-insensitive).
     */
    private const BOT_PATTERNS = [
        'Googlebot',
        'bingbot',
        'curl',
        'wget',
        'Slurp',
        'DuckDuckBot',
        'Baiduspider',
        'YandexBot',
        'Sogou',
        'Exabot',
        'facebot',
        'ia_archiver',
        'Twitterbot',
        'LinkedInBot',
        'Applebot',
        'Pinterestbot',
        'Discordbot',
        'TelegramBot',
        'WhatsApp',
        'Slackbot',
        'python-requests',
        'Go-http-client',
        'Java/',
        'libwww-perl',
        'msnbot',
        'AhrefsBot',
        'SemrushBot',
        'MJ12bot',
        'DotBot',
        'rogerbot',
        'archive.org_bot',
        'Screaming Frog',
        'HeadlessChrome',
        'PhantomJS',
    ];

    /**
     * Determine whether the given user-agent string belongs to a bot or crawler.
     */
    public function isBot(string $userAgent): bool
    {
        if ($userAgent === '') {
            return false;
        }

        $lowerUa = strtolower($userAgent);

        foreach (self::BOT_PATTERNS as $pattern) {
            if (str_contains($lowerUa, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record a click for the given link, unless the request comes from a bot.
     */
    public function record(Link $link, Request $request): void
    {
        $userAgent = $request->userAgent() ?? '';

        if ($this->isBot($userAgent)) {
            return;
        }

        Click::create([
            'link_id'    => $link->id,
            'clicked_at' => now(),
        ]);

        $link->increment('total_clicks');
    }
}
